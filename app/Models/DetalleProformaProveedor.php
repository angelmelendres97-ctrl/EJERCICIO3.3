<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DetalleProformaProveedor extends Pivot
{
    protected $table = 'detalle_proforma_proveedores';

    protected $fillable = [
        'id_detalle_proforma',
        'id_proveedor',
        'seleccionado',
        'costo',
        'correo',
        'contacto',
        'precio',
        'cantidad_oferta',
        'valor_unitario_oferta',
        'subtotal_oferta',
        'descuento_porcentaje',
        'iva_porcentaje',
        'otros_cargos',
        'total_oferta',
        'observacion_oferta',
        'es_aprobado',
        'cantidad_aprobada',
        'precio_aprobado',
        'observacion_aprobacion',
    ];

    protected $casts = [
        'seleccionado' => 'boolean',
        'es_aprobado' => 'boolean',
        'costo' => 'float',
        'cantidad_oferta' => 'float',
        'valor_unitario_oferta' => 'float',
        'subtotal_oferta' => 'float',
        'descuento_porcentaje' => 'float',
        'iva_porcentaje' => 'float',
        'otros_cargos' => 'float',
        'total_oferta' => 'float',
        'cantidad_aprobada' => 'float',
        'precio_aprobado' => 'float',
    ];

    public function detalleProforma()
    {
        return $this->belongsTo(DetalleProforma::class, 'id_detalle_proforma');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor');
    }
}
