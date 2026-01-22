<?php

namespace App\Http\Controllers;

use App\Models\SolicitudPago;
use App\Services\SolicitudPagoReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SolicitudPagoController extends Controller
{
    public function mostrarPdf(SolicitudPago $solicitudPago, SolicitudPagoReportService $service): StreamedResponse
    {
        return $service->streamPdf($solicitudPago);
    }
}
