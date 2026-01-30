<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Producto;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medidas';

    protected $fillable = [
        'nombre',
        'siglas',
        'id_usuario',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_unidad_medida');
    }
}
