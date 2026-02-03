<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    public ?int $jirehConexion = null;
    public ?string $jirehEmpresa = null;
    public ?string $jirehSucursal = null;
    public bool $jirehSoloVisualizar = false;
    public array $jirehPreviewRecords = [];

    public function getTabs(): array
    {
        return [
            'locales' => Tab::make('Locales'),
            'jireh' => Tab::make('JIREH')
                ->modifyQueryUsing(function (Builder $query): Builder {
                    if (!$this->jirehConexion || !$this->jirehEmpresa || !$this->jirehSucursal) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query
                        ->where('id_empresa', $this->jirehConexion)
                        ->where('amdg_id_empresa', $this->jirehEmpresa)
                        ->where('amdg_id_sucursal', $this->jirehSucursal);
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('cargarJireh')
                ->label('Sincronizar con JIREH')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn($livewire) => $livewire->activeTab === 'jireh')
                ->form([
                    Select::make('accion')
                        ->label('Acción')
                        ->options([
                            'insertar' => 'Cargar e insertar',
                            'visualizar' => 'Visualizar',
                        ])
                        ->default('insertar')
                        ->required(),
                    Select::make('conexion')
                        ->label('Conexión')
                        ->options(Empresa::query()->pluck('nombre_empresa', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    Select::make('empresa')
                        ->label('Empresa')
                        ->options(function (Get $get): array {
                            $empresaId = $get('conexion');
                            if (!$empresaId) {
                                return [];
                            }

                            $connectionName = ProductoResource::getExternalConnectionName((int) $empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            return DB::connection($connectionName)
                                ->table('saeempr')
                                ->pluck('empr_nom_empr', 'empr_cod_empr')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    Select::make('sucursal')
                        ->label('Sucursal')
                        ->options(function (Get $get): array {
                            $empresaId = $get('conexion');
                            $empresaCode = $get('empresa');
                            if (!$empresaId || !$empresaCode) {
                                return [];
                            }

                            $connectionName = ProductoResource::getExternalConnectionName((int) $empresaId);
                            if (!$connectionName) {
                                return [];
                            }

                            return DB::connection($connectionName)
                                ->table('saesucu')
                                ->where('sucu_cod_empr', $empresaCode)
                                ->pluck('sucu_nom_sucu', 'sucu_cod_sucu')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $accion = $data['accion'] ?? 'insertar';
                    if ($accion === 'visualizar') {
                        $this->previewJirehProductos($data);
                        return;
                    }

                    $this->syncJirehProductos($data);
                }),
        ];
    }

    protected function getTableRecords(): Collection
    {
        if ($this->activeTab === 'jireh' && $this->jirehSoloVisualizar) {
            return collect($this->jirehPreviewRecords);
        }

        return parent::getTableRecords();
    }

    protected function previewJirehProductos(array $data): void
    {
        [$productos, $conexionId, $empresaCode, $sucursalCode] = $this->fetchJirehProductos($data);

        if (!$conexionId || !$empresaCode || !$sucursalCode) {
            return;
        }

        $this->jirehPreviewRecords = $productos->map(function ($producto) use ($conexionId, $empresaCode, $sucursalCode) {
            return Producto::make([
                'id' => "{$conexionId}-{$empresaCode}-{$sucursalCode}-{$producto->sku}",
                'id_empresa' => $conexionId,
                'amdg_id_empresa' => $empresaCode,
                'amdg_id_sucursal' => $sucursalCode,
                'sku' => $producto->sku,
                'linea' => $producto->linea,
                'grupo' => $producto->grupo,
                'categoria' => $producto->categoria,
                'marca' => $producto->marca,
                'nombre' => $producto->nombre,
                'detalle' => $producto->detalle,
                'tipo' => (int) $producto->tipo,
                'stock_minimo' => (float) ($producto->stock_minimo ?? 0),
                'stock_maximo' => (float) ($producto->stock_maximo ?? 0),
                'iva_sn' => strtoupper((string) $producto->iva_sn) === 'S',
                'porcentaje_iva' => (float) ($producto->porcentaje_iva ?? 0),
            ]);
        })->all();

        $this->jirehSoloVisualizar = true;
        $this->jirehConexion = $conexionId;
        $this->jirehEmpresa = (string) $empresaCode;
        $this->jirehSucursal = (string) $sucursalCode;
        $this->resetTable();

        Notification::make()
            ->title("Productos JIREH visualizados: {$productos->count()}")
            ->success()
            ->send();
    }

    protected function syncJirehProductos(array $data): void
    {
        [$productos, $conexionId, $empresaCode, $sucursalCode] = $this->fetchJirehProductos($data);

        if (!$conexionId || !$empresaCode || !$sucursalCode) {
            return;
        }

        $empresa = Empresa::find($conexionId);
        $lineaNegocioId = $empresa?->linea_negocio_id;
        $userId = Auth::id() ?? 1;
        $syncCount = 0;

        foreach ($productos as $producto) {
            $unidadNombre = trim((string) ($producto->unidad_medida ?? ''));
            if ($unidadNombre === '') {
                $unidadNombre = 'UNIDAD';
            }

            $unidad = UnidadMedida::firstOrCreate(
                ['nombre' => $unidadNombre],
                [
                    'siglas' => $unidadNombre,
                    'id_usuario' => $userId,
                    'fecha_creacion' => now(),
                ],
            );

            $localProducto = Producto::updateOrCreate(
                [
                    'id_empresa' => $conexionId,
                    'amdg_id_empresa' => $empresaCode,
                    'amdg_id_sucursal' => $sucursalCode,
                    'sku' => $producto->sku,
                ],
                [
                    'linea' => $producto->linea,
                    'grupo' => $producto->grupo,
                    'categoria' => $producto->categoria,
                    'marca' => $producto->marca,
                    'nombre' => $producto->nombre,
                    'detalle' => $producto->detalle,
                    'tipo' => (int) $producto->tipo,
                    'id_unidad_medida' => $unidad->id,
                    'stock_minimo' => (float) ($producto->stock_minimo ?? 0),
                    'stock_maximo' => (float) ($producto->stock_maximo ?? 0),
                    'iva_sn' => strtoupper((string) $producto->iva_sn) === 'S',
                    'porcentaje_iva' => (float) ($producto->porcentaje_iva ?? 0),
                ],
            );

            if ($lineaNegocioId) {
                $localProducto->lineasNegocio()->syncWithoutDetaching([$lineaNegocioId]);
            }

            $syncCount++;
        }

        $this->jirehSoloVisualizar = false;
        $this->jirehPreviewRecords = [];
        $this->jirehConexion = $conexionId;
        $this->jirehEmpresa = (string) $empresaCode;
        $this->jirehSucursal = (string) $sucursalCode;

        $this->resetTable();

        Notification::make()
            ->title("Productos JIREH cargados: {$syncCount}")
            ->success()
            ->send();
    }

    protected function fetchJirehProductos(array $data): array
    {
        $conexionId = (int) ($data['conexion'] ?? 0);
        $empresaCode = $data['empresa'] ?? null;
        $sucursalCode = $data['sucursal'] ?? null;

        if (!$conexionId || !$empresaCode || !$sucursalCode) {
            Notification::make()
                ->title('Selecciona conexión, empresa y sucursal para continuar.')
                ->warning()
                ->send();
            return [collect(), 0, null, null];
        }

        $connectionName = ProductoResource::getExternalConnectionName($conexionId);
        if (!$connectionName) {
            Notification::make()
                ->title('No se pudo establecer la conexión con la empresa seleccionada.')
                ->danger()
                ->send();
            return [collect(), 0, null, null];
        }

        $productos = DB::connection($connectionName)
            ->table('saeprod as prod')
            ->join('saeprbo as prbo', function ($join) {
                $join->on('prbo.prbo_cod_prod', '=', 'prod.prod_cod_prod')
                    ->on('prbo.prbo_cod_empr', '=', 'prod.prod_cod_empr')
                    ->on('prbo.prbo_cod_sucu', '=', 'prod.prod_cod_sucu');
            })
            ->leftJoin('saeunid as unid', 'unid.unid_cod_unid', '=', 'prbo.prbo_cod_unid')
            ->where('prod.prod_cod_empr', $empresaCode)
            ->where('prod.prod_cod_sucu', $sucursalCode)
            ->select([
                'prod.prod_cod_prod as sku',
                'prod.prod_nom_prod as nombre',
                'prod.prod_det_prod as detalle',
                'prod.prod_cod_tpro as tipo',
                'prod.prod_cod_linp as linea',
                'prod.prod_cod_grpr as grupo',
                'prod.prod_cod_cate as categoria',
                'prod.prod_cod_marc as marca',
                DB::raw('MAX(prbo.prbo_smi_prod) as stock_minimo'),
                DB::raw('MAX(prbo.prbo_sma_prod) as stock_maximo'),
                DB::raw('MAX(prbo.prbo_iva_sino) as iva_sn'),
                DB::raw('MAX(prbo.prbo_iva_porc) as porcentaje_iva'),
                DB::raw('MAX(unid.unid_nom_unid) as unidad_medida'),
            ])
            ->groupBy(
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prod.prod_det_prod',
                'prod.prod_cod_tpro',
                'prod.prod_cod_linp',
                'prod.prod_cod_grpr',
                'prod.prod_cod_cate',
                'prod.prod_cod_marc',
            )
            ->get();

        return [$productos, $conexionId, $empresaCode, $sucursalCode];
    }
}
