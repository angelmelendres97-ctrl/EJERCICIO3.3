<?php

namespace App\Http\Controllers;

use App\Filament\Resources\OrdenCompraResource;
use App\Models\OrdenCompra;
// It's better to use the Facade alias if it's configured in app.php
// use Barryvdh\DomPDF\Facade\Pdf;
// If not, use the full class name and instantiate it.
// For this case I'll use the Facade as it is common practice.
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrdenCompraController extends Controller
{
    /**
     * Generate and download a PDF for the given purchase order.
     *
     * @param  \App\Models\OrdenCompra  $ordenCompra
     * @return \Illuminate\Http\Response
     */
    public function descargarPdf(OrdenCompra $ordenCompra)
    {
        // It is a good practice to load all necessary relationships to avoid N+1 problems in the view.
        $ordenCompra->load('detalles', 'empresa', 'usuario');

        $productoNombres = [];
        $connectionName = OrdenCompraResource::getExternalConnectionName((int) $ordenCompra->id_empresa);
        $empresaCodigo = $ordenCompra->amdg_id_empresa;
        $sucursalCodigo = $ordenCompra->amdg_id_sucursal;

        if ($connectionName && $empresaCodigo && $sucursalCodigo) {
            $codigosProducto = $ordenCompra->detalles
                ->pluck('codigo_producto')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($codigosProducto)) {
                try {
                    $productoNombres = DB::connection($connectionName)
                        ->table('saeprod')
                        ->where('prod_cod_empr', $empresaCodigo)
                        ->where('prod_cod_sucu', $sucursalCodigo)
                        ->whereIn('prod_cod_prod', $codigosProducto)
                        ->pluck('prod_nom_prod', 'prod_cod_prod')
                        ->toArray();
                } catch (\Exception $e) {
                    $productoNombres = [];
                }
            }
        }

        $nombreEmpresaTitulo = $ordenCompra->empresa->nombre_empresa ?? 'Nombre de Empresa no disponible';
        if ($ordenCompra->presupuesto === 'PB') {
            $nombreEmpresaTitulo = $ordenCompra->empresa->nombre_pb ?: $nombreEmpresaTitulo;
        } elseif ($ordenCompra->presupuesto === 'AZ') {
            $connectionName = OrdenCompraResource::getExternalConnectionName((int) $ordenCompra->id_empresa);
            if ($connectionName) {
                try {
                    $empresaNombre = DB::connection($connectionName)
                        ->table('saeempr')
                        ->where('empr_cod_empr', $ordenCompra->amdg_id_empresa)
                        ->value('empr_nom_empr');
                } catch (\Exception $e) {
                    $empresaNombre = null;
                }

                if ($empresaNombre) {
                    $nombreEmpresaTitulo = $empresaNombre;
                }
            }
        }

        // The view 'pdfs.orden_compra' will be created in the next step.
        $pdf = Pdf::loadView('pdfs.orden_compra', [
            'ordenCompra' => $ordenCompra,
            'nombreEmpresaTitulo' => $nombreEmpresaTitulo,
            'productoNombres' => $productoNombres,
        ]);

        // Returns the PDF as a download.
        return $pdf->stream('orden-compra-' . $ordenCompra->id . '.pdf');
    }



}
