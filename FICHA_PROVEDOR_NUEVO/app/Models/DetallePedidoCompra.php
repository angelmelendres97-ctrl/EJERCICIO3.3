<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'detalles_pedidos_compras';

    protected $fillable = [
        'id_pedido_compra',
        'dped_cod_dped',
        'id_bodega',
        'nombre_bodega',
        'codigo_producto',
        'producto',
        'cantidad',
        'costo',
        'inpuesto',
        'detalle',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'costo' => 'float',
        'inpuesto' => 'float',
    ];

    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'id_pedido_compra');
    }
}