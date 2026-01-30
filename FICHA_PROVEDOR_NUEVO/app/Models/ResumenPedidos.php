<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ResumenPedidos extends Model
{
    protected $fillable = [
        'id_empresa',
        'id_usuario',
        'amdg_id_empresa',
        'amdg_id_sucursal',
        'codigo_secuencial',
        'tipo',
        'descripcion',
        'anulada',
    ];

    protected $casts = [
        'anulada' => 'boolean',
    ];

    // Relación con empresas
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleResumenPedidos::class, 'id_resumen_pedidos');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    

    public function getPedidosImportadosAttribute(): string
    {
        // 1) IDs de orden de compra que están en este resumen
        $ordenIds = $this->detalles()
            ->pluck('id_orden_compra')
            ->filter()
            ->unique()
            ->values();

        if ($ordenIds->isEmpty()) {
            return '—';
        }

        // 2) Buscar en detalle_orden_compras los códigos de pedido origen (IMPORTADOS)
        // OJO: ajusta el nombre de la tabla si la tuya se llama diferente.
        $rows = \DB::table('detalle_orden_compras')
            ->whereIn('id_orden_compra', $ordenIds->all())
            ->get();

        // 3) Detectar el campo real del pedido importado (varios posibles)
        $posiblesCampos = [
            'id_pedido_compra',
            'pedido_compra_id',
            'id_pedido',
            'pedido_id',
            'amdg_id_pedido',
            'amdg_cod_pedi',
            'pedi_cod_pedi',
            'cod_pedido',
            'codigo_pedido',
            'numero_pedido',
        ];

        $campoEncontrado = null;
        if ($rows->isNotEmpty()) {
            $primero = (array) $rows->first();
            foreach ($posiblesCampos as $c) {
                if (array_key_exists($c, $primero)) {
                    $campoEncontrado = $c;
                    break;
                }
            }
        }

        // 4) Si encontramos un campo, devolvemos esos códigos de pedido
        if ($campoEncontrado) {
            $codigos = $rows
                ->pluck($campoEncontrado)
                ->filter()
                ->unique()
                ->values();

            return $codigos->isEmpty() ? '—' : $codigos->join(', ');
        }

        // 5) Fallback: mostrar las órdenes incluidas (para que nunca quede vacío)
        return 'OC: ' . $ordenIds->join(', ');
    }
}
