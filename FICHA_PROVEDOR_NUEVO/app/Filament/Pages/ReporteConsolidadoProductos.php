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

    public array $productos = [];

    public int $perPage = 10;

    public string $search = '';

    public ?string $sortField = 'producto_nombre';

    public string $sortDirection = 'asc';

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
        $this->loadProductos();
    }

    protected function resetProductosData(): void
    {
        $this->productos = [];
    }

    protected function syncSucursales(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresas = $this->filters['empresas'] ?? [];

        $this->filters['sucursales'] = $this->buildDefaultSucursalesSelection($conexiones, $empresas);
    }

    public function loadProductos(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
        $this->resetProductosData();

        if (empty($conexiones)) {
            return;
        }

        $connectionNames = Empresa::query()->pluck('nombre_empresa', 'id');
        $registros = collect();

        foreach ($conexiones as $conexion) {
            $empresas = $empresasSeleccionadas[$conexion] ?? array_keys(SolicitudPagoResource::getEmpresasOptions($conexion));

            if (empty($empresas)) {
                continue;
            }

            $sucursales = $sucursalesSeleccionadas[$conexion] ?? [];
            $registros = $registros->merge($this->fetchProductos(
                $conexion,
                $empresas,
                $sucursales,
                $connectionNames[$conexion] ?? '',
            ));
        }

        $this->productos = $this->groupByProducto($registros)->all();
    }

    protected function fetchProductos(int $conexion, array $empresas, array $sucursales, string $conexionNombre): array
    {
        $connectionName = SolicitudPagoResource::getExternalConnectionName($conexion);

        if (! $connectionName) {
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
                ->orderBy('prod.prod_nom_prod')
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        return $rows
            ->map(function ($row) use ($conexion, $conexionNombre, $empresasDisponibles, $sucursalesDisponibles) {
                $empresaCodigo = $row->prod_cod_empr;
                $sucursalCodigo = $row->prod_cod_sucu;

                return [
                    'conexion_id' => $conexion,
                    'conexion_nombre' => $conexionNombre,
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

    protected function groupByProducto(Collection $registros): Collection
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

            $agrupado[$productoKey]['stock_total'] += (float) ($row['stock'] ?? 0);

            $precio = (float) ($row['precio'] ?? 0);
            if ($precio > 0) {
                $agrupado[$productoKey]['precio_total'] += $precio;
                $agrupado[$productoKey]['precio_count']++;
            }

            if (empty($agrupado[$productoKey]['unidad']) && ! empty($row['unidad'])) {
                $agrupado[$productoKey]['unidad'] = $row['unidad'];
            }

            $agrupado[$productoKey]['ubicaciones'][] = [
                'conexion_id' => $row['conexion_id'] ?? null,
                'conexion_nombre' => $row['conexion_nombre'] ?? null,
                'empresa_codigo' => $row['empresa_codigo'] ?? null,
                'empresa_nombre' => $row['empresa_nombre'] ?? null,
                'sucursal_codigo' => $row['sucursal_codigo'] ?? null,
                'sucursal_nombre' => $row['sucursal_nombre'] ?? null,
                'bodega_codigo' => $row['bodega_codigo'] ?? null,
                'bodega_nombre' => $row['bodega_nombre'] ?? null,
                'precio' => (float) ($row['precio'] ?? 0),
                'iva' => (float) ($row['iva'] ?? 0),
                'stock' => (float) ($row['stock'] ?? 0),
                'stock_minimo' => (float) ($row['stock_minimo'] ?? 0),
                'stock_maximo' => (float) ($row['stock_maximo'] ?? 0),
                'unidad' => $row['unidad'] ?? null,
            ];
        }

        foreach ($agrupado as &$producto) {
            $producto['precio_promedio'] = $producto['precio_count'] > 0
                ? $producto['precio_total'] / $producto['precio_count']
                : 0;

            unset($producto['precio_total'], $producto['precio_count']);

            $producto['ubicaciones'] = collect($producto['ubicaciones'])
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

    protected function applySearch(Collection $productos): Collection
    {
        $termino = trim($this->search ?? '');

        if ($termino === '') {
            return $productos;
        }

        $termino = mb_strtolower($termino);

        return $productos->filter(function (array $producto) use ($termino) {
            $matchesProducto = str_contains(mb_strtolower($producto['producto_nombre'] ?? ''), $termino)
                || str_contains(mb_strtolower($producto['producto_codigo'] ?? ''), $termino)
                || str_contains(mb_strtolower($producto['producto_descripcion'] ?? ''), $termino)
                || str_contains(mb_strtolower($producto['producto_barra'] ?? ''), $termino);

            if ($matchesProducto) {
                return true;
            }

            foreach ($producto['ubicaciones'] ?? [] as $ubicacion) {
                if (
                    str_contains(mb_strtolower((string) ($ubicacion['conexion_nombre'] ?? '')), $termino)
                    || str_contains(mb_strtolower((string) ($ubicacion['empresa_nombre'] ?? '')), $termino)
                    || str_contains(mb_strtolower((string) ($ubicacion['sucursal_nombre'] ?? '')), $termino)
                    || str_contains(mb_strtolower((string) ($ubicacion['bodega_nombre'] ?? '')), $termino)
                ) {
                    return true;
                }
            }

            return false;
        });
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

    protected function getFilteredProductos(): Collection
    {
        $productos = collect($this->productos);

        $productos = $this->applySearch($productos);

        return $this->applySort($productos)->values();
    }

    public function getProductosPaginatedProperty(): LengthAwarePaginator
    {
        $productos = $this->getFilteredProductos();

        $page = $this->getPage();
        $items = $productos->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $productos->count(),
            $this->perPage,
            $page
        );
    }

    public function getProductosCountProperty(): int
    {
        return $this->getFilteredProductos()->count();
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
        $productos = $this->getFilteredProductos()->values();

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
}
