<?php

namespace App\Services;

use App\Models\Empresa;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UnidadMedida;

// Importamos el Resource solo para obtener la conexión externa (asumiendo que esa lógica está allí)
use App\Filament\Resources\OrdenCompraResource;
use Illuminate\Support\Facades\Auth;

class OrdenCompraSyncService
{
    /**
     * Sincroniza los datos del producto con las tablas externas (saeprod, saeprbo)
     * en cada una de las bases de datos PostgreSQL seleccionadas.
     *
     * @param Model $record El modelo local del Producto (ya creado o actualizado).
     * @param array $data Los datos completos del formulario de Filament.
     * @return void
     * @throws Exception Si ocurre un error de base de datos en las conexiones externas.
     */

    public static function sincronizar(Model $record, array $data): void
    {

        //dd($record->id);
        //dd($data);

        try {

            $id_empresa = $data['id_empresa'];

            $conexionPgsql = OrdenCompraResource::getExternalConnectionName($id_empresa);
            if (!$conexionPgsql) {
                Log::warning("No se pudo establecer conexión externa para la empresa");
                throw new Exception("No se pudo establecer conexión externa para la empresa");
            }

            DB::connection($conexionPgsql)->beginTransaction();


            $amdg_id_empresa = $data['amdg_id_empresa'];
            $amdg_id_sucursal = $data['amdg_id_sucursal'];
            $pedidos_importados = $data['pedidos_importados'];
            $uso_compra = $data['uso_compra'];
            $solicitado_por = $data['solicitado_por'];
            $formato = $data['formato'];
            $tipo_oc = $data['tipo_oc'];
            $presupuesto = $data['presupuesto'];
            $id_proveedor = $data['id_proveedor'];
            $identificacion = $data['identificacion'];
            $proveedor = $data['proveedor'];
            $info_proveedor = $data['info_proveedor'];
            $trasanccion = $data['trasanccion'];
            $fecha_pedido = $data['fecha_pedido'];
            $fecha_entrega = $data['fecha_entrega'];
            $observaciones = strtoupper($data['observaciones']);
            //$bodega = $data['bodega'];
            $subtotal = $data['subtotal'];
            $total_descuento = $data['total_descuento'];
            $total_impuesto = $data['total_impuesto'];
            $total = $data['total'];

            $fecha_server = date('Y-m-d H:i:s');
            $id_usuario = 1;
            $plazo = 0;
            $empleado = 0;
            $factura = null;
            $serie_prove = null;
            $auto_prove = null;
            $fecha_prove = null;
            $tipo_pago = null;
            $fpago_prove = null;
            $fac_ini = 0;
            $fac_fin = 9999;
            $usua_nom_usua = 'USU_API';
            $fecha_actual = date('Y-m-d');
            $secu_asto = null;
            $fpago_ord = null;
            $cuenta_ord = null;

            $array_detalles = $data['detalles'];


            if (!is_array($array_detalles)) {
                Log::error("No se han ingresado productos en la Orden de Compra");
                throw new Exception("No se han ingresado productos en la Orden de Compra");
            }


            // MONEDA BASE
            $sql_saepcon = DB::connection($conexionPgsql)
                ->table('saepcon')
                ->where('pcon_cod_empr', $amdg_id_empresa)
                ->first();
            $moneda = $sql_saepcon ? $sql_saepcon->pcon_mon_base : '';
            if (empty($moneda)) {
                Log::error("Moneda base no configurada en la tabla saepcon => pcon_mon_base");
                throw new Exception("Moneda base no configurada en la tabla saepcon => pcon_mon_base");
            }


            // TRANSACCION ORDEN DE COMPRA
            $sql_saetran = DB::connection($conexionPgsql)
                ->table('saetran')
                ->where('tran_des_tran', $trasanccion)
                ->where('tran_cod_empr', $amdg_id_empresa)
                ->first();
            $tran_cod_tran = $sql_saetran ? $sql_saetran->tran_cod_tran : '';
            if (empty($tran_cod_tran)) {
                Log::error("No existe la transaccion: $trasanccion");
                throw new Exception("No existe la transaccion: $trasanccion");
            }

            // CONSULTA EJERCICIO DE LA FECHA SELECCIONADA
            $anio = substr($fecha_pedido, 0, 4);
            $idprdo = intval(substr($fecha_pedido, 5, 2));
            $fecha_ejer = $anio . '-12-31';
            $sql_saeejer = DB::connection($conexionPgsql)
                ->table('saeejer')
                ->where('ejer_fec_finl', $fecha_ejer)
                ->where('ejer_cod_empr', $amdg_id_empresa)
                ->first();
            $ejer_cod_ejer = $sql_saeejer ? $sql_saeejer->ejer_cod_ejer : 0;
            if (empty($ejer_cod_ejer)) {
                Log::error("No existe el ejercicio seleccionado a la fecha $fecha_ejer");
                throw new Exception("No existe el ejercicio seleccionado a la fecha $fecha_ejer");
            }

            // CONSULTA EL TIPO DE CAMBIO INGRESADO
            $sql_saetcam = DB::connection($conexionPgsql)
                ->table('saetcam')
                ->where('tcam_cod_mone', $moneda)
                ->where('mone_cod_empr', $amdg_id_empresa)
                // ->where('tcam_fec_tcam', $fecha_actual)
                ->first();
            $tcam_cod_tcam = $sql_saetcam ? $sql_saetcam->tcam_cod_tcam : 1;
            $tcam_valc_tcam = $sql_saetcam ? $sql_saetcam->tcam_valc_tcam : 1;

            // SECUENCIAL Y FORMATO DE IMPRESION
            $sql_saedefi = DB::connection($conexionPgsql)
                ->table('saedefi')
                ->where('defi_cod_sucu', $amdg_id_sucursal)
                ->where('defi_cod_empr', $amdg_id_empresa)
                ->where('defi_cod_modu', 10)
                ->where('defi_tip_defi', 4)
                ->where('defi_cod_tran', $tran_cod_tran)
                ->first();
            $defi_trs_defi = $sql_saedefi ? $sql_saedefi->defi_trs_defi : 0;
            $defi_for_defi = $sql_saedefi ? $sql_saedefi->defi_for_defi : '';
            $secu_minv = str_pad($defi_trs_defi + 1, 8, '0', STR_PAD_LEFT);

            // MAX SAEMINV ORDEN DE COMPRA
            $minv_num_comp = DB::connection($conexionPgsql)
                ->table('saeminv')
                ->max('minv_num_comp') + 1;


            // INSERT SAEMINV ORDEN DE COMPRA
            $datos_minv = [
                'minv_num_comp' => $minv_num_comp,
                'minv_num_plaz' => $plazo,
                'minv_num_sec' => $secu_minv,
                'minv_cod_tcam' => $tcam_cod_tcam,
                'minv_cod_mone' => $moneda,
                'minv_cod_empr' => $amdg_id_empresa,
                'minv_cod_sucu' => $amdg_id_sucursal,
                'minv_cod_tran' => $tran_cod_tran,
                'minv_cod_modu' => 10,
                'minv_cod_empl' => $empleado,
               'minv_cod_ftrn' => is_numeric($defi_for_defi) ? (int)$defi_for_defi : null,

                'minv_fmov' => $fecha_pedido,
                'minv_dege_minv' => 0,
                'minv_cod_usua' => $id_usuario,
                'minv_num_prdo' => $idprdo,
                'minv_cod_ejer' => $ejer_cod_ejer,
                'minv_fac_prov' => $factura,
                'minv_fec_entr' => $fecha_entrega,
                'minv_fec_ser' => $fecha_server, // Equivalente a CURRENT_DATE (solo fecha)
                'minv_est_minv' => '1',
                'minv_tot_minv' => $total,
                'minv_con_iva' => $total,
                'minv_sin_iva' => 0,
                'minv_dge_valo' => $total_descuento,
                'minv_iva_valo' => $total_impuesto,
                'minv_otr_valo' => 0,
                'minv_fle_minv' => 0,
                'minv_aut_usua' => $auto_prove,
                'minv_aut_impr' => '',
                'minv_fac_inic' => $fac_ini,
                'minv_fac_fina' => $fac_fin,
                'minv_ser_docu' => $serie_prove,
                'minv_fec_valo' => $fecha_prove,
                'minv_sucu_clpv' => $amdg_id_sucursal,
                'minv_sno_esta' => 0,
                'minv_usu_minv' => $usua_nom_usua,
                'minv_cm1_minv' => $observaciones,
                'minv_fec_regc' => $fecha_actual, // Opcional, si deseas usar CURRENT_DATE
                'minv_cod_fpagop' => $fpago_prove,
                'minv_cod_tpago' => $tipo_pago,
                'minv_ani_minv' => $anio,
                'minv_mes_minv' => $idprdo,
                'minv_user_web' => $id_usuario,
                'minv_comp_cont' => $secu_asto,
                'minv_tran_minv' => $secu_asto,
                'minv_cod_clpv' => $id_proveedor,
                'minv_cm3_minv' => $proveedor,
                'minv_val_tcam' => $tcam_valc_tcam,
                'minv_cta_prov' => $cuenta_ord,
                'minv_fpag_prov' => $fpago_ord,
                'minv_cod_pedi' => $pedidos_importados,
                'minv_cod_pedp' => $record->id,
            ];

            $minv_num_comp = DB::connection($conexionPgsql)
                ->table('saeminv')
                ->insertGetId($datos_minv, 'minv_num_comp');


            // ACTUALIZACION DE SECUENCIAL SAEDEFI
            $registros_afectados = DB::connection($conexionPgsql)
                ->table(table: 'saedefi')
                ->where('defi_cod_empr', $amdg_id_empresa)
                ->where('defi_cod_sucu', $amdg_id_sucursal)
                ->where('defi_cod_modu', 10) // Valor fijo: 10
                ->where('defi_tip_defi', '4') // Valor fijo: '4'
                ->where('defi_cod_tran', $tran_cod_tran)
                ->update([
                    'defi_trs_defi' => $secu_minv,
                ]);



            // FOREACH PRODUCTOS ASOCIOADOS A LA OC
            $contador_dmov = 0;
            foreach ($array_detalles as $key => $data_detalle_producto) {

                $codigo_producto = $data_detalle_producto['codigo_producto'];
                $producto = $data_detalle_producto['producto'];
                $id_bodega = $data_detalle_producto['id_bodega'];
                //$bodega = $data_detalle_producto['bodega'];
                $cantidad = $data_detalle_producto['cantidad'];
                $costo = $data_detalle_producto['costo'];
                $descuento = $data_detalle_producto['descuento'];
                $impuesto = $data_detalle_producto['impuesto'];
                $valor_impuesto = (($cantidad * $costo) * $impuesto) / 100;

                // Calcular total
                $subtotal_linea = floatval($cantidad) * floatval($costo);
                $monto_descuento = $subtotal_linea * (floatval($descuento) / 100);
                $base_imponible = $subtotal_linea - $monto_descuento;
                $total = $base_imponible + $valor_impuesto;

                // UNIDAD MEDIDA PRDUCTO
                $sql_saeprbo = DB::connection($conexionPgsql)
                    ->table('saeprbo')
                    ->where('prbo_cod_bode', $id_bodega)
                    ->where('prbo_cod_prod', $codigo_producto)
                    // ->where('tcam_fec_tcam', $fecha_actual)
                    ->first();
                $prbo_cod_unid = $sql_saeprbo ? $sql_saeprbo->prbo_cod_unid : 1;

                $cero = 0;
                $estado = 1;
                $dis = 'N';
                $total_linea = $cantidad * $costo;
                $hora = date('Y-m-d H:i:s');
                $cod_pedi = null;
                $cod_ccosn = null;
                $cod_dped = null;
                $detalle = 'PRODUCTO INGRESADO DESDE PLATAFORMA MATRIZ';


                // ----------------------------------------------------------------------
                // VALIDACION CANTIDAD DETALLE PEDIDO
                // ----------------------------------------------------------------------

                $cantidad_restante = $cantidad;

                $array_ids_saepedi = array();
                if (str_contains($pedidos_importados, ',')) {
                    //echo "La cadena SÍ contiene una coma.";
                    $array_pedidos = explode(',', $pedidos_importados);
                    foreach ($array_pedidos as $key => $numero_pedi) {
                        array_push($array_ids_saepedi, $numero_pedi);
                    }
                } else {
                    //echo "La cadena NO contiene una coma.";
                    array_push($array_ids_saepedi, $pedidos_importados);
                }

                $numero_registros = count($array_ids_saepedi);
                if ($numero_registros > 0) {
                    foreach ($array_ids_saepedi as $key => $id_saepdi) {

                        $id_saepdi = (int) $id_saepdi;
                        $sql_saedped = DB::connection($conexionPgsql)
                            ->table('saedped')
                            ->where('dped_cod_empr', $amdg_id_sucursal)
                            ->where('dped_cod_sucu', $amdg_id_empresa)
                            ->where('dped_cod_pedi', $id_saepdi)
                            ->where('dped_cod_prod', $codigo_producto)
                            ->where('dped_cod_bode', $id_bodega)
                            ->first();
                        if ($sql_saedped) {
                            //echo "¡Se encontró el registro!"; 
                            $dped_can_ped = $sql_saedped ? $sql_saedped->dped_can_ped : 0;
                            $dped_can_ent = $sql_saedped ? $sql_saedped->dped_can_ent : 0;



                            if ($dped_can_ent < $dped_can_ped) {
                                $cantidad_por_entregar = $dped_can_ped - $dped_can_ent;

                                if ($cantidad_por_entregar < $cantidad_restante) {
                                    $cantidad_a_entregar = $cantidad_por_entregar;
                                } else {
                                    $cantidad_a_entregar = $cantidad_restante;
                                }

                                if ($cantidad_a_entregar > 0) {

                                    $registros_actualizados = DB::connection($conexionPgsql)
                                        ->table('saedped')
                                        ->where('dped_cod_empr', $amdg_id_sucursal)
                                        ->where('dped_cod_sucu', $amdg_id_empresa)
                                        ->where('dped_cod_pedi', $id_saepdi)
                                        ->where('dped_cod_prod', $codigo_producto)
                                        ->where('dped_cod_bode', $id_bodega)
                                        ->update([
                                            'dped_can_ent' => $dped_can_ent + $cantidad_a_entregar,
                                        ]);


                                    $cantidad_restante -= $cantidad_a_entregar;
                                }
                            }
                        }
                    }

                }


                // ----------------------------------------------------------------------
                // FIN VALIDACION CANTIDAD DETALLE PEDIDO
                // ----------------------------------------------------------------------


                $datos_dmov = [
                    'dmov_cod_dmov' => $contador_dmov,
                    'dmov_cod_prod' => $codigo_producto,
                    'dmov_cod_sucu' => $amdg_id_sucursal,
                    'dmov_cod_empr' => $amdg_id_empresa,
                    'dmov_cod_bode' => $id_bodega,
                    'dmov_cod_unid' => $prbo_cod_unid,
                    'dmov_cod_ejer' => $ejer_cod_ejer,
                    'dmov_num_comp' => $minv_num_comp, // Usando $ultimo_id como en el SQL anterior
                    'dmov_num_prdo' => $idprdo,
                    'dmov_can_dmov' => $cantidad,
                    'dmov_can_entr' => $cero,
                    'dmov_cun_dmov' => $costo,
                    'dmov_cto_dmov' => $total_linea,
                    'dmov_pun_dmov' => $costo,
                    'dmov_pto_dmov' => $cero,
                    'dmov_ds1_dmov' => $descuento,
                    'dmov_ds2_dmov' => $cero,
                    'dmov_ds3_dmov' => $cero,
                    'dmov_ds4_dmov' => $cero,
                    'dmov_des_tota' => $cero,
                    'dmov_imp_dmov' => $cero,
                    'dmov_est_dmov' => $estado,
                    'dmov_iva_dmov' => $valor_impuesto,
                    'dmov_iva_porc' => $impuesto,
                    'dmov_dis_dmov' => $dis,
                    'dmov_ice_dmov' => $cero,
                    'dmov_hor_crea' => $hora,
                    'dmov_cod_tran' => $tran_cod_tran,
                    'dmov_fac_prov' => $factura,
                    'dmov_cod_clpv' => $id_proveedor,
                    'dmov_fmov' => $fecha_pedido,
                    'dmov_pto1_dmov' => $cero,
                    'dmov_cod_pedi' => $cod_pedi,
                    'dmov_det1_dmov' => $detalle,
                    'dmov_cod_ccos' => $cod_ccosn,
                    'dmov_cod_dped' => $cod_dped,
                ];

                DB::connection($conexionPgsql)
                    ->table('saedmov')
                    ->insert($datos_dmov);

            $contador_dmov = intval($contador_dmov) + 1;

        }

            self::actualizarEstadoPedidosPorConexion(
                $conexionPgsql,
                $amdg_id_empresa,
                $amdg_id_sucursal,
                self::normalizePedidosImportados($pedidos_importados),
                'Atendido'
            );

            DB::connection($conexionPgsql)->commit();

        } catch (Exception $e) {
            if ($conexionPgsql) {
                DB::connection($conexionPgsql)->rollBack();
            }
            Log::error("Error al sincronizar producto en empresa" . $e->getMessage());
            throw new Exception("No se pudo crear el producto en la base de datos externa. Se ha revertido la operación. Error: " . $e->getMessage());
        }

    }

