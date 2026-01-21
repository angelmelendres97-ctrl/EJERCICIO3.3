<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleProformaProveedor extends Model
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
    ];

    protected $casts = [
        'seleccionado' => 'boolean',
        'costo' => 'float',
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
