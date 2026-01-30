<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Filament\Resources\ProveedorResource\RelationManagers;
use App\Models\Proveedores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Empresa;
use App\Models\LineaNegocio;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedores::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


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
        return $form->schema(self::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Información General')
                ->schema([
                    Forms\Components\Select::make('id_empresa')
                        ->label('Conexion')
                        ->relationship('empresa', 'nombre_empresa')
                        ->searchable()
                        ->preload()
                        ->live()

                        ->afterStateUpdated(function (callable $set, $state, $old): void {
                            // Si solo se está hidratando y no hubo cambio real, no limpies
                            if ($old === null) {
                                return;
                            }

                            if ($state !== $old) {
                                $set('admg_id_empresa', null);
                                $set('admg_id_sucursal', null);
                            }
                        })

                        ->required(),

                    Forms\Components\Select::make('admg_id_empresa')
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
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn(callable $set) => $set('admg_id_sucursal', null))
                        ->required(),

                    Forms\Components\Select::make('admg_id_sucursal')
                        ->label('Sucursal')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

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
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo Identificacion')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('comercial.tipo_iden_clpv')
                                    ->pluck('identificacion', 'identificacion')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('ruc')
                        ->label('Identificacion')
                        ->required()
                        ->maxLength(13)
                        ->suffixAction(
                            Action::make('buscar_sri')
                                ->label('Buscar')
                                ->icon('heroicon-o-magnifying-glass')
                                ->action(function (Get $get, Set $set): void {
                                    $ruc = preg_replace('/\D/', '', (string) $get('ruc'));

                                    if (!$ruc || strlen($ruc) < 10) {
                                        Notification::make()
                                            ->title('Ingresa un RUC/Cédula válido para consultar en el SRI.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $endpoint = 'https://srienlinea.sri.gob.ec/sri-catastro-sujeto-servicio-internet/rest/ConsolidadoContribuyente/obtenerPorNumerosRuc';

                                    try {
                                        $response = Http::timeout(15)
                                            ->acceptJson()
                                            ->get($endpoint, ['ruc' => $ruc]);
                                    } catch (\Throwable $e) {
                                        Notification::make()
                                            ->title('No se pudo conectar con el SRI.')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    if (!$response->ok()) {
                                        Notification::make()
                                            ->title('El SRI no respondió correctamente.')
                                            ->body('Código: ' . $response->status())
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $payload = $response->json();

                                    // La respuesta real viene como: [ { ... } ]
                                    $data = is_array($payload) ? ($payload[0] ?? null) : $payload;

                                    if (!is_array($data)) {
                                        Notification::make()
                                            ->title('Respuesta del SRI inesperada.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $razonSocial      = data_get($data, 'razonSocial');
                                    $numeroRuc        = data_get($data, 'numeroRuc');
                                    $agenteRetencion  = data_get($data, 'agenteRetencion'); // "SI" / "NO"
                                    $estadoRuc        = data_get($data, 'estadoContribuyenteRuc'); // "ACTIVO"
                                    $obligadoConta    = data_get($data, 'obligadoLlevarContabilidad'); // "SI"/"NO"
                                    $tipoContrib      = data_get($data, 'tipoContribuyente');
                                    $actividad        = data_get($data, 'actividadEconomicaPrincipal');

                                    if (empty($razonSocial)) {
                                        Notification::make()
                                            ->title('No se encontró razón social en la respuesta del SRI.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $toBoolSiNo = fn($v) => in_array(strtoupper(trim((string) $v)), ['SI', 'S', 'TRUE', '1'], true);

                                    // Autollenado principal
                                    $set('ruc', $numeroRuc ?: $ruc);
                                    $set('nombre', $razonSocial);
                                    $set('nombre_comercial', $razonSocial);

                                    // Retención (según SRI)
                                    $set('aplica_retencion_sn', $toBoolSiNo($agenteRetencion));

                                    // Si tienes campos adicionales, aquí los puedes setear (si existen en tu tabla/form)
                                    // $set('estado_sri', $estadoRuc);
                                    // $set('tipo_contribuyente', $tipoContrib);
                                    // $set('obligado_contabilidad_sn', $toBoolSiNo($obligadoConta));
                                    // $set('actividad_economica', $actividad);

                                    Notification::make()
                                        ->title('Datos del SRI cargados correctamente.')
                                        ->success()
                                        ->send();
                                })
                        ),


                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)  // <- escucha cambios en tiempo real 👈 SOLO cuando pierde el foco
                        ->afterStateUpdated(function ($state, callable $set) {
                            // cuando cambie nombre, llena nombre_comercial
                            $set('nombre_comercial', $state);
                        }),

                    Forms\Components\TextInput::make('nombre_comercial')
                        ->label('Nombre Comercial')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Telefono')
                        ->required()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('correo')
                        ->label('Email')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('direcccion')
                        ->label('Dirección')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Toggle::make('aplica_retencion_sn')
                        ->label('¿Aplica Retención?')
                        ->default(false),
                ])
                ->columns(3),

            Forms\Components\Section::make('Clasificación')
                ->schema([

                    Forms\Components\Select::make('grupo')
                        ->label('Grupo')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saegrpv')
                                    ->where('grpv_cod_empr', $amdgIdEmpresaCode)
                                    ->where('grpv_cod_modu', 4)
                                    ->pluck('grpv_nom_grpv', 'grpv_nom_grpv')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),


                    Forms\Components\Select::make('zona')
                        ->label('Zona')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saezona')
                                    ->where('zona_cod_empr', $amdgIdEmpresaCode)
                                    ->pluck('zona_nom_zona', 'zona_nom_zona')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('flujo_caja')
                        ->label('Flujo de Caja')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saecact')
                                    ->where('cact_cod_empr', $amdgIdEmpresaCode)
                                    ->pluck('cact_nom_cact', 'cact_nom_cact')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('tipo_proveedor')
                        ->label('Tipo de proveedor')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saetprov')
                                    ->where('tprov_cod_empr', $amdgIdEmpresaCode)
                                    ->pluck('tprov_des_tprov', 'tprov_des_tprov')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Condiciones de Pago')
                ->schema([

                    Forms\Components\Select::make('forma_pago')
                        ->label('Forma de Pago')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saefpagop')
                                    ->where('fpagop_cod_empr', $amdgIdEmpresaCode)
                                    ->pluck('fpagop_des_fpagop', 'fpagop_des_fpagop')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('destino_pago')
                        ->label('Destino Pago')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saetpago')
                                    ->where('tpago_cod_empr', $amdgIdEmpresaCode)
                                    ->pluck('tpago_des_tpago', 'tpago_des_tpago')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),


                    Forms\Components\Select::make('pais_pago')
                        ->label('Pais de Pago')
                        ->options(function (Get $get) {
                            $empresaId = $get('id_empresa');
                            $amdgIdEmpresaCode = $get('admg_id_empresa');

                            if (!$empresaId || !$amdgIdEmpresaCode) {
                                return [];
                            }

                            $connectionName = self::getExternalConnectionName($empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            try {
                                return DB::connection($connectionName)
                                    ->table('saepaisp')
                                    ->pluck('paisp_des_paisp', 'paisp_des_paisp')
                                    ->all();
                            } catch (\Exception $e) {
                                // Log or handle error, e.g., \Log::error($e->getMessage());
                                return [];
                            }
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('dias_pago')
                        ->numeric()
                        ->label('Días de Pago')
                        ->default(0),

                    Forms\Components\TextInput::make('limite_credito')
                        ->numeric()
                        ->label('Límite de Crédito')
                        ->step('0.01')
                        ->default(0),

                    Forms\Components\Select::make('lineasNegocio')
                        ->label('Líneas de Negocio')
                        ->relationship('lineasNegocio', 'nombre')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->live()
                        ->default(function ($livewire) {
                            $fromOrdenCompra = request()->query('from') === 'orden-compra';

                            if (! $fromOrdenCompra || ! $livewire instanceof Pages\CreateProveedor) {
                                return null;
                            }

                            return LineaNegocio::query()->pluck('id')->all();
                        })
                        ->required(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Empresas')
                ->schema([
                    Forms\Components\CheckboxList::make('empresas_proveedor')
                        ->label('Empresas para replicar')
                        ->options(fn(Get $get) => self::getEmpresasProveedorOptions($get))
                        ->afterStateHydrated(function (Get $get, callable $set, ?array $state) {
                            if (! empty($state)) {
                                return;
                            }

                            $seleccionados = self::getEmpresasProveedorDefaults($get);

                            if (! empty($seleccionados)) {
                                $set('empresas_proveedor', $seleccionados);
                            }
                        })
                        ->columns(2)
                ])->columns(1),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->columns([
                Tables\Columns\TextColumn::make('empresa.nombre_empresa')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                // Identificación en negrita + buscable
                Tables\Columns\TextColumn::make('ruc')
                    ->label('Identificación')
                    ->weight('bold')              // <- negrita
                    ->searchable(isIndividual: true)
                    ->sortable(),

                // Nombre SIN badge + buscable
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                // Nombre comercial buscable
                Tables\Columns\TextColumn::make('nombre_comercial')
                    ->label('Nombre Comercial')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                // Grupo CON badge
                Tables\Columns\TextColumn::make('grupo')
                    ->label('Grupo')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                // Fecha de creación
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha creación')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                /*
             * Columnas que NO van al inicio (solo visibles si el usuario las activa)
             */
                Tables\Columns\TextColumn::make('tipo_proveedor')
                    ->label('Tipo proveedor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('destino_pago')
                    ->label('Destino pago')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('dias_pago')
                    ->label('Días pago')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pais_pago')
                    ->label('País pago')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('limite_credito')
                    ->label('Límite crédito')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('aplica_retencion_sn')
                    ->label('Retención')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('lineasNegocio.nombre')
                    ->label('Líneas de negocio')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->visible(fn() => auth()->user()->can('Actualizar')),

                Tables\Actions\DeleteAction::make()
                    ->label('Anular')
                    ->visible(fn() => auth()->user()->can('Borrar')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Anular seleccionados')
                    ->visible(fn() => auth()->user()->can('Borrar')),
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
            'index' => Pages\ListProveedors::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'edit' => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }

    protected static function getEmpresasProveedorOptions(Get $get): array
    {
        $lineasNegocioIds = $get('lineasNegocio');

        if (empty($lineasNegocioIds)) {
            return [];
        }

        $empresas = Empresa::whereIn('linea_negocio_id', $lineasNegocioIds)
            ->where('status_conexion', true)
            ->get();

        $empresasOptions = [];

        foreach ($empresas as $empresa) {
            $connectionName = self::getExternalConnectionName($empresa->id);
            if (! $connectionName) {
                continue;
            }

            try {
                $externalEmpresas = DB::connection($connectionName)
                    ->table('saeempr')
                    ->get();

                foreach ($externalEmpresas as $data_empresa) {
                    $optionKey = $empresa->id . '-' . trim($data_empresa->empr_cod_empr);
                    $optionLabel = $empresa->nombre_empresa . ' - ' . $data_empresa->empr_nom_empr;

                    $empresasOptions[$optionKey] = $optionLabel;
                }
            } catch (\Exception $e) {
                \Log::error('Error al conectar con la base de datos externa para la empresa ID ' . $empresa->id . ': ' . $e->getMessage());
            }
        }

        return $empresasOptions;
    }

    protected static function getEmpresasProveedorDefaults(Get $get): array
    {
        $options = self::getEmpresasProveedorOptions($get);
        $seleccionados = array_keys($options);

        $empresaId = $get('id_empresa');
        $amdgEmpresa = $get('admg_id_empresa');

        if ($empresaId && $amdgEmpresa) {
            $activeKey = $empresaId . '-' . trim((string) $amdgEmpresa);
            if (isset($options[$activeKey]) && ! in_array($activeKey, $seleccionados, true)) {
                $seleccionados[] = $activeKey;
            }
        }

        return $seleccionados;
    }
}