    public static function actualizarEstadoPedidos(Model $record, ?array $pedidoIds = null, string $estado = 'Pendiente'): void
    {
        $conexionPgsql = OrdenCompraResource::getExternalConnectionName($record->id_empresa);
        if (!$conexionPgsql) {
            Log::warning('No se pudo establecer conexión externa para actualizar estado de pedidos.');
            return;
        }

        $pedidos = $pedidoIds ?? self::normalizePedidosImportados($record->pedidos_importados);

        self::actualizarEstadoPedidosPorConexion(
            $conexionPgsql,
            $record->amdg_id_empresa,
            $record->amdg_id_sucursal,
            $pedidos,
            $estado
        );
    }

    private static function actualizarEstadoPedidosPorConexion(
        string $conexionPgsql,
        int $amdgIdEmpresa,
        int $amdgIdSucursal,
        array $pedidoIds,
        string $estado
    ): void {
        if (empty($pedidoIds)) {
            return;
        }

        DB::connection($conexionPgsql)
            ->table('saepedi')
            ->where('pedi_cod_empr', $amdgIdEmpresa)
            ->where('pedi_cod_sucu', $amdgIdSucursal)
            ->whereIn('pedi_cod_pedi', $pedidoIds)
            ->update(['pedi_est_pedi' => $estado]);
    }

