<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ResumenPedidosController;
use App\Http\Controllers\SolicitudPagoController;

Route::get('/', function () {
    return view('landing');
});

Route::get('/orden-compra/{ordenCompra}/pdf', [OrdenCompraController::class, 'descargarPdf'])->name('orden-compra.pdf');
Route::get('/resumen-pedidos/{resumenPedidos}/pdf', [ResumenPedidosController::class, 'descargarPdf'])->name('resumen-pedidos.pdf');
Route::get('/solicitud-pago/{solicitudPago}/pdf', [SolicitudPagoController::class, 'mostrarPdf'])->name('solicitud-pago.pdf');
