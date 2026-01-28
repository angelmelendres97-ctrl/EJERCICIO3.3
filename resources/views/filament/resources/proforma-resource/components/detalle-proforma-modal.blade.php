<div class="space-y-6">
    <div class="grid gap-4 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 sm:grid-cols-2">
        <div>
            <div class="text-xs font-semibold uppercase text-gray-500">Núm.</div>
            <div class="text-base font-semibold">{{ $record->id }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase text-gray-500">Estado</div>
            <div class="text-base font-semibold">{{ $record->estado }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase text-gray-500">Proveedor</div>
            <div class="text-base">{{ $record->proveedor ?: 'N/A' }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold uppercase text-gray-500">Fecha</div>
            <div class="text-base">{{ optional($record->fecha_pedido)->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">Bodega</th>
                    <th class="px-4 py-3 text-right">Cantidad</th>
                    <th class="px-4 py-3 text-right">Costo</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse ($record->detalles as $detalle)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $detalle->es_manual ? 'Manual' : 'Inventario' }}
                        </td>
                        <td class="px-4 py-3">{{ $detalle->codigo_producto }}</td>
                        <td class="px-4 py-3">{{ $detalle->producto }}</td>
                        <td class="px-4 py-3">{{ $detalle->bodega ?? $detalle->id_bodega }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $detalle->costo, 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $detalle->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-3 text-center" colspan="7">Sin ítems registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-lg bg-amber-50 px-4 py-3 text-right text-base font-bold text-amber-800">
        Total general: ${{ number_format((float) $record->total, 2) }}
    </div>
</div>
