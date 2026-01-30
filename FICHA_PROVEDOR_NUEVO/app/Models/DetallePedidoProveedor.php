<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedidoProveedor extends Model
{
    protected $table = 'detalle_pedido_proveedores';

    protected $fillable = [
        'id_detalle_pedido',
        'id_proveedor',
        'seleccionado',
        'costo',
    ];

    protected $casts = [
        'seleccionado' => 'boolean',
        'costo' => 'float',
    ];

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedidoCompra::class, 'id_detalle_pedido');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor');
    }
}
