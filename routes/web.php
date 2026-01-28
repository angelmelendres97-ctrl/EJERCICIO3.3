<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ResumenPedidosController;
use App\Http\Controllers\SolicitudPagoController;

Route::get('/', function () {
    return view('landing');
});

// ORDEN DE COMPRA (descarga o según tu controlador)
Route::get('/orden-compra/{ordenCompra}/pdf', [OrdenCompraController::class, 'descargarPdf'])
    ->name('orden-compra.pdf');

// RESUMEN PEDIDOS
Route::get('/resumen-pedidos/{resumenPedidos}/pdf', [ResumenPedidosController::class, 'descargarPdf'])
    ->name('resumen-pedidos.pdf');

// SOLICITUD DE PAGO (ABRIR / INLINE - NO DESCARGA)
Route::get('/solicitud-pago/{solicitudPago}/pdf', [SolicitudPagoController::class, 'mostrarPdf'])
    ->name('solicitud-pago.pdf');

// (OPCIONAL) Si también quieres una ruta que SÍ descargue la Solicitud de Pago:
Route::get('/solicitud-pago/{solicitudPago}/pdf/descargar', [SolicitudPagoController::class, 'descargarPdf'])
    ->name('solicitud-pago.pdf.descargar');

Route::get('/solicitud-pago/{solicitudPago}/pdf/detallado', [SolicitudPagoController::class, 'mostrarPdfDetallado'])
    ->name('solicitud-pago.detallado.pdf');
