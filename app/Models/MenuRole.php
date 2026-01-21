<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MenuRole extends Pivot
{
    protected $table = 'menu_role';
    
    protected $fillable = [
        'menu_id',
        'role_id',
    ];
    
    public $timestamps = true;
}