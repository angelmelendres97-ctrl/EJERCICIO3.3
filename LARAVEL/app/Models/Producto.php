<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_empresa',
        'amdg_id_empresa',
        'amdg_id_sucursal',
        'linea',
        'grupo',
        'categoria',
        'marca',
        'sku',
        'nombre',
        'detalle',
        'tipo',
        'id_unidad_medida',
        'stock_minimo',
        'stock_maximo',
        'iva_sn',
        'porcentaje_iva',
    ];

    protected $casts = [
        'iva_sn' => 'boolean',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
        'porcentaje_iva' => 'decimal:2',
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'id_unidad_medida');
    }

    public function lineasNegocio()
    {
        return $this->belongsToMany(LineaNegocio::class, 'producto_linea_negocio', 'producto_id', 'linea_negocio_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
