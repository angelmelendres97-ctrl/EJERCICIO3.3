<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProformaResource\Pages;
use App\Models\Proforma;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\StaticAction;
use App\Models\Proveedores;
class ProformaResource extends Resource
{
    protected static ?string $model = Proforma::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Compras';

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
                                $set('detalles', []);
                                $set('amdg_id_empresa', null);
                                $set('amdg_id_sucursal', null);
                            })
                            ->required(),

                        Forms\Components\Select::make('amdg_id_empresa')
                            ->label('Empresa')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                if (!$empresaId)
                                    return [];
                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName)
                                    return [];

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
                            ->required(),

                        Forms\Components\Select::make('amdg_id_sucursal')
                            ->label('Sucursal')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $amdgIdEmpresaCode = $get('amdg_id_empresa');
                                if (!$empresaId || !$amdgIdEmpresaCode)
                                    return [];
                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName)
                                    return [];

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
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_pedido')
                            ->label('Fecha Proforma')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('info_proveedor')
                            ->label('Proveedor (Opcional)')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');
                                if (!$empresaId || !$amdg_id_empresa)
                                    return [];
                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName)
                                    return [];

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
                            ->columnSpan(2)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (empty($state)) {
                                    $set('identificacion', null);
                                    $set('proveedor', null);
                                    return;
                                }
                                $empresaId = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');
                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName)
                                    return;

                                $data = DB::connection($connectionName)
                                    ->table('saeclpv')
                                    ->where('clpv_cod_clpv', $state)
                                    ->where('clpv_cod_empr', $amdg_id_empresa)
                                    ->select('clpv_ruc_clpv', 'clpv_nom_clpv')
                                    ->first();

                                if ($data) {
                                    $set('identificacion', $data->clpv_ruc_clpv);
                                    $set('id_proveedor', $state);
                                    $set('proveedor', $data->clpv_nom_clpv);
                                }
                            }),

                        Forms\Components\Hidden::make('proveedor'),
                        Forms\Components\Hidden::make('id_proveedor'),
                        Forms\Components\TextInput::make('identificacion')
                            ->label('RUC / ID')
                            ->readOnly(),

                        Forms\Components\Textarea::make('observaciones')
                            ->columnSpanFull(),
                    ])->columns(4),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Forms\Components\Checkbox::make('es_manual')
                                            ->label('Ingreso manual')
                                            ->dehydrated(false)
                                            ->live()
                                            ->columnSpanFull() // ✅ arriba ocupando toda la fila
                                            ->afterStateHydrated(function (Forms\Components\Checkbox $component, Get $get): void {
                                                $isManual = empty($get('id_bodega')) && !empty($get('codigo_producto')) && !empty($get('producto'));
                                                $component->state($isManual);
                                            })
                                            ->afterStateUpdated(function (Set $set, Get $get, ?bool $state): void {
                                                // lo dejamos para el punto 2
                                            })
                                            ->afterStateUpdated(function (Set $set, Get $get, ?bool $state): void {

                                                if ($state) {
                                                    // ✅ “quemado” para cumplir NOT NULL
                                                    $set('id_bodega', 1); // <- AJUSTA si tu bodega manual no es 1
                                                }

                                                $set('codigo_producto', null);
                                                $set('producto', null);
                                                $set('costo', 0);
                                                $set('impuesto', 0);

                                                self::updateTotals($get, $set);
                                            }),

                                        // ✅ BODEGA cuando es MANUAL (fija)
                                        Forms\Components\Select::make('id_bodega')
                                            ->label('Bodega')
                                            ->options([1 => 'INGRESO MANUAL'])
                                            ->disabled()
                                            ->dehydrated(true) // ✅ se guarda en BD
                                            ->visible(fn(Get $get) => (bool) $get('es_manual'))
                                            ->required()
                                            ->columnSpan(3),

                                        // ✅ BODEGA normal cuando NO es manual
                                        Forms\Components\Select::make('id_bodega')
                                            ->label('Bodega')
                                            ->options(function (Get $get) {
                                                $empresaId = $get('../../id_empresa');
                                                $amdg_id_empresa = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');

                                                if (!$empresaId || !$amdg_id_empresa) return [];
                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName) return [];

                                                return DB::connection($connectionName)
                                                    ->table('saebode')
                                                    ->join('saesubo', 'subo_cod_bode', '=', 'bode_cod_bode')
                                                    ->where('subo_cod_empr', $amdg_id_empresa)
                                                    ->where('bode_cod_empr', $amdg_id_empresa)
                                                    ->where('subo_cod_sucu', $amdg_id_sucursal)
                                                    ->pluck('bode_nom_bode', 'bode_cod_bode')
                                                    ->all();
                                            })
                                            ->required(fn(Get $get) => !(bool) $get('es_manual'))
                                            ->live()
                                            ->visible(fn(Get $get) => !(bool) $get('es_manual'))
                                            ->columnSpan(3),

                                        Forms\Components\Select::make('producto_inventario')
                                            ->label('Producto inventario')
                                            ->options(function (Get $get) {
                                                $empresaId = $get('../../id_empresa');
                                                $amdg_id_empresa = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');
                                                $id_bodega = $get('id_bodega');
                                                if (!$empresaId || !$id_bodega)
                                                    return [];
                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName)
                                                    return [];

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
                                            ->required(fn(Get $get) => !(bool) $get('es_manual'))
                                            ->visible(fn(Get $get) => !(bool) $get('es_manual'))
                                            ->dehydrated(false)
                                            ->columnSpan(4)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                                if (!$state) {
                                                    $set('producto', null);
                                                    $set('codigo_producto', null);
                                                    $set('costo', 0);
                                                    $set('impuesto', 0);
                                                    self::updateTotals($get, $set);
                                                    return;
                                                }
                                                // Fetch product details
                                                $empresaId = $get('../../id_empresa');
                                                $amdg_id_empresa = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');
                                                $id_bodega = $get('id_bodega');
                                                $connectionName = self::getExternalConnectionName($empresaId);

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
                                                    $set('codigo_producto', $state);
                                                    $set('costo', number_format($data->prbo_uco_prod, 6, '.', ''));
                                                    $set('impuesto', 0); // Implicit 0 since requested to hide
                                                    $set('producto', $data->prod_nom_prod);
                                                    self::updateTotals($get, $set);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('codigo_producto')
                                            ->label('Código')
                                            ->required(fn(Get $get) => (bool) $get('es_manual'))
                                            ->readOnly(fn(Get $get) => !(bool) $get('es_manual'))
                                            ->live(debounce: 300)
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                self::updateTotals($get, $set);
                                            })
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('producto')
                                            ->label('Descripción')
                                            ->required(fn(Get $get) => (bool) $get('es_manual'))
                                            ->readOnly(fn(Get $get) => !(bool) $get('es_manual'))
                                            ->live(debounce: 300)
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->live(debounce: 300)
                                            ->required()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                self::updateTotals($get, $set);
                                            }),



                                        Forms\Components\TextInput::make('costo')
                                            ->label('Costo Ref.')
                                            ->numeric()
                                            ->prefix('$')
                                            ->live(debounce: 300)
                                            ->columnSpan(2)
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                self::updateTotals($get, $set);
                                            }),

                                        Forms\Components\Hidden::make('descuento')
                                            ->default(0),

                                        Forms\Components\Hidden::make('impuesto')
                                            ->default(0),

                                        Forms\Components\TextInput::make('total')
                                            ->label('Total')
                                            ->numeric()
                                            ->prefix('$')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                    ]),

                // Hidden fields for totals
                Forms\Components\Hidden::make('subtotal')->default(0)->dehydrated(),
                Forms\Components\Hidden::make('total_descuento')->default(0)->dehydrated(),
                Forms\Components\Hidden::make('total_impuesto')->default(0)->dehydrated(),
                Forms\Components\Hidden::make('total')->default(0)->dehydrated(),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Placeholder::make('lbl_total')
                            ->content(fn(Get $get) => '$' . number_format(floatval($get('total')), 2))
                            ->label('Total General')
                            ->extraAttributes(['class' => 'text-xl font-bold text-right']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Núm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amdg_id_empresa')
                    ->label('Empresa')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state)
                            return $state;
                        $connectionName = self::getExternalConnectionName($record->id_empresa);
                        if (!$connectionName)
                            return $state;
                        try {
                            return DB::connection($connectionName)
                                ->table('saeempr')
                                ->where('empr_cod_empr', $state)
                                ->value('empr_nom_empr') ?? $state;
                        } catch (\Exception $e) {
                            return $state;
                        }
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('amdg_id_sucursal')
                    ->label('Sucursal')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state)
                            return $state;
                        $connectionName = self::getExternalConnectionName($record->id_empresa);
                        if (!$connectionName)
                            return $state;
                        try {
                            return DB::connection($connectionName)
                                ->table('saesucu')
                                ->where('sucu_cod_empr', $record->amdg_id_empresa)
                                ->where('sucu_cod_sucu', $state)
                                ->value('sucu_nom_sucu') ?? $state;
                        } catch (\Exception $e) {
                            return $state;
                        }
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_pedido')->date()->label('Fecha'),
                Tables\Columns\TextColumn::make('proveedor')->searchable()->placeholder('N/A'),
                Tables\Columns\TextColumn::make('observaciones')->limit(30),
                Tables\Columns\TextColumn::make('total_calculated')
                    ->label('Total Ref.')
                    ->money('USD')
                    ->state(function (Proforma $record): float {
                        return $record->detalles->reduce(function ($carry, $detalle) {
                            return $carry + ($detalle->cantidad * $detalle->costo);
                        }, 0);
                    }),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'gray',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Anulada' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('ver_detalles')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle de proforma')
                    ->modalContent(fn(Proforma $record) => view('filament.resources.proforma-resource.widgets.detalle-proforma-modal', [
                        'record' => $record->loadMissing('detalles'),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
                    ->modalWidth('7xl'),
                Tables\Actions\EditAction::make()
                    ->visible(fn(Proforma $record) => $record->estado === 'Pendiente'),
                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(Proforma $record) => $record->estado === 'Pendiente')
                    ->action(function (Proforma $record) {
                        $record->update(['estado' => 'Anulada']);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => self::userIsAdmin())
                    ->authorize(fn() => self::userIsAdmin()),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => self::userIsAdmin())
                        ->authorize(fn() => self::userIsAdmin()),
                ]),
            ]);
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
            'index' => Pages\ListProformas::route('/'),
            'create' => Pages\CreateProforma::route('/create'),
            'edit' => Pages\EditProforma::route('/{record}/edit'),
        ];
    }

    public static function userIsAdmin(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('ADMINISTRADOR') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return $record->estado === 'Pendiente';
    }

    public static function canDelete(Model $record): bool
    {
        return self::userIsAdmin();
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        // 1. Determine Context & Get Details

        $detalles = null;
        $isItemContext = false;

        // Check if we are inside an item (via relative path up)
        // If we are at 'detalles.uuid.cantidad', ../../ gets the whole 'detalles' array
        $up2 = $get('../../');
        if (is_array($up2)) {
            $detalles = $up2;
            $isItemContext = true;
        } else {
            // Maybe we are at root
            $detalles = $get('detalles');
        }

        // 2. Update Row Total (if in item context)
        if ($isItemContext) {
            $c = floatval($get('cantidad') ?? 0);
            $co = floatval($get('costo') ?? 0);
            $set('total', number_format($c * $co, 2, '.', ''));
        }

        if (!is_array($detalles)) {
            return;
        }

        // 3. Calculate Globals
        $subtotalGeneral = 0;
        $descuentoGeneral = 0;
        $impuestoGeneral = 0;

        foreach ($detalles as $detalle) {
            $cantidad = floatval(str_replace(',', '', $detalle['cantidad'] ?? 0));
            $costo = floatval(str_replace(',', '', $detalle['costo'] ?? 0));
            $descuento = floatval(str_replace(',', '', $detalle['descuento'] ?? 0));
            $porcentajeIva = floatval(str_replace(',', '', $detalle['impuesto'] ?? 0));

            $subtotalItem = $cantidad * $costo;
            $valorIva = $subtotalItem * ($porcentajeIva / 100);

            $subtotalGeneral += $subtotalItem;
            $descuentoGeneral += $descuento;
            $impuestoGeneral += $valorIva;
        }

        $totalGeneral = ($subtotalGeneral - $descuentoGeneral) + $impuestoGeneral;

        // 4. Set Globals
        // If item context (detalles.uuid.field), '../../' is 'detalles' array.
        // 'subtotal' is a sibling of 'detalles'.
        // So we need to go up from 'detalles' array -> Parent.
        // In Filament state paths:
        // Field: detalles.uuid.field
        // ../ : detalles.uuid
        // ../../ : detalles (value)
        // ../../../ : Root container (where 'subtotal' lives)

        $prefix = $isItemContext ? '../../../' : '';

        $set($prefix . 'subtotal', number_format($subtotalGeneral, 2, '.', ''));
        $set($prefix . 'total_descuento', number_format($descuentoGeneral, 2, '.', ''));
        $set($prefix . 'total_impuesto', number_format($impuestoGeneral, 2, '.', ''));
        $set($prefix . 'total', number_format($totalGeneral, 2, '.', ''));
    }
}
