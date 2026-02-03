<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaeProducto extends Model
{
    protected $table = 'saeprod';

    protected $primaryKey = 'record_key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'iva_sn' => 'boolean',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
        'porcentaje_iva' => 'decimal:2',
        'tipo' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
