<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AprobacionProformaResource\Pages;
use App\Models\Proforma;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AprobacionProformaResource extends Resource
{
    protected static ?string $model = Proforma::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Aprobación Proformas';

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
                            ->disabled() // Read-only for approval
                            ->required(),

                        Forms\Components\Select::make('amdg_id_empresa')
                            ->label('Empresa')
                            ->options(function (Get $get) {
                                // Keep options logic for display correctness, though disabled
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
                            ->disabled() // Read-only
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
                            ->disabled() // Read-only
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_pedido')
                            ->label('Fecha Proforma')
                            ->disabled() // Read-only
                            ->required(),

                        Forms\Components\Select::make('info_proveedor')
                            ->label('Proveedor')
                            ->options(function (Get $get) {
                                // Keep options logic
                                $empresaId = $get('id_empresa');
                                $amdg_id_empresa = $get('amdg_id_empresa');
                                if (!$empresaId || !$amdg_id_empresa)
                                    return [];
                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName)
                                    return [];

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saeclpv')
                                        ->where('clpv_cod_empr', $amdg_id_empresa)
                                        ->where('clpv_clopv_clpv', 'PV')
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
                            ->disabled() // Read-only
                            ->columnSpan(2),

                        Forms\Components\Hidden::make('proveedor'),
                        Forms\Components\Hidden::make('id_proveedor'),
                        Forms\Components\TextInput::make('identificacion')
                            ->label('RUC / ID')
                            ->disabled(), // Read-only

                        Forms\Components\Textarea::make('observaciones')
                            ->disabled() // Read-only
                            ->columnSpanFull(),
                    ])->columns(4),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Forms\Components\Select::make('id_bodega')
                                            ->label('Bodega')
                                            ->options(function (Get $get) {
                                                // Simplified options for display
                                                $empresaId = $get('../../id_empresa');
                                                $amdg_id_empresa = $get('../../amdg_id_empresa');
                                                $amdg_id_sucursal = $get('../../amdg_id_sucursal');
                                                if (!$empresaId || !$amdg_id_empresa)
                                                    return [];
                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName)
                                                    return [];
                                                try {
                                                    return DB::connection($connectionName)
                                                        ->table('saebode')
                                                        ->join('saesubo', 'subo_cod_bode', '=', 'bode_cod_bode')
                                                        ->where('subo_cod_empr', $amdg_id_empresa)
                                                        ->where('bode_cod_empr', $amdg_id_empresa)
                                                        ->where('subo_cod_sucu', $amdg_id_sucursal)
                                                        ->pluck('bode_nom_bode', 'bode_cod_bode')
                                                        ->all();
                                                } catch (\Exception $e) {
                                                    return [];
                                                }
                                            })
                                            ->disabled() // Read-only
                                            ->columnSpan(3),

                                        Forms\Components\Select::make('codigo_producto')
                                            ->label('Producto')
                                            ->options(function (Get $get) {
                                                // Keep options logic otherwise select shows ID
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
                                                        ->pluck('productos_etiqueta', 'prod_cod_prod');
                                                } catch (\Exception $e) {
                                                    return [];
                                                }
                                            })
                                            ->disabled() // Read-only
                                            ->columnSpan(4),

                                        Forms\Components\Hidden::make('producto'),

                                        Forms\Components\TextInput::make('cantidad')
                                            ->label('Cant. Solicitada')
                                            ->numeric()
                                            ->disabled() // Read-only
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('cantidad_aprobada')
                                            ->label('Cant. Aprobada')
                                            ->numeric()
                                            ->formatStateUsing(fn($state, $record) => $state ?? $record->cantidad)
                                            // This is the ONLY editable field
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('costo')
                                            ->label('Costo Ref.')
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled() // Read-only
                                            ->columnSpan(2),

                                        Forms\Components\Hidden::make('descuento')->default(0),
                                        Forms\Components\Hidden::make('impuesto')->default(0),

                                        Forms\Components\TextInput::make('total')
                                            ->label('Total Solicitado')
                                            ->numeric()
                                            ->prefix('$')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->addable(false)      // Cannot add items
                            ->deletable(false)    // Cannot delete items
                            ->reorderable(false), // Cannot reorder items
                    ]),

                // Totals
                Forms\Components\Hidden::make('subtotal')->default(0)->dehydrated(),
                Forms\Components\Hidden::make('total_descuento')->default(0)->dehydrated(),
                Forms\Components\Hidden::make('total_impuesto')->default(0)->dehydrated(),
                Forms\Components\Hidden::make('total')->default(0)->dehydrated(),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Placeholder::make('lbl_total')
                            ->content(fn(Get $get) => '$' . number_format(floatval($get('total')), 2))
                            ->label('Total (Referencial)')
                            ->extraAttributes(['class' => 'text-xl font-bold text-right']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('fecha_pedido')->date()->label('Fecha'),
                Tables\Columns\TextColumn::make('proveedor')->searchable()->placeholder('N/A'),
                Tables\Columns\TextColumn::make('observaciones')->label('Observaciones')->limit(50)->tooltip(fn($state) => $state),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total Ref.')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'gray',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(fn(Tables\Actions\Action $action) => $action->hidden())
            ->filters([
                Tables\Filters\Filter::make('fecha_pedido')
                    ->columnSpanFull()
                    ->form([
                        Forms\Components\Grid::make(12)
                            ->schema([
                                Forms\Components\DatePicker::make('desde')
                                    ->label('Desde')
                                    ->default(now()->startOfMonth())
                                    ->columnSpan(6),
                                Forms\Components\DatePicker::make('hasta')->label('Hasta')->default(now())->columnSpan(6),
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('consultar')
                                        ->label('Consultar')
                                        ->button()
                                        ->color('primary')
                                        ->extraAttributes(['class' => 'w-full md:w-auto', 'wire:click' => 'applyTableFilters'])
                                ])
                                    ->label('Click para consultar')
                                    ->alignment('center')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_pedido', '>=', $date),
                            )
                            ->when(
                                $data['hasta'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_pedido', '<=', $date),
                            );
                    })
                    ->visible(fn($livewire) => property_exists($livewire, 'activeTab') && $livewire->activeTab === 'historial')
            ])
            ->actions([
                Tables\Actions\EditAction::make('aprobar')
                    ->label('Aprobar')
                    ->visible(fn(Proforma $record) => $record->estado === 'Pendiente')
                    ->modalHeading('Aprobar Proforma')
                    ->modalWidth('7xl')
                    ->form([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Forms\Components\TextInput::make('id_bodega')
                                            ->label('Bodega')
                                            ->disabled()
                                            ->formatStateUsing(function ($state, $record) {
                                                if (!$state || !$record || !$record->proforma)
                                                    return $state;
                                                $connectionName = AprobacionProformaResource::getExternalConnectionName($record->proforma->id_empresa);
                                                if (!$connectionName)
                                                    return $state;
                                                try {
                                                    return DB::connection($connectionName)->table('saebode')->where('bode_cod_bode', $state)->value('bode_nom_bode') ?? $state;
                                                } catch (\Exception $e) {
                                                    return $state;
                                                }
                                            })
                                            ->columnSpan(3),

                                        Forms\Components\TextInput::make('codigo_producto')
                                            ->label('Producto')
                                            ->disabled()
                                            ->formatStateUsing(function ($state, $record) {
                                                if (!$state || !$record || !$record->proforma)
                                                    return $state;
                                                $connectionName = AprobacionProformaResource::getExternalConnectionName($record->proforma->id_empresa);
                                                if (!$connectionName)
                                                    return $state;
                                                try {
                                                    return DB::connection($connectionName)->table('saeprod')->where('prod_cod_prod', $state)->value('prod_nom_prod') ?? $state;
                                                } catch (\Exception $e) {
                                                    return $state;
                                                }
                                            })
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('cantidad')
                                            ->label('Solicitada')
                                            ->numeric()
                                            ->disabled()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('cantidad_aprobada')
                                            ->label('Aprobada')
                                            ->numeric()
                                            ->default(fn($record) => $record?->cantidad)
                                            ->formatStateUsing(fn($state, $record) => $state ?? $record->cantidad)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('costo')
                                            ->label('Costo')
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled()
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->using(function (Proforma $record, array $data): Proforma {
                        $record->estado = 'Aprobado';
                        $record->save();

                        if (isset($data['detalles']) && is_array($data['detalles'])) {
                            foreach ($data['detalles'] as $detailId => $detailData) {
                                $detail = \App\Models\DetalleProforma::find($detailId);
                                if ($detail) {
                                    $aprobada = $detailData['cantidad_aprobada'] ?? $detail->cantidad;
                                    if ($aprobada === '' || $aprobada === null)
                                        $aprobada = $detail->cantidad;

                                    $detail->cantidad_aprobada = floatval(str_replace(',', '', $aprobada));
                                    $detail->save();
                                }
                            }
                        }

                        return $record;
                    }),

                Tables\Actions\ViewAction::make('ver_aprobacion')
                    ->label('Ver Aprobación')
                    ->visible(fn(Proforma $record) => $record->estado !== 'Pendiente')
                    ->modalHeading('Detalle de Aprobación')
                    ->modalWidth('7xl')
                    ->form([
                        Forms\Components\ViewField::make('detalles_tabla')
                            ->view('filament.resources.aprobacion-proforma-resource.components.aprobar-tabla')
                            ->columnSpanFull(),
                    ])
            ])
            ->bulkActions([
                // No bulk actions ideally
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAprobacionProformas::route('/'),
        ];
    }
}
