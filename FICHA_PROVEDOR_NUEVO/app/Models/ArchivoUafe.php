<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoUafe extends Model
{
    use HasFactory;

    protected $table = 'archivos_uafe';

    protected $fillable = [
        'empr_cod_empr',
        'titulo',
        'estado',
    ];
}
