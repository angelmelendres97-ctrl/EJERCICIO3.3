<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DetalleProforma extends Model
{
    protected $fillable = [
        'id_proforma',
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
        'cantidad_aprobada',
        'unidad',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'costo' => 'float',
        'descuento' => 'float',
        'impuesto' => 'float',
        'valor_impuesto' => 'float',
        'total' => 'float',
    ];

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'id_proforma');
    }

    public function proveedoresAsignados()
    {
        return $this->hasMany(DetalleProformaProveedor::class, 'id_detalle_proforma');
    }

    public function proveedores()
    {
        return $this->belongsToMany(Proveedores::class, 'detalle_proforma_proveedores', 'id_detalle_proforma', 'id_proveedor')
            ->using(DetalleProformaProveedor::class)
            ->withPivot([
                'seleccionado',
                'costo',
                'cantidad_oferta',
                'valor_unitario_oferta',
                'total_oferta',
                'descuento_porcentaje',
                'iva_porcentaje',
                'otros_cargos',
                'subtotal_oferta',
                'observacion_oferta',
                'es_aprobado',
                'cantidad_aprobada',
                'precio_aprobado',
                'observacion_aprobacion'
            ])
            ->withTimestamps();
    }
}
