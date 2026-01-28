<div class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs uppercase text-gray-500">Núm. Proforma</p>
                <p class="font-semibold">#{{ $record->id }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Estado</p>
                <p class="font-semibold">{{ $record->estado }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Fecha</p>
                <p class="font-semibold">{{ optional($record->fecha_pedido)->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">Proveedor</p>
                <p class="font-semibold">{{ $record->proveedor ?: 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-200">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3 text-right">Cantidad</th>
                    <th class="px-4 py-3 text-right">Costo</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3">Origen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($record->detalles as $detalle)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-3">{{ $detalle->codigo_producto ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $detalle->producto ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float) $detalle->costo, 2) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float) $detalle->total, 2) }}</td>
                        <td class="px-4 py-3">
                            {{ $detalle->id_bodega ? 'Inventario' : 'Manual' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No hay ítems registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
