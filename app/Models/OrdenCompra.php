<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OrdenCompra extends Model
{
    protected $fillable = [
        'id_empresa',
        'id_usuario',
        'amdg_id_empresa',
        'amdg_id_sucursal',
        'uso_compra',
        'solicitado_por',
        'numero_factura_proforma',
        'formato',
        'tipo_oc',
        'nombre_reembolso',
        'presupuesto',
        'trasanccion',
        'id_proveedor',
        'identificacion',
        'proveedor',
        'fecha_pedido',
        'fecha_entrega',
        'observaciones',
        'pedidos_importados',
        'subtotal',
        'total_descuento',
        'total_impuesto',
        'total',
        'anulada',
    ];

    protected $casts = [
        'fecha_pedido' => 'date',
        'fecha_entrega' => 'date',
        'subtotal' => 'float',
        'total_descuento' => 'float',
        'total_impuesto' => 'float',
        'total' => 'float',
        'anulada' => 'boolean',
    ];

    // Relación con empresas
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function resumenDetalle()
    {
        return $this->hasOne(DetalleResumenPedidos::class, 'id_orden_compra');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrdenCompra::class, 'id_orden_compra')
            ->orderBy('pedido_codigo')
            ->orderBy('pedido_detalle_id')
            ->orderBy('id');
    }

    protected static function booted()
    {
        static::deleting(function ($ordenCompra) {
            // Eliminar detalles asociados
            $ordenCompra->detalles()->each(function($detalle) {
                $detalle->delete();
            });
        });
    }
}
