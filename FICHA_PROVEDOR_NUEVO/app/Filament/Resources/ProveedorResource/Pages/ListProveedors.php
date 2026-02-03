<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use App\Models\Empresa;
use App\Models\Proveedores;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListProveedors extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

    protected ?string $jirehSyncKey = null;

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', false)),
            'anuladas' => Tab::make('Anuladas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', true)),
            'jireh' => Tab::make('JIREH'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->activeTab === 'jireh') {
            $context = $this->getJirehContext();
            $this->syncJirehProveedores($context);

            if (empty($context['id_empresa']) || empty($context['admg_id_empresa']) || empty($context['admg_id_sucursal'])) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->where('id_empresa', $context['id_empresa'])
                ->where('admg_id_empresa', $context['admg_id_empresa'])
                ->where('admg_id_sucursal', $context['admg_id_sucursal']);
        }

        return $query;
    }

    private function getJirehContext(): array
    {
        $filters = $this->tableFilters ?? [];
        $context = data_get($filters, 'jireh_context');

        return is_array($context) ? $context : [];
    }

    private function syncJirehProveedores(array $context): void
    {
        $empresaId = isset($context['id_empresa']) ? (int) $context['id_empresa'] : null;
        $empresaCodigo = isset($context['admg_id_empresa']) ? (int) $context['admg_id_empresa'] : null;
        $sucursalCodigo = isset($context['admg_id_sucursal']) ? (int) $context['admg_id_sucursal'] : null;

        if (!$empresaId || !$empresaCodigo || !$sucursalCodigo) {
            return;
        }

        $syncKey = "{$empresaId}|{$empresaCodigo}|{$sucursalCodigo}";
        if ($this->jirehSyncKey === $syncKey) {
            return;
        }
        $this->jirehSyncKey = $syncKey;

        $connectionName = ProveedorResource::getExternalConnectionName($empresaId);
        if (!$connectionName) {
            return;
        }

        try {
            $proveedores = DB::connection($connectionName)
                ->table('saeclpv as c')
                ->leftJoin('saegrpv as g', function ($join) use ($empresaCodigo) {
                    $join->on('c.grpv_cod_grpv', '=', 'g.grpv_cod_grpv')
                        ->where('g.grpv_cod_empr', '=', $empresaCodigo);
                })
                ->leftJoin('saezona as z', function ($join) use ($empresaCodigo) {
                    $join->on('c.clpv_cod_zona', '=', 'z.zona_cod_zona')
                        ->where('z.zona_cod_empr', '=', $empresaCodigo);
                })
                ->leftJoin('saecact as cc', function ($join) use ($empresaCodigo) {
                    $join->on('c.clpv_cod_cact', '=', 'cc.cact_cod_cact')
                        ->where('cc.cact_cod_empr', '=', $empresaCodigo);
                })
                ->leftJoin('saetprov as tp', function ($join) use ($empresaCodigo) {
                    $join->on('c.clpv_cod_tprov', '=', 'tp.tprov_cod_tprov')
                        ->where('tp.tprov_cod_empr', '=', $empresaCodigo);
                })
                ->leftJoin('saefpagop as fp', function ($join) use ($empresaCodigo) {
                    $join->on('c.clpv_cod_fpagop', '=', 'fp.fpagop_cod_fpagop')
                        ->where('fp.fpagop_cod_empr', '=', $empresaCodigo);
                })
                ->leftJoin('saetpago as tpago', function ($join) use ($empresaCodigo) {
                    $join->on('c.clpv_cod_tpago', '=', 'tpago.tpago_cod_tpago')
                        ->where('tpago.tpago_cod_empr', '=', $empresaCodigo);
                })
                ->leftJoin('saepaisp as pp', 'c.clpv_cod_paisp', '=', 'pp.paisp_cod_paisp')
                ->leftJoin('comercial.tipo_iden_clpv as ti', 'c.clv_con_clpv', '=', 'ti.tipo')
                ->leftJoin('saetlcp as t', function ($join) use ($empresaCodigo, $sucursalCodigo) {
                    $join->on('c.clpv_cod_clpv', '=', 't.tlcp_cod_clpv')
                        ->where('t.tlcp_cod_empr', '=', $empresaCodigo)
                        ->where('t.tlcp_cod_sucu', '=', $sucursalCodigo);
                })
                ->leftJoin('saeemai as e', function ($join) use ($empresaCodigo, $sucursalCodigo) {
                    $join->on('c.clpv_cod_clpv', '=', 'e.emai_cod_clpv')
                        ->where('e.emai_cod_empr', '=', $empresaCodigo)
                        ->where('e.emai_cod_sucu', '=', $sucursalCodigo);
                })
                ->leftJoin('saedire as d', function ($join) use ($empresaCodigo, $sucursalCodigo) {
                    $join->on('c.clpv_cod_clpv', '=', 'd.dire_cod_clpv')
                        ->where('d.dire_cod_empr', '=', $empresaCodigo)
                        ->where('d.dire_cod_sucu', '=', $sucursalCodigo);
                })
                ->where('c.clpv_cod_empr', $empresaCodigo)
                ->where('c.clpv_cod_sucu', $sucursalCodigo)
                ->where('c.clpv_clopv_clpv', 'PV')
                ->groupBy(
                    'c.clpv_cod_clpv',
                    'c.clpv_cod_empr',
                    'c.clpv_cod_sucu',
                    'c.clv_con_clpv',
                    'c.clpv_ruc_clpv',
                    'c.clpv_nom_clpv',
                    'c.clpv_nom_come',
                    'c.clpv_cod_zona',
                    'c.clpv_cod_cact',
                    'c.clpv_cod_tprov',
                    'c.clpv_cod_fpagop',
                    'c.clpv_cod_tpago',
                    'c.clpv_cod_paisp',
                    'c.clpv_pro_pago',
                    'c.clpv_lim_cred',
                    'c.clpv_ret_sn',
                    'c.clpv_est_clpv',
                    'g.grpv_nom_grpv',
                    'z.zona_nom_zona',
                    'cc.cact_nom_cact',
                    'tp.tprov_des_tprov',
                    'fp.fpagop_des_fpagop',
                    'tpago.tpago_des_tpago',
                    'pp.paisp_des_paisp',
                    'ti.identificacion'
                )
                ->select([
                    'c.clpv_cod_clpv',
                    'c.clpv_ruc_clpv',
                    'c.clpv_nom_clpv',
                    'c.clpv_nom_come',
                    'c.clpv_pro_pago',
                    'c.clpv_lim_cred',
                    'c.clpv_ret_sn',
                    'c.clpv_est_clpv',
                    'g.grpv_nom_grpv',
                    'z.zona_nom_zona',
                    'cc.cact_nom_cact',
                    'tp.tprov_des_tprov',
                    'fp.fpagop_des_fpagop',
                    'tpago.tpago_des_tpago',
                    'pp.paisp_des_paisp',
                    'ti.identificacion',
                    DB::raw('MAX(t.tlcp_tlf_tlcp) as telefono'),
                    DB::raw('MAX(e.emai_ema_emai) as correo'),
                    DB::raw('MAX(d.dire_dir_dire) as direccion'),
                ])
                ->get();

            $empresa = Empresa::find($empresaId);
            $lineaNegocioId = $empresa?->linea_negocio_id;

            foreach ($proveedores as $proveedor) {
                $registro = Proveedores::updateOrCreate(
                    [
                        'id_empresa' => $empresaId,
                        'admg_id_empresa' => $empresaCodigo,
                        'admg_id_sucursal' => $sucursalCodigo,
                        'ruc' => trim((string) $proveedor->clpv_ruc_clpv),
                    ],
                    [
                        'tipo' => trim((string) ($proveedor->identificacion ?? '01')),
                        'nombre' => trim((string) $proveedor->clpv_nom_clpv),
                        'nombre_comercial' => trim((string) ($proveedor->clpv_nom_come ?? $proveedor->clpv_nom_clpv)),
                        'grupo' => trim((string) ($proveedor->grpv_nom_grpv ?? '')),
                        'zona' => trim((string) ($proveedor->zona_nom_zona ?? '')),
                        'flujo_caja' => trim((string) ($proveedor->cact_nom_cact ?? '')),
                        'tipo_proveedor' => trim((string) ($proveedor->tprov_des_tprov ?? '')),
                        'forma_pago' => trim((string) ($proveedor->fpagop_des_fpagop ?? '')),
                        'destino_pago' => trim((string) ($proveedor->tpago_des_tpago ?? '')),
                        'pais_pago' => trim((string) ($proveedor->paisp_des_paisp ?? '')),
                        'dias_pago' => (int) ($proveedor->clpv_pro_pago ?? 0),
                        'limite_credito' => (float) ($proveedor->clpv_lim_cred ?? 0),
                        'aplica_retencion_sn' => ($proveedor->clpv_ret_sn ?? 'N') === 'S',
                        'telefono' => $proveedor->telefono ? trim((string) $proveedor->telefono) : null,
                        'direcccion' => $proveedor->direccion ? trim((string) $proveedor->direccion) : null,
                        'correo' => $proveedor->correo ? trim((string) $proveedor->correo) : null,
                        'anulada' => ($proveedor->clpv_est_clpv ?? 'A') !== 'A',
                    ]
                );

                if ($lineaNegocioId) {
                    $registro->lineasNegocio()->syncWithoutDetaching([$lineaNegocioId]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al sincronizar proveedores JIREH: ' . $e->getMessage());
        }
    }
}