    private static function normalizePedidosImportados(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        return collect(preg_split('/\\s*,\\s*/', trim((string) $value)))
            ->filter()
            ->map(fn($pedido) => (int) ltrim((string) $pedido, '0'))
            ->filter(fn($pedido) => $pedido > 0)
            ->values()
            ->all();
    }

    public static function eliminar(Model $record): void
    {

        //dd($record);
        //dd($data);

        try {

            $id_oc_matriz = $record->id;
            $id_empresa = $record->id_empresa;
            $pedidos_importados = $record->pedidos_importados;
            $amdg_id_empresa = $record->amdg_id_empresa;
            $amdg_id_sucursal = $record->amdg_id_sucursal;

            $conexionPgsql = OrdenCompraResource::getExternalConnectionName($id_empresa);
            if (!$conexionPgsql) {
                Log::warning("No se pudo establecer conexión externa para la empresa");
                throw new Exception("No se pudo establecer conexión externa para la empresa");
            }

            DB::connection($conexionPgsql)->beginTransaction();



            // ----------------------------------------------------------------------
            // VALIDACION CANTIDAD DETALLE PEDIDO
            // ----------------------------------------------------------------------




            $sql_data_saeminv = DB::connection($conexionPgsql)
                ->table('saeminv')
                ->where('minv_cod_pedp', $id_oc_matriz)
                ->where('minv_cod_empr', $amdg_id_empresa)
                ->get();


            $array_ids_saepedi = array();
            if (str_contains($pedidos_importados, ',')) {
                //echo "La cadena SÍ contiene una coma.";
                $array_pedidos = explode(',', $pedidos_importados);
                foreach ($array_pedidos as $key => $numero_pedi) {
                    array_push($array_ids_saepedi, $numero_pedi);
                }
            } else {
                //echo "La cadena NO contiene una coma.";
                array_push($array_ids_saepedi, $pedidos_importados);
            }

            $numero_registros = count($array_ids_saepedi);
            if ($numero_registros > 0) {

                foreach ($sql_data_saeminv as $key1 => $data_saeminv) {
                    $minv_num_comp = $data_saeminv->minv_num_comp;

                    $sql_data_saedmov = DB::connection($conexionPgsql)
                        ->table('saedmov')
                        ->where('dmov_num_comp', $minv_num_comp)
                        ->get();
                    foreach ($sql_data_saedmov as $key2 => $data_saedmov) {
                        $dmov_cod_prod = $data_saedmov->dmov_cod_prod;
                        $dmov_can_dmov = $data_saedmov->dmov_can_dmov;
                        $dmov_cod_bode = $data_saedmov->dmov_cod_bode;


                        $cantidad_restante = $dmov_can_dmov;
                        foreach ($array_ids_saepedi as $key => $id_saepdi) {

                            $id_saepdi = (int) $id_saepdi;
                            $sql_saedped = DB::connection($conexionPgsql)
                                ->table('saedped')
                                ->where('dped_cod_empr', $amdg_id_sucursal)
                                ->where('dped_cod_sucu', $amdg_id_empresa)
                                ->where('dped_cod_pedi', $id_saepdi)
                                ->where('dped_cod_prod', $dmov_cod_prod)
                                ->where('dped_cod_bode', $dmov_cod_bode)
                                ->first();
                            if ($sql_saedped) {
                                //echo "¡Se encontró el registro!"; 
                                $dped_can_ped = $sql_saedped ? $sql_saedped->dped_can_ped : 0;
                                $dped_can_ent = $sql_saedped ? $sql_saedped->dped_can_ent : 0;

                                if ($cantidad_restante <= $dped_can_ent) {
                                    $cantidad_por_retornar = $cantidad_restante;
                                } else {
                                    $cantidad_por_retornar = $dped_can_ent;
                                }

                                DB::connection($conexionPgsql)
                                    ->table('saedped')
                                    ->where('dped_cod_empr', $amdg_id_sucursal)
                                    ->where('dped_cod_sucu', $amdg_id_empresa)
                                    ->where('dped_cod_pedi', $id_saepdi)
                                    ->where('dped_cod_prod', $dmov_cod_prod)
                                    ->where('dped_cod_bode', $dmov_cod_bode)
                                    ->update([
                                        'dped_can_ent' => $dped_can_ent - $cantidad_por_retornar,
                                    ]);

                                $cantidad_restante -= $cantidad_por_retornar;

                            }
                        }

                    }

                }

            }

            self::actualizarEstadoPedidosPorConexion(
                $conexionPgsql,
                $amdg_id_empresa,
                $amdg_id_sucursal,
                self::normalizePedidosImportados($pedidos_importados),
                'Pendiente'
            );

            foreach ($sql_data_saeminv as $key1 => $data_saeminv) {
                $minv_num_comp = $data_saeminv->minv_num_comp ?? 0;

                $registros_saedmov = DB::connection($conexionPgsql)
                    ->table('saedmov')
                    ->where('dmov_num_comp', $minv_num_comp)
                    ->delete();

                $registros_saeminv = DB::connection($conexionPgsql)
                    ->table('saeminv')
                    ->where('minv_num_comp', $minv_num_comp)
                    ->delete();
            }

            // ----------------------------------------------------------------------
            // FIN VALIDACION CANTIDAD DETALLE PEDIDO
            // ----------------------------------------------------------------------





            DB::connection($conexionPgsql)->commit();

        } catch (Exception $e) {
            if ($conexionPgsql) {
                DB::connection($conexionPgsql)->rollBack();
            }
            Log::error("Error al sincronizar producto en empresa" . $e->getMessage());
            throw new Exception("No se pudo crear el producto en la base de datos externa. Se ha revertido la operación. Error: " . $e->getMessage());
        }

    }
}
