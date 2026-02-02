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
use Illuminate\Database\Query\Builder;
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

    public bool $reportLoaded = false;

    public int $perPage = 10;

    public string $search = '';

    public ?string $sortField = 'producto_nombre';

    public string $sortDirection = 'asc';

    protected ?int $productosCountCache = null;

    public function mount(): void
    {
        $this->form->fill([
            'conexiones' => [],
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->resetProductosCache();
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
                                $this->markReportStale();
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
                                $this->markReportStale();
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
                                $this->markReportStale();
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
        $this->reportLoaded = true;
        $this->resetProductosCache();
    }

    protected function markReportStale(): void
    {
        $this->reportLoaded = false;
        $this->resetProductosCache();
    }

    protected function resetProductosCache(): void
    {
        $this->productosCountCache = null;
    }

    protected function syncSucursales(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresas = $this->filters['empresas'] ?? [];

        $this->filters['sucursales'] = $this->buildDefaultSucursalesSelection($conexiones, $empresas);
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

    public function getProductosPaginatedProperty(): LengthAwarePaginator
    {
        $page = $this->getPage();
        $perPage = $this->perPage;

        if (! $this->shouldLoadProductos()) {
            return $this->buildEmptyPaginator($page, $perPage);
        }

        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
        $connectionNames = Empresa::query()->pluck('nombre_empresa', 'id');
        $limit = $page * $perPage;
        $productos = collect();

        foreach ($conexiones as $conexion) {
            $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

            if (! $connectionName) {
                continue;
            }

            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $rows = $this->fetchProductosResumen(
                $connectionName,
                $empresas,
                $sucursales,
                $limit
            );

            $productos = $productos->merge($rows->map(function ($row) use ($conexion, $connectionNames) {
                return [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $connectionNames[$conexion] ?? '',
                    'producto_codigo' => (string) ($row->prod_cod_prod ?? ''),
                    'producto_nombre' => $row->prod_nom_prod ?? null,
                    'producto_descripcion' => $row->producto_det ?: ($row->producto_des ?? ''),
                    'producto_barra' => $row->producto_barra ?? null,
                    'unidad' => $row->unidad ?? null,
                    'stock_total' => (float) ($row->stock_total ?? 0),
                    'precio_total' => (float) ($row->precio_total ?? 0),
                    'precio_count' => (int) ($row->precio_count ?? 0),
                ];
            }));
        }

        $productos = $this->consolidateProductos($productos);
        $productos = $this->applySort($productos)->values();
        $items = $productos->forPage($page, $perPage)->values();
        $items = $this->attachUbicaciones(
            $items,
            $conexiones,
            $empresasSeleccionadas,
            $sucursalesSeleccionadas,
            $connectionNames,
        );

        return new LengthAwarePaginator(
            $items,
            $this->getProductosCountProperty(),
            $perPage,
            $page
        );
    }

    public function getProductosCountProperty(): int
    {
        if (! $this->shouldLoadProductos()) {
            return 0;
        }

        if ($this->productosCountCache !== null) {
            return $this->productosCountCache;
        }

        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);

        $this->productosCountCache = $this->countUniqueProductos(
            $conexiones,
            $empresasSeleccionadas,
            $sucursalesSeleccionadas
        );

        return $this->productosCountCache;
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
        $productos = $this->getProductosForExport();

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

    protected function buildEmptyPaginator(int $page, int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            $page
        );
    }

    protected function shouldLoadProductos(): bool
    {
        return $this->reportLoaded && ! empty($this->filters['conexiones'] ?? []);
    }

    protected function buildProductosQuery(string $connectionName, array $empresas, array $sucursales): Builder
    {
        return DB::connection($connectionName)
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
            ->when(! empty($sucursales), fn($q) => $q->whereIn('prod.prod_cod_sucu', $sucursales));
    }

    protected function fetchProductosResumen(
        string $connectionName,
        array $empresas,
        array $sucursales,
        ?int $limit = null
    ): Collection {
        $query = $this->buildProductosQuery($connectionName, $empresas, $sucursales)
            ->select([
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                DB::raw('MAX(prod.prod_det_prod) as producto_det'),
                DB::raw('MAX(prod.prod_des_prod) as producto_des'),
                DB::raw('MAX(prod.prod_cod_barra) as producto_barra'),
                DB::raw('COALESCE(MAX(unid.unid_sigl_unid), MAX(unid.unid_nom_unid)) as unidad'),
                DB::raw('SUM(prbo.prbo_dis_prod) as stock_total'),
                DB::raw('SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN prbo.prbo_uco_prod ELSE 0 END) as precio_total'),
                DB::raw('SUM(CASE WHEN prbo.prbo_uco_prod > 0 THEN 1 ELSE 0 END) as precio_count'),
            ])
            ->groupBy('prod.prod_cod_prod', 'prod.prod_nom_prod');

        $this->applySearchToQuery($query);
        $this->applySortToQuery($query);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    protected function applySearchToQuery(Builder $query): void
    {
        $termino = trim((string) $this->search);

        if ($termino === '') {
            return;
        }

        $termino = mb_strtolower($termino);
        $like = '%' . $termino . '%';

        $query->where(function ($subQuery) use ($like) {
            $subQuery
                ->whereRaw('LOWER(prod.prod_nom_prod) LIKE ?', [$like])
                ->orWhereRaw('LOWER(prod.prod_cod_prod) LIKE ?', [$like])
                ->orWhereRaw('LOWER(prod.prod_det_prod) LIKE ?', [$like])
                ->orWhereRaw('LOWER(prod.prod_des_prod) LIKE ?', [$like])
                ->orWhereRaw('LOWER(prod.prod_cod_barra) LIKE ?', [$like])
                ->orWhereRaw('LOWER(bode.bode_nom_bode) LIKE ?', [$like])
                ->orWhereRaw('LOWER(sucu.sucu_nom_sucu) LIKE ?', [$like]);
        });
    }

    protected function applySortToQuery(Builder $query): void
    {
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        if ($this->sortField === 'producto_codigo') {
            $query->orderBy('prod.prod_cod_prod', $direction);
            return;
        }

        if ($this->sortField === 'stock_total') {
            $query->orderBy('stock_total', $direction);
            return;
        }

        if ($this->sortField === 'precio_promedio') {
            $query->orderByRaw('CASE WHEN precio_count > 0 THEN precio_total / precio_count ELSE 0 END ' . $direction);
            return;
        }

        $query->orderBy('prod.prod_nom_prod', $direction);
    }

    protected function consolidateProductos(Collection $registros): Collection
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
                    'precio_total' => 0,
                    'precio_count' => 0,
                    'precio_promedio' => 0,
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

        foreach ($agrupado as &$producto) {
            $producto['precio_promedio'] = $producto['precio_count'] > 0
                ? $producto['precio_total'] / $producto['precio_count']
                : 0;

            unset($producto['precio_total'], $producto['precio_count']);
        }
        unset($producto);

        return collect($agrupado)->values();
    }

    protected function attachUbicaciones(
        Collection $productos,
        array $conexiones,
        array $empresasSeleccionadas,
        array $sucursalesSeleccionadas,
        Collection $connectionNames,
    ): Collection {
        if ($productos->isEmpty()) {
            return $productos;
        }

        $productosPorKey = $productos->keyBy('key')->map(function (array $producto) {
            $producto['ubicaciones'] = [];
            return $producto;
        })->all();

        $codigos = $productos
            ->pluck('producto_codigo')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $nombres = $productos
            ->filter(fn(array $producto) => empty($producto['producto_codigo']))
            ->pluck('producto_nombre')
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($conexiones as $conexion) {
            $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

            if (! $connectionName) {
                continue;
            }

            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $empresasDisponibles = SolicitudPagoResource::getEmpresasOptions($conexion);
            $sucursalesDisponibles = SolicitudPagoResource::getSucursalesOptions($conexion, $empresas);
            $ubicaciones = $this->fetchUbicaciones(
                $connectionName,
                $empresas,
                $sucursales,
                $codigos,
                $nombres
            );

            foreach ($ubicaciones as $ubicacion) {
                $productoKey = $this->buildProductoKey($ubicacion['producto_codigo'] ?? '', $ubicacion['producto_nombre'] ?? '');

                if (! isset($productosPorKey[$productoKey])) {
                    continue;
                }

                $empresaCodigo = $ubicacion['empresa_codigo'] ?? null;
                $sucursalCodigo = $ubicacion['sucursal_codigo'] ?? null;

                $productosPorKey[$productoKey]['ubicaciones'][] = [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $connectionNames[$conexion] ?? '',
                    'empresa_codigo' => $empresaCodigo,
                    'empresa_nombre' => $empresasDisponibles[$empresaCodigo] ?? $empresaCodigo,
                    'sucursal_codigo' => $sucursalCodigo,
                    'sucursal_nombre' => $ubicacion['sucursal_nombre'] ?? ($sucursalesDisponibles[$sucursalCodigo] ?? $sucursalCodigo),
                    'bodega_codigo' => $ubicacion['bodega_codigo'] ?? null,
                    'bodega_nombre' => $ubicacion['bodega_nombre'] ?? ($ubicacion['bodega_codigo'] ?? null),
                    'precio' => (float) ($ubicacion['precio'] ?? 0),
                    'iva' => (float) ($ubicacion['iva'] ?? 0),
                    'stock' => (float) ($ubicacion['stock'] ?? 0),
                    'stock_minimo' => (float) ($ubicacion['stock_minimo'] ?? 0),
                    'stock_maximo' => (float) ($ubicacion['stock_maximo'] ?? 0),
                    'unidad' => $ubicacion['unidad'] ?? null,
                ];
            }
        }

        foreach ($productosPorKey as &$producto) {
            $producto['ubicaciones'] = collect($producto['ubicaciones'])
                ->sortBy(fn(array $ubicacion) => ($ubicacion['conexion_nombre'] ?? '') . ($ubicacion['empresa_nombre'] ?? '') . ($ubicacion['bodega_nombre'] ?? ''))
                ->values()
                ->all();
        }
        unset($producto);

        return collect($productosPorKey)->values();
    }

    protected function fetchUbicaciones(
        string $connectionName,
        array $empresas,
        array $sucursales,
        array $codigos,
        array $nombres
    ): Collection {
        if (empty($codigos) && empty($nombres)) {
            return collect();
        }

        $query = $this->buildProductosQuery($connectionName, $empresas, $sucursales)
            ->select([
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
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
            ->where(function ($subQuery) use ($codigos, $nombres) {
                if (! empty($codigos)) {
                    $subQuery->whereIn('prod.prod_cod_prod', $codigos);
                }

                if (! empty($nombres)) {
                    $subQuery->orWhereIn('prod.prod_nom_prod', $nombres);
                }
            })
            ->orderBy('prod.prod_nom_prod');

        return $query->get()
            ->map(function ($row) {
                return [
                    'empresa_codigo' => $row->prod_cod_empr ?? null,
                    'sucursal_codigo' => $row->prod_cod_sucu ?? null,
                    'sucursal_nombre' => $row->sucu_nom_sucu ?? null,
                    'bodega_codigo' => $row->prbo_cod_bode ?? null,
                    'bodega_nombre' => $row->bode_nom_bode ?? null,
                    'producto_codigo' => $row->prod_cod_prod ?? null,
                    'producto_nombre' => $row->prod_nom_prod ?? null,
                    'precio' => (float) ($row->prbo_uco_prod ?? 0),
                    'iva' => (float) ($row->prbo_iva_porc ?? 0),
                    'stock' => (float) ($row->prbo_dis_prod ?? 0),
                    'stock_minimo' => (float) ($row->prbo_smi_prod ?? 0),
                    'stock_maximo' => (float) ($row->prbo_sma_prod ?? 0),
                    'unidad' => $row->unid_sigl_unid ?: $row->unid_nom_unid,
                ];
            });
    }

    protected function countUniqueProductos(
        array $conexiones,
        array $empresasSeleccionadas,
        array $sucursalesSeleccionadas
    ): int {
        $keys = [];

        foreach ($conexiones as $conexion) {
            $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

            if (! $connectionName) {
                continue;
            }

            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $query = $this->buildProductosQuery($connectionName, $empresas, $sucursales)
                ->select(['prod.prod_cod_prod', 'prod.prod_nom_prod'])
                ->groupBy('prod.prod_cod_prod', 'prod.prod_nom_prod');

            $this->applySearchToQuery($query);

            foreach ($query->cursor() as $row) {
                $key = $this->buildProductoKey($row->prod_cod_prod ?? '', $row->prod_nom_prod ?? '');
                $keys[$key] = true;
            }
        }

        return count($keys);
    }

    protected function getProductosForExport(): Collection
    {
        if (! $this->shouldLoadProductos()) {
            return collect();
        }

        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
        $connectionNames = Empresa::query()->pluck('nombre_empresa', 'id');
        $productos = collect();

        foreach ($conexiones as $conexion) {
            $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

            if (! $connectionName) {
                continue;
            }

            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $rows = $this->fetchProductosResumen($connectionName, $empresas, $sucursales);

            $productos = $productos->merge($rows->map(function ($row) use ($conexion, $connectionNames) {
                return [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $connectionNames[$conexion] ?? '',
                    'producto_codigo' => (string) ($row->prod_cod_prod ?? ''),
                    'producto_nombre' => $row->prod_nom_prod ?? null,
                    'producto_descripcion' => $row->producto_det ?: ($row->producto_des ?? ''),
                    'producto_barra' => $row->producto_barra ?? null,
                    'unidad' => $row->unidad ?? null,
                    'stock_total' => (float) ($row->stock_total ?? 0),
                    'precio_total' => (float) ($row->precio_total ?? 0),
                    'precio_count' => (int) ($row->precio_count ?? 0),
                ];
            }));
        }

        $productos = $this->consolidateProductos($productos);
        $productos = $this->applySort($productos)->values();

        return $this->attachUbicaciones(
            $productos,
            $conexiones,
            $empresasSeleccionadas,
            $sucursalesSeleccionadas,
            $connectionNames,
        );
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
}
