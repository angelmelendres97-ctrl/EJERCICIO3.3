<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SolicitudPagoResource;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ReporteConsolidadoProductos extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationGroup = 'Inventarios';

    protected static ?string $title = 'Reporte consolidado de productos';

    protected static ?string $navigationLabel = 'Reporte consolidado de productos';

    protected static string $view = 'filament.pages.reporte-consolidado-productos';

    public ?array $filters = [];

    public int $perPage = 10;

    public string $search = '';

    public ?string $sortField = 'producto_nombre';

    public string $sortDirection = 'asc';

    public bool $reporteCargado = false;

    public int $productosTotal = 0;

    public function mount(): void
    {
        $this->form->fill([
            'conexiones' => [],
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('filters')
            ->schema([
                Section::make('Filtros')
                    ->columns(4)
                    ->schema([
                        Select::make('conexiones')
                            ->label('Conexiones')
                            ->multiple()
                            ->options(Empresa::query()->pluck('nombre_empresa', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?array $state): void {
                                $empresas = $this->buildDefaultEmpresasSelection($state ?? []);
                                $sucursales = $this->buildDefaultSucursalesSelection($state ?? [], $empresas);

                                $set('empresas', $empresas);
                                $set('sucursales', $sucursales);
                                $this->resetPage();
                                $this->resetProductosData();
                            }),
                        Select::make('empresas')
                            ->label('Empresas')
                            ->multiple()
                            ->options(fn(Forms\Get $get): array => $this->getEmpresasOptionsByConnections($get('conexiones') ?? []))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->syncSucursales();
                                $this->resetPage();
                                $this->resetProductosData();
                            }),
                        Select::make('sucursales')
                            ->label('Sucursales')
                            ->multiple()
                            ->options(fn(Forms\Get $get): array => $this->getSucursalesOptionsByConnections(
                                $get('conexiones') ?? [],
                                $this->groupOptionsByConnection($get('empresas') ?? []),
                            ))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->resetPage();
                                $this->resetProductosData();
                            }),
                        Actions::make([
                            FormAction::make('generateReport')
                                ->label('Cargar productos')
                                ->color('primary')
                                ->icon('heroicon-o-document-arrow-down')
                                ->action(fn() => $this->generateReport()),
                        ])
                            ->columnSpan(1)
                            ->extraAttributes(['class' => 'flex items-end']),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->form([
                    Forms\Components\TextInput::make('descripcion_reporte')
                        ->label('Descripción del reporte')
                        ->placeholder('Reporte consolidado de productos')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(fn(array $data) => $this->exportPdf($data['descripcion_reporte'] ?? '')),
        ];
    }

    public function generateReport(): void
    {
        $this->resetPage();
        $this->reporteCargado = true;
    }

    protected function resetProductosData(): void
    {
        $this->productosTotal = 0;
        $this->reporteCargado = false;
    }

    protected function syncSucursales(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresas = $this->filters['empresas'] ?? [];

        $this->filters['sucursales'] = $this->buildDefaultSucursalesSelection($conexiones, $empresas);
    }

    protected function fetchProductosAggregated(
        int $conexion,
        array $empresas,
        array $sucursales,
        string $terminoBusqueda,
        ?int $perPage = null,
        ?int $page = null
    ): array {
        $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

        if (! $connectionName) {
            return [
                'items' => [],
                'total' => 0,
            ];
        }

        $query = DB::connection($connectionName)
            ->table('saeprod as prod')
            ->join('saeprbo as prbo', function ($join) {
                $join->on('prbo.prbo_cod_prod', '=', 'prod.prod_cod_prod')
                    ->on('prbo.prbo_cod_empr', '=', 'prod.prod_cod_empr')
                    ->on('prbo.prbo_cod_sucu', '=', 'prod.prod_cod_sucu');
            })
            ->leftJoin('saebode as bode', function ($join) {
                $join->on('bode.bode_cod_bode', '=', 'prbo.prbo_cod_bode')
                    ->on('bode.bode_cod_empr', '=', 'prbo.prbo_cod_empr');
            })
            ->leftJoin('saesucu as sucu', function ($join) {
                $join->on('sucu.sucu_cod_sucu', '=', 'prod.prod_cod_sucu')
                    ->on('sucu.sucu_cod_empr', '=', 'prod.prod_cod_empr');
            })
            ->leftJoin('saeunid as unid', function ($join) {
                $join->on('unid.unid_cod_unid', '=', 'prbo.prbo_cod_unid');
            })
            ->whereIn('prod.prod_cod_empr', $empresas)
            ->when(! empty($sucursales), fn($q) => $q->whereIn('prod.prod_cod_sucu', $sucursales))
            ->when($terminoBusqueda !== '', function ($q) use ($terminoBusqueda) {
                $like = '%' . $terminoBusqueda . '%';

                $q->where(function ($builder) use ($like) {
                    $builder
                        ->where('prod.prod_nom_prod', 'like', $like)
                        ->orWhere('prod.prod_cod_prod', 'like', $like)
                        ->orWhere('prod.prod_det_prod', 'like', $like)
                        ->orWhere('prod.prod_des_prod', 'like', $like)
                        ->orWhere('prod.prod_cod_barra', 'like', $like)
                        ->orWhere('sucu.sucu_nom_sucu', 'like', $like)
                        ->orWhere('bode.bode_nom_bode', 'like', $like);
                });
            })
            ->select([
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prod.prod_det_prod',
                'prod.prod_des_prod',
                'prod.prod_cod_barra',
                DB::raw('MAX(unid.unid_nom_unid) as unid_nom_unid'),
                DB::raw('MAX(unid.unid_sigl_unid) as unid_sigl_unid'),
                DB::raw('SUM(prbo.prbo_dis_prod) as stock_total'),
                DB::raw('SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN prbo.prbo_uco_prod ELSE 0 END) as precio_total'),
                DB::raw('SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN 1 ELSE 0 END) as precio_count'),
            ])
            ->groupBy([
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prod.prod_det_prod',
                'prod.prod_des_prod',
                'prod.prod_cod_barra',
            ]);

        $direccion = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        match ($this->sortField) {
            'producto_codigo' => $query->orderBy('prod.prod_cod_prod', $direccion),
            'stock_total' => $query->orderBy('stock_total', $direccion),
            'precio_promedio' => $query->orderByRaw(
                'CASE WHEN SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN 1 ELSE 0 END) = 0 THEN 0 ELSE SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN prbo.prbo_uco_prod ELSE 0 END) / SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN 1 ELSE 0 END) END ' . $direccion
            ),
            default => $query->orderBy('prod.prod_nom_prod', $direccion),
        };

        if ($perPage && $page) {
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);
            $items = $paginator->items();
            $total = $paginator->total();
        } else {
            $items = $query->get()->all();
            $total = count($items);
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    protected function fetchUbicaciones(
        int $conexion,
        array $empresas,
        array $sucursales,
        array $productosCodigo
    ): array {
        $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

        if (! $connectionName || empty($productosCodigo)) {
            return [];
        }

        $empresasDisponibles = SolicitudPagoResource::getEmpresasOptions($conexion);
        $sucursalesDisponibles = SolicitudPagoResource::getSucursalesOptions($conexion, $empresas);

        try {
            $rows = DB::connection($connectionName)
                ->table('saeprod as prod')
                ->join('saeprbo as prbo', function ($join) {
                    $join->on('prbo.prbo_cod_prod', '=', 'prod.prod_cod_prod')
                        ->on('prbo.prbo_cod_empr', '=', 'prod.prod_cod_empr')
                        ->on('prbo.prbo_cod_sucu', '=', 'prod.prod_cod_sucu');
                })
                ->join('saebode as bode', function ($join) {
                    $join->on('bode.bode_cod_bode', '=', 'prbo.prbo_cod_bode')
                        ->on('bode.bode_cod_empr', '=', 'prbo.prbo_cod_empr');
                })
                ->leftJoin('saesucu as sucu', function ($join) {
                    $join->on('sucu.sucu_cod_sucu', '=', 'prod.prod_cod_sucu')
                        ->on('sucu.sucu_cod_empr', '=', 'prod.prod_cod_empr');
                })
                ->leftJoin('saeunid as unid', function ($join) {
                    $join->on('unid.unid_cod_unid', '=', 'prbo.prbo_cod_unid');
                })
                ->whereIn('prod.prod_cod_empr', $empresas)
                ->when(! empty($sucursales), fn($q) => $q->whereIn('prod.prod_cod_sucu', $sucursales))
                ->whereIn('prod.prod_cod_prod', $productosCodigo)
                ->select([
                    'prod.prod_cod_prod',
                    'prod.prod_nom_prod',
                    'prod.prod_det_prod',
                    'prod.prod_des_prod',
                    'prod.prod_cod_barra',
                    'prod.prod_cod_empr',
                    'prod.prod_cod_sucu',
                    'prbo.prbo_cod_bode',
                    'prbo.prbo_uco_prod',
                    'prbo.prbo_iva_porc',
                    'prbo.prbo_dis_prod',
                    'prbo.prbo_sma_prod',
                    'prbo.prbo_smi_prod',
                    'bode.bode_nom_bode',
                    'sucu.sucu_nom_sucu',
                    'unid.unid_nom_unid',
                    'unid.unid_sigl_unid',
                ])
                ->distinct()
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        return $rows
            ->map(function ($row) use ($conexion, $empresasDisponibles, $sucursalesDisponibles) {
                $empresaCodigo = $row->prod_cod_empr;
                $sucursalCodigo = $row->prod_cod_sucu;

                return [
                    'conexion_id' => $conexion,
                    'empresa_codigo' => $empresaCodigo,
                    'empresa_nombre' => $empresasDisponibles[$empresaCodigo] ?? $empresaCodigo,
                    'sucursal_codigo' => $sucursalCodigo,
                    'sucursal_nombre' => $row->sucu_nom_sucu ?? ($sucursalesDisponibles[$sucursalCodigo] ?? $sucursalCodigo),
                    'bodega_codigo' => $row->prbo_cod_bode,
                    'bodega_nombre' => $row->bode_nom_bode ?? $row->prbo_cod_bode,
                    'producto_codigo' => $row->prod_cod_prod,
                    'producto_nombre' => $row->prod_nom_prod,
                    'producto_descripcion' => $row->prod_det_prod ?: ($row->prod_des_prod ?? ''),
                    'producto_barra' => $row->prod_cod_barra,
                    'precio' => (float) ($row->prbo_uco_prod ?? 0),
                    'iva' => (float) ($row->prbo_iva_porc ?? 0),
                    'stock' => (float) ($row->prbo_dis_prod ?? 0),
                    'stock_minimo' => (float) ($row->prbo_smi_prod ?? 0),
                    'stock_maximo' => (float) ($row->prbo_sma_prod ?? 0),
                    'unidad' => $row->unid_sigl_unid ?: $row->unid_nom_unid,
                ];
            })
            ->all();
    }

    protected function groupAggregatedProductos(Collection $registros, array $ubicaciones): Collection
    {
        $agrupado = [];

        foreach ($registros as $row) {
            $productoKey = $this->buildProductoKey($row['producto_codigo'] ?? '', $row['producto_nombre'] ?? '');

            if (! isset($agrupado[$productoKey])) {
                $agrupado[$productoKey] = [
                    'key' => $productoKey,
                    'producto_codigo' => (string) ($row['producto_codigo'] ?? ''),
                    'producto_nombre' => $row['producto_nombre'] ?? null,
                    'producto_descripcion' => $row['producto_descripcion'] ?? null,
                    'producto_barra' => $row['producto_barra'] ?? null,
                    'unidad' => $row['unidad'] ?? null,
                    'stock_total' => 0,
                    'precio_promedio' => 0,
                    'precio_count' => 0,
                    'precio_total' => 0,
                    'ubicaciones' => [],
                ];
            }

            $agrupado[$productoKey]['stock_total'] += (float) ($row['stock_total'] ?? 0);
            $agrupado[$productoKey]['precio_total'] += (float) ($row['precio_total'] ?? 0);
            $agrupado[$productoKey]['precio_count'] += (int) ($row['precio_count'] ?? 0);

            if (empty($agrupado[$productoKey]['producto_descripcion']) && ! empty($row['producto_descripcion'])) {
                $agrupado[$productoKey]['producto_descripcion'] = $row['producto_descripcion'];
            }

            if (empty($agrupado[$productoKey]['producto_barra']) && ! empty($row['producto_barra'])) {
                $agrupado[$productoKey]['producto_barra'] = $row['producto_barra'];
            }

            if (empty($agrupado[$productoKey]['unidad']) && ! empty($row['unidad'])) {
                $agrupado[$productoKey]['unidad'] = $row['unidad'];
            }
        }

        foreach ($agrupado as $key => &$producto) {
            $producto['precio_promedio'] = $producto['precio_count'] > 0
                ? $producto['precio_total'] / $producto['precio_count']
                : 0;

            unset($producto['precio_total'], $producto['precio_count']);

            $producto['ubicaciones'] = collect($ubicaciones[$key] ?? [])
                ->sortBy(fn(array $ubicacion) => ($ubicacion['conexion_nombre'] ?? '') . ($ubicacion['empresa_nombre'] ?? '') . ($ubicacion['bodega_nombre'] ?? ''))
                ->values()
                ->all();
        }
        unset($producto);

        return collect($agrupado)->sortBy('producto_nombre')->values();
    }

    protected function buildProductoKey(?string $codigo, ?string $nombre): string
    {
        $codigo = trim((string) $codigo);

        if ($codigo !== '') {
            return 'cod:' . mb_strtolower($codigo);
        }

        $nombre = trim((string) $nombre);

        if ($nombre !== '') {
            return 'nom:' . md5(mb_strtolower($nombre));
        }

        return 'prod:' . uniqid('', true);
    }

    protected function applySort(Collection $productos): Collection
    {
        if (! $this->sortField) {
            return $productos;
        }

        return $productos->sortBy(
            function (array $producto) {
                return match ($this->sortField) {
                    'producto_codigo' => mb_strtolower($producto['producto_codigo'] ?? ''),
                    'stock_total' => (float) ($producto['stock_total'] ?? 0),
                    'precio_promedio' => (float) ($producto['precio_promedio'] ?? 0),
                    default => mb_strtolower($producto['producto_nombre'] ?? ''),
                };
            },
            descending: $this->sortDirection === 'desc'
        );
    }

    protected function buildPaginatedProductos(): LengthAwarePaginator
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);

        if (empty($conexiones)) {
            return $this->emptyPaginator();
        }

        $page = $this->getPage();
        $terminoBusqueda = trim($this->search ?? '');
        $terminoLower = mb_strtolower($terminoBusqueda);
        $connectionNames = Empresa::query()->pluck('nombre_empresa', 'id');
        $perConnection = max(1, (int) ceil($this->perPage / max(1, count($conexiones))));
        $registros = collect();
        $ubicaciones = [];
        $total = 0;

        foreach ($conexiones as $conexion) {
            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $searchTermForConnection = $terminoBusqueda;
            $conexionNombre = $connectionNames[$conexion] ?? '';

            if ($terminoBusqueda !== '' && $conexionNombre !== '' && str_contains(mb_strtolower($conexionNombre), $terminoLower)) {
                $searchTermForConnection = '';
            }

            if ($terminoBusqueda !== '' && $searchTermForConnection !== '') {
                $empresasDisponibles = SolicitudPagoResource::getEmpresasOptions($conexion);
                $empresasMatch = collect($empresasDisponibles)
                    ->filter(fn($nombre) => str_contains(mb_strtolower($nombre), $terminoLower))
                    ->keys()
                    ->all();

                if (! empty($empresasMatch)) {
                    $empresas = array_values(array_intersect($empresas, $empresasMatch));
                    $searchTermForConnection = '';
                }
            }

            if (empty($empresas)) {
                continue;
            }

            $resultado = $this->fetchProductosAggregated(
                $conexion,
                $empresas,
                $sucursales,
                $searchTermForConnection,
                $perConnection,
                $page
            );

            $total += $resultado['total'];

            $items = collect($resultado['items'])->map(function ($row) use ($conexion, $connectionNames) {
                return [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $connectionNames[$conexion] ?? '',
                    'producto_codigo' => $row->prod_cod_prod ?? '',
                    'producto_nombre' => $row->prod_nom_prod ?? '',
                    'producto_descripcion' => $row->prod_det_prod ?: ($row->prod_des_prod ?? ''),
                    'producto_barra' => $row->prod_cod_barra ?? '',
                    'unidad' => $row->unid_sigl_unid ?: $row->unid_nom_unid,
                    'stock_total' => (float) ($row->stock_total ?? 0),
                    'precio_total' => (float) ($row->precio_total ?? 0),
                    'precio_count' => (int) ($row->precio_count ?? 0),
                ];
            });

            $registros = $registros->merge($items);

            $productosCodigos = $items
                ->pluck('producto_codigo')
                ->filter(fn($codigo) => $codigo !== null && $codigo !== '')
                ->unique()
                ->values()
                ->all();

            $ubicaciones = array_merge(
                $ubicaciones,
                $this->buildUbicacionesConNombres(
                    $conexion,
                    $connectionNames[$conexion] ?? '',
                    $empresas,
                    $sucursales,
                    $productosCodigos
                )
            );
        }

        $ubicacionesAgrupadas = $this->groupUbicacionesByProductoKey($ubicaciones);
        $productos = $this->groupAggregatedProductos($registros, $ubicacionesAgrupadas);
        $productos = $this->applySort($productos)->values();

        if ($productos->count() > $this->perPage) {
            $productos = $productos->take($this->perPage)->values();
        }

        $this->productosTotal = $total;

        return new LengthAwarePaginator(
            $productos,
            $total,
            $this->perPage,
            $page
        );
    }

    protected function buildUbicacionesConNombres(
        int $conexion,
        string $conexionNombre,
        array $empresas,
        array $sucursales,
        array $productosCodigo
    ): array {
        $ubicaciones = $this->fetchUbicaciones($conexion, $empresas, $sucursales, $productosCodigo);

        return collect($ubicaciones)
            ->map(function (array $ubicacion) use ($conexionNombre) {
                $ubicacion['conexion_nombre'] = $conexionNombre;

                return $ubicacion;
            })
            ->all();
    }

    protected function groupUbicacionesByProductoKey(array $ubicaciones): array
    {
        $agrupado = [];

        foreach ($ubicaciones as $ubicacion) {
            $productoKey = $this->buildProductoKey($ubicacion['producto_codigo'] ?? '', $ubicacion['producto_nombre'] ?? '');
            $agrupado[$productoKey][] = $ubicacion;
        }

        return $agrupado;
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $this->perPage, $this->getPage());
    }

    public function getProductosPaginatedProperty(): LengthAwarePaginator
    {
        if (! $this->reporteCargado) {
            return $this->emptyPaginator();
        }

        return $this->buildPaginatedProductos();
    }

    public function getProductosCountProperty(): int
    {
        if (! $this->reporteCargado) {
            return 0;
        }

        if ($this->productosTotal === 0) {
            $this->buildPaginatedProductos();
        }

        return $this->productosTotal;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    protected function exportPdf(string $descripcionReporte)
    {
        $productos = $this->getProductosParaExportar()->values();

        if ($productos->isEmpty()) {
            Notification::make()
                ->title('No hay productos para exportar')
                ->warning()
                ->send();

            return null;
        }

        return response()->streamDownload(function () use ($productos, $descripcionReporte) {
            echo Pdf::loadView('pdfs.reporte-consolidado-productos', [
                'productos' => $productos->all(),
                'descripcionReporte' => $descripcionReporte,
                'usuario' => Auth::user()?->name,
            ])->setPaper('a4', 'landscape')->stream();
        }, 'reporte-consolidado-productos.pdf');
    }

    protected function getEmpresasOptionsByConnections(array $conexiones): array
    {
        return collect($conexiones)
            ->flatMap(function ($conexion) {
                return collect(SolicitudPagoResource::getEmpresasOptions($conexion))
                    ->mapWithKeys(fn($nombre, $codigo) => [
                        $conexion . '|' . $codigo => $nombre,
                    ]);
            })
            ->all();
    }

    protected function getSucursalesOptionsByConnections(array $conexiones, array $empresasSeleccionadas): array
    {
        return collect($conexiones)
            ->flatMap(function ($conexion) use ($empresasSeleccionadas) {
                $empresas = $empresasSeleccionadas[$conexion] ?? [];

                return collect(SolicitudPagoResource::getSucursalesOptions($conexion, $empresas))
                    ->mapWithKeys(fn($nombre, $codigo) => [
                        $conexion . '|' . $codigo => $nombre,
                    ]);
            })
            ->all();
    }

    protected function groupOptionsByConnection(array $optionKeys): array
    {
        $agrupado = [];

        foreach ($optionKeys as $value) {
            [$conexion, $codigo] = array_pad(explode('|', (string) $value, 2), 2, null);

            if ($conexion && $codigo) {
                $agrupado[(int) $conexion][] = $codigo;
            }
        }

        return $agrupado;
    }

    protected function buildDefaultEmpresasSelection(array $conexiones): array
    {
        return collect($conexiones)
            ->flatMap(fn($conexion) => collect(SolicitudPagoResource::getEmpresasOptions($conexion))
                ->keys()
                ->map(fn($codigo) => $conexion . '|' . $codigo))
            ->values()
            ->all();
    }

    protected function buildDefaultSucursalesSelection(array $conexiones, array $empresasSeleccionadas): array
    {
        $empresas = $this->groupOptionsByConnection($empresasSeleccionadas);

        return collect($conexiones)
            ->flatMap(fn($conexion) => collect(SolicitudPagoResource::getSucursalesOptions($conexion, $empresas[$conexion] ?? []))
                ->keys()
                ->map(fn($codigo) => $conexion . '|' . $codigo))
            ->values()
            ->all();
    }

    protected function getProductosParaExportar(): Collection
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);

        if (empty($conexiones)) {
            return collect();
        }

        $terminoBusqueda = trim($this->search ?? '');
        $terminoLower = mb_strtolower($terminoBusqueda);
        $connectionNames = Empresa::query()->pluck('nombre_empresa', 'id');
        $registros = collect();
        $ubicaciones = [];

        foreach ($conexiones as $conexion) {
            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $searchTermForConnection = $terminoBusqueda;
            $conexionNombre = $connectionNames[$conexion] ?? '';

            if ($terminoBusqueda !== '' && $conexionNombre !== '' && str_contains(mb_strtolower($conexionNombre), $terminoLower)) {
                $searchTermForConnection = '';
            }

            if ($terminoBusqueda !== '' && $searchTermForConnection !== '') {
                $empresasDisponibles = SolicitudPagoResource::getEmpresasOptions($conexion);
                $empresasMatch = collect($empresasDisponibles)
                    ->filter(fn($nombre) => str_contains(mb_strtolower($nombre), $terminoLower))
                    ->keys()
                    ->all();

                if (! empty($empresasMatch)) {
                    $empresas = array_values(array_intersect($empresas, $empresasMatch));
                    $searchTermForConnection = '';
                }
            }

            if (empty($empresas)) {
                continue;
            }

            $resultado = $this->fetchProductosAggregated(
                $conexion,
                $empresas,
                $sucursales,
                $searchTermForConnection
            );

            $items = collect($resultado['items'])->map(function ($row) use ($conexion, $connectionNames) {
                return [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $connectionNames[$conexion] ?? '',
                    'producto_codigo' => $row->prod_cod_prod ?? '',
                    'producto_nombre' => $row->prod_nom_prod ?? '',
                    'producto_descripcion' => $row->prod_det_prod ?: ($row->prod_des_prod ?? ''),
                    'producto_barra' => $row->prod_cod_barra ?? '',
                    'unidad' => $row->unid_sigl_unid ?: $row->unid_nom_unid,
                    'stock_total' => (float) ($row->stock_total ?? 0),
                    'precio_total' => (float) ($row->precio_total ?? 0),
                    'precio_count' => (int) ($row->precio_count ?? 0),
                ];
            });

            $registros = $registros->merge($items);

            $productosCodigos = $items
                ->pluck('producto_codigo')
                ->filter(fn($codigo) => $codigo !== null && $codigo !== '')
                ->unique()
                ->values()
                ->all();

            $ubicaciones = array_merge(
                $ubicaciones,
                $this->buildUbicacionesConNombres(
                    $conexion,
                    $connectionNames[$conexion] ?? '',
                    $empresas,
                    $sucursales,
                    $productosCodigos
                )
            );
        }

        $ubicacionesAgrupadas = $this->groupUbicacionesByProductoKey($ubicaciones);
        $productos = $this->groupAggregatedProductos($registros, $ubicacionesAgrupadas);

        return $this->applySort($productos)->values();
    }
}
