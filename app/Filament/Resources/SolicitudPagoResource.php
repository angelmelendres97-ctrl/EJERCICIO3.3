<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudPagoResource\Pages;
use App\Filament\Resources\SolicitudPagoResource\RelationManagers\DetallesRelationManager;
use App\Models\Empresa;
use App\Models\SolicitudPago;
use App\Models\SolicitudPagoDetalle;
use App\Models\SolicitudPagoAdjunto;
use App\Services\SolicitudPagoReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Actions\StaticAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Pages\SolicitudPagoFacturas;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

class SolicitudPagoResource extends Resource
{
    private const ADJUNTOS_REQUERIDOS = [
        'Solicitud de pago',
        'Solicitud de pago aprobada',
        'Otros',
    ];

    protected static ?string $model = SolicitudPago::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Solicitudes de Pago y Aprobaciones';

    public static function isEstadoAprobado(?string $estado): bool
    {
        return in_array(strtoupper((string) $estado), [
            'APROBADA',
            strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA),
            strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA),
        ], true);
    }

    public static function getRepeaterItemIndex(Component $component): ?int
    {
        $statePath = $component->getContainer()?->getStatePath();

        if (! $statePath) {
            return null;
        }

        if (preg_match('/\.(\d+)(?:\.|$)/', $statePath, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    public static function getExternalConnectionName(int $empresaId): ?string
    {
        $empresa = Empresa::find($empresaId);
        if (! $empresa || ! $empresa->status_conexion) {
            return null;
        }

        $connectionName = 'external_db_' . $empresaId;

        if (! Config::has("database.connections.{$connectionName}")) {
            $dbConfig = [
                'driver' => $empresa->motor,
                'host' => $empresa->host,
                'port' => $empresa->puerto,
                'database' => $empresa->nombre_base,
                'username' => $empresa->usuario,
                'password' => $empresa->clave,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'options' => [
                    \PDO::ATTR_PERSISTENT => true,
                ],
            ];
            Config::set("database.connections.{$connectionName}", $dbConfig);
        }

        return $connectionName;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos de la Solicitud')
                ->schema([
                    Grid::make(12)
                        ->schema([
                            Select::make('id_empresa')
                                ->label('Conexión')
                                ->relationship('empresa', 'nombre_empresa')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->columnSpan(4)
                                ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                    $empresas = array_keys(self::getEmpresasOptions($state));
                                    $set('empresas_seleccionadas', $empresas);

                                    $sucursales = array_keys(self::getSucursalesOptions($state, $empresas));
                                    $set('sucursales_seleccionadas', $sucursales);

                                    $set('seleccionar_todos_proveedores', false);
                                    $set('proveedores_seleccionados', []);

                                    self::loadFacturas($set, $get);
                                }),

                            Select::make('empresas_seleccionadas')
                                ->label('Empresas')
                                ->multiple()
                                ->options(fn(Get $get) => self::getEmpresasOptions($get('id_empresa')))
                                ->live()
                                ->required()
                                ->default(fn(Get $get) => array_keys(self::getEmpresasOptions($get('id_empresa'))))
                                ->columnSpan(4)
                                ->afterStateUpdated(function (Set $set, Get $get, ?array $state) {
                                    $sucursales = self::getSucursalesOptions($get('id_empresa'), $state ?? []);
                                    $sucursalesKeys = array_keys($sucursales);
                                    $set('sucursales_seleccionadas', $sucursalesKeys);

                                    $set('seleccionar_todos_proveedores', false);
                                    $set('proveedores_seleccionados', []);
                                    self::loadFacturas($set, $get);
                                }),

                            Select::make('sucursales_seleccionadas')
                                ->label('Sucursales')
                                ->multiple()
                                ->options(fn(Get $get) => self::getSucursalesOptions($get('id_empresa'), $get('empresas_seleccionadas') ?? []))
                                ->live()
                                ->required()
                                ->default(fn(Get $get) => array_keys(self::getSucursalesOptions($get('id_empresa'), $get('empresas_seleccionadas') ?? [])))
                                ->columnSpan(4)
                                ->afterStateUpdated(function (Set $set, Get $get, ?array $state) {
                                    $set('seleccionar_todos_proveedores', false);
                                    $set('proveedores_seleccionados', []);

                                    self::loadFacturas($set, $get);
                                }),
                        ]),

                    Grid::make(12)
                        ->schema([
                            Select::make('proveedores_seleccionados')
                                ->label('Proveedores')
                                ->multiple()
                                ->options(fn(Get $get) => self::getProveedoresOptionsDisponibles(
                                    $get('id_empresa'),
                                    $get('empresas_seleccionadas') ?? [],
                                    $get('sucursales_seleccionadas') ?? []
                                ))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->default([])
                                ->placeholder('Seleccione uno o más proveedores')
                                ->columnSpan(9)
                                ->afterStateUpdated(function (Set $set, Get $get, ?array $state) {
                                    $options = self::getProveedoresOptions(
                                        $get('id_empresa'),
                                        $get('empresas_seleccionadas') ?? [],
                                        $get('sucursales_seleccionadas') ?? []
                                    );

                                    $set(
                                        'seleccionar_todos_proveedores',
                                        filled($state) && count($state ?? []) === count($options)
                                    );

                                    self::loadFacturas($set, $get);
                                }),

                            Toggle::make('seleccionar_todos_proveedores')
                                ->label('Seleccionar todos los proveedores')
                                ->inline(false)
                                ->columnSpan(3)
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, bool $state) {
                                    $proveedores = self::getProveedoresOptions(
                                        $get('id_empresa'),
                                        $get('empresas_seleccionadas') ?? [],
                                        $get('sucursales_seleccionadas') ?? []
                                    );

                                    $set('proveedores_seleccionados', $state ? array_keys($proveedores) : []);
                                    self::loadFacturas($set, $get);
                                }),
                        ]),

                    Grid::make(12)
                        ->schema([
                            DatePicker::make('fecha')
                                ->label('Fecha de Solicitud')
                                ->default(now())
                                ->required()
                                ->columnSpan(4),

                            Select::make('tipo_solicitud')
                                ->label('Tipo de solicitud')
                                ->options([
                                    'Pago de Facturas' => 'Pago de Facturas',
                                    'Reembolso' => 'Reembolso',
                                ])
                                ->default('Pago de Facturas')
                                ->required()
                                ->columnSpan(4),

                            Textarea::make('motivo')
                                ->label('Motivo')
                                ->rows(2)
                                ->columnSpan(4),
                        ]),
                ])
                ->columns(1),

            Section::make('')
                ->hidden(fn(Page $livewire) => $livewire instanceof ViewRecord)
                ->schema([
                    // Nivel EMPRESA
                    Repeater::make('facturas_disponibles')
                        ->label('')
                        ->schema([
                            Section::make(fn(array $state): string => $state['empresa_nombre'] ?? 'Empresa')
                                ->schema([
                                    Toggle::make('seleccionar_empresa')
                                        ->extraAttributes(['class' => 'text-xs'])
                                        ->label('Seleccionar todas las facturas de la empresa')
                                        ->inline() // misma línea
                                        ->live()
                                        ->columnSpanFull()
                                        ->dehydrated(false)
                                        ->afterStateUpdated(function (Set $set, Get $get, bool $state) {
                                            $set('sucursales', collect($get('sucursales') ?? [])->map(function ($sucursal) use ($state) {
                                                $sucursal['seleccionar_sucursal'] = $state;

                                                $sucursal['proveedores'] = collect($sucursal['proveedores'] ?? [])->map(function ($proveedor) use ($state) {
                                                    // Por coherencia, mantenemos este campo aunque no haya toggle visible
                                                    $proveedor['seleccionar_proveedor'] = $state;

                                                    $proveedor['facturas'] = collect($proveedor['facturas'] ?? [])->map(function ($factura) use ($state) {
                                                        $factura['seleccionado'] = $state; // aquí se marcan/desmarcan todas
                                                        return $factura;
                                                    })->all();

                                                    return $proveedor;
                                                })->all();

                                                return $sucursal;
                                            })->all());

                                            self::syncSelectionFlags($set, $get);
                                        }),


                                    // Nivel SUCURSAL
                                    Repeater::make('sucursales')
                                        ->label('')
                                        ->columnSpanFull()
                                        ->schema([
                                            Section::make(fn(array $state): string => 'SUCURSAL: ' . strtoupper($state['sucursal_nombre'] ?? 'Sucursal'))
                                                ->schema([
                                                    Toggle::make('seleccionar_sucursal')
                                                        ->columnSpanFull()
                                                        ->label('Seleccionar todas las facturas de la sucursal')
                                                        ->inline(true)
                                                        ->live()
                                                        ->dehydrated(false)
                                                        ->afterStateUpdated(function (Set $set, Get $get, bool $state) {
                                                            $set('proveedores', collect($get('proveedores') ?? [])->map(function ($proveedor) use ($state) {
                                                                $proveedor['seleccionar_proveedor'] = $state;
                                                                $proveedor['facturas'] = collect($proveedor['facturas'] ?? [])->map(function ($factura) use ($state) {
                                                                    $factura['seleccionado'] = $state;
                                                                    return $factura;
                                                                })->all();

                                                                return $proveedor;
                                                            })->all());

                                                            self::syncSelectionFlags($set, $get);
                                                        }),


                                                    // Nivel PROVEEDOR
                                                    Repeater::make('proveedores')
                                                        ->label('')
                                                        ->columnSpanFull()
                                                        ->schema([
                                                            Section::make(function (array $state): string {
                                                                $nombre = $state['proveedor_nombre'] ?? 'Proveedor';
                                                                $ruc    = $state['proveedor_ruc'] ?? null;

                                                                return $ruc
                                                                    ? 'Proveedor: ' . $nombre . ' (' . $ruc . ')'
                                                                    : 'Proveedor: ' . $nombre;
                                                            })
                                                                ->schema([


                                                                    // Nivel FACTURAS
                                                                    Repeater::make('facturas')
                                                                        ->label('Facturas')
                                                                        ->columnSpanFull()
                                                                        ->schema([
                                                                            Toggle::make('seleccionado')
                                                                                ->label('Seleccionar')
                                                                                ->inline(false)
                                                                                ->default(false)
                                                                                ->live()
                                                                                ->afterStateUpdated(fn(Set $set, Get $get) => self::syncSelectionFlags($set, $get))
                                                                                ->columnSpan(2),

                                                                            TextInput::make('numero')
                                                                                ->label('N° Factura')
                                                                                ->disabled()
                                                                                ->dehydrated()
                                                                                ->columnSpan(3),

                                                                            DatePicker::make('fecha_emision')
                                                                                ->label('Emisión')
                                                                                ->disabled()
                                                                                ->dehydrated()
                                                                                ->columnSpan(3),

                                                                            DatePicker::make('fecha_vencimiento')
                                                                                ->label('Vence')
                                                                                ->disabled()
                                                                                ->dehydrated()
                                                                                ->columnSpan(2),

                                                                            TextInput::make('saldo')
                                                                                ->label('Saldo')
                                                                                ->numeric()
                                                                                ->disabled()
                                                                                ->dehydrated()
                                                                                ->columnSpan(2),
                                                                        ])
                                                                        ->columns(12)
                                                                        ->disableItemCreation()
                                                                        ->disableItemDeletion()
                                                                        ->disableItemMovement(),
                                                                ])
                                                                ->columns(12)
                                                                ->collapsible(),
                                                        ])
                                                        ->disableItemCreation()
                                                        ->disableItemDeletion()
                                                        ->disableItemMovement(),
                                                ])
                                                ->columns(12)
                                                ->collapsible(),
                                        ])
                                        ->disableItemCreation()
                                        ->disableItemDeletion()
                                        ->disableItemMovement(),
                                ])
                                ->columns(12)
                                ->collapsible(),
                        ])
                        ->default([])
                        ->disableItemCreation()
                        ->disableItemDeletion()
                        ->disableItemMovement(),

                    // RESUMEN
                    Placeholder::make('resumen_facturas')
                        ->label('RESUMEN DE FACTURAS SELECCIONADAS')
                        ->content(function (Get $get) {
                            $empresas = collect($get('facturas_disponibles') ?? []);

                            // Aplanar facturas seleccionadas desde la estructura anidada
                            $facturas = $empresas->flatMap(function ($empresa) {
                                $empresaNombre = $empresa['empresa_nombre'] ?? 'Empresa';

                                return collect($empresa['sucursales'] ?? [])->flatMap(function ($sucursal) use ($empresaNombre) {
                                    $sucursalNombre = $sucursal['sucursal_nombre'] ?? 'Sucursal';

                                    return collect($sucursal['proveedores'] ?? [])->flatMap(function ($proveedor) use ($empresaNombre, $sucursalNombre) {
                                        $proveedorNombre = $proveedor['proveedor_nombre'] ?? 'Proveedor';

                                        return collect($proveedor['facturas'] ?? [])->map(function ($factura) use ($empresaNombre, $sucursalNombre, $proveedorNombre) {
                                            return array_merge($factura, [
                                                'empresa_nombre'   => $empresaNombre,
                                                'sucursal_nombre'  => $sucursalNombre,
                                                'proveedor_nombre' => $proveedorNombre,
                                            ]);
                                        });
                                    });
                                });
                            });

                            $seleccionadas = $facturas->where('seleccionado', true);

                            if ($seleccionadas->isEmpty()) {
                                return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">No hay facturas seleccionadas.</p>');
                            }

                            $agrupadas = $seleccionadas
                                ->groupBy('empresa_nombre')
                                ->map(function ($items) {
                                    $sucursales = $items
                                        ->groupBy('sucursal_nombre')
                                        ->map(function ($facturasSucursal) {
                                            $facturasOrdenadas = $facturasSucursal
                                                ->sortBy('fecha_emision')
                                                ->values();

                                            return [
                                                'total' => $facturasOrdenadas->sum(fn($factura) => (float) ($factura['saldo'] ?? 0)),
                                                'facturas' => $facturasOrdenadas,
                                            ];
                                        });

                                    return [
                                        'total'      => $items->sum(fn($factura) => (float) ($factura['saldo'] ?? 0)),
                                        'sucursales' => $sucursales,
                                    ];
                                });

                            $totalGeneral = $seleccionadas->sum(fn($factura) => (float) ($factura['saldo'] ?? 0));

                            $resumen = '<div class="space-y-4">';

                            foreach ($agrupadas as $empresaNombre => $infoEmpresa) {
                                $resumen .= '<div class="rounded-lg border border-gray-200 bg-white shadow-sm">';
                                $resumen .= '<div class="flex items-center justify-between border-b border-gray-100 bg-slate-50 px-4 py-3">';
                                $resumen .= '<div class="font-semibold text-slate-800">' . e($empresaNombre) . '</div>';
                                $resumen .= '<div class="text-sm font-semibold text-blue-700">Total empresa: $' . number_format($infoEmpresa['total'], 2, '.', ',') . '</div>';
                                $resumen .= '</div>';

                                $resumen .= '<div class="divide-y divide-gray-100">';
                                foreach ($infoEmpresa['sucursales'] as $sucursalNombre => $infoSucursal) {
                                    $resumen .= '<div class="p-4 space-y-2">';
                                    $resumen .= '<div class="flex items-center justify-between text-sm font-medium text-slate-700">';
                                    $resumen .= '<span>Sucursal: ' . e($sucursalNombre) . '</span>';
                                    $resumen .= '<span class="text-amber-700">Subtotal: $' . number_format($infoSucursal['total'], 2, '.', ',') . '</span>';
                                    $resumen .= '</div>';

                                    $resumen .= '<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">';
                                    foreach ($infoSucursal['facturas'] as $factura) {
                                        $fechaEmision = filled($factura['fecha_emision'] ?? null)
                                            ? Carbon::parse($factura['fecha_emision'])->format('Y-m-d')
                                            : '';
                                        $fechaVencimiento = filled($factura['fecha_vencimiento'] ?? null)
                                            ? Carbon::parse($factura['fecha_vencimiento'])->format('Y-m-d')
                                            : '';

                                        $resumen .= '<div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">';
                                        $resumen .= '<div class="flex items-center justify-between text-xs text-slate-500">';
                                        $resumen .= '<span>Factura ' . e($factura['numero'] ?? '') . '</span>';
                                        $resumen .= '<span class="font-semibold text-emerald-700 text-lg">$' . number_format((float) ($factura['saldo'] ?? 0), 2, '.', ',') . '</span>';
                                        $resumen .= '</div>';
                                        $resumen .= '<div class="mt-2 space-y-1 text-sm text-slate-700">';
                                        $resumen .= '<div class="font-semibold">' . e($factura['proveedor_nombre'] ?? '') . '</div>';
                                        $resumen .= '<div class="flex items-center justify-between text-xs text-slate-500">';
                                        $resumen .= '<span>Emisión: ' . e($fechaEmision) . '</span>';
                                        $resumen .= '<span>Vence: ' . e($fechaVencimiento) . '</span>';
                                        $resumen .= '</div>';
                                        $resumen .= '</div>';
                                        $resumen .= '</div>';
                                    }
                                    $resumen .= '</div>';
                                    $resumen .= '</div>';
                                }
                                $resumen .= '</div>';
                                $resumen .= '</div>';
                            }

                            $resumen .= '<div class="rounded-md bg-amber-50 px-4 py-3 text-right text-base font-extrabold text-amber-800">TOTAL GENERAL: $' . number_format($totalGeneral, 2, '.', ',') . '</div>';
                            $resumen .= '</div>';

                            return new \Illuminate\Support\HtmlString($resumen);
                        }),
                ])
                ->columns(1),

        ]);
    }

    public static function getEmpresasOptions(?int $empresaId): array
    {
        if (! $empresaId) {
            return [];
        }

        $connectionName = self::getExternalConnectionName($empresaId);

        if (! $connectionName) {
            return [];
        }

        try {
            return DB::connection($connectionName)
                ->table('saeempr')
                ->pluck('empr_nom_empr', 'empr_cod_empr')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function getSucursalesOptions(?int $empresaId, array $empresas): array
    {
        if (! $empresaId || empty($empresas)) {
            return [];
        }

        $connectionName = self::getExternalConnectionName($empresaId);

        if (! $connectionName) {
            return [];
        }

        try {
            return DB::connection($connectionName)
                ->table('saesucu')
                ->whereIn('sucu_cod_empr', $empresas)
                ->pluck('sucu_nom_sucu', 'sucu_cod_sucu')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function getProveedoresOptions(?int $empresaId, array $empresas, array $sucursales): array
    {
        if (! $empresaId || empty($empresas)) {
            return [];
        }

        $connectionName = self::getExternalConnectionName($empresaId);
        if (! $connectionName) {
            return [];
        }

        $empresaOptions  = self::getEmpresasOptions($empresaId);
        $sucursalOptions = self::getSucursalesOptions($empresaId, $empresas);

        try {
            $proveedores = DB::connection($connectionName)
                ->table('saedmcp')
                ->join('saeclpv as prov', function ($join) {
                    $join->on('prov.clpv_cod_empr', '=', 'saedmcp.dmcp_cod_empr')
                        ->on('prov.clpv_cod_sucu', '=', 'saedmcp.dmcp_cod_sucu')
                        ->on('prov.clpv_cod_clpv', '=', 'saedmcp.clpv_cod_clpv');
                })
                ->whereIn('saedmcp.dmcp_cod_empr', $empresas)
                ->when(! empty($sucursales), fn($q) => $q->whereIn('saedmcp.dmcp_cod_sucu', $sucursales))
                ->select([
                    'saedmcp.dmcp_cod_empr',
                    'saedmcp.dmcp_cod_sucu',
                    'saedmcp.clpv_cod_clpv',
                    'prov.clpv_nom_clpv',
                    'prov.clpv_ruc_clpv',
                ])
                ->groupBy(
                    'saedmcp.dmcp_cod_empr',
                    'saedmcp.dmcp_cod_sucu',
                    'saedmcp.clpv_cod_clpv',
                    'prov.clpv_nom_clpv',
                    'prov.clpv_ruc_clpv',
                )
                ->orderBy('saedmcp.dmcp_cod_empr')
                ->orderBy('saedmcp.dmcp_cod_sucu')
                ->orderBy('prov.clpv_nom_clpv')
                ->get()
                ->mapWithKeys(function ($p) use ($empresaOptions, $sucursalOptions) {
                    $empresaNombre  = $empresaOptions[$p->dmcp_cod_empr] ?? $p->dmcp_cod_empr;
                    $sucursalNombre = $sucursalOptions[$p->dmcp_cod_sucu] ?? $p->dmcp_cod_sucu;

                    $key = $p->dmcp_cod_empr . '|' . $p->dmcp_cod_sucu . '|' . $p->clpv_cod_clpv;

                    $nombre = trim(($p->clpv_nom_clpv ?? $p->clpv_cod_clpv) . ' (' . ($p->clpv_ruc_clpv ?? '') . ')');

                    return [
                        $key => "{$nombre} - {$empresaNombre} / {$sucursalNombre}",
                    ];
                });
        } catch (\Throwable $e) {
            $proveedores = collect();
        }

        // Registrados (MISMA clave empr|sucu|prov)
        $proveedoresRegistrados = SolicitudPagoDetalle::query()
            ->where('erp_conexion', (string) $empresaId)
            ->when(! empty($empresas), fn($q) => $q->whereIn('erp_empresa_id', $empresas))
            ->when(! empty($sucursales), fn($q) => $q->whereIn('erp_sucursal', $sucursales))
            ->select(['erp_empresa_id', 'erp_sucursal', 'proveedor_codigo', 'proveedor_nombre'])
            ->get()
            ->mapWithKeys(fn($d) => [
                ($d->erp_empresa_id ?? '') . '|' . ($d->erp_sucursal ?? '') . '|' . $d->proveedor_codigo
                => $d->proveedor_nombre ?? $d->proveedor_codigo,
            ]);

        return $proveedores
            ->merge($proveedoresRegistrados)
            ->unique() // ok, porque la key ya es única
            ->all();
    }

    public static function getProveedoresBase(?int $empresaId, array $empresas, array $sucursales): array
    {
        if (! $empresaId || empty($empresas)) {
            return [];
        }

        $connectionName = self::getExternalConnectionName($empresaId);

        if (! $connectionName) {
            return [];
        }

        try {
            $proveedores = DB::connection($connectionName)
                ->table('saedmcp')
                ->join('saeclpv as prov', function ($join) {
                    $join->on('prov.clpv_cod_empr', '=', 'saedmcp.dmcp_cod_empr')
                        ->on('prov.clpv_cod_sucu', '=', 'saedmcp.dmcp_cod_sucu')
                        ->on('prov.clpv_cod_clpv', '=', 'saedmcp.clpv_cod_clpv');
                })
                ->whereIn('saedmcp.dmcp_cod_empr', $empresas)
                ->when(! empty($sucursales), fn($q) => $q->whereIn('saedmcp.dmcp_cod_sucu', $sucursales))
                ->select([
                    'saedmcp.dmcp_cod_empr',
                    'saedmcp.dmcp_cod_sucu',
                    'saedmcp.clpv_cod_clpv',
                    'prov.clpv_nom_clpv',
                    'prov.clpv_ruc_clpv',
                ])
                ->groupBy(
                    'saedmcp.dmcp_cod_empr',
                    'saedmcp.dmcp_cod_sucu',
                    'saedmcp.clpv_cod_clpv',
                    'prov.clpv_nom_clpv',
                    'prov.clpv_ruc_clpv',
                )
                ->orderBy('saedmcp.dmcp_cod_empr')
                ->orderBy('saedmcp.dmcp_cod_sucu')
                ->orderBy('prov.clpv_nom_clpv')
                ->get()
                ->mapWithKeys(function ($p) {
                    $key = $p->dmcp_cod_empr . '|' . $p->dmcp_cod_sucu . '|' . $p->clpv_cod_clpv;

                    return [
                        $key => [
                            'nombre' => $p->clpv_nom_clpv ?? $p->clpv_cod_clpv,
                            'ruc'    => $p->clpv_ruc_clpv ?? null,
                        ],
                    ];
                });
        } catch (\Throwable $e) {
            $proveedores = collect();
        }

        $proveedoresRegistrados = SolicitudPagoDetalle::query()
            ->where('erp_conexion', (string) $empresaId)
            ->when(! empty($empresas), fn($q) => $q->whereIn('erp_empresa_id', $empresas))
            ->when(! empty($sucursales), fn($q) => $q->whereIn('erp_sucursal', $sucursales))
            ->select([
                'erp_empresa_id',
                'erp_sucursal',
                'proveedor_codigo',
                'proveedor_nombre',
                'proveedor_ruc',
            ])
            ->get()
            ->mapWithKeys(fn($d) => [
                ($d->erp_empresa_id ?? '') . '|' . ($d->erp_sucursal ?? '') . '|' . $d->proveedor_codigo => [
                    'nombre' => $d->proveedor_nombre ?? $d->proveedor_codigo,
                    'ruc'    => $d->proveedor_ruc,
                ],
            ]);

        return $proveedores
            ->union($proveedoresRegistrados)
            ->all();
    }


    protected static function loadFacturas(Set $set, Get $get): void
    {
        $empresaId   = $get('id_empresa');
        $empresas    = $get('empresas_seleccionadas') ?? [];
        $sucursales  = $get('sucursales_seleccionadas') ?? [];
        $proveedoresSeleccionados = collect($get('proveedores_seleccionados') ?? [])
            ->map(fn(string $valor) => self::parseProveedorKey($valor))
            ->filter(fn($item) => filled($item['proveedor']))
            ->values()
            ->all();

        if (! $empresaId || empty($empresas) || empty($proveedoresSeleccionados)) {
            $set('facturas_disponibles', []);
            return;
        }

        $seleccionadasActuales = self::extractSelectedFacturaKeys($get('facturas_disponibles'));

        $facturas = self::buildFacturasDisponibles(
            $empresaId,
            $empresas,
            $sucursales,
            $proveedoresSeleccionados,
            $seleccionadasActuales
        );

        $set('facturas_disponibles', $facturas);
        self::syncSelectionFlags($set, $get, $facturas);
    }


    public static function getProveedoresOptionsDisponibles(?int $empresaId, array $empresas, array $sucursales): array
    {
        if (! $empresaId || empty($empresas)) {
            return [];
        }

        $connectionName = self::getExternalConnectionName($empresaId);
        if (! $connectionName) {
            return [];
        }

        // 1) Facturas ya registradas (de solicitudes NO anuladas/rechazadas)
        $registradas = SolicitudPagoDetalle::query()
            ->where('erp_conexion', (string) $empresaId)
            ->whereHas('solicitudPago', function ($q) {
                $q->whereNull('estado')
                    ->orWhereNotIn('estado', ['ANULADA', 'RECHAZADA']);
            })
            ->get(['erp_empresa_id', 'erp_sucursal', 'proveedor_codigo', 'numero_factura'])
            ->map(fn($d) => ($d->erp_empresa_id ?? '') . '|' . ($d->erp_sucursal ?? '') . '|' . ($d->proveedor_codigo ?? '') . '|' . ($d->numero_factura ?? ''))
            ->flip(); // set

        $empresaOptions  = self::getEmpresasOptions($empresaId);
        $sucursalOptions = self::getSucursalesOptions($empresaId, $empresas);

        // 2) Traer facturas abiertas del externo (saldo != 0) y luego excluir las registradas
        $q = DB::connection($connectionName)
            ->table('saedmcp')
            ->join('saeclpv as prov', function ($join) {
                $join->on('prov.clpv_cod_empr', '=', 'saedmcp.dmcp_cod_empr')
                    ->on('prov.clpv_cod_sucu', '=', 'saedmcp.dmcp_cod_sucu')
                    ->on('prov.clpv_cod_clpv', '=', 'saedmcp.clpv_cod_clpv');
            })
            ->whereIn('saedmcp.dmcp_cod_empr', $empresas)
            ->when(! empty($sucursales), fn($qq) => $qq->whereIn('saedmcp.dmcp_cod_sucu', $sucursales))
            ->where('saedmcp.dmcp_est_dcmp', '<>', 'AN')
            ->selectRaw('
            saedmcp.dmcp_cod_empr  as empr,
            saedmcp.dmcp_cod_sucu  as sucu,
            saedmcp.clpv_cod_clpv  as provcod,
            prov.clpv_nom_clpv     as provnom,
            prov.clpv_ruc_clpv     as provruc,
            saedmcp.dmcp_num_fac   as numfac,
            SUM(COALESCE(saedmcp.dcmp_deb_ml,0) - COALESCE(saedmcp.dcmp_cre_ml,0)) as saldo
        ')
            ->groupBy('empr', 'sucu', 'provcod', 'provnom', 'provruc', 'numfac')
            ->havingRaw('SUM(COALESCE(saedmcp.dcmp_deb_ml,0) - COALESCE(saedmcp.dcmp_cre_ml,0)) <> 0');

        $rows = $q->get();

        // 3) Dejar solo facturas NO registradas, y de ahí sacar proveedores únicos
        $proveedores = collect($rows)
            ->reject(function ($r) use ($registradas) {
                $k = $r->empr . '|' . $r->sucu . '|' . $r->provcod . '|' . $r->numfac;
                return $registradas->has($k);
            })
            ->groupBy(fn($r) => $r->empr . '|' . $r->sucu . '|' . $r->provcod)
            ->map(function ($items, $key) use ($empresaOptions, $sucursalOptions) {
                $r = $items->first();
                $empresaNombre  = $empresaOptions[$r->empr] ?? $r->empr;
                $sucursalNombre = $sucursalOptions[$r->sucu] ?? $r->sucu;

                $nombre = trim(($r->provnom ?? $r->provcod) . ' (' . ($r->provruc ?? '') . ')');

                return "{$nombre} - {$empresaNombre} / {$sucursalNombre}";
            })
            ->sort()
            ->all();

        return $proveedores;
    }



    /**
     * @param array<int, string> $selectedKeys
     * @return array<int, array<string, mixed>>
     */
    public static function buildFacturasDisponibles(int $empresaId, array $empresas, array $sucursales, array $proveedores, array $selectedKeys = []): array
    {
        $empresaOptions   = [];
        $sucursalOptions  = [];
        $proveedorOptions = self::getProveedoresOptions($empresaId, $empresas, $sucursales);
        $proveedoresBase  = self::getProveedoresBase($empresaId, $empresas, $sucursales);

        $detallesRegistrados = \App\Models\SolicitudPagoDetalle::query()
            ->where('erp_conexion', (string) $empresaId)
            ->when(
                fn($query) => $query->getModel()->relationLoaded('solicitudPago') === false,
                fn($query) => $query->with('solicitudPago'),
            )
            ->whereHas('solicitudPago', function ($q) {
                $q->whereNull('estado')
                    ->orWhereNotIn('estado', ['ANULADA', 'RECHAZADA']);
            })
            ->get(['erp_empresa_id', 'erp_sucursal', 'proveedor_codigo', 'numero_factura'])
            ->map(fn($detalle) => $detalle->erp_empresa_id . '|' . $detalle->erp_sucursal . '|' . $detalle->proveedor_codigo . '|' . $detalle->numero_factura)
            ->all();

        $agrupado = [];

        $connectionName = self::getExternalConnectionName($empresaId);

        if (! $connectionName) {
            return [];
        }

        $empresaOptions  = self::getEmpresasOptions($empresaId);
        $sucursalOptions = self::getSucursalesOptions($empresaId, $empresas);

        foreach ($empresas as $empresaCodigo) {
            foreach ($proveedores as $proveedorSeleccionado) {
                $provCodigo = $proveedorSeleccionado['proveedor'] ?? null;
                $sucursalFiltro = $proveedorSeleccionado['sucursal'] ?? null;

                if (! $provCodigo) {
                    continue;
                }

                try {
                    $query = DB::connection($connectionName)
                        ->table('saedmcp')
                        ->where('dmcp_cod_empr', $empresaCodigo)
                        ->where('clpv_cod_clpv', $provCodigo)
                        ->where('dmcp_est_dcmp', '<>', 'AN')
                        ->selectRaw('
                        dmcp_cod_empr,
                        dmcp_cod_sucu,
                        clpv_cod_clpv,
                        dmcp_num_fac,
                        dmcp_cod_mone,
                        MIN(dcmp_fec_emis) AS dcmp_fec_emis,
                        MAX(dmcp_fec_ven)  AS dcmp_fec_ven,
                        SUM(dcmp_deb_ml)   AS dcmp_deb_ml,
                        SUM(COALESCE(dcmp_deb_ml,0) - COALESCE(dcmp_cre_ml,0)) AS saldo
                    ')
                        ->groupBy('dmcp_cod_empr', 'dmcp_cod_sucu', 'clpv_cod_clpv', 'dmcp_num_fac', 'dmcp_cod_mone')
                        ->havingRaw('SUM(COALESCE(dcmp_deb_ml,0) - COALESCE(dcmp_cre_ml,0)) <> 0')
                        ->orderBy('dcmp_fec_emis');

                    if ($sucursalFiltro) {
                        $query->where('dmcp_cod_sucu', $sucursalFiltro);
                    } elseif (! empty($sucursales)) {
                        $query->whereIn('dmcp_cod_sucu', $sucursales);
                    }

                    $facturasDb = $query->get();

                    foreach ($facturasDb as $factura) {
                        $empresaCodigo   = $factura->dmcp_cod_empr;
                        $sucursalCodigo  = $factura->dmcp_cod_sucu;
                        $provCodigo      = $factura->clpv_cod_clpv;
                        $numeroFactura   = $factura->dmcp_num_fac;

                        $keyDetalle = $empresaCodigo . '|' . $sucursalCodigo . '|' . $provCodigo . '|' . $numeroFactura;
                        $seleccionada = in_array($keyDetalle, $selectedKeys, true);

                        if (in_array($keyDetalle, $detallesRegistrados, true) && ! $seleccionada) {
                            continue;
                        }

                        $proveedorKey    = $empresaCodigo . '|' . $sucursalCodigo . '|' . $provCodigo;
                        $proveedorBase   = $proveedoresBase[$proveedorKey] ?? null;

                        $empresaNombre   = $empresaOptions[$empresaCodigo]   ?? $empresaCodigo;
                        $sucursalNombre  = $sucursalOptions[$sucursalCodigo] ?? $sucursalCodigo;
                        $proveedorNombre = $proveedorBase['nombre'] ?? ($proveedorOptions[$proveedorKey] ?? $provCodigo);
                        $proveedorRuc    = $proveedorBase['ruc'] ?? null;


                        if (! isset($agrupado[$empresaCodigo])) {
                            $agrupado[$empresaCodigo] = [
                                'empresa_codigo' => $empresaCodigo,
                                'empresa_nombre' => $empresaNombre,
                                'sucursales'     => [],
                            ];
                        }

                        if (! isset($agrupado[$empresaCodigo]['sucursales'][$sucursalCodigo])) {
                            $agrupado[$empresaCodigo]['sucursales'][$sucursalCodigo] = [
                                'sucursal_codigo' => $sucursalCodigo,
                                'sucursal_nombre' => $sucursalNombre,
                                'proveedores'     => [],
                            ];
                        }

                        if (! isset($agrupado[$empresaCodigo]['sucursales'][$sucursalCodigo]['proveedores'][$provCodigo])) {
                            $agrupado[$empresaCodigo]['sucursales'][$sucursalCodigo]['proveedores'][$provCodigo] = [
                                'proveedor_codigo' => $provCodigo,
                                'proveedor_nombre' => $proveedorNombre,
                                'proveedor_ruc'    => $proveedorRuc,
                                'facturas'         => [],
                            ];
                        }

                        $agrupado[$empresaCodigo]['sucursales'][$sucursalCodigo]['proveedores'][$provCodigo]['facturas'][] = [
                            'erp_empresa_id'    => $empresaCodigo,
                            'erp_sucursal'      => $sucursalCodigo,
                            'empresa_nombre'    => $empresaNombre,
                            'sucursal_nombre'   => $sucursalNombre,
                            'proveedor_codigo'  => $provCodigo,
                            'proveedor_nombre'  => $proveedorNombre,
                            'proveedor_ruc'     => $proveedorRuc,
                            'numero'            => $numeroFactura,
                            'fecha_emision'     => $factura->dcmp_fec_emis,
                            'fecha_vencimiento' => $factura->dcmp_fec_ven,
                            'monto'             => (float) $factura->dcmp_deb_ml,
                            'saldo'             => (float) $factura->saldo,
                            'seleccionado'      => $seleccionada,
                        ];
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        $facturasFinal = collect($agrupado)
            ->map(function ($empresa) {
                $empresa['sucursales'] = collect($empresa['sucursales'])
                    ->map(function ($sucursal) {
                        $sucursal['proveedores'] = collect($sucursal['proveedores'])
                            ->map(function ($proveedor) {
                                $proveedor['facturas'] = array_values($proveedor['facturas']);
                                return $proveedor;
                            })
                            ->values()
                            ->all();

                        return $sucursal;
                    })
                    ->values()
                    ->all();

                return $empresa;
            })
            ->values()
            ->all();

        return self::applySelectionFlags($facturasFinal);
    }

    /**
     * @return array<int, string>
     */
    protected static function extractSelectedFacturaKeys(?array $facturasDisponibles): array
    {
        return collect($facturasDisponibles ?? [])
            ->flatMap(fn($empresa) => collect($empresa['sucursales'] ?? []))
            ->flatMap(fn($sucursal) => collect($sucursal['proveedores'] ?? []))
            ->flatMap(fn($proveedor) => collect($proveedor['facturas'] ?? []))
            ->filter(fn($factura) => $factura['seleccionado'] ?? false)
            ->map(fn($factura) => self::getFacturaKey($factura))
            ->values()
            ->all();
    }

    public static function getFacturaKey(array $factura): string
    {
        $raw = ($factura['erp_empresa_id'] ?? '') . '|' . ($factura['erp_sucursal'] ?? '') . '|' . ($factura['proveedor_codigo'] ?? '') . '|' . ($factura['numero'] ?? '') . '|' . ($factura['proveedor_ruc'] ?? '');

        return hash('sha256', $raw);
    }

    /**
     * @param array<int, array<string, mixed>> $empresas
     * @return array<int, array<string, mixed>>
     */
    protected static function applySelectionFlags(array $empresas): array
    {
        return collect($empresas)
            ->map(function ($empresa) {
                $empresa['sucursales'] = collect($empresa['sucursales'] ?? [])
                    ->map(function ($sucursal) {
                        $sucursal['proveedores'] = collect($sucursal['proveedores'] ?? [])
                            ->map(function ($proveedor) {
                                $allFacturasSeleccionadas = collect($proveedor['facturas'] ?? [])
                                    ->every(fn($factura) => $factura['seleccionado'] ?? false);

                                $proveedor['seleccionar_proveedor'] = $allFacturasSeleccionadas;

                                return $proveedor;
                            })
                            ->values()
                            ->all();

                        $sucursal['seleccionar_sucursal'] = collect($sucursal['proveedores'])
                            ->every(fn($proveedor) => $proveedor['seleccionar_proveedor'] ?? false);

                        return $sucursal;
                    })
                    ->values()
                    ->all();

                $empresa['seleccionar_empresa'] = collect($empresa['sucursales'])
                    ->every(fn($sucursal) => $sucursal['seleccionar_sucursal'] ?? false);

                return $empresa;
            })
            ->values()
            ->all();
    }

    protected static function syncSelectionFlags(Set $set, Get $get, ?array $facturas = null): void
    {
        $facturas ??= $get('facturas_disponibles') ?? [];

        $set('facturas_disponibles', self::applySelectionFlags($facturas));
    }

    public static function parseProveedorKey(string $valor): array
    {
        $partes = explode('|', $valor);

        if (count($partes) >= 3) {
            return [
                'empresa'   => $partes[0] ?? null,
                'sucursal'  => $partes[1] ?? null,
                'proveedor' => $partes[2] ?? null,
            ];
        }

        if (count($partes) === 2) {
            return [
                'empresa'   => null,
                'sucursal'  => $partes[0] ?? null,
                'proveedor' => $partes[1] ?? null,
            ];
        }

        return [
            'empresa'   => null,
            'sucursal'  => null,
            'proveedor' => $partes[0] ?? null,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            //poner al inicio las acciones
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)

            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->recordAction('verFacturas')
            ->columns([
                TextColumn::make('id')
                    ->label('Num')
                    ->sortable()
                    ->searchable(),
                // OCULTA POR DEFECTO (se puede activar)
                TextColumn::make('empresa.nombre_empresa')
                    ->label('Conexión')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creadoPor.name')
                    ->label('Creado por')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),                // VISIBLES POR DEFECTO
                /* TextColumn::make('fecha')
                    ->date()
                    ->label('Fecha')
                    ->sortable(), */

                TextColumn::make('tipo_solicitud')
                    ->label('Tipo')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('monto_estimado')
                    ->money('USD')
                    ->label('Total')
                    ->sortable(),


                TextColumn::make('monto_aprobado')
                    ->money('USD')
                    ->label('Abono aprobado')
                    ->sortable(),
                TextColumn::make('monto_utilizado')
                    ->money('USD')
                    ->label('Abono utilizado')
                    ->sortable(),


                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(40),

                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        return match (strtoupper($state)) {
                            'APROBADA' => 'Aprobada',
                            strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA) => 'Solicitud Aprobada Anulada',
                            strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA) => 'Aprobada',
                            'ANULADA' => 'Anulada',
                            'BORRADOR' => 'Borrador',
                            default => $state,
                        };
                    })
                    ->color(fn(string $state) => match (strtoupper($state)) {
                        'BORRADOR' => 'warning',
                        'APROBADA' => 'success',
                        strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA) => 'danger',
                        strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA) => 'success',
                        'ANULADA' => 'danger',
                        default => 'gray',
                    })
                    ->label('Estado')
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('verFacturas')
                        ->label('Ver facturas')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalContent(function (SolicitudPago $record): \Illuminate\Contracts\View\View {
                            $empresaOptions = [];
                            $sucursalOptions = [];

                            $conexiones = $record->detalles->pluck('erp_conexion')->filter()->unique()->all();
                            foreach ($conexiones as $conexion) {
                                $conexionId = (int) $conexion;
                                $empresaOptions[$conexionId] = self::getEmpresasOptions($conexionId);

                                $empresasSeleccionadas = $record->detalles
                                    ->where('erp_conexion', (string) $conexion)
                                    ->pluck('erp_empresa_id')
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->all();

                                $sucursalOptions[$conexionId] = self::getSucursalesOptions($conexionId, $empresasSeleccionadas);
                            }

                            return view(
                                'filament.resources.solicitud-pago-resource.actions.ver-facturas',
                                [
                                    'detalles' => $record->detalles,
                                    'solicitud' => $record,
                                    'empresaOptions' => $empresaOptions,
                                    'sucursalOptions' => $sucursalOptions,
                                ],
                            );
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),

                    Tables\Actions\Action::make('adjuntos')
                        ->label('Adjuntos')
                        ->icon('heroicon-o-paper-clip')
                        ->color('success')
                        // ✅ Ya NO la ocultes en aprobadas
                        // ->hidden(fn(SolicitudPago $record) => strtoupper($record->estado ?? '') === 'APROBADA')

                        ->form([
                            Repeater::make('adjuntos')
                                ->label('Archivos adjuntos')
                                ->disabled(fn(SolicitudPago $record) => self::isEstadoAprobado($record->estado ?? ''))
                                ->defaultItems(count(self::ADJUNTOS_REQUERIDOS))
                                ->minItems(count(self::ADJUNTOS_REQUERIDOS))
                                ->maxItems(5)
                                ->schema([
                                    Hidden::make('id'),

                                    TextInput::make('nombre')
                                        ->label('Nombre')
                                        ->required()
                                        ->dehydrated()
                                        ->disabled(function (SolicitudPago $record, Component $component): bool {
                                            if (self::isEstadoAprobado($record->estado ?? '')) {
                                                return true;
                                            }

                                            $index = self::getRepeaterItemIndex($component);

                                            return $index !== null && $index < count(self::ADJUNTOS_REQUERIDOS);
                                        }),

                                    FileUpload::make('archivo')
                                        ->label('Archivo')
                                        ->disk('public')
                                        ->maxSize(10240)
                                        ->directory('solicitud-pagos/adjuntos')
                                        ->preserveFilenames()
                                        ->required(function (SolicitudPago $record, Component $component): bool {
                                            if (self::isEstadoAprobado($record->estado ?? '')) {
                                                return false;
                                            }

                                            $index = self::getRepeaterItemIndex($component);

                                            return $index !== null && $index < count(self::ADJUNTOS_REQUERIDOS);
                                        })
                                        // ✅ Que se pueda abrir/descargar siempre (ver)
                                        ->downloadable()
                                        ->openable()
                                        ->disabled(fn(SolicitudPago $record) => self::isEstadoAprobado($record->estado ?? '')),
                                ])
                                // ✅ No permitir agregar items en aprobada
                                ->addable(fn(SolicitudPago $record) => ! self::isEstadoAprobado($record->estado ?? ''))
                                // ✅ No permitir borrar/reordenar en aprobada
                                ->deletable(fn(SolicitudPago $record) => ! self::isEstadoAprobado($record->estado ?? ''))
                                ->reorderable(false)
                                ->createItemButtonLabel('Agregar adjunto')
                                ->columns(1),
                        ])

                        ->fillForm(function (SolicitudPago $record) {
                            $requeridos = self::ADJUNTOS_REQUERIDOS;
                            return [
                                'adjuntos' => collect($record->adjuntos)
                                    ->map(fn(SolicitudPagoAdjunto $adjunto) => [
                                        'id' => $adjunto->id,
                                        'nombre' => $adjunto->nombre,
                                        'archivo' => $adjunto->archivo,
                                    ])
                                    ->pipe(function ($adjuntos) use ($requeridos) {
                                        $porNombre = $adjuntos->keyBy('nombre');

                                        $base = collect($requeridos)->map(function (string $nombre) use ($porNombre) {
                                            return $porNombre->get($nombre, [
                                                'id' => null,
                                                'nombre' => $nombre,
                                                'archivo' => null,
                                            ]);
                                        });

                                        $extras = $adjuntos
                                            ->reject(fn(array $adjunto) => in_array($adjunto['nombre'] ?? '', $requeridos, true))
                                            ->values();

                                        return $base->merge($extras)->take(5)->values()->all();
                                    }),
                            ];
                        })

                        // ✅ Evita guardar si está aprobada
                        ->action(function (SolicitudPago $record, array $data) {
                            if (self::isEstadoAprobado($record->estado ?? '')) {
                                return; // solo lectura
                            }

                            // ... tu lógica actual de guardar/actualizar/eliminar adjuntos ...
                            $adjuntos = collect($data['adjuntos'] ?? []);
                            $requeridos = self::ADJUNTOS_REQUERIDOS;

                            if ($adjuntos->count() > 5) {
                                throw ValidationException::withMessages([
                                    'adjuntos' => 'Solo se permiten hasta 5 adjuntos.',
                                ]);
                            }

                            foreach ($requeridos as $index => $nombre) {
                                $adjunto = $adjuntos->get($index);

                                if (! $adjunto || ($adjunto['nombre'] ?? '') !== $nombre) {
                                    throw ValidationException::withMessages([
                                        "adjuntos.$index.nombre" => 'Debe mantener los tipos de adjunto requeridos en las primeras posiciones.',
                                    ]);
                                }

                                if (empty($adjunto['archivo'])) {
                                    throw ValidationException::withMessages([
                                        "adjuntos.$index.archivo" => 'Debe cargar un archivo para este adjunto.',
                                    ]);
                                }
                            }

                            $existentes = $record->adjuntos()->get()->keyBy('id');
                            $mantenidos = [];

                            $adjuntos->each(function ($adjunto) use ($record, $existentes, &$mantenidos) {
                                $adjuntoId = $adjunto['id'] ?? null;
                                $archivoNuevo = $adjunto['archivo'] ?? null;

                                if ($adjuntoId && $existentes->has($adjuntoId)) {
                                    $registro = $existentes[$adjuntoId];
                                    $rutaAnterior = $registro->archivo;

                                    $registro->fill([
                                        'nombre'  => $adjunto['nombre'] ?? $registro->nombre,
                                        'archivo' => $archivoNuevo ?: $rutaAnterior,
                                    ])->save();

                                    if ($archivoNuevo && $archivoNuevo !== $rutaAnterior) {
                                        Storage::disk('public')->delete($rutaAnterior);
                                    }

                                    $mantenidos[] = $adjuntoId;
                                } else {
                                    $nuevo = $record->adjuntos()->create([
                                        'nombre'  => $adjunto['nombre'] ?? 'Archivo',
                                        'archivo' => $archivoNuevo,
                                    ]);

                                    $mantenidos[] = $nuevo->id;
                                }
                            });

                            $existentes
                                ->reject(fn($adjunto) => in_array($adjunto->id, $mantenidos, true))
                                ->each(function (SolicitudPagoAdjunto $adjunto) {
                                    Storage::disk('public')->delete($adjunto->archivo);
                                    $adjunto->delete();
                                });
                        })

                        // ✅ Si está aprobada, no mostrar botón Guardar, solo “Cerrar”
                        ->modalSubmitAction(function (StaticAction $action, SolicitudPago $record) {
                            if (self::isEstadoAprobado($record->estado ?? '')) {
                                return $action->hidden();
                            }

                            return $action->label('Guardar adjuntos');
                        })
                        ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),

                    Tables\Actions\Action::make('descargarPdf')
                        ->label('Solicitud PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('danger')
                        ->visible(fn(SolicitudPago $record) => in_array(strtoupper($record->estado ?? ''), [
                            'APROBADA',
                            strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA),
                            strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA),
                            'BORRADOR',
                            'PENDIENTE',
                        ], true))
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportPdf($record)),
                    Tables\Actions\Action::make('descargarPdfDetallado')
                        ->label('Solicitud PDF Detallado')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('danger')
                        ->visible(fn(SolicitudPago $record) => in_array(strtoupper($record->estado ?? ''), [
                            'APROBADA',
                            strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA),
                            strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA),
                            'BORRADOR',
                            'PENDIENTE',
                        ], true))
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportDetailedPdf($record)),

                   /*  Tables\Actions\Action::make('descargarExcel')
                        ->label('Solicitud EXCEL')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->visible(fn(SolicitudPago $record) => in_array(strtoupper($record->estado ?? ''), [
                            'APROBADA',
                            strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA),
                            'BORRADOR',
                            'PENDIENTE',
                        ], true))
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportExcel($record)),
                    Tables\Actions\Action::make('descargarExcelDetallado')
                        ->label('Solicitud EXCEL Detallado')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->visible(fn(SolicitudPago $record) => in_array(strtoupper($record->estado ?? ''), [
                            'APROBADA',
                            strtoupper(SolicitudPago::ESTADO_SOLICITUD_COMPLETADA),
                            'BORRADOR',
                            'PENDIENTE',
                        ], true))
                        ->action(fn(SolicitudPago $record) => app(SolicitudPagoReportService::class)->exportDetailedExcel($record)), */

                    Tables\Actions\Action::make('gestionar')
                        ->label('Asignar abonos facturas')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->visible(fn(SolicitudPago $record) => strtoupper($record->estado ?? '') === 'BORRADOR')
                        ->url(fn(SolicitudPago $record) => SolicitudPagoFacturas::getUrl([
                            'record' => $record,
                            'mode' => 'edit',
                        ])),

                    Tables\Actions\Action::make('anular')
                        ->label('Anular')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(SolicitudPago $record) => strtoupper($record->estado ?? '') === 'BORRADOR')
                        ->action(fn(SolicitudPago $record) => $record->update(['estado' => 'ANULADA'])),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray')
                    ->button(), // opcional: hace que se vea como botón “Acciones”
            ])

            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudPagos::route('/'),
            'create' => Pages\CreateSolicitudPago::route('/create'),
            'view' => Pages\ViewSolicitudPago::route('/{record}'),
            'edit' => Pages\EditSolicitudPago::route('/{record}/edit'),
        ];
    }
}
