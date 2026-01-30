<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedores extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'id_empresa',
        'admg_id_empresa',
        'admg_id_sucursal',
        'tipo',
        'ruc',
        'nombre',
        'nombre_comercial',
        'grupo',
        'zona',
        'flujo_caja',
        'tipo_proveedor',
        'forma_pago',
        'destino_pago',
        'pais_pago',
        'dias_pago',
        'limite_credito',
        'aplica_retencion_sn',
        'telefono',
        'direcccion',
        'correo',
    ];

    public function lineasNegocio()
    {
        return $this->belongsToMany(LineaNegocio::class, 'proveedor_linea_negocios', 'proveedor_id', 'linea_negocio_id');
    }

    // Relación con empresas
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
