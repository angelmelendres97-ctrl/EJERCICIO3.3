<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumenPedidosResource\Pages;
use App\Filament\Resources\ResumenPedidosResource\RelationManagers;
use App\Models\ResumenPedidos;
use Config;
use DB;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Empresa;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Actions;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model; // ESTA LÍNEA ES NECESARIA
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;

class ResumenPedidosResource extends Resource
{
    protected static ?string $model = ResumenPedidos::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static function userIsAdmin(): bool
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

    protected static function getEmpresasOptionsByConnections(array $conexiones): array
    {
        return collect($conexiones)
            ->flatMap(function ($conexion) {
                return collect(self::getEmpresasOptions((int) $conexion))
                    ->mapWithKeys(fn($nombre, $codigo) => [
                        $conexion . '|' . $codigo => $nombre,
                    ]);
            })
            ->all();
    }

    protected static function getSucursalesOptionsByConnections(array $conexiones, array $empresasSeleccionadas): array
    {
        return collect($conexiones)
            ->flatMap(function ($conexion) use ($empresasSeleccionadas) {
                $empresas = $empresasSeleccionadas[$conexion] ?? [];

                return collect(self::getSucursalesOptions((int) $conexion, $empresas))
                    ->mapWithKeys(fn($nombre, $codigo) => [
                        $conexion . '|' . $codigo => $nombre,
                    ]);
            })
            ->all();
    }

    protected static function getEmpresasOptions(?int $empresaId): array
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

    protected static function getSucursalesOptions(?int $empresaId, array $empresas): array
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

    public static function groupOptionsByConnection(array $optionKeys): array
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

    protected static function buildDefaultEmpresasSelection(array $conexiones): array
    {
        return collect($conexiones)
            ->flatMap(fn($conexion) => collect(self::getEmpresasOptions((int) $conexion))
                ->keys()
                ->map(fn($codigo) => $conexion . '|' . $codigo))
            ->values()
            ->all();
    }

