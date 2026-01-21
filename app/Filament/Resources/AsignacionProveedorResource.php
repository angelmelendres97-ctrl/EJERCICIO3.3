<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignacionProveedorResource\Pages;
use App\Models\Proforma;
use App\Models\Proveedores;
use App\Models\DetalleProformaProveedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Log;

class AsignacionProveedorResource extends Resource
{
    protected static ?string $model = Proforma::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Compras';

    protected static ?string $navigationLabel = 'Asignación Proveedores';

    protected static ?string $slug = 'asignacion-proveedores';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('estado', '!=', 'Pendiente');
    }

    public static function getExternalConnectionName(int $empresaId): ?string
    {
        $empresa = \App\Models\Empresa::find($empresaId);
        if (!$empresa || !$empresa->status_conexion) {
            return null;
        }

        $connectionName = 'external_db_' . $empresaId;

        if (!\Illuminate\Support\Facades\Config::has("database.connections.{$connectionName}")) {
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
            \Illuminate\Support\Facades\Config::set("database.connections.{$connectionName}", $dbConfig);
        }

        return $connectionName;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Proforma')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('finalizar_asignacion')
                            ->label('Finalizar Asignación')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->visible(fn(Proforma $record) => $record->estado !== 'Asignado Proveedores')
                            ->action(function (Proforma $record) {
                                $record->update(['estado' => 'Asignado Proveedores']);
                                Notification::make()
                                    ->title('Estado actualizado a Asignado Proveedores')
                                    ->success()
                                    ->send();
                                return redirect(request()->header('Referer'));
                            }),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Proforma #')
                            ->disabled(),
                        Forms\Components\DatePicker::make('fecha_pedido')
                            ->label('Fecha')
                            ->disabled(),
                        Forms\Components\TextInput::make('proveedor')
                            ->label('Proveedor Referencial')
                            ->disabled(),

                        Forms\Components\Hidden::make('id_empresa'),
                        Forms\Components\Hidden::make('amdg_id_empresa'),
                        Forms\Components\Hidden::make('amdg_id_sucursal'),
                    ])->columns(3),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('agregar_proveedor')
                        ->label('Agregar Proveedor +')
                        ->color('success')
                        ->modalWidth('4xl')
                        ->visible(fn(Proforma $record) => $record->estado !== 'Asignado Proveedores')
                        ->form([
                            Forms\Components\Grid::make(12)
                                ->schema([
                                    Forms\Components\TextInput::make('ruc_proveedor')
                                        ->label('RUC Proveedor')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->columnSpan(4),

                                    Forms\Components\Select::make('proveedor_id')
                                        ->label('Seleccione Proveedor')
                                        ->options(function (Proforma $record) {
                                            $empresaId = $record->id_empresa;
                                            $amdg_id_empresa = $record->amdg_id_empresa;

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
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Proforma $record) {
                                            if (!$state) {
                                                $set('ruc_proveedor', null);
                                                $set('correo_proveedor', null);
                                                $set('contacto_proveedor', null);
                                                return;
                                            }

                                            $connectionName = self::getExternalConnectionName($record->id_empresa);
                                            if ($connectionName) {
                                                try {
                                                    // Ensure we look up by correctly matching the ID passed from Select options
                                                    $provider = DB::connection($connectionName)
                                                        ->table('saeclpv')
                                                        ->where('clpv_cod_clpv', (string) $state) // Ensure string type match
                                                        ->where('clpv_cod_empr', $record->amdg_id_empresa)
                                                        ->select(['clpv_ruc_clpv', 'clpv_nom_clpv', 'clpv_rep_clpv'])
                                                        ->first();

                                                    $email = null;
                                                    if ($provider) {
                                                        // Fetch Email separately from saeemai
                                                        try {
                                                            $email = DB::connection($connectionName)
                                                                ->table('saeemai')
                                                                ->where('emai_cod_clpv', (string) $state)
                                                                ->where('emai_cod_empr', $record->amdg_id_empresa)
                                                                ->value('emai_ema_emai');
                                                        } catch (\Exception $ex) {
                                                        }

                                                        $set('ruc_proveedor', trim($provider->clpv_ruc_clpv));
                                                        $set('correo_proveedor', $email ? trim($email) : '');
                                                        // Use Representative as Contact
                                                        $set('contacto_proveedor', isset($provider->clpv_rep_clpv) ? trim($provider->clpv_rep_clpv) : '');
                                                    } else {
                                                        $set('ruc_proveedor', 'Not Found');
                                                    }
                                                } catch (\Exception $e) {
                                                    $set('ruc_proveedor', 'Error: ' . $e->getMessage());
                                                }
                                            }
                                        })
                                        ->columnSpan(8),

                                    Forms\Components\TextInput::make('correo_proveedor')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->columnSpan(6),

                                    Forms\Components\TextInput::make('contacto_proveedor')
                                        ->label('Contacto')
                                        ->columnSpan(6),
                                ]),

                            Forms\Components\Repeater::make('productos_seleccionados')
                                ->view('filament.resources.asignacion-proveedor-resource.components.repeater-tabla-productos')
                                ->label('Productos a Cotizar')
                                ->hiddenLabel()
                                ->schema([
                                    // Only Components accessed by getComponent() in Blade need to be here
                                    Forms\Components\Checkbox::make('seleccionado')
                                        ->hiddenLabel(),

                                    Forms\Components\TextInput::make('precio')
                                        ->hiddenLabel()
                                        ->numeric()
                                        ->default(0)
                                        ->extraInputAttributes(['class' => '!border !border-gray-300 !bg-white !rounded !px-2 !py-1 !text-xs !w-full'])
                                        ->columnSpan(1),

                                    // Hidden fields to store state for the View loop
                                    Forms\Components\Hidden::make('id'),
                                    Forms\Components\Hidden::make('bodega'),
                                    Forms\Components\Hidden::make('producto'),
                                    Forms\Components\Hidden::make('cantidad'),
                                ])
                                ->addable(false)
                                ->deletable(true)
                                ->reorderable(false)
                                ->default(function (Proforma $record) {
                                    $connectionName = self::getExternalConnectionName($record->id_empresa);

                                    return $record->detalles->map(function ($detalle) use ($connectionName) {
                                        $bodegaNombre = $detalle->id_bodega;

                                        if ($connectionName && $detalle->id_bodega) {
                                            try {
                                                $nombre = DB::connection($connectionName)
                                                    ->table('saebode')
                                                    ->where('bode_cod_empr', $detalle->proforma->amdg_id_empresa) // Corrected Where
                                                    ->where('bode_cod_bode', $detalle->id_bodega)
                                                    ->value('bode_nom_bode');
                                                if ($nombre)
                                                    $bodegaNombre = $nombre;
                                            } catch (\Exception $e) {
                                            }
                                        }

                                        return [
                                            'id' => $detalle->id,
                                            'seleccionado' => true,
                                            'bodega' => $bodegaNombre,
                                            'producto' => $detalle->producto,
                                            'cantidad' => number_format($detalle->cantidad_aprobada ?? $detalle->cantidad, 2), // Use approved or qty
                                            'precio' => 0,
                                        ];
                                    })->toArray();
                                })
                                ->columns(1),
                        ])
                        ->action(function (array $data, Proforma $record) {
                            $proveedorExternoId = $data['proveedor_id'];
                            $repeaterItems = $data['productos_seleccionados'] ?? [];

                            $nuevoCorreo = $data['correo_proveedor'] ?? null;
                            $nuevoContacto = $data['contacto_proveedor'] ?? null;

                            $selectedItems = array_filter($repeaterItems, fn($item) => isset($item['seleccionado']) && $item['seleccionado']);

                            if (empty($selectedItems)) {
                                Notification::make()
                                    ->title('No se seleccionaron productos')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $connectionName = AsignacionProveedorResource::getExternalConnectionName($record->id_empresa);
                            if (!$connectionName) {
                                Notification::make()->title('Error de conexión a BD Externa')->danger()->send();
                                return;
                            }

                            try {
                                $extProvider = DB::connection($connectionName)
                                    ->table('saeclpv')
                                    ->where('clpv_cod_clpv', $proveedorExternoId)
                                    ->where('clpv_cod_empr', $record->amdg_id_empresa)
                                    ->first();

                                if (!$extProvider) {
                                    Notification::make()->title('Proveedor no encontrado en externa')->danger()->send();
                                    return;
                                }

                                $localProvider = Proveedores::firstOrCreate(
                                    [
                                        'ruc' => $extProvider->clpv_ruc_clpv,
                                        'id_empresa' => $record->id_empresa,
                                    ],
                                    [
                                        'admg_id_empresa' => $record->amdg_id_empresa,
                                        'admg_id_sucursal' => $record->amdg_id_sucursal ?? 1,
                                        'tipo' => 'JURIDICA',
                                        'nombre' => $extProvider->clpv_nom_clpv,
                                        'nombre_comercial' => $extProvider->clpv_nom_clpv,
                                        'grupo' => 'GENERAL',
                                        'zona' => 'GENERAL',
                                        'flujo_caja' => 'S/N',
                                        'tipo_proveedor' => 'LOCAL',
                                        'forma_pago' => 'CONTADO',
                                        'destino_pago' => 'TRANSFERENCIA',
                                        'pais_pago' => 'ECUADOR',
                                        'dias_pago' => 0,
                                        'limite_credito' => 0.00,
                                        'telefono' => $extProvider->clpv_tel_clpv ?? null,
                                        'direcccion' => $extProvider->clpv_dir_clpv ?? null,
                                        // 'correo' - NO LONGER UPDATING HERE
                                        'aplica_retencion_sn' => false,
                                    ]
                                );

                                if (!$localProvider) {
                                    Notification::make()->title('Error al crear proveedor local')->danger()->send();
                                    return;
                                }

                                // 3. Creates Assignments with Transactional Details
                                $count = 0;
                                foreach ($selectedItems as $item) {
                                    $detalleId = $item['id'];
                                    $itemPrecio = $item['precio'] ?? 0;

                                    try {
                                        DetalleProformaProveedor::firstOrCreate([
                                            'id_detalle_proforma' => $detalleId,
                                            'id_proveedor' => $localProvider->id
                                        ], [
                                            'seleccionado' => true,
                                            'correo' => $nuevoCorreo,
                                            'contacto' => $nuevoContacto,
                                            'precio' => $itemPrecio,
                                            'costo' => $itemPrecio
                                        ]);
                                        $count++;
                                    } catch (\Exception $e) {
                                    }
                                }

                                Notification::make()
                                    ->title("Proveedor asignado a {$count} items correctamente")
                                    ->success()
                                    ->send();

                                return redirect(request()->header('Referer'));

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error al procesar asignación')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])->columnSpanFull(),

                Forms\Components\Section::make('Detalle de Productos y Asignación')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship()
                            ->label(false)
                            ->schema([
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        Forms\Components\Group::make([
                                            Forms\Components\Placeholder::make('info_producto')
                                                ->label('Detalles del Item')
                                                ->content(fn($record) => new \Illuminate\Support\HtmlString(
                                                    (function () use ($record) {
                                                        $bodegaNombre = $record->bodega;

                                                        if ($record->proforma && $record->proforma->id_empresa) {
                                                            $connectionName = AsignacionProveedorResource::getExternalConnectionName($record->proforma->id_empresa);
                                                            if ($connectionName && $record->id_bodega) {
                                                                try {
                                                                    $fetched = DB::connection($connectionName)
                                                                        ->table('saebode')
                                                                        ->where('bode_cod_bode', $record->id_bodega)
                                                                        ->value('bode_nom_bode');
                                                                    if ($fetched)
                                                                        $bodegaNombre = $fetched;
                                                                } catch (\Exception $e) {}
                                                            }
                                                        }

                                                        if (empty($bodegaNombre) && $record->id_bodega) {
                                                            $bodegaNombre = $record->id_bodega;
                                                        }

                                                        return "<div class='text-sm'>" .
                                                            "<strong>Bodega:</strong> {$bodegaNombre}<br>" .
                                                            "<strong>Producto:</strong> {$record->producto} (" . ($record->codigo_producto ?? 'N/A') . ")<br>" .
                                                            "<strong>Cantidad Proforma:</strong> " . number_format($record->cantidad, 2) . "<br>" .
                                                            "<strong>Aprobado:</strong> " . number_format($record->cantidad_aprobada, 2) .
                                                            "</div>";
                                                    })()
                                                ))
                                                ->columnSpanFull(),
                                        ])->columnSpan(5),

                                        Forms\Components\Repeater::make('proveedoresAsignados')
                                            ->relationship('proveedoresAsignados')
                                            ->deletable(fn($livewire) => $livewire->record->estado !== 'Asignado Proveedores')
                                            ->schema([
                                                Forms\Components\Grid::make(12)
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('info_proveedor')
                                                            ->hiddenLabel()
                                                            ->content(fn($record) => new \Illuminate\Support\HtmlString(
                                                                "<div class='text-sm'>" .
                                                                "<strong>" . ($record->proveedor->nombre ?? 'N/A') . "</strong><br>" .
                                                                "<span class='text-gray-500 text-xs'>Email: " . ($record->correo ?? '-') . "</span> | " .
                                                                "<span class='text-gray-500 text-xs'>Contacto: " . ($record->contacto ?? '-') . "</span>" .
                                                                "</div>"
                                                            ))
                                                            ->columnSpan(9),

                                                        Forms\Components\Placeholder::make('costo_display')
                                                            ->hiddenLabel()
                                                            ->content(fn($record) => new \Illuminate\Support\HtmlString(
                                                                "<div class='flex items-center justify-end h-full'>" .
                                                                "<span class='text-sm font-bold text-black'>$" . number_format($record->costo, 2) . "</span>" .
                                                                "</div>"
                                                            ))
                                                            ->columnSpan(3),
                                                    ]),
                                            ])
                                            ->addable(false)
                                            ->columnSpan(7),
                                    ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('fecha_pedido')->date()->label('Fecha'),
                Tables\Columns\TextColumn::make('proveniencia')
                    ->label('Proveedor Referencial')
                    ->formatStateUsing(fn($record) => $record->proveedor)
                    ->limit(20),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total Referencial')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Comentario')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->observaciones),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Asignado Proveedores' => 'danger',
                        'Comparativo Precios' => 'primary',
                        default => 'success',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Asignar')
                    ->visible(fn(Proforma $record) => $record->estado === 'Aprobado'),
                Tables\Actions\Action::make('comparativo')
                    ->label('Cuadro Comparativo')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('info')
                    ->visible(fn(Proforma $record) => $record->estado === 'Asignado Proveedores')
                    ->url(fn(Proforma $record) => Pages\ComparativoProveedores::getUrl(['record' => $record])),
                Tables\Actions\Action::make('ver_comparativo')
                    ->label('Comparativo')
                    ->icon('heroicon-o-scale')
                    ->color('danger')
                    ->visible(fn(Proforma $record) => $record->estado === 'Comparativo Precios')
                    ->url(fn(Proforma $record) => Pages\ComparativoProveedores::getUrl(['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsignacionProveedors::route('/'),
            'edit' => Pages\EditAsignacionProveedor::route('/{record}/edit'),
            'comparativo' => Pages\ComparativoProveedores::route('/{record}/comparativo'),
        ];
    }
}
