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
use Filament\Forms\Components\TextInput;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string $view = 'filament.pages.reporte-consolidado-productos';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $title = 'Reporte Consolidado de Productos';

    protected static ?string $navigationLabel = 'Reporte Consolidado de Productos';

    public ?array $filters = [];

    public array $productos = [];

    public int $perPage = 15;
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
                                $this->resetProductos();
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
                                $this->resetProductos();
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
                                $this->resetProductos();
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

    public function generateReport(): void
    {
        $this->resetPage();
        $this->loadReporte();
    }

    protected function resetProductos(): void
    {
        $this->productos = [];
    }

    protected function syncSucursales(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresas = $this->filters['empresas'] ?? [];

        $this->filters['sucursales'] = $this->buildDefaultSucursalesSelection($conexiones, $empresas);
    }

    public function loadReporte(): void
    {
        $conexiones = $this->filters['conexiones'] ?? [];
        $empresasSeleccionadas = $this->groupOptionsByConnection($this->filters['empresas'] ?? []);
        $sucursalesSeleccionadas = $this->groupOptionsByConnection($this->filters['sucursales'] ?? []);
        $this->resetProductos();

        if (empty($conexiones)) {
            return;
        }

        $this->productos = $this->buildConsolidado(
            $conexiones,
            $empresasSeleccionadas,
            $sucursalesSeleccionadas,
        );
    }

    protected function buildConsolidado(array $conexiones, array $empresasSeleccionadas, array $sucursalesSeleccionadas): array
    {
        $productos = [];
        $empresas = Empresa::query()->whereIn('id', $conexiones)->get()->keyBy('id');

        foreach ($conexiones as $conexionId) {
            $connectionName = SolicitudPagoResource::getExternalConnectionName((int) $conexionId);
            if (! $connectionName) {
                continue;
            }

            $empresaOptions = SolicitudPagoResource::getEmpresasOptions((int) $conexionId);
            $empresasFiltradas = $empresasSeleccionadas[$conexionId] ?? array_keys($empresaOptions);

            if (empty($empresasFiltradas)) {
                continue;
            }

            $sucursalOptions = SolicitudPagoResource::getSucursalesOptions((int) $conexionId, $empresasFiltradas);
            $sucursalesFiltradas = $sucursalesSeleccionadas[$conexionId] ?? array_keys($sucursalOptions);

            $schema = DB::connection($connectionName)->getSchemaBuilder();
            $descripcionColumn = $schema->hasColumn('saeprod', 'prod_des_prod')
                ? 'prod_des_prod'
                : ($schema->hasColumn('saeprod', 'prod_desc_prod') ? 'prod_desc_prod' : null);
            $stockColumn = $schema->hasColumn('saeprbo', 'prbo_dis_prod')
                ? 'prbo_dis_prod'
                : ($schema->hasColumn('saeprbo', 'prbo_can_dis') ? 'prbo_can_dis' : null);
            $stockMinColumn = $schema->hasColumn('saeprbo', 'prbo_smi_prod') ? 'prbo_smi_prod' : null;
            $stockMaxColumn = $schema->hasColumn('saeprbo', 'prbo_sma_prod') ? 'prbo_sma_prod' : null;
            $bodegaHasEmpresa = $schema->hasColumn('saebode', 'bode_cod_empr');

            $select = [
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prbo.prbo_uco_prod',
                'prbo.prbo_iva_porc',
                'prbo.prbo_cod_empr',
                'prbo.prbo_cod_sucu',
                'prbo.prbo_cod_bode',
                'bode.bode_nom_bode',
            ];

            if ($descripcionColumn) {
                $select[] = "prod.{$descripcionColumn} as prod_descripcion";
            }

            if ($stockColumn) {
                $select[] = "prbo.{$stockColumn} as prbo_stock";
            }

            if ($stockMinColumn) {
                $select[] = "prbo.{$stockMinColumn} as prbo_stock_minimo";
            }

            if ($stockMaxColumn) {
                $select[] = "prbo.{$stockMaxColumn} as prbo_stock_maximo";
            }

            try {
                $query = DB::connection($connectionName)
                    ->table('saeprbo as prbo')
                    ->join('saeprod as prod', 'prod.prod_cod_prod', '=', 'prbo.prbo_cod_prod')
                    ->leftJoin('saesubo as subo', function ($join) {
                        $join->on('subo.subo_cod_bode', '=', 'prbo.prbo_cod_bode')
                            ->on('subo.subo_cod_empr', '=', 'prbo.prbo_cod_empr')
                            ->on('subo.subo_cod_sucu', '=', 'prbo.prbo_cod_sucu');
                    })
                    ->leftJoin('saebode as bode', function ($join) use ($bodegaHasEmpresa) {
                        $join->on('bode.bode_cod_bode', '=', 'subo.subo_cod_bode');
                        if ($bodegaHasEmpresa) {
                            $join->on('bode.bode_cod_empr', '=', 'subo.subo_cod_empr');
                        }
                    })
                    ->whereIn('prbo.prbo_cod_empr', $empresasFiltradas)
                    ->when(! empty($sucursalesFiltradas), fn($q) => $q->whereIn('prbo.prbo_cod_sucu', $sucursalesFiltradas))
                    ->when($schema->hasColumn('saeprod', 'prod_cod_empr'), fn($q) => $q->whereColumn('prod.prod_cod_empr', 'prbo.prbo_cod_empr'))
                    ->when($schema->hasColumn('saeprod', 'prod_cod_sucu'), fn($q) => $q->whereColumn('prod.prod_cod_sucu', 'prbo.prbo_cod_sucu'))
                    ->select($select)
                    ->orderBy('prod.prod_nom_prod')
                    ->orderBy('prbo.prbo_cod_prod')
                    ->get();
            } catch (\Throwable $e) {
                continue;
            }

            $conexionNombre = $empresas[$conexionId]->nombre_empresa ?? (string) $conexionId;

            foreach ($query as $row) {
                $productos[] = [
                    'key' => hash('sha256', $conexionId . '|' . $row->prbo_cod_empr . '|' . $row->prbo_cod_sucu . '|' . $row->prbo_cod_bode . '|' . $row->prod_cod_prod),
                    'conexion_id' => $conexionId,
                    'conexion_nombre' => $conexionNombre,
                    'empresa_codigo' => $row->prbo_cod_empr,
                    'empresa_nombre' => $empresaOptions[$row->prbo_cod_empr] ?? (string) $row->prbo_cod_empr,
                    'sucursal_codigo' => $row->prbo_cod_sucu,
                    'sucursal_nombre' => $sucursalOptions[$row->prbo_cod_sucu] ?? (string) $row->prbo_cod_sucu,
                    'bodega_codigo' => $row->prbo_cod_bode,
                    'bodega_nombre' => $row->bode_nom_bode ?? (string) $row->prbo_cod_bode,
                    'producto_codigo' => $row->prod_cod_prod,
                    'producto_nombre' => $row->prod_nom_prod ?? '',
                    'producto_descripcion' => $row->prod_descripcion ?? null,
                    'precio' => (float) ($row->prbo_uco_prod ?? 0),
                    'iva' => (float) ($row->prbo_iva_porc ?? 0),
                    'stock' => (float) ($row->prbo_stock ?? 0),
                    'stock_minimo' => (float) ($row->prbo_stock_minimo ?? 0),
                    'stock_maximo' => (float) ($row->prbo_stock_maximo ?? 0),
                ];
            }
        }

        return $productos;
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
            ->flatMap(fn($conexion) => collect(SolicitudPagoResource::getEmpresasOptions($conexion))->keys()->map(fn($codigo) => $conexion . '|' . $codigo))
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

    public function getProductosPaginatedProperty(): LengthAwarePaginator
    {
        $productos = collect($this->productos);

        $productos = $this->applySearch($productos);
        $productos = $this->applySort($productos)->values();

        $page = $this->getPage();
        $items = $productos->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $productos->count(),
            $this->perPage,
            $page
        );
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

    protected function applySort(Collection $productos): Collection
    {
        if (! $this->sortField) {
            return $productos;
        }

        return $productos->sortBy(
            function (array $producto) {
                return match ($this->sortField) {
                    'stock' => (float) ($producto['stock'] ?? 0),
                    'precio' => (float) ($producto['precio'] ?? 0),
                    'empresa' => mb_strtolower($producto['empresa_nombre'] ?? ''),
                    'sucursal' => mb_strtolower($producto['sucursal_nombre'] ?? ''),
                    'bodega' => mb_strtolower($producto['bodega_nombre'] ?? ''),
                    'conexion' => mb_strtolower($producto['conexion_nombre'] ?? ''),
                    'codigo' => mb_strtolower($producto['producto_codigo'] ?? ''),
                    default => mb_strtolower($producto['producto_nombre'] ?? ''),
                };
            },
            descending: $this->sortDirection === 'desc'
        );
    }

    protected function applySearch(Collection $productos): Collection
    {
        $search = trim($this->search);

        if ($search === '') {
            return $productos;
        }

        $needle = mb_strtolower($search);

        return $productos->filter(function (array $producto) use ($needle) {
            $values = [
                $producto['producto_codigo'] ?? '',
                $producto['producto_nombre'] ?? '',
                $producto['producto_descripcion'] ?? '',
                $producto['empresa_nombre'] ?? '',
                $producto['sucursal_nombre'] ?? '',
                $producto['bodega_nombre'] ?? '',
                $producto['conexion_nombre'] ?? '',
            ];

            foreach ($values as $value) {
                if ($value !== '' && str_contains(mb_strtolower((string) $value), $needle)) {
                    return true;
                }
            }

            return false;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->form([
                    TextInput::make('descripcion_reporte')
                        ->label('Descripción del reporte')
                        ->placeholder('Reporte consolidado de productos')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(fn(array $data) => $this->exportPdf($data['descripcion_reporte'] ?? '')),
        ];
    }

    protected function exportPdf(string $descripcionReporte)
    {
        $productos = $this->applySort($this->applySearch(collect($this->productos)))->values();

        if ($productos->isEmpty()) {
            Notification::make()
                ->title('No hay productos para exportar')
                ->warning()
                ->send();

            return null;
        }

        return response()->streamDownload(function () use ($descripcionReporte, $productos) {
            echo Pdf::loadView('pdfs.reporte-consolidado-productos', [
                'productos' => $productos->all(),
                'totalProductos' => $productos->count(),
                'descripcionReporte' => $descripcionReporte,
                'usuario' => Auth::user()?->name,
            ])->setPaper('a4', 'landscape')->stream();
        }, 'reporte-consolidado-productos.pdf');
    }
}
