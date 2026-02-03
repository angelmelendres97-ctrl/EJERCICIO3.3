<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Empresa;

class LineaNegocio extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_linea_negocio', 'linea_negocio_id', 'producto_id');
    }

    public function proveedores()
    {
        return $this->belongsToMany(Producto::class, 'proveedor_linea_negocios', 'linea_negocio_id', 'proveedor_id');
    }
}
