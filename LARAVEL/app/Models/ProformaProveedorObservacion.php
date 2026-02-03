<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaProveedorObservacion extends Model
{
    protected $fillable = [
        'id_proforma',
        'id_proveedor',
        'observacion',
    ];

    public function proforma()
    {
        return $this->belongsTo(Proforma::class, 'id_proforma');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
}