    protected static function buildDefaultSucursalesSelection(array $conexiones, array $empresasSeleccionadas): array
    {
        $empresas = self::groupOptionsByConnection($empresasSeleccionadas);

        return collect($conexiones)
            ->flatMap(fn($conexion) => collect(self::getSucursalesOptions((int) $conexion, $empresas[$conexion] ?? []))
                ->keys()
                ->map(fn($codigo) => $conexion . '|' . $codigo))
            ->values()
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conexión y Empresa')
                    ->schema([
                        Forms\Components\Select::make('conexiones')
                            ->label('Conexiones')
                            ->multiple()
                            ->options(Empresa::query()->pluck('nombre_empresa', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->default(fn(?ResumenPedidos $record) => $record ? [$record->id_empresa] : [])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                $empresas = self::buildDefaultEmpresasSelection($state ?? []);
                                $sucursales = self::buildDefaultSucursalesSelection($state ?? [], $empresas);

                                $set('empresas', $empresas);
                                $set('sucursales', $sucursales);
                            }),
                        Forms\Components\Select::make('empresas')
                            ->label('Empresas')
                            ->multiple()
                            ->options(fn(Get $get): array => self::getEmpresasOptionsByConnections($get('conexiones') ?? []))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->default(fn(?ResumenPedidos $record) => $record ? [$record->id_empresa . '|' . $record->amdg_id_empresa] : [])
                            ->afterStateUpdated(function (Get $get, Set $set, ?array $state): void {
                                $sucursales = self::buildDefaultSucursalesSelection($get('conexiones') ?? [], $state ?? []);
                                $set('sucursales', $sucursales);
                            }),
                        Forms\Components\Select::make('sucursales')
                            ->label('Sucursales')
                            ->multiple()
                            ->options(fn(Get $get): array => self::getSucursalesOptionsByConnections(
                                $get('conexiones') ?? [],
                                self::groupOptionsByConnection($get('empresas') ?? []),
                            ))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->default(fn(?ResumenPedidos $record) => $record ? [$record->id_empresa . '|' . $record->amdg_id_sucursal] : []),

                        Forms\Components\Select::make(name: 'tipo_presupuesto')
                            ->label('Presupuesto:')
                            ->options(['AZ' => 'AZ', 'PB' => 'PB'])
                            ->required(),

                    ])->columns(3),

                Forms\Components\Section::make('Traer información Ordenes Compra')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_desde')
                                    ->label('Fecha Desde')
                                    ->default(Carbon::create(2026, 1, 1)->startOfDay())
                                    ->minDate(Carbon::create(2026, 1, 1))
                                    ->maxDate(now())
                                    ->required(),
                                Forms\Components\DatePicker::make('fecha_hasta')
                                    ->label('Fecha Hasta')
                                    ->default(now()->endOfDay()),
                            ]),
                        Forms\Components\Toggle::make('solo_mias')
                            ->label('Solo mis órdenes')
                            ->default(true)
                            ->helperText('Si está activo, solo se listarán órdenes creadas por el usuario actual.')
                            ->live(),
                        Forms\Components\Checkbox::make('seleccionar_todas_ordenes')
                            ->label('Seleccionar todas las órdenes visibles')
                            ->helperText('Marca o desmarca todas las órdenes filtradas en pantalla.')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?bool $state): void {
                                $ordenes = $get('ordenes_compra') ?? [];
                                if (! is_array($ordenes)) {
                                    return;
                                }

                                foreach ($ordenes as $index => $orden) {
                                    $ordenes[$index]['checkbox_oc'] = (bool) $state;
                                }

                                $set('ordenes_compra', $ordenes);
                            }),
                        Forms\Components\Repeater::make('ordenes_compra')
                            ->schema([
                                Forms\Components\TextInput::make('id_orden_compra')->label('Secuencial')->readOnly()->columnSpan(2),
                                Forms\Components\TextInput::make('nombre_empresa')->label('Conexion')->readOnly()->columnSpan(3),
                                Forms\Components\TextInput::make('empresa')->label('Empresa')->readOnly()->columnSpan(3),
                                Forms\Components\TextInput::make('sucursal')->label('Sucursal')->readOnly()->columnSpan(3),
                                Forms\Components\TextInput::make('proveedor')->label('Preveedor')->readOnly()->columnSpan(3),
                                Forms\Components\TextInput::make('total_fact')->label('Total')->readOnly()->prefix('$')->columnSpan(2),
                                Forms\Components\DatePicker::make('fecha_oc')->label('Fecha OC')->readOnly()->columnSpan(2),
                                Forms\Components\Actions::make([
                                    Action::make('ver_detalle')
                                        ->label('Ver')
                                        ->icon('heroicon-o-eye')
                                        ->modalContent(function (Get $get) {
                                            $ordenCompraId = $get('id_orden_compra');
                                            if (!$ordenCompraId) {
                                                return 'No se pudo cargar el detalle.';
                                            }
                                            $detalles = \App\Models\DetalleOrdenCompra::where('id_orden_compra', $ordenCompraId)->get();
                                            return view('filament.resources.resumen-pedidos-resource.widgets.detalle-orden-compra-modal', ['detalles' => $detalles]);
                                        })
                                        ->modalSubmitAction(false)
                                        ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),
                                ])->columnSpan(2),
                               Forms\Components\Checkbox::make('checkbox_oc')
                                ->label('Marcar')
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get): void {

                                    // OJO: aquí debemos leer TODO el repeater, no el item actual
                                    $ordenes = $get('../../../../ordenes_compra') ?? [];

                                    if (! is_array($ordenes)) {
                                        return;
                                    }

                                    $allChecked = collect($ordenes)
                                        ->filter(fn ($orden) => is_array($orden)) // evita strings
                                        ->every(fn ($orden) => (bool) ($orden['checkbox_oc'] ?? false));

                                    // "seleccionar_todas_ordenes" está fuera del repeater
                                    $set('../../../../seleccionar_todas_ordenes', $allChecked);
                                })
                                ->columnSpan(1),

                            ])
                            ->columns(16)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->grid(columns: 1)
                    ])
                    ->headerActions([
                        Action::make('consultar_ordenes_compra')
                            ->label('Consultar Ordenes Compra')
                            ->icon('heroicon-o-magnifying-glass')
                            ->action(function (Get $get, Set $set) {
                                $conexiones = $get('conexiones') ?? [];
                                $empresasSeleccionadas = self::groupOptionsByConnection($get('empresas') ?? []);
                                $sucursalesSeleccionadas = self::groupOptionsByConnection($get('sucursales') ?? []);
                                $fecha_desde = $get('fecha_desde');
                                $fecha_hasta = $get('fecha_hasta');

                                if (empty($conexiones) || empty($empresasSeleccionadas)) {
                                    return;
                                }

                                $ordenesExistentes = \App\Models\DetalleResumenPedidos::query()
                                    ->whereHas('resumenPedido', fn($query) => $query->where('anulada', false))
                                    ->pluck('id_orden_compra')
                                    ->all();

                                $query = \App\Models\OrdenCompra::query()
                                    ->whereNotIn('id', $ordenesExistentes)
                                    ->where('anulada', false);

                                $aplicoFiltro = false;
                                $query->where(function (Builder $query) use ($conexiones, $empresasSeleccionadas, $sucursalesSeleccionadas, &$aplicoFiltro) {
                                    foreach ($conexiones as $conexionId) {
                                        $empresas = $empresasSeleccionadas[$conexionId] ?? [];
                                        if (empty($empresas)) {
                                            continue;
                                        }

                                        $sucursales = $sucursalesSeleccionadas[$conexionId] ?? [];

                                        $query->orWhere(function (Builder $subQuery) use ($conexionId, $empresas, $sucursales) {
                                            $subQuery->where('id_empresa', $conexionId)
                                                ->whereIn('amdg_id_empresa', $empresas);

                                            if (! empty($sucursales)) {
                                                $subQuery->whereIn('amdg_id_sucursal', $sucursales);
                                            }
                                        });

                                        $aplicoFiltro = true;
                                    }
                                });

                                if (! $aplicoFiltro) {
                                    $set('ordenes_compra', []);
                                    return;
                                }

                                if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                                    $query->whereBetween('fecha_pedido', [$fecha_desde, $fecha_hasta]);
                                }

                                $soloMias = (bool) $get('solo_mias');
                                if ($soloMias) {
                                    $query->whereBelongsTo(auth()->user(), 'usuario');
                                }

                                $ordenes = $query->get();

                                $empresaNombrePorConexion = [];
                                $sucursalNombrePorConexion = [];

                                $ordenes->groupBy('id_empresa')->each(function ($ordenesConexion, $conexionId) use (&$empresaNombrePorConexion, &$sucursalNombrePorConexion) {
                                    $connectionName = self::getExternalConnectionName((int) $conexionId);
                                    if (! $connectionName) {
                                        return;
                                    }

                                    $empresaCodes = $ordenesConexion->pluck('amdg_id_empresa')->filter()->unique()->values()->all();
                                    $sucursalCodes = $ordenesConexion->pluck('amdg_id_sucursal')->filter()->unique()->values()->all();

                                    if (! empty($empresaCodes)) {
                                        try {
                                            $empresaNombrePorConexion[$conexionId] = DB::connection($connectionName)
                                                ->table('saeempr')
                                                ->whereIn('empr_cod_empr', $empresaCodes)
                                                ->pluck('empr_nom_empr', 'empr_cod_empr')
                                                ->all();
                                        } catch (\Exception $e) {
                                            $empresaNombrePorConexion[$conexionId] = [];
                                        }
                                    }

                                    if (! empty($empresaCodes) && ! empty($sucursalCodes)) {
                                        try {
                                            $sucursales = DB::connection($connectionName)
                                                ->table('saesucu')
                                                ->whereIn('sucu_cod_empr', $empresaCodes)
                                                ->whereIn('sucu_cod_sucu', $sucursalCodes)
                                                ->get(['sucu_cod_empr', 'sucu_cod_sucu', 'sucu_nom_sucu']);

                                            foreach ($sucursales as $sucursal) {
                                                $sucursalNombrePorConexion[$conexionId][$sucursal->sucu_cod_empr][$sucursal->sucu_cod_sucu] = $sucursal->sucu_nom_sucu;
                                            }
                                        } catch (\Exception $e) {
                                            $sucursalNombrePorConexion[$conexionId] = [];
                                        }
                                    }
                                });

                                $pedidos = $ordenes->map(function ($orden) use ($empresaNombrePorConexion, $sucursalNombrePorConexion) {
                                    $empresaNombre = $empresaNombrePorConexion[$orden->id_empresa][$orden->amdg_id_empresa] ?? $orden->amdg_id_empresa;
                                    $sucursalNombre = $sucursalNombrePorConexion[$orden->id_empresa][$orden->amdg_id_empresa][$orden->amdg_id_sucursal] ?? $orden->amdg_id_sucursal;

                                    return [
                                        'id_orden_compra' => $orden->id,
                                        'id_conexion' => $orden->id_empresa,
                                        'nombre_empresa' => $orden->empresa->nombre_empresa ?? '',
                                        'empresa' => $empresaNombre,
                                        'sucursal' => $sucursalNombre,
                                        'proveedor' => $orden->proveedor,
                                        'total_fact' => $orden->total,
                                        'fecha_oc' => $orden->fecha_pedido ? $orden->fecha_pedido->format('Y-m-d') : null,
                                    ];
                                })->toArray();

                                $set('ordenes_compra', $pedidos);
                            })
                        //->visible(fn(Get $get) => !empty($get('id_empresa')) && !empty($get('amdg_id_empresa')) && !empty($get('amdg_id_sucursal')))
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->columns([
                Tables\Columns\TextColumn::make('codigo_secuencial')
                    ->label('N° Resumen')
                    ->formatStateUsing(fn(string $state): string => str_pad($state, 8, '0', STR_PAD_LEFT))
                    ->searchable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pedidos_importados')
                    ->label('Órdenes Importadas')
                    ->state(function (ResumenPedidos $record): string {
                        // lo mismo que ya estás mostrando: "OC: 355, 356..."
                        $ids = $record->detalles()
                            ->whereHas('ordenCompra', fn($query) => $query->where('anulada', false))
                            ->pluck('id_orden_compra')
                            ->filter()
                            ->unique()
                            ->values();

                        return $ids->isEmpty() ? '—' : 'OC: ' . $ids->join(', ');
                    })
                    ->wrap()
                    ->toggleable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: true,
                        query: function (Builder $query, string $search): Builder {
                            $search = trim($search);

                            if ($search === '') {
                                return $query;
                            }

                            // Busca por ID de OC dentro de los detalles del resumen
                            // (Postgres: ILIKE)
                            return $query->whereHas('detalles', function (Builder $q) use ($search) {
                                $q->where('id_orden_compra', 'ilike', "%{$search}%");
                            });
                        }
                    ),
                Tables\Columns\TextColumn::make('total_resumen')
                    ->label('Total')
                    ->state(function (ResumenPedidos $record) {
                        // Suma de las órdenes incluidas en el resumen
                        return $record->detalles()
                            ->with('ordenCompra:id,total')
                            ->get()
                            ->sum(fn($det) => (float) ($det->ordenCompra->total ?? 0));
                    })
                    ->money('USD', locale: 'es_EC')
                    ->sortable(false) // importante: no es columna real
                    ->toggleable(),

                Tables\Columns\TextColumn::make('empresa.nombre_empresa')
                    ->label('Conexión')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amdg_id_empresa')
                    ->label('Empresa')
                    ->sortable()
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
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Presupuesto')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PB' => 'warning',
                        'AZ' => 'success',
                        default => 'gray',
                    })
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Creado Por')
                    ->sortable()
                    ->toggleable(),


                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('anulada')
                    ->label('Anulada')
                    ->boolean(),
            ])
            ->filters([
                Filter::make('mis_resumenes')
                    ->label('Mis resumenes')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereBelongsTo(auth()->user(), 'usuario')
                    )
                    ->default(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('ver_ordenes')
                    ->label('Ver Órdenes')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn($record) => view(
                        'filament.resources.resumen-pedidos-resource.widgets.ordenes-compra-modal',
                        ['record' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar')),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn(ResumenPedidos $record) => route('resumen-pedidos.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn(ResumenPedidos $record) => (auth()->user()->can('Actualizar') || self::userIsAdmin())
                            && !$record->anulada
                    )
                    ->action(function (ResumenPedidos $record) {
                        $record->update(['anulada' => true]);

                        Notification::make()
                            ->title('Resumen de pedidos anulado')
                            ->success()
                            ->send();
                    }),

                //Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(ResumenPedidos $record) => self::userIsAdmin())
                    ->authorize(fn() => self::userIsAdmin())
                    ->disabled(fn(ResumenPedidos $record) => $record->anulada),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        return !$record->anulada;
    }

    public static function canDelete(Model $record): bool
    {
        return self::userIsAdmin() && !$record->anulada;
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
            'index' => Pages\ListResumenPedidos::route('/'),
            'create' => Pages\CreateResumenPedidos::route('/create'),
            'edit' => Pages\EditResumenPedidos::route('/{record}/edit'),
        ];
    }
}
