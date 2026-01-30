<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjuntoProveedorUafe extends Model
{
    use HasFactory;

    protected $table = 'adjuntos_clpv';

    protected $fillable = [
        'id_empresa',
        'id_sucursal',
        'id_clpv',
        'id_archivo_uafe',
        'titulo',
        'estado',
        'ruta',
        'user_web',
        'fecha_server',
        'fecha_entrega',
        'fecha_vencimiento_uafe',
        'periodo_uafe',
    ];

    protected $casts = [
        'fecha_server' => 'datetime',
        'fecha_entrega' => 'date',
        'fecha_vencimiento_uafe' => 'date',
    ];

    public function archivoUafe()
    {
        return $this->belongsTo(ArchivoUafe::class, 'id_archivo_uafe');
    }
}
