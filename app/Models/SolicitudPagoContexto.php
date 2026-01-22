<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SolicitudPago;

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
            $estado = strtoupper((string) $model->solicitudPago?->estado);
            $estadoAnuladaAprobada = strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA);

            if ($estado && in_array($estado, ['APROBADA', $estadoAnuladaAprobada], true)) {
                throw new \RuntimeException('No se pueden modificar contextos de una solicitud aprobada.');
            }
        };

        static::updating($guard);
        static::deleting($guard);
    }
}
