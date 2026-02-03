<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DetalleOrdenCompra extends Model
{
    // 4. Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_orden_compra',
        'pedido_codigo',
        'pedido_detalle_id',
        'id_bodega',
        'bodega',
        'codigo_producto',
        'producto',
        'cantidad',
        'costo',
        'descuento',
        'impuesto',
        'valor_impuesto',
        'total',
        'detalle',
        'unidad',
    ];

    // 5. Casting de Atributos (opcional)
    protected $casts = [
        'cantidad' => 'float',
        'costo' => 'float',
        'descuento' => 'float',
        'impuesto' => 'float',
        'valor_impuesto' => 'float',
        'pedido_codigo' => 'integer',
        'pedido_detalle_id' => 'integer',
    ];

    /**
     * Obtiene la orden de compra a la que pertenece el detalle.
     */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class, 'id_orden_compra');
    }
}
