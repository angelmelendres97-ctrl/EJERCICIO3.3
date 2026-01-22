<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SolicitudPago extends Model
{
    public const ESTADO_APROBADA_ANULADA = 'SOLICITUD APROBADA ANULADA';
    public const ESTADO_SOLICITUD_COMPLETADA = 'SOLICITUD COMPLETADA';

    protected $fillable = [
        'id',
        'id_empresa',
        'fecha',
        'motivo',
        'tipo_solicitud',
        'monto_aprobado',
        'monto_estimado',
        'monto_utilizado',
        'creado_por_id',
        'aprobado_por_id',
        'aprobada_at',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_aprobado' => 'decimal:2',
        'monto_estimado' => 'decimal:2',
        'monto_utilizado' => 'decimal:2',
        'aprobada_at' => 'datetime',
    ];

    protected static function booted(): void
    {


        static::updating(function (self $model) {
            $estadoOriginal = strtoupper((string) $model->getOriginal('estado'));
            $estadoNuevo = strtoupper((string) $model->estado);
            $estadoAnuladaAprobada = strtoupper(self::ESTADO_APROBADA_ANULADA);
            $estadoCompletada = strtoupper(self::ESTADO_SOLICITUD_COMPLETADA);

            if ($estadoOriginal === 'APROBADA' && in_array($estadoNuevo, [$estadoAnuladaAprobada, $estadoCompletada], true)) {
                return;
            }

            if (in_array($estadoOriginal, ['APROBADA', $estadoAnuladaAprobada, $estadoCompletada], true)) {
                throw new \RuntimeException('La solicitud aprobada no puede modificarse.');
            }
        });

        static::deleting(function (self $model) {
            $estado = strtoupper((string) $model->estado);
            $estadoAnuladaAprobada = strtoupper(self::ESTADO_APROBADA_ANULADA);
            $estadoCompletada = strtoupper(self::ESTADO_SOLICITUD_COMPLETADA);

            if (in_array($estado, ['APROBADA', $estadoAnuladaAprobada, $estadoCompletada], true)) {
                throw new \RuntimeException('La solicitud aprobada no puede eliminarse.');
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_id');
    }

    public function detalles()
    {
        return $this->hasMany(SolicitudPagoDetalle::class);
    }

    public function contextos()
    {
        return $this->hasMany(SolicitudPagoContexto::class);
    }

    public function adjuntos()
    {
        return $this->hasMany(SolicitudPagoAdjunto::class);
    }

    public function asientos()
    {
        return $this->hasMany(SolicitudPagoAsiento::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }
}
