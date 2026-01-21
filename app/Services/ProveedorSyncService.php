<?php

namespace App\Services;

use App\Models\Empresa;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Importamos el Resource solo para obtener la conexión externa (asumiendo que esa lógica está allí)
use App\Filament\Resources\ProveedorResource;

class ProveedorSyncService
{
    /**
     * Sincroniza los datos del proveedor con las tablas externas (saeclpv, saetlcp, saeemai, saedire)
     * en cada una de las bases de datos PostgreSQL seleccionadas.
     *
     * @param Model $record El modelo local del Proveedor (ya creado o actualizado).
     * @param array $data Los datos completos del formulario de Filament.
     * @return void
     * @throws Exception Si ocurre un error de base de datos en las conexiones externas.
     */
    public static function sincronizar(Model $record, array $data): void
    {

        // 1. Parse selected empresas/bodegas from the form
        $selectedEmpresasByEmpresa = [];
        if (!empty($data['empresas_proveedor'])) {
            foreach ($data['empresas_proveedor'] as $empresaValue) {
                // El formato es 'empresa_id-admg_empresa'
                list($empresaId, $admg_empresa) = explode('-', $empresaValue, 2);
                $selectedEmpresasByEmpresa[(int)$empresaId] = trim($admg_empresa);
            }
        }

        if (empty($selectedEmpresasByEmpresa)) {
            return;
        }

        // 2. Extraer datos comunes del formulario
        $admg_id_empresa = $data['admg_id_empresa'];
        $tipo_documento = $data['tipo'];
        $identificacion = $data['ruc'];
        $nombre = strtoupper($data['nombre']);
        $nombre_comercial = strtoupper($data['nombre_comercial']);
        $grupo = $data['grupo'];
        $zona = $data['zona'];
        $flujo_caja = $data['flujo_caja'];
        $tipo_proveedor = $data['tipo_proveedor'];
        $forma_pago = $data['forma_pago'];
        $destino_pago = $data['destino_pago'];
        $pais_pago = $data['pais_pago'];
        $dias_pago = $data['dias_pago'];
        $limite_credito = $data['limite_credito'];

        $telefono = $data['telefono'];
        $direcccion = strtoupper($data['direcccion']);
        $correo = $data['correo'];

        $aplica_retencion_sn = $data['aplica_retencion_sn'] ? 'S' : 'N';
        $fecha_server = date('Y-m-d');


        // 3. Iterar sobre cada empresa seleccionada
        foreach ($selectedEmpresasByEmpresa as $empresaId => $admg_empresa) {
            $conexionPgsql = null;
            $empresa = null;

            try {
                $empresa = Empresa::find($empresaId);
                if (!$empresa) {
                    Log::warning("Empresa local con ID {$empresaId} no encontrada al sincronizar proveedor.");
                    continue;
                }

                $conexionPgsql = ProveedorResource::getExternalConnectionName($empresaId);
                if (!$conexionPgsql) {
                    Log::warning("No se pudo establecer conexión externa para la empresa {$empresa->nombre_empresa} (ID: {$empresaId}).");
                    continue;
                }

                DB::connection($conexionPgsql)->beginTransaction();

                // 4. Obtener códigos de referencia

                // Sucursal por defecto
                $sql_sucursal_default = DB::connection($conexionPgsql)->table('saesucu')->where('sucu_cod_empr', $admg_empresa)->first();
                $admg_sucursal = $sql_sucursal_default ? $sql_sucursal_default->sucu_cod_sucu : 1;

                // tipo documento
                $sql_tipo_iden = DB::connection($conexionPgsql)->table('comercial.tipo_iden_clpv')->where('identificacion', $tipo_documento)->first();
                $id_iden_clpv = $sql_tipo_iden ? $sql_tipo_iden->tipo : '01';

                // grupo
                $sql_saegrpv = DB::connection($conexionPgsql)->table('saegrpv')->where('grpv_nom_grpv', $grupo)->where('grpv_cod_empr', $admg_empresa)->first();
                $grpv_cod_grpv = $sql_saegrpv ? $sql_saegrpv->grpv_cod_grpv : 1;
                $grpv_cta_grpv = $sql_saegrpv ? $sql_saegrpv->grpv_cta_grpv : '';

                // zona
                $sql_saezona = DB::connection($conexionPgsql)->table('saezona')->where('zona_nom_zona', $zona)->where('zona_cod_empr', $admg_empresa)->first();
                $zona_cod_zona = $sql_saezona ? $sql_saezona->zona_cod_zona : 1;

                // flujo de caja
                $sql_saecact = DB::connection($conexionPgsql)->table('saecact')->where('cact_nom_cact', $flujo_caja)->where('cact_cod_empr', $admg_empresa)->first();
                $cact_cod_cact = $sql_saecact ? $sql_saecact->cact_cod_cact : 1;

                // tipo proveedor
                $sql_saetprov = DB::connection($conexionPgsql)->table('saetprov')->where('tprov_des_tprov', $tipo_proveedor)->where('tprov_cod_empr', $admg_empresa)->first();
                $tprov_cod_tprov = $sql_saetprov ? $sql_saetprov->tprov_cod_tprov : 1;

                // forma pago
                $sql_saefpagop = DB::connection($conexionPgsql)->table('saefpagop')->where('fpagop_des_fpagop', $forma_pago)->where('fpagop_cod_empr', $admg_empresa)->first();
                $fpagop_cod_fpagop = $sql_saefpagop ? $sql_saefpagop->fpagop_cod_fpagop : 1;

                // destino pago
                $sql_saetpago = DB::connection($conexionPgsql)->table('saetpago')->where('tpago_des_tpago', $destino_pago)->where('tpago_cod_empr', $admg_empresa)->first();
                $tpago_cod_tpago = $sql_saetpago ? $sql_saetpago->tpago_cod_tpago : 1;

                // destino pago
                $sql_saepaisp = DB::connection($conexionPgsql)->table('saepaisp')->where('paisp_des_paisp', $pais_pago)->first();
                $paisp_cod_paisp = $sql_saepaisp ? $sql_saepaisp->paisp_cod_paisp : 1;


                // 5. Buscar o crear/actualizar saeclpv
                $existeProveedor = DB::connection($conexionPgsql)->table('saeclpv')
                    ->where('clpv_cod_empr', $admg_empresa)
                    ->where('clpv_ruc_clpv', $identificacion)
                    ->where('clv_con_clpv', $id_iden_clpv)
                    ->where('clpv_clopv_clpv', 'PV')
                    ->first();

                $clpv_cod_clpv = $existeProveedor ? $existeProveedor->clpv_cod_clpv : null;

                $proveedorData = [
                    'clpv_cod_sucu' => $admg_sucursal,
                    'clpv_cod_empr' => $admg_empresa,
                    'clpv_cod_cuen' => $grpv_cta_grpv,
                    'clpv_cod_zona' => $zona_cod_zona,
                    'clv_con_clpv' => $id_iden_clpv,
                    'clpv_cod_char' => $identificacion,
                    'clpv_clopv_clpv' => 'PV',
                    'clpv_nom_clpv' => $nombre,
                    'clpv_ruc_clpv' => $identificacion,
                    'clpv_est_clpv' => 'A',
                    'clpv_fec_has' => $fecha_server,
                    'clpv_fec_reno' => $fecha_server,
                    'clpv_nom_come' => $nombre_comercial,
                    'clpv_cal_clpv' => 'A',
                    'clpv_est_mon' => 'N',
                    'clpv_lim_cred' => $limite_credito,
                    'clpv_pro_pago' => $dias_pago,
                    'grpv_cod_grpv' => $grpv_cod_grpv,
                    'clpv_dsc_clpv' => '0',
                    'clpv_dsc_prpg' => '0',
                    'clpv_cod_fpagop' => $fpagop_cod_fpagop,
                    'clpv_cod_tprov' => $tprov_cod_tprov,
                    'clpv_cod_tpago' => $tpago_cod_tpago,
                    'clpv_cod_paisp' => $paisp_cod_paisp,
                    'clpv_cod_cact' => $cact_cod_cact,
                    'clpv_etu_clpv' => 'N',
                    'clpv_rep_clpv' => '',
                    'clpv_nov_clpv' => '',
                    'clpv_ret_sn' => $aplica_retencion_sn,
                    'clpv_cod_mone' => '1',
                ];

                if (!$clpv_cod_clpv) {
                    // Solo en la creación se establece la fecha de inicio
                    $proveedorData['clpv_fec_des'] = $fecha_server;
                    $clpv_cod_clpv = DB::connection($conexionPgsql)
                        ->table('saeclpv')
                        ->insertGetId($proveedorData, 'clpv_cod_clpv');
                } else {
                    DB::connection($conexionPgsql)
                        ->table('saeclpv')
                        ->where('clpv_cod_clpv', $clpv_cod_clpv)
                        ->update($proveedorData);
                }

                // 6. Sincronizar Teléfono (saetlcp) - Usando Delete + Insert para asegurar unicidad
                if (!empty($clpv_cod_clpv) && !empty($telefono)) {
                    DB::connection($conexionPgsql)
                        ->table('saetlcp')
                        ->where('tlcp_cod_empr', $admg_empresa)
                        ->where('tlcp_cod_sucu', $admg_sucursal)
                        ->where('tlcp_cod_clpv', $clpv_cod_clpv)
                        ->where('tlcp_tlf_tlcp', $telefono)
                        ->delete();

                    DB::connection($conexionPgsql)->table('saetlcp')->insert([
                        'tlcp_cod_empr' => $admg_empresa,
                        'tlcp_cod_sucu' => $admg_sucursal,
                        'tlcp_cod_clpv' => $clpv_cod_clpv,
                        'tlcp_tlf_tlcp' => $telefono,
                    ]);
                }

                // 7. Sincronizar Correo (saeemai) - Usando Delete + Insert
                if (!empty($clpv_cod_clpv) && !empty($correo)) {
                    DB::connection($conexionPgsql)
                        ->table('saeemai')
                        ->where('emai_cod_empr', $admg_empresa)
                        ->where('emai_cod_sucu', $admg_sucursal)
                        ->where('emai_cod_clpv', $clpv_cod_clpv)
                        ->where('emai_ema_emai', $correo)
                        ->delete();

                    DB::connection($conexionPgsql)->table('saeemai')->insert([
                        'emai_cod_empr' => $admg_empresa,
                        'emai_cod_sucu' => $admg_sucursal,
                        'emai_cod_clpv' => $clpv_cod_clpv,
                        'emai_ema_emai' => $correo,
                    ]);
                }

                // 8. Sincronizar Dirección (saedire) - Usando Delete + Insert
                if (!empty($clpv_cod_clpv) && !empty($direcccion)) {
                    DB::connection($conexionPgsql)
                        ->table('saedire')
                        ->where('dire_cod_empr', $admg_empresa)
                        ->where('dire_cod_sucu', $admg_sucursal)
                        ->where('dire_cod_clpv', $clpv_cod_clpv)
                        ->where('dire_dir_dire', $direcccion)
                        ->delete();

                    DB::connection($conexionPgsql)->table('saedire')->insert([
                        'dire_cod_empr' => $admg_empresa,
                        'dire_cod_sucu' => $admg_sucursal,
                        'dire_cod_clpv' => $clpv_cod_clpv,
                        'dire_dir_dire' => $direcccion,
                        'dire_est_dire' => 'A',
                        'dire_cobr_sn' => 'S',
                    ]);
                }

                DB::connection($conexionPgsql)->commit();
            } catch (Exception $e) {
                if ($conexionPgsql && DB::connection($conexionPgsql)->inTransaction()) {
                    DB::connection($conexionPgsql)->rollBack();
                }

                // Asegúrate de que $empresa está definido antes de intentar acceder a sus propiedades
                $nombreEmpresa = $empresa ? $empresa->nombre_empresa : 'Desconocida';

                Log::error("Error al sincronizar proveedor en empresa {$nombreEmpresa}: " . $e->getMessage());

                // Relanzar la excepción para que Filament pueda mostrar un error
                throw new Exception("No se pudo sincronizar el proveedor en la base de datos externa: {$nombreEmpresa}. Se ha revertido la operación. Error: " . $e->getMessage());
            }
        }
    }
}
