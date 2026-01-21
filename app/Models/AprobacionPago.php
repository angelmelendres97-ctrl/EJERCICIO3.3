<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AprobacionPago extends Model
{
    protected $fillable = [
        'solicitud_pago_id',
        'user_id',
        'monto_aprobado',
        'facturas_pagadas',
        'facturas_pendientes',
    ];

    protected $casts = [
        'monto_aprobado' => 'float',
    ];

    public function solicitudPago()
    {
        return $this->belongsTo(SolicitudPago::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(AprobacionPagoDetalle::class);
    }
}
