<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudPagoAsiento extends Model
{
    protected $fillable = [
        'solicitud_pago_id',
        'conexion',
        'empresa_id',
        'sucursal_codigo',
        'ejercicio',
        'periodo',
        'asto_cod_asto',
    ];

    public function solicitudPago(): BelongsTo
    {
        return $this->belongsTo(SolicitudPago::class);
    }
}
