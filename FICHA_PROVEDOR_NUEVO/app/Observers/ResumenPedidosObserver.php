<?php

namespace App\Observers;

use App\Models\ResumenPedidos;

class ResumenPedidosObserver
{
    /**
     * Handle the ResumenPedidos "deleting" event.
     *
     * @param  \App\Models\ResumenPedidos  $resumenPedidos
     * @return void
     */
    public function deleting(ResumenPedidos $resumenPedidos)
    {
        $resumenPedidos->detalles()->delete();
    }
}
