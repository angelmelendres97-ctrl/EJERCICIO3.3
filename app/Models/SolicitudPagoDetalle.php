<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SolicitudPago;

class SolicitudPagoDetalle extends Model
{
    protected $fillable = [
        'solicitud_pago_id',
        'erp_tabla',
        'erp_conexion',
        'erp_empresa_id',
        'erp_sucursal',
        'erp_clave',
        'proveedor_ruc',
        'proveedor_codigo',
        'proveedor_nombre',
        'numero_factura',
        'fecha_emision',
        'fecha_vencimiento',
        'monto_factura',
        'saldo_al_crear',
        'abono_aplicado',
        'estado_abono',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'monto_factura' => 'decimal:2',
        'saldo_al_crear' => 'decimal:2',
        'abono_aplicado' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $guard = function (self $model) {
            $estado = strtoupper((string) $model->solicitudPago?->estado);
            $estadoAnuladaAprobada = strtoupper(SolicitudPago::ESTADO_APROBADA_ANULADA);

            if ($estado && in_array($estado, ['APROBADA', $estadoAnuladaAprobada], true)) {
                throw new \RuntimeException('No se pueden modificar detalles de una solicitud aprobada.');
            }
        };

        static::updating($guard);
        static::deleting($guard);
    }

    public function solicitudPago()
    {
        return $this->belongsTo(SolicitudPago::class);
    }
}
