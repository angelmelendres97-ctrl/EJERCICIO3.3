<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = [
        'ruc',
        'nombre_empresa',
        'nombre_pb',
        'tipo',
        'linea_negocio_id',
        'motor',
        'puerto',
        'host',
        'usuario',
        'clave',
        'nombre_base',
        'status_conexion',
        'mensaje_conexion',
    ];

    public function lineaNegocio()
    {
        return $this->belongsTo(LineaNegocio::class, 'linea_negocio_id');
    }
}
