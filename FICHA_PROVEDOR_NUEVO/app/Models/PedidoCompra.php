<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'pedidos_compras';

    protected $fillable = [
        'id_empresa',
        'pedi_cod_pedi',
        'amdg_id_empresa',
        'amdg_id_sucursal',
        'identificacion_proveedor',
        'nombre_proveedor',
        'fecha_pedido',
        'fecha_entrega',
        'observaciones',
        'fecha_creacion',
        'estado',
    ];

    protected $casts = [
        'fecha_pedido' => 'date',
        'fecha_entrega' => 'date',
        'fecha_creacion' => 'datetime',
        'pedi_cod_pedi' => 'integer',
        'amdg_id_empresa' => 'integer',
        'amdg_id_sucursal' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}