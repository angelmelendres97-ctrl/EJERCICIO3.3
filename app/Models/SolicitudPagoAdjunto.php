<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudPagoAdjunto extends Model
{
    protected $fillable = [
        'solicitud_pago_id',
        'nombre',
        'archivo',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudPago::class, 'solicitud_pago_id');
    }
}
