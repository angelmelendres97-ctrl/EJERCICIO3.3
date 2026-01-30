<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenCompraResource\Pages;
use App\Models\OrdenCompra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Empresa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Set;
use App\Filament\Resources\ProveedorResource;
use App\Filament\Resources\ProductoResource;
use App\Models\Proveedores;
use App\Models\Producto;
use App\Services\ProveedorSyncService;
use App\Services\ProductoSyncService;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Get;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\View;
use Filament\Actions\StaticAction;
use Illuminate\Database\Eloquent\Model; // ESTA LÍNEA ES NECESARIA
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class OrdenCompraResource extends Resource
{
    protected static ?string $model = OrdenCompra::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function userIsAdmin(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('ADMINISTRADOR') ?? false;
    }

    public static function getExternalConnectionName(int $empresaId): ?string
    {
        $empresa = Empresa::find($empresaId);
        if (!$empresa || !$empresa->status_conexion) {
            return null;
        }

        $connectionName = 'external_db_' . $empresaId;

        if (!Config::has("database.connections.{$connectionName}")) {
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

    protected static function buildResumenTotales(array $detalles): array
    {
        $basePorIva = [];
        $descPorIva = [];
        $ivaPorIva = [];

        foreach ($detalles as $detalle) {
            $rate = (float) ($detalle['impuesto'] ?? 0);
            $rateKey = (string) $rate;

            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $costo = (float) ($detalle['costo'] ?? 0);
            $descuento = (float) ($detalle['descuento'] ?? 0);

            $base = $cantidad * $costo;

            $basePorIva[$rateKey] = ($basePorIva[$rateKey] ?? 0) + $base;
            $descPorIva[$rateKey] = ($descPorIva[$rateKey] ?? 0) + $descuento;

            $baseNeta = max(0, $base - $descuento);
            $ivaPorIva[$rateKey] = ($ivaPorIva[$rateKey] ?? 0) + ($baseNeta * ($rate / 100));
        }

        $tarifas = collect($basePorIva)
            ->filter(fn($base) => round((float) $base, 6) > 0)
            ->keys()
            ->map(fn($rate) => (float) $rate)
            ->values();

        $ordenPreferido = collect([15, 0, 5, 8, 18]);
        $tarifas = $ordenPreferido
            ->intersect($tarifas)
            ->merge($tarifas->diff($ordenPreferido)->sort())
            ->values();

        $subtotalGeneral = array_sum($basePorIva);
        $descuentoGeneral = array_sum($descPorIva);
        $ivaGeneral = array_sum($ivaPorIva);
        $totalGeneral = $subtotalGeneral - $descuentoGeneral + $ivaGeneral;

        return [
            'basePorIva' => $basePorIva,
            'ivaPorIva' => $ivaPorIva,
            'tarifas' => $tarifas,
            'subtotalGeneral' => $subtotalGeneral,
            'descuentoGeneral' => $descuentoGeneral,
            'totalGeneral' => $totalGeneral,
        ];
    }

    public static function normalizePedidosImportados(array|string|null $pedidos): array
    {
        if (empty($pedidos)) {
            return [];
        }

        $lista = is_array($pedidos) ? $pedidos : preg_split('/\\s*,\\s*/', trim((string) $pedidos));

        return collect($lista)
            ->filter()
            ->map(fn($pedido) => (int) ltrim((string) $pedido, '0'))
            ->filter(fn($pedido) => $pedido > 0)
            ->values()
            ->all();
    }

    public static function formatPedidosImportados(array|string|null $pedidos): string
    {
        $normalizados = self::normalizePedidosImportados($pedidos);

        return implode(', ', array_map(
            fn($pedi) => str_pad((string) $pedi, 8, '0', STR_PAD_LEFT),
            $normalizados
        ));
    }

    protected static function calculateTotals(array $detalles): array
    {
        $subtotalGeneral = 0;
        $descuentoGeneral = 0;
        $impuestoGeneral = 0;

        foreach ($detalles as $detalle) {
            $cantidad = floatval($detalle['cantidad'] ?? 0);
            $costo = floatval($detalle['costo'] ?? 0);
            $descuento = floatval($detalle['descuento'] ?? 0);
            $porcentajeIva = floatval($detalle['impuesto'] ?? 0);

            $subtotalItem = $cantidad * $costo;
            $baseNeta = max(0, $subtotalItem - $descuento);
            $impuestoGeneral += $baseNeta * ($porcentajeIva / 100);
            $subtotalGeneral += $subtotalItem;
            $descuentoGeneral += $descuento;
        }

        $totalGeneral = ($subtotalGeneral - $descuentoGeneral) + $impuestoGeneral;

        return [
            'subtotal' => number_format($subtotalGeneral, 2, '.', ''),
            'total_descuento' => number_format($descuentoGeneral, 2, '.', ''),
            'total_impuesto' => number_format($impuestoGeneral, 2, '.', ''),
            'total' => number_format($totalGeneral, 2, '.', ''),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Conexión y Empresa')

                    ->schema([
                        Forms\Components\Select::make('id_empresa')
                            ->label('Conexión')
                            ->relationship('empresa', 'nombre_empresa')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('pedidos_importados', null);
                                $set('detalles', []);
                            })

                            ->required(),

                        Forms\Components\Select::make('amdg_id_empresa')
                            ->label('Empresa')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                if (!$empresaId) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saeempr')
                                        ->pluck('empr_nom_empr', 'empr_cod_empr')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('pedidos_importados', null);
                                $set('detalles', []);
                            })

                            ->required(),

                        Forms\Components\Select::make('amdg_id_sucursal')
                            ->label('Sucursal')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $amdgIdEmpresaCode = $get('amdg_id_empresa');

                                if (!$empresaId || !$amdgIdEmpresaCode) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saesucu')
                                        ->where('sucu_cod_empr', $amdgIdEmpresaCode)
                                        ->pluck('sucu_nom_sucu', 'sucu_cod_sucu')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('pedidos_importados', null);
                                $set('detalles', []);
                            })

                            ->required(),
                    ])->columns(3),

                /*
                |--------------------------------------------------------------------------
                | ESTE MODAL SÍ SE REACTIVA (Importar desde Pedido)
                |--------------------------------------------------------------------------
                */
                Forms\Components\Section::make('Información Presupuesto')
                    ->headerActions([
                        Action::make('importar_pedido')
                            ->label('Importar desde Pedido')
                            ->icon('heroicon-o-magnifying-glass')

                            ->modalContent(function (Get $get) {
                                $id_empresa = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');
                                $amdg_id_sucursal = $get('amdg_id_sucursal');
                                $pedidos_importados = self::formatPedidosImportados($get('pedidos_importados'));

                                return view('livewire.buscar-pedidos-compra-container', compact(
                                    'id_empresa',
                                    'amdg_id_empresa',
                                    'amdg_id_sucursal',
                                    'pedidos_importados'
                                ));
                            })
                            ->modalHeading('Buscar Pedidos de Compra para Importar')
                            ->modalSubmitAction(false)
                            ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
                            ->visible(fn(Get $get, $livewire) => $livewire instanceof Pages\CreateOrdenCompra
                                && !empty($get('id_empresa'))
                                && !empty($get('amdg_id_empresa'))
                                && !empty($get('amdg_id_sucursal')))
                    ])
                    ->schema([

                        Forms\Components\Select::make('pedidos_importados')
                            ->label('Pedidos Importados')
                            ->multiple()
                            ->options(fn(Get $get) => collect(self::normalizePedidosImportados($get('pedidos_importados')))
                                ->mapWithKeys(fn($pedido) => [$pedido => str_pad((string) $pedido, 8, '0', STR_PAD_LEFT)])
                                ->all())
                            ->formatStateUsing(fn($state) => self::normalizePedidosImportados($state))
                            ->dehydrateStateUsing(fn($state) => self::formatPedidosImportados($state))
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $pedidosSeleccionados = self::normalizePedidosImportados($state);
                                $detalles = $get('detalles') ?? [];
                                $detallesFiltrados = array_values(array_filter(
                                    $detalles,
                                    fn($detalle) => empty($detalle['pedido_codigo'])
                                        || in_array((int) $detalle['pedido_codigo'], $pedidosSeleccionados, true)
                                ));

                                if (count($detallesFiltrados) !== count($detalles)) {
                                    $set('detalles', $detallesFiltrados);
                                }

                                $totales = self::calculateTotals($detallesFiltrados);
                                $set('subtotal', $totales['subtotal']);
                                $set('total_descuento', $totales['total_descuento']);
                                $set('total_impuesto', $totales['total_impuesto']);
                                $set('total', $totales['total']);
                            })
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('uso_compra')
                            ->label('Para Uso De:')
                            ->required()
                            ->maxLength(2550)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('solicitado_por')
                            ->label('Solicitado Por:')
                            ->required()
                            ->maxLength(2550)
                            ->columnSpan(2),

                        Forms\Components\Select::make(name: 'formato')
                            ->label('Formato:')
                            ->options(['F' => 'FACTURA', 'P' => 'PROFORMA'])
                            ->required(),

                        Forms\Components\TextInput::make('numero_factura_proforma')
                            ->label(fn(Get $get) => $get('formato') === 'P' ? 'Número de proforma' : 'Número de factura')
                            ->helperText('Ingrese el número según el formato seleccionado.')
                            ->visible(fn(Get $get) => filled($get('formato')))
                            ->maxLength(255)
                            ->extraAttributes([
                                'style' => 'max-width: 220px; white-space: normal; word-break: break-word;',
                            ]),

                        Forms\Components\Select::make(name: 'tipo_oc')
                            ->label('Tipo Orden Compra:')
                            ->options([
                                'REEMB' => 'REEMBOLSO',
                                'COMPRA' => 'COMPRA',
                                'PAGO' => 'PAGO',
                                'REGUL' => 'REGULARIZACIÓN',
                                'CAJAC' => 'CAJA CHICA'
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('nombre_reembolso')
                            ->label('Nombre de a quien se reembolsa')
                            ->visible(fn(Get $get) => $get('tipo_oc') === 'REEMB')
                            ->maxLength(255),

                        Forms\Components\Select::make(name: 'presupuesto')
                            ->label('Presupuesto:')
                            ->options(['AZ' => 'AZ', 'PB' => 'PB'])
                            ->required(),

                    ])->columns(4),

                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('info_proveedor')
                            ->label('Proveedor')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');

                                if (!$empresaId) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    $anulados = Proveedores::query()
                                        ->where('id_empresa', $empresaId)
                                        ->where('anulada', true)
                                        ->pluck('ruc')
                                        ->filter()
                                        ->all();

                                    return DB::connection($connectionName)
                                        ->table('saeclpv')
                                        ->where('clpv_cod_empr', $amdg_id_empresa)
                                        ->where('clpv_clopv_clpv', 'PV')
                                        ->when(!empty($anulados), function ($query) use ($anulados) {
                                            $query->whereNotIn('clpv_ruc_clpv', $anulados);
                                        })
                                        ->select([
                                            'clpv_cod_clpv',
                                            DB::raw("clpv_nom_clpv || ' (' || clpv_ruc_clpv || ')' AS proveedor_etiqueta")
                                        ])
                                        ->pluck('proveedor_etiqueta', 'clpv_cod_clpv')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->required()
                            ->columnSpan(2)
                            ->suffixAction(
                                Action::make('crear_proveedor_link')
                                    ->label('+')
                                    ->tooltip('Crear proveedor (abrir formulario)')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->url(fn(Get $get) => ProveedorResource::getUrl('create', [
                                        // Lo que OrdenCompra usa
                                        'id_empresa'        => $get('id_empresa'),
                                        'amdg_id_empresa'   => $get('amdg_id_empresa'),
                                        'amdg_id_sucursal'  => $get('amdg_id_sucursal'),
                                        'from_orden_compra' => true,

                                        // Lo que ProveedorResource suele usar (ojo tu inconsistencia admg/amdg)
                                        'admg_id_empresa'   => $get('amdg_id_empresa'),
                                        'admg_id_sucursal'  => $get('amdg_id_sucursal'),
                                    ]))
                                    ->openUrlInNewTab()


                            )
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (empty($state)) {
                                    $set('identificacion', null);
                                    return;
                                }

                                $empresaId = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    $set('identificacion', null);
                                    return;
                                }

                                $data = DB::connection($connectionName)
                                    ->table('saeclpv')
                                    ->where('clpv_cod_clpv', $state)
                                    ->where('clpv_cod_empr', $amdg_id_empresa)
                                    ->select('clpv_ruc_clpv', 'clpv_cod_clpv', 'clpv_nom_clpv')
                                    ->first();

                                if ($data) {
                                    $set('identificacion', $data->clpv_ruc_clpv);
                                    $set('id_proveedor', $data->clpv_cod_clpv);
                                    $set('proveedor', $data->clpv_nom_clpv);
                                } else {
                                    $set('identificacion', null);
                                    $set('id_proveedor', null);
                                    $set('proveedor', null);
                                }
                            }),

                        Forms\Components\Hidden::make('proveedor'),

                        Forms\Components\TextInput::make('id_proveedor')
                            ->numeric()
                            ->required()
                            ->label('ID Proveedor')
                            ->readOnly()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('identificacion')
                            ->maxLength(20)
                            ->label('Identificación (RUC/DNI)')
                            ->readOnly()
                            ->columnSpan(1),

                        Forms\Components\Select::make('trasanccion')
                            ->label('Transacción')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');
                                $amdg_id_sucursal = $get('amdg_id_sucursal');

                                if (!$empresaId) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saetran as t')
                                        ->join('saedefi as d', 't.tran_cod_tran', '=', 'd.defi_cod_tran')
                                        ->where('t.tran_cod_empr', $amdg_id_empresa)
                                        ->where('t.tran_cod_sucu', $amdg_id_sucursal)
                                        ->where('t.tran_cod_modu', 10)
                                        ->where('d.defi_cod_empr', $amdg_id_empresa)
                                        ->where('d.defi_tip_defi', '4')
                                        ->where('d.defi_cod_modu', 10)
                                        ->select([
                                            't.tran_des_tran',
                                            DB::raw("t.tran_des_tran || ' (' || t.tran_cod_tran || ')' AS transaccion_etiqueta")
                                        ])
                                        ->groupBy('t.tran_des_tran', 'transaccion_etiqueta')
                                        ->orderBy('transaccion_etiqueta', 'asc')
                                        ->pluck('transaccion_etiqueta', 't.tran_cod_tran')

                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->default('ORDEN DE COMPRA')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\DatePicker::make('fecha_pedido')
                            ->label('Fecha del Pedido')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('fecha_entrega')
                            ->label('Fecha de Entrega Estimada')
                            ->default(now()->addWeek())
                            ->required(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->maxLength(65535)
                            ->reactive()
                            ->afterStateUpdated(function (string|null $state, Set $set): void {
                                $set('observaciones', $state ? mb_strtoupper($state) : $state);
                            })
                            ->dehydrateStateUsing(fn(?string $state) => $state ? mb_strtoupper($state) : $state)
                            ->columnSpanFull(),
                    ])->columns(4),

                Forms\Components\Section::make('Productos')
                    ->headerActions([
                        Action::make('crear_producto_link')
                            ->label('Nuevo producto')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn(Get $get) => ProductoResource::getUrl('create', [
                                'id_empresa'        => $get('id_empresa'),
                                'amdg_id_empresa'   => $get('amdg_id_empresa'),
                                'amdg_id_sucursal'  => $get('amdg_id_sucursal'),
                            ]))
                            ->openUrlInNewTab(),
                    ])
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->schema([
                                Grid::make(14)
                                    ->schema([
                                        Forms\Components\Hidden::make('es_auxiliar'),
                                        Forms\Components\Hidden::make('es_servicio'),
                                        Forms\Components\Hidden::make('detalle'),
                                        Forms\Components\Hidden::make('pedido_codigo'),
                                        Forms\Components\Hidden::make('pedido_detalle_id'),

                                        Forms\Components\TextInput::make('detalle_pedido')
                                            ->label('Detalle del pedido')
                                            ->disabled()
                                            ->dehydrated(false) // no se guarda en tu tabla local
                                            ->afterStateHydrated(function (Set $set, Get $get, $state) {

                                                // Si ya tiene valor, no hacemos nada
                                                if (filled($state)) {
                                                    return;
                                                }

                                                $empresaId = $get('../../id_empresa');
                                                $amdgEmpresa = $get('../../amdg_id_empresa');
                                                $amdgSucu = $get('../../amdg_id_sucursal');

                                                $pedidoCodigo = $get('pedido_codigo');
                                                $pedidoDetalleId = $get('pedido_detalle_id');

                                                if (!$empresaId || !$amdgEmpresa || !$amdgSucu || !$pedidoCodigo || !$pedidoDetalleId) {
                                                    return;
                                                }

                                                $connectionName = \App\Filament\Resources\OrdenCompraResource::getExternalConnectionName((int) $empresaId);
                                                if (!$connectionName) {
                                                    return;
                                                }

                                                $schema = DB::connection($connectionName)->getSchemaBuilder();

                                                $cols = [
                                                    'dped_det_dped',
                                                    'dped_cod_auxiliar',
                                                    'dped_desc_auxiliar',
                                                    'dped_cod_prod',
                                                ];

                                                // Algunas bases tienen columnas con nombres distintos (o no las tienen)
                                                if ($schema->hasColumn('saedped', 'dped_desc_axiliar')) { // si existiera con x
                                                    $cols[] = 'dped_desc_axiliar';
                                                }

                                                $row = DB::connection($connectionName)
                                                    ->table('saedped')
                                                    ->where('dped_cod_pedi', (int) $pedidoCodigo)
                                                    ->where('dped_cod_dped', (int) $pedidoDetalleId)
                                                    ->select($cols)
                                                    ->first();

                                                if (!$row) {
                                                    return;
                                                }

                                                // Usa solo lo que exista (si no existe dped_desc_axiliar, quedará null)
                                                $descAux = $row->dped_desc_auxiliar
                                                    ?? ($schema->hasColumn('saedped', 'dped_desc_axiliar') ? ($row->dped_desc_axiliar ?? null) : null);

                                                // 1) Detalle del pedido
                                                $detallePedido = trim((string) ($row->dped_det_dped ?? ''));
                                                $set('detalle_pedido', $detallePedido !== '' ? $detallePedido : null);

                                                // 2) Auxiliar (helper)
                                                $esAuxiliar = !empty($row->dped_cod_auxiliar) || !empty($row->dped_desc_auxiliar) || !empty($row->dped_desc_axiliar);
                                                $set('es_auxiliar', $esAuxiliar);

                                                if ($esAuxiliar) {
                                                    $descAux = $row->dped_desc_auxiliar ?? $row->dped_desc_axiliar;
                                                    $auxTxt = trim(collect([
                                                        $row->dped_cod_auxiliar ? 'Código: ' . $row->dped_cod_auxiliar : null,
                                                        $descAux ? 'Nombre: ' . $descAux : null,
                                                    ])->filter()->implode(' | '));

                                                    $set('producto_auxiliar', $auxTxt ?: null);

                                                    // También deja listo el JSON "detalle" si lo usas en otras partes
                                                    $set('detalle', json_encode([
                                                        'codigo' => $row->dped_cod_auxiliar,
                                                        'descripcion' => $row->dped_det_dped,
                                                        'descripcion_auxiliar' => $descAux,
                                                    ], JSON_UNESCAPED_UNICODE));
                                                }

                                                // 3) Servicio (helper)
                                                $codigoProd = (string) ($row->dped_cod_prod ?? '');
                                                $esServicio = (bool) preg_match('/^SP[-\\s]*SP[-\\s]*SP/i', $codigoProd);
                                                $set('es_servicio', $esServicio);

                                                if ($esServicio) {
                                                    $srvTxt = trim(collect([
                                                        $codigoProd ? 'Código servicio: ' . $codigoProd : null,
                                                        $detallePedido ? 'Descripción: ' . $detallePedido : null,
                                                    ])->filter()->implode(' | '));

                                                    $set('producto_servicio', $srvTxt ?: null);
                                                }
                                            })
                                            ->visible(fn(Get $get) => filled($get('detalle_pedido')))
                                            ->columnSpan(['default' => 12, 'lg' => 14]),

                                        Forms\Components\TextInput::make('producto_auxiliar')
                                            ->label('Producto auxiliar')
                                            ->disabled()
                                            ->dehydrated(false)
                                            //->visible(fn(Get $get) => (bool) $get('es_auxiliar'))
                                            //nunca visible
                                            ->visible(false)

                                            ->columnSpan(['default' => 12, 'lg' => 14]),
                                        Forms\Components\Hidden::make('unidad'),

                                        Forms\Components\TextInput::make('producto_servicio')
                                            ->label('Servicio')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->visible(fn(Get $get) => (bool) $get('es_servicio'))
                                            ->columnSpan(['default' => 12, 'lg' => 14]),

                                        Forms\Components\Select::make('id_bodega')
                                            ->label('Bodega')
                                            ->placeholder('Seleccione')
                                            ->options(function (Get $get) {
                                                $empresaId = $get('../../id_empresa');
                                                $amdgIdEmpresaCode = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');

                                                if (!$empresaId || !$amdgIdEmpresaCode) {
                                                    return [];
                                                }

                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName) {
                                                    return [];
                                                }

                                                try {
                                                    return DB::connection($connectionName)
                                                        ->table('saebode')
                                                        ->join('saesubo', 'subo_cod_bode', '=', 'bode_cod_bode')
                                                        ->where('subo_cod_empr', $amdgIdEmpresaCode)
                                                        ->where('bode_cod_empr', $amdgIdEmpresaCode)
                                                        ->where('subo_cod_sucu', $amdg_id_sucursal)
                                                        ->pluck('bode_nom_bode', 'bode_cod_bode')
                                                        ->all();
                                                } catch (\Exception $e) {
                                                    return [];
                                                }
                                            })
                                            ->searchable()
                                            ->live()
                                            ->required()
                                            ->columnSpan(['default' => 12, 'lg' => 2]),

                                        Forms\Components\Select::make('codigo_producto')
                                            ->label('Producto')
                                            ->options(function (Get $get) {
                                                $empresaId = $get('../../id_empresa');
                                                $amdg_id_empresa = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');
                                                $id_bodega = $get('id_bodega');

                                                if (!$empresaId || !$id_bodega) {
                                                    return [];
                                                }

                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName) {
                                                    return [];
                                                }

                                                try {
                                                    return DB::connection($connectionName)
                                                        ->table('saeprod')
                                                        ->join('saeprbo', 'prbo_cod_prod', '=', 'prod_cod_prod')
                                                        ->where('prod_cod_sucu', $amdg_id_sucursal)
                                                        ->where('prod_cod_empr', $amdg_id_empresa)
                                                        ->where('prbo_cod_empr', $amdg_id_empresa)
                                                        ->where('prbo_cod_sucu', $amdg_id_sucursal)
                                                        ->where('prbo_cod_bode', $id_bodega)
                                                        ->select([
                                                            'prod_cod_prod',
                                                            DB::raw("prod_nom_prod || ' (' || prod_cod_prod || ')' AS productos_etiqueta")
                                                        ])
                                                        ->orderBy('productos_etiqueta', 'asc')
                                                        ->pluck('productos_etiqueta', 'prod_cod_prod');
                                                } catch (\Exception $e) {
                                                    return [];
                                                }
                                            })
                                            ->searchable()
                                            ->live()
                                            ->required()
                                            ->helperText(function (Get $get) {
                                                if ((bool) $get('es_auxiliar')) {
                                                    // Muestra el nombre que ya tienes en el campo "Producto auxiliar"
                                                    return $get('producto_auxiliar') ?: null;
                                                }

                                                if ((bool) $get('es_servicio')) {
                                                    // Muestra el nombre que ya tienes en el campo "Servicio"
                                                    return $get('producto_servicio') ?: null;
                                                }

                                                return null;
                                            })
                                            ->columnSpan(['default' => 12, 'lg' => 3])
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                                if (empty($state)) {
                                                    $set('producto', null);
                                                    $set('costo', 0);
                                                    $set('impuesto', 0);
                                                    return;
                                                }

                                                $empresaId = $get('../../id_empresa');
                                                $amdg_id_empresa = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');
                                                $id_bodega = $get('id_bodega');

                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName) {
                                                    return;
                                                }

                                                $data = DB::connection($connectionName)
                                                    ->table('saeprod')
                                                    ->join('saeprbo', 'prbo_cod_prod', '=', 'prod_cod_prod')
                                                    ->where('prod_cod_sucu', $amdg_id_sucursal)
                                                    ->where('prod_cod_empr', $amdg_id_empresa)
                                                    ->where('prbo_cod_empr', $amdg_id_empresa)
                                                    ->where('prbo_cod_sucu', $amdg_id_sucursal)
                                                    ->where('prbo_cod_bode', $id_bodega)
                                                    ->where('prbo_cod_prod', $state)
                                                    ->where('prod_cod_prod', $state)
                                                    ->select('prbo_uco_prod', 'prbo_iva_porc', 'prod_nom_prod')
                                                    ->first();

                                                if ($data) {
                                                    $set('costo', number_format($data->prbo_uco_prod, 6, '.', ''));
                                                    $impuesto = round($data->prbo_iva_porc, 2);
                                                    $set('impuesto', $impuesto == 8.0 ? 18 : $impuesto);
                                                    $set('producto', $data->prod_nom_prod . ' (' . $state . ')');
                                                }
                                            }),

                                        Forms\Components\Hidden::make('producto'),

                                        Forms\Components\TextInput::make('cantidad')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->default(1)
                                            ->columnSpan(['default' => 12, 'lg' => 1]),

                                        Forms\Components\TextInput::make('costo')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->prefix('$')
                                            ->columnSpan(['default' => 12, 'lg' => 2]),

                                        Forms\Components\TextInput::make('descuento')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->default(0)
                                            ->prefix('$')
                                            ->columnSpan(['default' => 12, 'lg' => 2]),

                                        Forms\Components\Placeholder::make('subtotal_linea')
                                            ->label('Subtotal')
                                            ->content(function (Get $get) {
                                                $cantidad = floatval($get('cantidad'));
                                                $costo = floatval($get('costo'));
                                                $subtotal = $cantidad * $costo;

                                                return '$' . number_format($subtotal, 4, '.', '');
                                            })
                                            ->columnSpan(['default' => 12, 'lg' => 1]),

                                        Forms\Components\Select::make('impuesto')
                                            ->options(['0' => '0%', '5' => '5%', '8' => '8%', '15' => '15%', '18' => '18%'])
                                            ->required()
                                            ->live()
                                            ->columnSpan(['default' => 12, 'lg' => 1]),

                                        /*    Forms\Components\Placeholder::make('valor_iva')
                                            ->label('IVA')
                                            ->content(function (Get $get) {
                                                $cantidad = floatval($get('cantidad'));
                                                $costo = floatval($get('costo'));
                                                $iva = floatval($get('impuesto'));
                                                $valorIva = ($cantidad * $costo) * ($iva / 100);
                                                return '$' . number_format($valorIva, 4, '.', '');
                                            })
                                            ->columnSpan(['default' => 12, 'lg' => 1]), */







                                        Forms\Components\Placeholder::make('total_linea')
                                            ->label('Total Item')
                                            ->content(function (Get $get) {
                                                $cantidad = floatval($get('cantidad'));
                                                $costo = floatval($get('costo'));
                                                $descuento = floatval($get('descuento'));
                                                $iva = floatval($get('impuesto'));

                                                $subtotal = $cantidad * $costo;
                                                $valorIva = $subtotal * ($iva / 100);
                                                $total = ($subtotal + $valorIva) - $descuento;

                                                return '$' . number_format($total, 4, '.', '');
                                            })
                                            ->columnSpan(['default' => 12, 'lg' => 2]),
                                    ]),
                            ])
                            ->relationship()
                            ->columns(1)
                            ->addActionLabel('Agregar Producto')
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $detalles = $get('detalles');
                                $subtotalGeneral = 0;
                                $descuentoGeneral = 0;
                                $impuestoGeneral = 0;

                                foreach ($detalles as $detalle) {
                                    $cantidad = floatval($detalle['cantidad'] ?? 0);
                                    $costo = floatval($detalle['costo'] ?? 0);
                                    $descuento = floatval($detalle['descuento'] ?? 0);
                                    $porcentajeIva = floatval($detalle['impuesto'] ?? 0);

                                    $subtotalItem = $cantidad * $costo;
                                    $baseNeta = max(0, $subtotalItem - $descuento);
                                    $valorIva = $baseNeta * ($porcentajeIva / 100);

                                    $subtotalGeneral += $subtotalItem;
                                    $descuentoGeneral += $descuento;
                                    $impuestoGeneral += $valorIva;
                                }

                                $totalGeneral = ($subtotalGeneral - $descuentoGeneral) + $impuestoGeneral;

                                $set('subtotal', number_format($subtotalGeneral, 2, '.', ''));
                                $set('total_descuento', number_format($descuentoGeneral, 2, '.', ''));
                                $set('total_impuesto', number_format($impuestoGeneral, 2, '.', ''));
                                $set('total', number_format($totalGeneral, 2, '.', ''));
                            })
                            ->live(),
                    ]),

                // Hidden fields for totals
                Forms\Components\Hidden::make('subtotal')->default(0),
                Forms\Components\Hidden::make('total_descuento')->default(0),
                Forms\Components\Hidden::make('total_impuesto')->default(0),
                Forms\Components\Hidden::make('total')->default(0),

                Section::make('Resumen de Totales')
                    ->schema([
                        View::make('resumen_totales')
                            ->view('filament.resources.orden-compra-resource.components.resumen-totales')
                            ->viewData(fn(Get $get) => self::buildResumenTotales($get('detalles') ?? [])),
                    ])->columns(1),

            ])->live()->extraAttributes([
                'onkeydown' => "if (event.key === 'Enter') { event.preventDefault(); return false; }"
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('Código OC')
                    ->searchable()
                    ->searchable(isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('empresa.nombre_empresa')
                    ->label('Conexión')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('formato')
                    ->label('Formato')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'P' => 'PROFORMA',
                        'F' => 'FACTURA',
                        default => 'Desconocido',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'P' => 'warning',
                        'F' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_factura_proforma')
                    ->label('N° Fact/Proforma')
                    ->searchable()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amdg_id_empresa')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function (object $record) {
                        $empresaId = $record->id_empresa;
                        $amdg_id_empresa = $record->amdg_id_empresa;

                        if (!$empresaId || !$amdg_id_empresa) {
                            return 'N/A (Faltan IDs)';
                        }

                        $connectionName = self::getExternalConnectionName($empresaId);

                        if (!$connectionName) {
                            return 'N/A (No hay conexión)';
                        }

                        try {
                            $empresa = DB::connection($connectionName)
                                ->table('saeempr')
                                ->where('empr_cod_empr', $amdg_id_empresa)
                                ->select(DB::raw(" '(' || empr_cod_empr || ') ' || empr_nom_empr AS nombre_empresa"))
                                ->first();

                            return $empresa->nombre_empresa ?? 'Empresa no encontrada';
                        } catch (\Exception $e) {
                            return 'Error DB';
                        }
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('presupuesto')
                    ->label('Presupuesto')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PB' => 'warning',
                        'AZ' => 'success',
                        default => 'gray',
                    })
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pedidos_importados')
                    ->label('Pedidos Importados')
                    ->searchable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('amdg_id_sucursal')
                    ->label('Sucursal')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function (object $record) {
                        $empresaId = $record->id_empresa;
                        $amdg_id_sucursal = $record->amdg_id_sucursal;

                        if (!$empresaId || !$amdg_id_sucursal) {
                            return 'N/A (Faltan IDs)';
                        }

                        $connectionName = self::getExternalConnectionName($empresaId);

                        if (!$connectionName) {
                            return 'N/A (No hay conexión)';
                        }

                        try {
                            $sucursal = DB::connection($connectionName)
                                ->table('saesucu')
                                ->where('sucu_cod_sucu', $amdg_id_sucursal)
                                ->select(DB::raw(" '(' || sucu_cod_sucu || ') ' || sucu_nom_sucu AS nombre_sucursal"))
                                ->first();

                            return $sucursal->nombre_sucursal ?? 'Sucursal no encontrada';
                        } catch (\Exception $e) {
                            return 'Error DB';
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('identificacion')
                    ->label('Identificación')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('proveedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Creado Por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('trasanccion')
                    ->label('Transacción')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->date()
                    ->label('F. Pedido')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fecha_entrega')
                    ->date()
                    ->label('F. Entrega')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('uso_compra')
                    ->label('Uso Compra')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('solicitado_por')
                    ->label('Solicitado Por')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),



                Tables\Columns\TextColumn::make('tipo_oc')
                    ->label('Tipo Orden Compra')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'REEMB' => 'REEMBOLSO',
                        'COMPRA' => 'COMPRA',
                        'PAGO' => 'PAGO',
                        'REGUL' => 'REGULARIZACIÓN',
                        'CAJAC' => 'CAJA CHICA',
                        default => 'Desconocido',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'REEMB' => 'warning',
                        'COMPRA' => 'success',
                        'PAGO' => 'info',
                        'REGUL' => 'danger',
                        'CAJAC' => 'primary',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),



                Tables\Columns\TextColumn::make('observaciones')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_descuento')
                    ->money('USD')
                    ->label('Descuento')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_impuesto')
                    ->money('USD')
                    ->label('Impuesto')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->label('Total')
                    ->sortable(),

                Tables\Columns\TextColumn::make('resumenDetalle.resumenPedido.descripcion')
                    ->label('Grupo Resumen')
                    ->getStateUsing(fn(OrdenCompra $record) => $record->resumenDetalle?->resumenPedido?->descripcion ?? 'Sin grupo de resumen')
                    ->toggleable(isToggledHiddenByDefault: true),


                Tables\Columns\TextColumn::make('anulada')
                    ->label('Anulada')
                    ->getStateUsing(fn($record) => $record->anulada ? 'SI' : 'NO')
                    ->badge() // opcional
                    ->color(fn($state) => $state === 'SI' ? 'danger' : 'success'),

            ])
            ->filters([
                //ademas selecionada por defecto

                Filter::make('mis_ordenes')
                    ->label('Mis órdenes')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereBelongsTo(auth()->user(), 'usuario')
                    )
                    ->default(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->visible(fn(OrdenCompra $record) => self::canEdit($record)),

                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn(OrdenCompra $record) => route('orden-compra.pdf', $record))
                    ->openUrlInNewTab(),

                /*
                |--------------------------------------------------------------------------
                | MODAL "Ver Productos" (DESACTIVADO TEMPORALMENTE)
                |--------------------------------------------------------------------------
                */
                // Tables\Actions\Action::make('verProductos')
                //     ->label('Ver Productos')
                //     ->icon('heroicon-o-eye')
                //     ->color('info')
                //     ->modalContent(fn(OrdenCompra $record): \Illuminate\Contracts\View\View => view(
                //         'filament.resources.orden-compra-resource.actions.ver-productos',
                //         ['detalles' => $record->detalles],
                //     ))
                //     ->modalSubmitAction(false)
                //     ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),


                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(OrdenCompra $record) => self::canAnnul($record))
                    ->action(function (OrdenCompra $record) {
                        $record->update(['anulada' => true]);

                        \App\Services\OrdenCompraSyncService::actualizarEstadoPedidos($record, null, '0');

                        Notification::make()
                            ->title('Orden de compra anulada')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->requiresConfirmation()
                    ->visible(fn(OrdenCompra $record) => self::userIsAdmin())
                    ->authorize(fn() => self::userIsAdmin())
                    ->action(function (OrdenCompra $record) {
                        \App\Services\OrdenCompraSyncService::eliminar($record);
                        \App\Services\OrdenCompraSyncService::actualizarEstadoPedidos($record, null, '0');
                        $record->delete();
                    })
                //->disabled(fn(OrdenCompra $record) => $record->anulada),

            ])
            ->bulkActions([
                // Acciones masivas
                //Accion masiva para eliminar registros

            ]);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('Actualizar')
            && !$record->anulada
            && (int) $record->id_usuario === (int) auth()->id();
    }

    public static function canAnnul(OrdenCompra $record): bool
    {
        return auth()->user()->can('Actualizar')
            && !$record->anulada
            && (int) $record->id_usuario === (int) auth()->id();
    }

    public static function canDelete(Model $record): bool
    {
        return self::userIsAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['usuario', 'resumenDetalle.resumenPedido']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenCompras::route('/'),
            'create' => Pages\CreateOrdenCompra::route('/create'),
            'edit' => Pages\EditOrdenCompra::route('/{record}/edit'),
        ];
    }
}
