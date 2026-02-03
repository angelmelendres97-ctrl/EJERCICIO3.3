<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\LineaNegocio;
use App\Models\UnidadMedida;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Config;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Hidden;


class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Sets up and returns the name of a persistent database connection for the given company.
     *
     * @param int $empresaId
     * @return string|null
     */
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
                Forms\Components\Section::make('Conexion e informacion principal')
                    ->schema([
                        Forms\Components\Select::make('id_empresa')
                            ->label('Conexion')
                            ->relationship('empresa', 'nombre_empresa')
                            ->searchable()
                            ->preload()
                            ->live()
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
                                    // Log or handle error, e.g., \Log::error($e->getMessage());
                                    return [];
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->live()
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
                                    // Log or handle error, e.g., \Log::error($e->getMessage());
                                    return [];
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('linea')
                            ->label('Línea')
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
                                        ->table('saelinp')
                                        ->where('linp_cod_empr', $amdgIdEmpresaCode)
                                        ->pluck('linp_des_linp', 'linp_cod_linp')
                                        ->all();
                                } catch (\Exception $e) {
                                    // Log or handle error, e.g., \Log::error($e->getMessage());
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('grupo')
                            ->label('Grupo')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $lineaCode = $get('linea');
                                $amdgIdEmpresaCode = $get('amdg_id_empresa');

                                if (!$empresaId || !$lineaCode || !$amdgIdEmpresaCode) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saegrpr')
                                        ->where('grpr_cod_linp', $lineaCode)
                                        ->where('grpr_cod_empr', $amdgIdEmpresaCode)
                                        ->pluck('grpr_des_grpr', 'grpr_cod_grpr')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('categoria')
                            ->label('Categoria')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $grupoCode = $get('grupo');
                                $amdgIdEmpresaCode = $get('amdg_id_empresa');

                                if (!$empresaId || !$grupoCode || !$amdgIdEmpresaCode) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saecate')
                                        ->where('cate_cod_grpr', $grupoCode)
                                        ->where('cate_cod_empr', $amdgIdEmpresaCode)
                                        ->pluck('cate_nom_cate', 'cate_cod_cate')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('marca')
                            ->label('Marca')
                            ->options(function (Get $get) {
                                $empresaId = $get('id_empresa');
                                $categoriaCode = $get('categoria');
                                $amdgIdEmpresaCode = $get('amdg_id_empresa');

                                if (!$empresaId || !$categoriaCode || !$amdgIdEmpresaCode) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saemarc')
                                        ->where('marc_cod_cate', $categoriaCode)
                                        ->where('marc_cod_empr', $amdgIdEmpresaCode)
                                        ->pluck('marc_des_marc', 'marc_cod_marc')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\Actions::make([

                            Action::make('search_inventory_tree_action')
                                ->label('Buscar Árbol Inventario')
                                ->disabled(fn(Get $get) => !$get('amdg_id_empresa'))
                                ->mountUsing(function ($form, Get $get) {
                                    $form->fill(['id_empresa' => $get('id_empresa'), 'amdg_id_empresa' => $get('amdg_id_empresa')]);
                                })
                                ->action(function () {
                                    // La lógica principal se manejará en el frontend con AlpineJS
                                })
                                ->form([
                                    Forms\Components\TextInput::make('search_term')
                                        ->label('Buscar Coincidencia')
                                        ->live(debounce: '500ms')
                                        ->extraAttributes(['wire:keydown.enter.prevent' => ''])
                                        ->autofocus(),
                                    Forms\Components\Hidden::make('id_empresa'),
                                    Forms\Components\Hidden::make('amdg_id_empresa'),

                                    // Hook para el script de AlpineJS
                                    Forms\Components\View::make('filament.hooks.set-product-tree-values'),

                                    Placeholder::make('search_results')
                                        ->disableLabel()
                                        ->content(function (Get $get) {
                                            $empresaId = $get('id_empresa');
                                            $amdgIdEmpresaCode = $get('amdg_id_empresa');
                                            $searchTerm = $get('search_term');

                                            if (!$empresaId || !$amdgIdEmpresaCode) return 'Seleccione una empresa antes de buscar.';
                                            if (!$searchTerm) return 'Ingrese un término de búsqueda.';

                                            try {
                                                $connectionName = self::getExternalConnectionName($empresaId);
                                                if (!$connectionName) return 'Error de conexión.';

                                                $searchTermUpper = strtoupper($searchTerm);
                                                $results = DB::connection($connectionName)
                                                    ->table('saelinp as l')
                                                    ->leftJoin('saegrpr as g', function ($join) use ($amdgIdEmpresaCode) {
                                                        $join->on('l.linp_cod_linp', '=', 'g.grpr_cod_linp')
                                                            ->where('g.grpr_cod_empr', '=', $amdgIdEmpresaCode);
                                                    })
                                                    ->leftJoin('saecate as c', function ($join) use ($amdgIdEmpresaCode) {
                                                        $join->on('g.grpr_cod_grpr', '=', 'c.cate_cod_grpr')
                                                            ->where('c.cate_cod_empr', '=', $amdgIdEmpresaCode);
                                                    })
                                                    ->leftJoin('saemarc as m', function ($join) use ($amdgIdEmpresaCode) {
                                                        $join->on('c.cate_cod_cate', '=', 'm.marc_cod_cate')
                                                            ->where('m.marc_cod_empr', '=', $amdgIdEmpresaCode);
                                                    })
                                                    ->select(
                                                        'l.linp_cod_linp',
                                                        'l.linp_des_linp',
                                                        'g.grpr_cod_grpr',
                                                        'g.grpr_des_grpr',
                                                        'c.cate_cod_cate',
                                                        'c.cate_nom_cate',
                                                        'm.marc_cod_marc',
                                                        'm.marc_des_marc'
                                                    )
                                                    ->where('l.linp_cod_empr', $amdgIdEmpresaCode)
                                                    ->where(function ($query) use ($searchTermUpper) {
                                                        $query->whereRaw('UPPER(l.linp_des_linp) LIKE ?', ["%{$searchTermUpper}%"])
                                                            ->orWhereRaw('UPPER(g.grpr_des_grpr) LIKE ?', ["%{$searchTermUpper}%"])
                                                            ->orWhereRaw('UPPER(c.cate_nom_cate) LIKE ?', ["%{$searchTermUpper}%"])
                                                            ->orWhereRaw('UPPER(m.marc_des_marc) LIKE ?', ["%{$searchTermUpper}%"]);
                                                    })
                                                    ->distinct()
                                                    ->limit(50)
                                                    ->get();

                                                if ($results->isEmpty()) return 'No se encontraron resultados.';

                                                $tableHtml = '<table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">';
                                                $tableHtml .= '<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"><tr>';
                                                $tableHtml .= '<th scope="col" class="px-6 py-3">Línea</th><th scope="col" class="px-6 py-3">Grupo</th><th scope="col" class="px-6 py-3">Categoría</th><th scope="col" class="px-6 py-3">Marca</th><th scope="col" class="px-6 py-3">Acción</th>';
                                                $tableHtml .= '</tr></thead><tbody>';

                                                foreach ($results as $row) {
                                                    $data = htmlspecialchars(json_encode([
                                                        'linea' => trim($row->linp_cod_linp ?? ''),
                                                        'grupo' => trim($row->grpr_cod_grpr ?? ''),
                                                        'categoria' => trim($row->cate_cod_cate ?? ''),
                                                        'marca' => trim($row->marc_cod_marc ?? ''),
                                                    ]), ENT_QUOTES, 'UTF-8');

                                                    $tableHtml .= '<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">';
                                                    $tableHtml .= '<td class="px-6 py-4">' . ($row->linp_des_linp ?? '') . '</td>';
                                                    $tableHtml .= '<td class="px-6 py-4">' . ($row->grpr_des_grpr ?? '') . '</td>';
                                                    $tableHtml .= '<td class="px-6 py-4">' . ($row->cate_nom_cate ?? '') . '</td>';
                                                    $tableHtml .= '<td class="px-6 py-4">' . ($row->marc_des_marc ?? '') . '</td>';
                                                    $tableHtml .= '<td class="px-6 py-4"><button type="button" x-on:click.prevent="$dispatch(\'fill-from-tree\', { data: ' . $data . ' });close()" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Seleccionar</button></td>';
                                                    $tableHtml .= '</tr>';
                                                }

                                                $tableHtml .= '</tbody></table>';
                                                return new HtmlString($tableHtml);
                                            } catch (\Exception $e) {
                                                \Log::error('Error en búsqueda de árbol de inventario: ' . $e->getMessage());
                                                return 'Error al realizar la búsqueda.';
                                            }
                                        }),
                                ])
                                ->modalWidth('4xl')
                                ->modalHeading('Buscar en Árbol de Inventario')
                        ])

                    ])->columns(2),
                Forms\Components\Section::make('Información Producto')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('Codigo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('detalle')
                            ->label('Detalle')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->required()
                            ->options([
                                1 => 'Servicio',
                                2 => 'Producto',
                            ])
                            ->default(2),

                        Forms\Components\Select::make('id_unidad_medida')
                            ->label('Unidad de Medida')
                            ->relationship('unidadMedida', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('stock_minimo')
                            ->label('Stock Mínimo')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('stock_maximo')
                            ->label('Stock Máximo')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Checkbox::make('iva_sn')
                            ->label('¿Aplica IVA?')
                            ->default(false),
                        Forms\Components\TextInput::make('porcentaje_iva')
                            ->label('Porcentaje IVA (%)')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Select::make('lineasNegocio')
                            ->label('Líneas de Negocio')
                            ->relationship('lineasNegocio', 'nombre')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->live()
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Sucursales y Bodegas Externas')
                    ->schema([
                        Forms\Components\CheckboxList::make('bodegas')
                            ->label('Bodegas para replicar')
                            ->options(function (Get $get) {
                                $lineasNegocioIds = $get('lineasNegocio');
                                if (empty($lineasNegocioIds)) {
                                    return [];
                                }

                                $empresas = Empresa::whereIn('linea_negocio_id', $lineasNegocioIds)
                                    ->where('status_conexion', true)->get();

                                $bodegasOptions = [];

                                foreach ($empresas as $empresa) {
                                    $connectionName = self::getExternalConnectionName($empresa->id);
                                    if (!$connectionName) {
                                        continue;
                                    }

                                    try {
                                        $externalBodegas = DB::connection($connectionName)
                                            ->table('saebode as b')
                                            ->join('saesubo as sb', 'b.bode_cod_bode', '=', 'sb.subo_cod_bode')
                                            ->join('saesucu as s', 'sb.subo_cod_sucu', '=', 's.sucu_cod_sucu')
                                            ->select('b.bode_cod_bode', 'b.bode_nom_bode', 's.sucu_nom_sucu')
                                            ->get();

                                        foreach ($externalBodegas as $bodega) {
                                            $optionKey = $empresa->id . '-' . trim($bodega->bode_cod_bode);
                                            $optionLabel = $empresa->nombre_empresa . ' - ' . $bodega->sucu_nom_sucu . ' - ' . $bodega->bode_nom_bode;
                                            $bodegasOptions[$optionKey] = $optionLabel;
                                        }
                                    } catch (\Exception $e) {
                                        \Log::error('Error al conectar con la base de datos externa para la empresa ID ' . $empresa->id . ': ' . $e->getMessage());
                                        continue;
                                    }
                                }

                                return $bodegasOptions;
                            })
                            ->afterStateHydrated(function (Get $get, callable $set) {

                                $lineasNegocioIds = $get('lineasNegocio');
                                $sku = $get('sku');

                                if (empty($lineasNegocioIds)) {
                                    return;
                                }

                                $seleccionados = [];

                                $empresas = Empresa::whereIn('linea_negocio_id', $lineasNegocioIds)
                                    ->where('status_conexion', true)
                                    ->get();

                                foreach ($empresas as $empresa) {

                                    $connectionName = self::getExternalConnectionName($empresa->id);
                                    if (!$connectionName) {
                                        continue;
                                    }

                                    try {


                                        $externalBodegas = DB::connection($connectionName)
                                            ->table('saebode as b')
                                            ->join('saesubo as sb', 'b.bode_cod_bode', '=', 'sb.subo_cod_bode')
                                            ->join('saesucu as s', 'sb.subo_cod_sucu', '=', 's.sucu_cod_sucu')
                                            ->select('b.bode_cod_bode', 'b.bode_nom_bode', 's.sucu_nom_sucu', 's.sucu_cod_empr')
                                            ->get();

                                        foreach ($externalBodegas as $bodega) {
                                            $optionKey = $empresa->id . '-' . trim($bodega->bode_cod_bode);
                                            $optionLabel = $empresa->nombre_empresa . ' - ' . $bodega->sucu_nom_sucu . ' - ' . $bodega->bode_nom_bode;
                                            $bodegasOptions[$optionKey] = $optionLabel;

                                            // -------------------------------
                                            // VERIFICACIÓN DE EXISTENCIA
                                            // -------------------------------
                                            $existeProdBode = DB::connection($connectionName)
                                                ->table('saeprbo')
                                                ->where('prbo_cod_empr', $bodega->sucu_cod_empr)
                                                ->where('prbo_cod_bode', trim($bodega->bode_cod_bode))
                                                ->where('prbo_cod_prod', $sku) // proveedor
                                                ->exists();

                                            // Si existe → lo marcamos
                                            if ($existeProdBode) {
                                                $seleccionados[] = $optionKey;
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        \Log::error("Error en conexión externa empresa {$empresa->id}: " . $e->getMessage());
                                        continue;
                                    }
                                }

                                // -------------------------------
                                // Setear los checkboxes marcados
                                // -------------------------------
                                $set('bodegas', $seleccionados);
                            })
                            ->columns(2)
                    ])->columns(1),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('empresa_nombre')
                    ->label('Empresa')
                    ->getStateUsing(fn($record) => $record->empresa?->nombre_empresa ?? $record->empresa_nombre ?? null)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->formatStateUsing(fn($state): string => match ((int) $state) {
                        1 => 'Servicio',
                        2 => 'Producto',
                        default => 'Desconocido',
                    })
                    ->color(fn($state): string => match ((int) $state) {
                        1 => 'warning',
                        2 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad de Medida')
                    ->getStateUsing(fn($record) => $record->unidadMedida?->nombre ?? $record->unidad_medida ?? null)
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_maximo')
                    ->label('Stock Máximo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('iva_sn')
                    ->label('IVA')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        $iva = $record->iva_sn ?? false;
                        if (is_string($iva)) {
                            return strtoupper($iva) === 'S';
                        }

                        return (bool) $iva;
                    }),
                Tables\Columns\TextColumn::make('porcentaje_iva')
                    ->label('IVA (%)')
                    ->numeric(2)
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('lineas_negocio')
                    ->badge()
                    ->label('Líneas de Negocio')
                    ->getStateUsing(function ($record) {
                        if (isset($record->empresa_nombre)) {
                            return $record->lineas_negocio ?? [];
                        }

                        return $record->lineasNegocio?->pluck('nombre')->all() ?? [];
                    })
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->color('success'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('jireh')
                    ->label('JIREH')
                    ->form([
                        Forms\Components\Select::make('conexion')
                            ->label('Conexión')
                            ->options(Empresa::query()->pluck('nombre_empresa', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('empresa', null)),
                        Forms\Components\Select::make('empresa')
                            ->label('Empresa')
                            ->options(function (Forms\Get $get): array {
                                $empresaId = $get('conexion');
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
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('sucursal', null)),
                        Forms\Components\Select::make('sucursal')
                            ->label('Sucursal')
                            ->options(function (Forms\Get $get): array {
                                $empresaId = $get('conexion');
                                $empresaCodigo = $get('empresa');

                                if (!$empresaId || !$empresaCodigo) {
                                    return [];
                                }

                                $connectionName = self::getExternalConnectionName($empresaId);
                                if (!$connectionName) {
                                    return [];
                                }

                                try {
                                    return DB::connection($connectionName)
                                        ->table('saesucu')
                                        ->where('sucu_cod_empr', $empresaCodigo)
                                        ->pluck('sucu_nom_sucu', 'sucu_cod_sucu')
                                        ->all();
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3)
                    ->query(fn(Builder $query): Builder => $query)
                    ->visible(fn($livewire) => $livewire->activeTab === 'jireh'),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        1 => 'Servicio',
                        2 => 'Producto',
                    ]),
                Tables\Filters\TernaryFilter::make('iva_sn')
                    ->label('¿Aplica IVA?'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record, $livewire) => $livewire->activeTab !== 'jireh' && auth()->user()->can('Actualizar')),
                Tables\Actions\Action::make('editar_jireh')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn($record, $livewire) => $livewire->activeTab === 'jireh' && auth()->user()->can('Actualizar'))
                    ->action(function ($record, $livewire): void {
                        $producto = $livewire->syncJirehProductoToLocal($record);
                        $livewire->redirect(ProductoResource::getUrl('edit', ['record' => $producto]));
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record, $livewire) => $livewire->activeTab !== 'jireh' && auth()->user()->can('Borrar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->can('Borrar')),
                ]),
            ]
            );
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
