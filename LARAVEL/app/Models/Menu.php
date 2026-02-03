<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'nombre',
        'grupo',
        'icono',
        'ruta',
        'activo',
        'orden',
    ];

    public function roles()
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'menu_role')
                    ->using(MenuRole::class);
    }
}
