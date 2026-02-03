<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AprobacionPagoDetalle extends Model
{
    protected $fillable = [
        'aprobacion_pago_id',
        'solicitud_pago_detalle_id',
        'monto_aprobado',
    ];

    protected $casts = [
        'monto_aprobado' => 'float',
    ];

    public function aprobacion()
    {
        return $this->belongsTo(AprobacionPago::class, 'aprobacion_pago_id');
    }

    public function solicitudDetalle()
    {
        return $this->belongsTo(SolicitudPagoDetalle::class, 'solicitud_pago_detalle_id');
    }
}
