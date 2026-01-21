<div>
    <div class="p-4">
        <h2 class="text-lg font-bold mb-4">Órdenes de Compra para el Resumen #{{ $record->id }}</h2>
        @if(empty($groupedDetalles))
            <p>No hay órdenes de compra para este resumen.</p>
        @else
            @foreach ($groupedDetalles as $grupo)
                <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <div class="font-semibold">Conexión: {{ $grupo['conexion_nombre'] ?: $grupo['conexion_id'] }}</div>
                    <div>Empresa: {{ $grupo['empresa_nombre'] ?: $grupo['empresa_id'] }}</div>
                    <div>Sucursal: {{ $grupo['sucursal_nombre'] ?: $grupo['sucursal_id'] }}</div>
                    <div class="mt-2 font-semibold">Total grupo: {{ number_format($grupo['total'], 2) }}</div>
                </div>

                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 mb-6">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID Orden</th>
                            <th scope="col" class="px-6 py-3">Conexión</th>
                            <th scope="col" class="px-6 py-3">Proveedor</th>
                            <th scope="col" class="px-6 py-3">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grupo['detalles'] as $detalle)
                            <tr wire:key="detalle-{{ $detalle->id }}"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">

                                <td class="px-6 py-4">{{ $detalle->ordenCompra->id }}</td>
                                <td class="px-6 py-4">{{ $detalle->ordenCompra->empresa->nombre_empresa ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $detalle->ordenCompra->proveedor }}</td>
                                <td class="px-6 py-4">
                                    {{ $detalle->ordenCompra->fecha_pedido ? $detalle->ordenCompra->fecha_pedido->format('Y-m-d') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">{{ number_format($detalle->ordenCompra->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </div>
</div>
