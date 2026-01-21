<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudPagoContexto extends Model
{
    protected $fillable = [
        'solicitud_pago_id',
        'conexion',
        'empresa_id',
        'sucursal_codigo',
    ];

    public function solicitudPago()
    {
        return $this->belongsTo(SolicitudPago::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    protected static function booted(): void
    {
        $guard = function (self $model) {
            if ($model->solicitudPago && strtoupper((string) $model->solicitudPago->estado) === 'APROBADA') {
                throw new \RuntimeException('No se pueden modificar contextos de una solicitud aprobada.');
            }
        };

        static::updating($guard);
        static::deleting($guard);
    }
}
