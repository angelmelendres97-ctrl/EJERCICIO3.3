<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Proforma extends Model
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
        'observacao_comparativo', // Intentionally using correct column name from migration if needed, but file says 'observacion_comparativo'
        'observacion_comparativo',
        'subtotal',
        'total_descuento',
        'total_impuesto',
        'total',
        'estado',
    ];

    protected $casts = [
        'fecha_pedido' => 'date',
        'fecha_entrega' => 'date',
        'subtotal' => 'float',
        'total_descuento' => 'float',
        'total_impuesto' => 'float',
        'total' => 'float',
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

    public function detalles()
    {
        return $this->hasMany(DetalleProforma::class, 'id_proforma');
    }

    // Relación con observaciones generales por proveedor
    public function observacionesProveedores()
    {
        return $this->hasMany(ProformaProveedorObservacion::class, 'id_proforma');
    }

    protected static function booted()
    {
        static::deleting(function ($proforma) {
            // Eliminar detalles asociados
            $proforma->detalles()->each(function ($detalle) {
                $detalle->delete();
            });
            // Eliminar observaciones de proveedores
            $proforma->observacionesProveedores()->delete();
        });
    }
}
