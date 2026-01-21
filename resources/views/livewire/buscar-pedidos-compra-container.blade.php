<div>
    @livewire('buscar-pedidos-compra', [
        'id_empresa' => $id_empresa,
        'amdg_id_empresa' => $amdg_id_empresa,
        'amdg_id_sucursal' => $amdg_id_sucursal,
        'pedidos_importados' => $pedidos_importados ?? null,
    ])
</div>
