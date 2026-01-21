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
use App\Filament\Resources\ProductoResource;
use Illuminate\Support\Facades\Auth;

class ProductoSyncService
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

        // 3. Parse selected bodegas from the form
        // The format is 'empresa_id-bode_cod_bode'
        $selectedBodegasByEmpresa = [];
        if (!empty($data['bodegas'])) {
            foreach ($data['bodegas'] as $bodegaValue) {
                list($empresaId, $bodegaCode) = explode('-', $bodegaValue, 2);
                $selectedBodegasByEmpresa[(int)$empresaId][] = trim($bodegaCode);
            }
        }

        // If no external bodegas were selected, we're done.
        if (empty($selectedBodegasByEmpresa)) {
            return;
        }

        // 4. Get common data from the form for external insertion
        $sku = $data['sku'];
        $nombre_producto = $data['nombre'];
        $prod_det = $data['detalle'];
        $prod_tpro = $data['tipo'];
        $stock_minimo = $data['stock_minimo'];
        $stock_maximo = $data['stock_maximo'];
        $iva_sn = $data['iva_sn'];
        $porcentaje_iva = $data['porcentaje_iva'];
        $id_unidad_medida = $data['id_unidad_medida'];
        $linp_cod_linp = $data['linea'];
        $grpr_cod_grpr = $data['grupo'];
        $cate_cod_cate = $data['categoria'];
        $marc_cod_marc = $data['marca'];

        $fecha_server = date('Y-m-d H:i:s');
        $id_usuario = Auth::id() ?? 1;
        $codigo_barras = $sku;
        $prod_fin_prod = 1;
        $color = 0;
        $medi_cod_medi = 1; // Default value, consider making it dynamic if needed

        // 5. Iterate over each company that has selected bodegas
        foreach ($selectedBodegasByEmpresa as $empresaId => $bodegaCodes) {
            $conexionPgsql = null;
            try {
                $empresa = Empresa::find($empresaId);
                if (!$empresa) {
                    Log::warning("Empresa local con ID {$empresaId} no encontrada al sincronizar producto.");
                    continue;
                }

                $conexionPgsql = ProductoResource::getExternalConnectionName($empresaId);
                if (!$conexionPgsql) {
                    Log::warning("No se pudo establecer conexión externa para la empresa {$empresa->nombre_empresa} (ID: {$empresaId}).");
                    continue;
                }

                DB::connection($conexionPgsql)->beginTransaction();

                // 6. Get all necessary info for the selected bodegas in this company
                $bodegasInfo = DB::connection($conexionPgsql)->table('saebode as b')
                    ->join('saesubo as sb', 'sb.subo_cod_bode', '=', 'b.bode_cod_bode')
                    ->join('saesucu as s', 's.sucu_cod_sucu', '=', 'sb.subo_cod_sucu')
                    ->whereIn('b.bode_cod_bode', $bodegaCodes)
                    ->select('b.*', 's.sucu_cod_sucu', 's.sucu_cod_empr')
                    ->get();

                // Group by sucursal to create the main product entry once per sucursal
                $sucursalesAfectadas = $bodegasInfo->groupBy(['sucu_cod_empr', 'sucu_cod_sucu']);

                foreach ($sucursalesAfectadas as $empr_cod_empr => $sucursales) {
                    foreach ($sucursales as $sucu_cod_sucu => $bodegasEnSucursal) {
                        // 7. Insert into saeprod (main product table) if it doesn't exist for the sucursal
                        $existeProducto = DB::connection($conexionPgsql)->table('saeprod')
                            ->where('prod_cod_prod', $sku)
                            ->where('prod_cod_empr', $empr_cod_empr)
                            ->where('prod_cod_sucu', $sucu_cod_sucu)
                            ->exists();

                        if (!$existeProducto) {
                            DB::connection($conexionPgsql)->table('saeprod')->insert([
                                'prod_cod_prod'       => $sku,
                                'prod_cod_empr'       => $empr_cod_empr,
                                'prod_nom_prod'       => $nombre_producto,
                                'prod_fin_prod'       => $prod_fin_prod,
                                'prod_cod_colr'       => $color,
                                'prod_cod_marc'       => $marc_cod_marc,
                                'prod_cod_tpro'       => $prod_tpro,
                                'prod_cod_medi'       => $medi_cod_medi,
                                'prod_cod_sucu'       => $sucu_cod_sucu,
                                'prod_cod_linp'       => $linp_cod_linp,
                                'prod_cod_grpr'       => $grpr_cod_grpr,
                                'prod_cod_cate'       => $cate_cod_cate,
                                'prod_cod_barra'      => $codigo_barras,
                                'prod_des_prod'       => $prod_det,
                                'prod_det_prod'       => $prod_det,
                                'prod_user_web'       => $id_usuario,
                                'prod_fec_server'     => $fecha_server,
                            ]);
                        } else {

                            $datosUpdate = [
                                'prod_nom_prod'       => $nombre_producto,
                                'prod_fin_prod'       => $prod_fin_prod,
                                'prod_cod_colr'       => $color,
                                'prod_cod_marc'       => $marc_cod_marc,
                                'prod_cod_tpro'       => $prod_tpro,
                                'prod_cod_medi'       => $medi_cod_medi,
                                'prod_cod_linp'       => $linp_cod_linp,
                                'prod_cod_grpr'       => $grpr_cod_grpr,
                                'prod_cod_cate'       => $cate_cod_cate,
                                'prod_cod_barra'      => $codigo_barras,
                                'prod_des_prod'       => $prod_det,
                                'prod_det_prod'       => $prod_det,
                                'prod_user_web'       => $id_usuario,
                                'prod_fec_server'     => $fecha_server,
                            ];

                            DB::connection($conexionPgsql)->table('saeprod')
                                ->where('prod_cod_prod', $sku)
                                ->where('prod_cod_empr', $empr_cod_empr)
                                ->where('prod_cod_sucu', $sucu_cod_sucu)
                                ->update($datosUpdate);
                        }

                        // 8. Insert into saeprbo (product-bodega link table) for each selected bodega
                        foreach ($bodegasEnSucursal as $bodega_data) {
                            $existePrbo = DB::connection($conexionPgsql)->table('saeprbo')
                                ->where('prbo_cod_prod', $sku)
                                ->where('prbo_cod_bode', $bodega_data->bode_cod_bode)
                                ->exists();

                            $nombre_unidad_medida = UnidadMedida::find($id_unidad_medida)->nombre;
                            $sql_unidad_medida = DB::connection($conexionPgsql)->table('saeunid')->where('unid_nom_unid', $nombre_unidad_medida)->first();
                            $unid_cod_unid = $sql_unidad_medida ? $sql_unidad_medida->unid_cod_unid : 1;


                            if (!$existePrbo) {

                                DB::connection($conexionPgsql)->table('saeprbo')->insert([
                                    'prbo_cod_prod' => $sku,
                                    'prbo_cod_bode' => $bodega_data->bode_cod_bode,
                                    'prbo_cta_inv' => $bodega_data->bode_cta_inv,
                                    'prbo_cta_cven' => $bodega_data->bode_cta_cven,
                                    'prbo_cta_vent' => $bodega_data->bode_cta_vent,
                                    'prbo_cta_desc' => $bodega_data->bode_cta_desc,
                                    'prbo_cta_devo' => $bodega_data->bode_cta_devo,
                                    'prbo_cta_ideb' => $bodega_data->bode_cta_ideb,
                                    'prbo_cta_icre' => $bodega_data->bode_cta_icre,
                                    'prbo_cod_unid' => $unid_cod_unid,
                                    'prbo_cod_empr' => $empr_cod_empr,
                                    'prbo_cod_sucu' => $sucu_cod_sucu,
                                    'prbo_est_prod' => 1,
                                    'prbo_iva_sino' => $iva_sn ? 'S' : 'N',
                                    'prbo_iva_porc' => $porcentaje_iva ?? 0,
                                    'prbo_cos_prod' => 2, // Valor por defecto, revisar si es correcto
                                    'prbo_sma_prod' => $stock_maximo ?? 0,
                                    'prbo_smi_prod' => $stock_minimo ?? 0,
                                    'prbo_dis_prod' => 0,
                                ]);
                            } else {
                                $datosUpdatePrbo = [
                                    'prbo_cod_unid' => $unid_cod_unid,
                                    'prbo_cod_empr' => $empr_cod_empr,
                                    'prbo_cod_sucu' => $sucu_cod_sucu,
                                    'prbo_iva_sino' => $iva_sn ? 'S' : 'N',
                                    'prbo_iva_porc' => $porcentaje_iva ?? 0,
                                    'prbo_sma_prod' => $stock_maximo ?? 0,
                                    'prbo_smi_prod' => $stock_minimo ?? 0,
                                ];

                                DB::connection($conexionPgsql)->table('saeprbo')
                                    ->where('prbo_cod_prod', $sku)
                                    ->where('prbo_cod_bode', $bodega_data->bode_cod_bode)
                                    ->update($datosUpdatePrbo);
                            }
                        }
                    }
                }

                DB::connection($conexionPgsql)->commit();
            } catch (Exception $e) {
                if ($conexionPgsql) {
                    DB::connection($conexionPgsql)->rollBack();
                }
                Log::error("Error al sincronizar producto en empresa {$empresa->nombre_empresa}: " . $e->getMessage());
                throw new Exception("No se pudo crear el producto en la base de datos externa: {$empresa->nombre_empresa}. Se ha revertido la operación. Error: " . $e->getMessage());
            }
        }

        //
    }
}
