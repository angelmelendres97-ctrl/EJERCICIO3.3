<?php

namespace App\Services;

use App\Filament\Resources\ProductoResource;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JirehProductoImportService
{
    public static function importar(int $empresaId, string $amdgEmpresa, string $amdgSucursal): int
    {
        $connectionName = ProductoResource::getExternalConnectionName($empresaId);

        if (!$connectionName) {
            return 0;
        }

        $productos = DB::connection($connectionName)
            ->table('saeprod as prod')
            ->leftJoin('saeprbo as prbo', function ($join) {
                $join->on('prbo.prbo_cod_prod', '=', 'prod.prod_cod_prod')
                    ->on('prbo.prbo_cod_empr', '=', 'prod.prod_cod_empr')
                    ->on('prbo.prbo_cod_sucu', '=', 'prod.prod_cod_sucu');
            })
            ->leftJoin('saeunid as unid', 'unid.unid_cod_unid', '=', 'prbo.prbo_cod_unid')
            ->where('prod.prod_cod_empr', $amdgEmpresa)
            ->where('prod.prod_cod_sucu', $amdgSucursal)
            ->groupBy([
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prod.prod_det_prod',
                'prod.prod_des_prod',
                'prod.prod_cod_tpro',
                'prod.prod_cod_linp',
                'prod.prod_cod_grpr',
                'prod.prod_cod_cate',
                'prod.prod_cod_marc',
            ])
            ->select([
                'prod.prod_cod_prod',
                'prod.prod_nom_prod',
                'prod.prod_det_prod',
                'prod.prod_des_prod',
                'prod.prod_cod_tpro',
                'prod.prod_cod_linp',
                'prod.prod_cod_grpr',
                'prod.prod_cod_cate',
                'prod.prod_cod_marc',
                DB::raw('MAX(prbo.prbo_smi_prod) as stock_minimo'),
                DB::raw('MAX(prbo.prbo_sma_prod) as stock_maximo'),
                DB::raw("MAX(CASE WHEN prbo.prbo_iva_sino = 'S' THEN 1 ELSE 0 END) as iva_sn"),
                DB::raw('MAX(prbo.prbo_iva_porc) as porcentaje_iva'),
                DB::raw('MAX(unid.unid_nom_unid) as unid_nom_unid'),
            ])
            ->get();

        $empresa = Empresa::find($empresaId);
        $lineaNegocioId = $empresa?->linea_negocio_id;
        $importados = 0;

        foreach ($productos as $producto) {
            try {
                $unidadMedidaId = null;
                if (!empty($producto->unid_nom_unid)) {
                    $unidadMedidaId = UnidadMedida::query()
                        ->where('nombre', trim($producto->unid_nom_unid))
                        ->value('id');
                }

                if (!$unidadMedidaId) {
                    $unidadMedidaId = UnidadMedida::query()->value('id');
                }

                $tipoProducto = (int) $producto->prod_cod_tpro;
                if (!in_array($tipoProducto, [1, 2], true)) {
                    $tipoProducto = 2;
                }

                $record = Producto::updateOrCreate(
                    [
                        'id_empresa' => $empresaId,
                        'amdg_id_empresa' => $amdgEmpresa,
                        'amdg_id_sucursal' => $amdgSucursal,
                        'sku' => trim((string) $producto->prod_cod_prod),
                    ],
                    [
                        'nombre' => trim((string) $producto->prod_nom_prod),
                        'detalle' => $producto->prod_det_prod ?: $producto->prod_des_prod,
                        'tipo' => $tipoProducto,
                        'linea' => $producto->prod_cod_linp,
                        'grupo' => $producto->prod_cod_grpr,
                        'categoria' => $producto->prod_cod_cate,
                        'marca' => $producto->prod_cod_marc,
                        'id_unidad_medida' => $unidadMedidaId,
                        'stock_minimo' => $producto->stock_minimo ?? 0,
                        'stock_maximo' => $producto->stock_maximo ?? 0,
                        'iva_sn' => (bool) $producto->iva_sn,
                        'porcentaje_iva' => $producto->porcentaje_iva ?? 0,
                    ]
                );

                if ($lineaNegocioId) {
                    $record->lineasNegocio()->syncWithoutDetaching([$lineaNegocioId]);
                }

                $importados++;
            } catch (\Throwable $e) {
                Log::error('Error al importar producto JIREH: ' . $e->getMessage(), [
                    'empresa_id' => $empresaId,
                    'amdg_empresa' => $amdgEmpresa,
                    'amdg_sucursal' => $amdgSucursal,
                    'producto' => $producto->prod_cod_prod ?? null,
                ]);
            }
        }

        return $importados;
    }
}
