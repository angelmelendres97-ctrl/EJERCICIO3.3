<?php

namespace App\Http\Controllers;

use App\Models\SolicitudPago;
use App\Services\SolicitudPagoReportService;
use Illuminate\Http\Response;

class SolicitudPagoController extends Controller
{
    public function mostrarPdf(SolicitudPago $solicitudPago, SolicitudPagoReportService $service): Response
    {
        return $service->streamPdf($solicitudPago);
    }
}
