<?php

namespace App\Http\Controllers;

use App\Models\SolicitudPago;
use App\Services\SolicitudPagoReportService;
use Illuminate\Http\Response;

class SolicitudPagoController extends Controller
{
    public function mostrarPdf(SolicitudPago $solicitudPago, SolicitudPagoReportService $service)
    {
        // Abre en el navegador (inline)
        return $service->streamPdf($solicitudPago);
    }

    // (Opcional) si habilitas la ruta de descarga:
    public function descargarPdf(SolicitudPago $solicitudPago, SolicitudPagoReportService $service)
    {
        // Descarga
        return $service->exportPdf($solicitudPago);
    }

    public function mostrarPdfDetallado(\App\Models\SolicitudPago $solicitudPago, \App\Services\SolicitudPagoReportService $service)
    {
        return $service->streamDetailedPdf($solicitudPago);
    }
}
