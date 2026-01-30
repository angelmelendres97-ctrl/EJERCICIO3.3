<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleResumenPedidos extends Model
{

    protected $table = 'detalle_resumen_pedidos';

    protected $fillable = [
        'id_orden_compra',
        'id_resumen_pedidos',
    ];

    public function resumenPedido()
    {
        return $this->belongsTo(\App\Models\ResumenPedidos::class, 'id_resumen_pedidos');
    }


    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'id_orden_compra');
    }
}
