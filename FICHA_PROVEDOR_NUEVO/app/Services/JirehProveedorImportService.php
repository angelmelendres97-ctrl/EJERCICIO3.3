<?php

namespace App\Services;

use App\Filament\Resources\ProveedorResource;
use App\Models\Empresa;
use App\Models\Proveedores;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JirehProveedorImportService
{
    public static function importar(int $empresaId, string $admgEmpresa, string $admgSucursal): int
    {
        $connectionName = ProveedorResource::getExternalConnectionName($empresaId);

        if (!$connectionName) {
            return 0;
        }

        $proveedores = DB::connection($connectionName)
            ->table('saeclpv as clpv')
            ->leftJoin('comercial.tipo_iden_clpv as tipo', 'tipo.tipo', '=', 'clpv.clv_con_clpv')
            ->leftJoin('saegrpv as grpv', function ($join) {
                $join->on('grpv.grpv_cod_grpv', '=', 'clpv.grpv_cod_grpv')
                    ->on('grpv.grpv_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('saezona as zona', function ($join) {
                $join->on('zona.zona_cod_zona', '=', 'clpv.clpv_cod_zona')
                    ->on('zona.zona_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('saecact as cact', function ($join) {
                $join->on('cact.cact_cod_cact', '=', 'clpv.clpv_cod_cact')
                    ->on('cact.cact_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('saetprov as tprov', function ($join) {
                $join->on('tprov.tprov_cod_tprov', '=', 'clpv.clpv_cod_tprov')
                    ->on('tprov.tprov_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('saefpagop as fpagop', function ($join) {
                $join->on('fpagop.fpagop_cod_fpagop', '=', 'clpv.clpv_cod_fpagop')
                    ->on('fpagop.fpagop_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('saetpago as tpago', function ($join) {
                $join->on('tpago.tpago_cod_tpago', '=', 'clpv.clpv_cod_tpago')
                    ->on('tpago.tpago_cod_empr', '=', 'clpv.clpv_cod_empr');
            })
            ->leftJoin('saepaisp as paisp', 'paisp.paisp_cod_paisp', '=', 'clpv.clpv_cod_paisp')
            ->leftJoin('saeemai as emai', function ($join) {
                $join->on('emai.emai_cod_clpv', '=', 'clpv.clpv_cod_clpv')
                    ->on('emai.emai_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('emai.emai_cod_sucu', '=', 'clpv.clpv_cod_sucu');
            })
            ->leftJoin('saetlcp as tlcp', function ($join) {
                $join->on('tlcp.tlcp_cod_clpv', '=', 'clpv.clpv_cod_clpv')
                    ->on('tlcp.tlcp_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('tlcp.tlcp_cod_sucu', '=', 'clpv.clpv_cod_sucu');
            })
            ->leftJoin('saedire as dire', function ($join) {
                $join->on('dire.dire_cod_clpv', '=', 'clpv.clpv_cod_clpv')
                    ->on('dire.dire_cod_empr', '=', 'clpv.clpv_cod_empr')
                    ->on('dire.dire_cod_sucu', '=', 'clpv.clpv_cod_sucu');
            })
            ->where('clpv.clpv_cod_empr', $admgEmpresa)
            ->where('clpv.clpv_cod_sucu', $admgSucursal)
            ->where('clpv.clpv_clopv_clpv', 'PV')
            ->groupBy([
                'clpv.clpv_cod_clpv',
                'clpv.clpv_ruc_clpv',
                'clpv.clpv_nom_clpv',
                'clpv.clpv_nom_come',
                'clpv.clpv_pro_pago',
                'clpv.clpv_lim_cred',
                'clpv.clpv_ret_sn',
                'clpv.clpv_est_clpv',
                'tipo.identificacion',
                'grpv.grpv_nom_grpv',
                'zona.zona_nom_zona',
                'cact.cact_nom_cact',
                'tprov.tprov_des_tprov',
                'fpagop.fpagop_des_fpagop',
                'tpago.tpago_des_tpago',
                'paisp.paisp_des_paisp',
            ])
            ->select([
                'clpv.clpv_ruc_clpv',
                'clpv.clpv_nom_clpv',
                'clpv.clpv_nom_come',
                'clpv.clpv_pro_pago',
                'clpv.clpv_lim_cred',
                'clpv.clpv_ret_sn',
                'clpv.clpv_est_clpv',
                'tipo.identificacion',
                'grpv.grpv_nom_grpv',
                'zona.zona_nom_zona',
                'cact.cact_nom_cact',
                'tprov.tprov_des_tprov',
                'fpagop.fpagop_des_fpagop',
                'tpago.tpago_des_tpago',
                'paisp.paisp_des_paisp',
                DB::raw('MAX(emai.emai_ema_emai) as correo'),
                DB::raw('MAX(tlcp.tlcp_tlf_tlcp) as telefono'),
                DB::raw('MAX(dire.dire_dir_dire) as direcccion'),
            ])
            ->get();

        $empresa = Empresa::find($empresaId);
        $lineaNegocioId = $empresa?->linea_negocio_id;
        $importados = 0;

        foreach ($proveedores as $proveedor) {
            try {
                $record = Proveedores::updateOrCreate(
                    [
                        'id_empresa' => $empresaId,
                        'admg_id_empresa' => $admgEmpresa,
                        'admg_id_sucursal' => $admgSucursal,
                        'ruc' => trim((string) $proveedor->clpv_ruc_clpv),
                    ],
                    [
                        'tipo' => $proveedor->identificacion,
                        'nombre' => trim((string) $proveedor->clpv_nom_clpv),
                        'nombre_comercial' => trim((string) $proveedor->clpv_nom_come),
                        'grupo' => $proveedor->grpv_nom_grpv,
                        'zona' => $proveedor->zona_nom_zona,
                        'flujo_caja' => $proveedor->cact_nom_cact,
                        'tipo_proveedor' => $proveedor->tprov_des_tprov,
                        'forma_pago' => $proveedor->fpagop_des_fpagop,
                        'destino_pago' => $proveedor->tpago_des_tpago,
                        'pais_pago' => $proveedor->paisp_des_paisp,
                        'dias_pago' => $proveedor->clpv_pro_pago,
                        'limite_credito' => $proveedor->clpv_lim_cred,
                        'aplica_retencion_sn' => ($proveedor->clpv_ret_sn ?? '') === 'S',
                        'telefono' => $proveedor->telefono,
                        'direcccion' => $proveedor->direcccion,
                        'correo' => $proveedor->correo,
                        'anulada' => ($proveedor->clpv_est_clpv ?? '') !== 'A',
                    ]
                );

                if ($lineaNegocioId) {
                    $record->lineasNegocio()->syncWithoutDetaching([$lineaNegocioId]);
                }

                $importados++;
            } catch (\Throwable $e) {
                Log::error('Error al importar proveedor JIREH: ' . $e->getMessage(), [
                    'empresa_id' => $empresaId,
                    'admg_empresa' => $admgEmpresa,
                    'admg_sucursal' => $admgSucursal,
                    'proveedor' => $proveedor->clpv_ruc_clpv ?? null,
                ]);
            }
        }

        return $importados;
    }
}
