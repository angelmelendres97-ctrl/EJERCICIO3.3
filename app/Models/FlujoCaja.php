<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlujoCaja extends Model
{
    use HasFactory;

    // This model is primarily for the Filament Resource structure
    // and does not necessarily map to a physical table needed for the report.
    protected $table = 'flujo_caja_dummy';
}
