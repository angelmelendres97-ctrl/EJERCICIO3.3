<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoVencidoTemporal extends Model
{
    protected $connection = 'sqlite_memory';
    protected $table = 'temp_saldos';
    public $timestamps = false;
    protected $guarded = [];
}
