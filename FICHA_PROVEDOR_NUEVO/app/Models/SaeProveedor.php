<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaeProveedor extends Model
{
    protected $table = 'saeclpv';

    protected $primaryKey = 'record_key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'anulada' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
