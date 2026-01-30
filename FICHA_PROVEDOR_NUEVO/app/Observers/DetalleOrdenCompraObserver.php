<?php

namespace App\Observers;

use App\Models\DetalleOrdenCompra;

class DetalleOrdenCompraObserver
{
    /**
     * Handle the DetalleOrdenCompra "creating" event.
     */
    public function creating(DetalleOrdenCompra $detalleOrdenCompra): void
    {
        $cantidad = floatval($detalleOrdenCompra->cantidad ?? 0);
        $costo = floatval($detalleOrdenCompra->costo ?? 0);
        $descuento = floatval($detalleOrdenCompra->descuento ?? 0);
        $porcentajeIva = floatval($detalleOrdenCompra->impuesto ?? 0);

        $subtotalItem = $cantidad * $costo;
        $valorIva = $subtotalItem * ($porcentajeIva / 100);
        $totalItem = ($subtotalItem - $descuento) + $valorIva;

        // Asignar los valores calculados al modelo antes de que se guarde
        $detalleOrdenCompra->valor_impuesto = $valorIva;
        $detalleOrdenCompra->total = $totalItem;
    }
}
