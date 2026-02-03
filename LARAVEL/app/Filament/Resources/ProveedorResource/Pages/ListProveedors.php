<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use App\Models\Empresa;
use App\Models\Proveedores;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ListProveedors extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

    public ?int $jirehConexion = null;
    public ?string $jirehEmpresa = null;
    public ?string $jirehSucursal = null;
    public bool $jirehPreviewMode = false;
    public array $jirehPreviewRecords = [];

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', false)),
            'anuladas' => Tab::make('Anuladas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', true)),
            'jireh' => Tab::make('JIREH')
                ->modifyQueryUsing(function (Builder $query): Builder {
                    if (!$this->jirehConexion || !$this->jirehEmpresa || !$this->jirehSucursal) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query
                        ->where('id_empresa', $this->jirehConexion)
                        ->where('admg_id_empresa', $this->jirehEmpresa)
                        ->where('admg_id_sucursal', $this->jirehSucursal);
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

                            $connectionName = ProveedorResource::getExternalConnectionName((int) $empresaId);
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

                            $connectionName = ProveedorResource::getExternalConnectionName((int) $empresaId);
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
                    if (($data['accion'] ?? 'insertar') === 'visualizar') {
                        $this->previewJirehProveedores($data);
                        return;
                    }
                    $this->syncJirehProveedores($data);
                }),
        ];
    }

    protected function getTableRecords(): Collection
    {
        if ($this->jirehPreviewMode && $this->activeTab === 'jireh') {
            return collect($this->jirehPreviewRecords);
        }

        return parent::getTableRecords();
    }

    protected function fetchJirehDefaults(string $connectionName, string $empresaCode): array
    {
        return [
            'tipo' => DB::connection($connectionName)
                ->table('comercial.tipo_iden_clpv')
                ->value('identificacion'),
            'grupo' => DB::connection($connectionName)
                ->table('saegrpv')
                ->where('grpv_cod_empr', $empresaCode)
                ->where('grpv_cod_modu', 4)
                ->value('grpv_nom_grpv'),
            'zona' => DB::connection($connectionName)
                ->table('saezona')
                ->where('zona_cod_empr', $empresaCode)
                ->value('zona_nom_zona'),
            'flujo_caja' => DB::connection($connectionName)
                ->table('saecact')
                ->where('cact_cod_empr', $empresaCode)
                ->value('cact_nom_cact'),
            'tipo_proveedor' => DB::connection($connectionName)
                ->table('saetprov')
                ->where('tprov_cod_empr', $empresaCode)
                ->value('tprov_des_tprov'),
            'forma_pago' => DB::connection($connectionName)
                ->table('saefpagop')
                ->where('fpagop_cod_empr', $empresaCode)
                ->value('fpagop_des_fpagop'),
            'destino_pago' => DB::connection($connectionName)
                ->table('saetpago')
                ->where('tpago_cod_empr', $empresaCode)
                ->value('tpago_des_tpago'),
            'pais_pago' => DB::connection($connectionName)
                ->table('saepaisp')
                ->value('paisp_des_paisp'),
        ];
    }

    protected function fetchJirehProveedores(string $connectionName, string $empresaCode, string $sucursalCode): Collection
    {
        return DB::connection($connectionName)
            ->table('saeclpv as clpv')
            ->leftJoin('saetlcp as tlcp', function ($join) {
                $join->on('tlcp.tlcp_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('tlcp.tlcp_cod_sucu', '=', 'clpv.clpv_cod_sucu')
                    ->on('tlcp.tlcp_cod_clpv', '=', 'clpv.clpv_cod_clpv');
            })
            ->leftJoin('saeemai as emai', function ($join) {
                $join->on('emai.emai_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('emai.emai_cod_sucu', '=', 'clpv.clpv_cod_sucu')
                    ->on('emai.emai_cod_clpv', '=', 'clpv.clpv_cod_clpv');
            })
            ->leftJoin('saedire as dire', function ($join) {
                $join->on('dire.dire_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('dire.dire_cod_sucu', '=', 'clpv.clpv_cod_sucu')
                    ->on('dire.dire_cod_clpv', '=', 'clpv.clpv_cod_clpv');
            })
            ->leftJoin('saegrpv as grpv', function ($join) {
                $join->on('grpv.grpv_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('grpv.grpv_cod_grpv', '=', 'clpv.clpv_cod_grpv')
                    ->where('grpv.grpv_cod_modu', 4);
            })
            ->leftJoin('saezona as zona', function ($join) {
                $join->on('zona.zona_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('zona.zona_cod_zona', '=', 'clpv.clpv_cod_zona');
            })
            ->leftJoin('saecact as cact', function ($join) {
                $join->on('cact.cact_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('cact.cact_cod_cact', '=', 'clpv.clpv_cod_cact');
            })
            ->leftJoin('saetprov as tprov', function ($join) {
                $join->on('tprov.tprov_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('tprov.tprov_cod_tprov', '=', 'clpv.clpv_cod_tprov');
            })
            ->leftJoin('saefpagop as fpagop', function ($join) {
                $join->on('fpagop.fpagop_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('fpagop.fpagop_cod_fpagop', '=', 'clpv.clpv_cod_fpagop');
            })
            ->leftJoin('saetpago as tpago', function ($join) {
                $join->on('tpago.tpago_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('tpago.tpago_cod_tpago', '=', 'clpv.clpv_cod_tpago');
            })
            ->leftJoin('saepaisp as paisp', 'paisp.paisp_cod_paisp', '=', 'clpv.clpv_cod_paisp')
            ->leftJoin('comercial.tipo_iden_clpv as tipo', 'tipo.tipo', '=', 'clpv.clv_con_clpv')
            ->where('clpv.clpv_cod_empr', $empresaCode)
            ->where('clpv.clpv_cod_sucu', $sucursalCode)
            ->where('clpv.clpv_clopv_clpv', 'PV')
            ->select([
                'clpv.clpv_cod_clpv',
                'clpv.clpv_ruc_clpv as ruc',
                'clpv.clpv_nom_clpv as nombre',
                'clpv.clpv_nom_come as nombre_comercial',
                'clpv.clpv_ret_sn as aplica_retencion',
                'clpv.clpv_est_clpv as estado',
                'clpv.clpv_pro_pago as dias_pago',
                'clpv.clpv_lim_cred as limite_credito',
                'tipo.identificacion as tipo_identificacion',
                'grpv.grpv_nom_grpv as grupo',
                'zona.zona_nom_zona as zona',
                'cact.cact_nom_cact as flujo_caja',
                'tprov.tprov_des_tprov as tipo_proveedor',
                'fpagop.fpagop_des_fpagop as forma_pago',
                'tpago.tpago_des_tpago as destino_pago',
                'paisp.paisp_des_paisp as pais_pago',
                DB::raw('MAX(tlcp.tlcp_tlf_tlcp) as telefono'),
                DB::raw('MAX(emai.emai_ema_emai) as correo'),
                DB::raw('MAX(dire.dire_dir_dire) as direccion'),
            ])
            ->groupBy(
                'clpv.clpv_cod_clpv',
                'clpv.clpv_ruc_clpv',
                'clpv.clpv_nom_clpv',
                'clpv.clpv_nom_come',
                'clpv.clpv_ret_sn',
                'clpv.clpv_est_clpv',
                'clpv.clpv_pro_pago',
                'clpv.clpv_lim_cred',
                'tipo.identificacion',
                'grpv.grpv_nom_grpv',
                'zona.zona_nom_zona',
                'cact.cact_nom_cact',
                'tprov.tprov_des_tprov',
                'fpagop.fpagop_des_fpagop',
                'tpago.tpago_des_tpago',
                'paisp.paisp_des_paisp',
            )
            ->get();
    }

    protected function buildPreviewProveedor(object $proveedor, int $conexionId, string $empresaCode, string $sucursalCode, array $defaults): Proveedores
    {
        $preview = new Proveedores();
        $preview->id_empresa = $conexionId;
        $preview->admg_id_empresa = $empresaCode;
        $preview->admg_id_sucursal = $sucursalCode;
        $preview->ruc = $proveedor->ruc;
        $preview->tipo = $proveedor->tipo_identificacion ?: $defaults['tipo'];
        $preview->nombre = $proveedor->nombre;
        $preview->nombre_comercial = $proveedor->nombre_comercial ?: $proveedor->nombre;
        $preview->grupo = $proveedor->grupo ?: $defaults['grupo'];
        $preview->zona = $proveedor->zona ?: $defaults['zona'];
        $preview->flujo_caja = $proveedor->flujo_caja ?: $defaults['flujo_caja'];
        $preview->tipo_proveedor = $proveedor->tipo_proveedor ?: $defaults['tipo_proveedor'];
        $preview->forma_pago = $proveedor->forma_pago ?: $defaults['forma_pago'];
        $preview->destino_pago = $proveedor->destino_pago ?: $defaults['destino_pago'];
        $preview->pais_pago = $proveedor->pais_pago ?: $defaults['pais_pago'];
        $preview->dias_pago = (int) ($proveedor->dias_pago ?? 0);
        $preview->limite_credito = (float) ($proveedor->limite_credito ?? 0);
        $preview->aplica_retencion_sn = strtoupper((string) $proveedor->aplica_retencion) === 'S';
        $preview->telefono = $proveedor->telefono;
        $preview->direcccion = $proveedor->direccion;
        $preview->correo = $proveedor->correo;
        $preview->anulada = strtoupper((string) $proveedor->estado) !== 'A';
        $preview->created_at = now();
        $preview->setRelation('empresa', Empresa::find($conexionId));
        $preview->setRelation('lineasNegocio', collect());

        return $preview;
    }

    protected function previewJirehProveedores(array $data): void
    {
        $conexionId = (int) ($data['conexion'] ?? 0);
        $empresaCode = $data['empresa'] ?? null;
        $sucursalCode = $data['sucursal'] ?? null;

        if (!$conexionId || !$empresaCode || !$sucursalCode) {
            Notification::make()
                ->title('Selecciona conexión, empresa y sucursal para continuar.')
                ->warning()
                ->send();
            return;
        }

        $connectionName = ProveedorResource::getExternalConnectionName($conexionId);
        if (!$connectionName) {
            Notification::make()
                ->title('No se pudo establecer la conexión con la empresa seleccionada.')
                ->danger()
                ->send();
            return;
        }

        $defaults = $this->fetchJirehDefaults($connectionName, (string) $empresaCode);
        $proveedores = $this->fetchJirehProveedores($connectionName, (string) $empresaCode, (string) $sucursalCode);

        $this->jirehConexion = $conexionId;
        $this->jirehEmpresa = (string) $empresaCode;
        $this->jirehSucursal = (string) $sucursalCode;
        $this->jirehPreviewMode = true;
        $this->jirehPreviewRecords = $proveedores
            ->map(fn($proveedor) => $this->buildPreviewProveedor($proveedor, $conexionId, (string) $empresaCode, (string) $sucursalCode, $defaults))
            ->all();

        $this->resetTable();

        Notification::make()
            ->title('Vista previa JIREH cargada.')
            ->body('Revisa los registros en la pestaña JIREH. No se insertaron datos en la base local.')
            ->success()
            ->send();
    }

    protected function syncJirehProveedores(array $data): void
    {
        $conexionId = (int) ($data['conexion'] ?? 0);
        $empresaCode = $data['empresa'] ?? null;
        $sucursalCode = $data['sucursal'] ?? null;

        if (!$conexionId || !$empresaCode || !$sucursalCode) {
            Notification::make()
                ->title('Selecciona conexión, empresa y sucursal para continuar.')
                ->warning()
                ->send();
            return;
        }

        $connectionName = ProveedorResource::getExternalConnectionName($conexionId);
        if (!$connectionName) {
            Notification::make()
                ->title('No se pudo establecer la conexión con la empresa seleccionada.')
                ->danger()
                ->send();
            return;
        }

        $defaults = $this->fetchJirehDefaults($connectionName, (string) $empresaCode);
        $proveedores = $this->fetchJirehProveedores($connectionName, (string) $empresaCode, (string) $sucursalCode);

        $empresa = Empresa::find($conexionId);
        $lineaNegocioId = $empresa?->linea_negocio_id;
        $syncCount = 0;

        foreach ($proveedores as $proveedor) {
            $local = Proveedores::updateOrCreate(
                [
                    'id_empresa' => $conexionId,
                    'admg_id_empresa' => $empresaCode,
                    'admg_id_sucursal' => $sucursalCode,
                    'ruc' => $proveedor->ruc,
                ],
                [
                    'tipo' => $proveedor->tipo_identificacion ?: $defaults['tipo'],
                    'nombre' => $proveedor->nombre,
                    'nombre_comercial' => $proveedor->nombre_comercial ?: $proveedor->nombre,
                    'grupo' => $proveedor->grupo ?: $defaults['grupo'],
                    'zona' => $proveedor->zona ?: $defaults['zona'],
                    'flujo_caja' => $proveedor->flujo_caja ?: $defaults['flujo_caja'],
                    'tipo_proveedor' => $proveedor->tipo_proveedor ?: $defaults['tipo_proveedor'],
                    'forma_pago' => $proveedor->forma_pago ?: $defaults['forma_pago'],
                    'destino_pago' => $proveedor->destino_pago ?: $defaults['destino_pago'],
                    'pais_pago' => $proveedor->pais_pago ?: $defaults['pais_pago'],
                    'dias_pago' => (int) ($proveedor->dias_pago ?? 0),
                    'limite_credito' => (float) ($proveedor->limite_credito ?? 0),
                    'aplica_retencion_sn' => strtoupper((string) $proveedor->aplica_retencion) === 'S',
                    'telefono' => $proveedor->telefono,
                    'direcccion' => $proveedor->direccion,
                    'correo' => $proveedor->correo,
                    'anulada' => strtoupper((string) $proveedor->estado) !== 'A',
                ],
            );

            if ($lineaNegocioId) {
                $local->lineasNegocio()->syncWithoutDetaching([$lineaNegocioId]);
            }

            $syncCount++;
        }

        $this->jirehConexion = $conexionId;
        $this->jirehEmpresa = (string) $empresaCode;
        $this->jirehSucursal = (string) $sucursalCode;
        $this->jirehPreviewMode = false;
        $this->jirehPreviewRecords = [];

        $this->resetTable();

        Notification::make()
            ->title("Proveedores JIREH cargados: {$syncCount}")
            ->success()
            ->send();
    }
}
