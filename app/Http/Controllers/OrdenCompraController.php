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
        ]);

        // Returns the PDF as a download.
        return $pdf->stream('orden-compra-' . $ordenCompra->id . '.pdf');
    }



}
