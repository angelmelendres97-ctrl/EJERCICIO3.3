<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl dark:bg-gray-800">
                <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Detalle del Pedido: {{ $pedi_cod_pedi }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Producto</th>
                                    <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-right">Costo</th>
                                    <th scope="col" class="px-6 py-3 text-right">Total</th>
                                    <th scope="col" class="px-6 py-3 text-right">Cantidad Recibida</th>
                                    <th scope="col" class="px-6 py-3 text-right">Por recibir</th>
                                    <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $detail)
                                    @php $detail = (object) $detail; @endphp
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4">{{ $detail->dped_prod_nom }} ({{$detail->dped_cod_prod}})</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($detail->dped_can_ped, 2) }}</td>
                                        <td class="px-6 py-4 text-right">${{ number_format($detail->dped_prc_dped, 2) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            ${{ number_format($detail->dped_can_ped * $detail->dped_prc_dped, 2) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($detail->dped_can_ent, 2) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            {{ number_format($detail->dped_can_ped - $detail->dped_can_ent, 2) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <button class="text-red-600 hover:text-red-800"
                                                onclick="confirm('¿Estás seguro que deseas dar por finalizado este item?') || event.stopImmediatePropagation()"
                                                wire:click="deleteDetail({{ $detail->dped_cod_pedi }}, '{{ $detail->dped_cod_prod }}')"
                                                title="Eliminar">
                                                <!-- Ícono de eliminar (trash) -->
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21a48.108 48.108 0 00-3.478-.397m-12 
                                                    .38c1.12-.135 2.3-.255 3.536-.356m6.332 0c1.236.101 2.416.221 3.536.356m-8.868 
                                                    0V5.118c0-.89.716-1.618 1.606-1.618h3.456c.89 0 1.606.728 
                                                    1.606 1.618V6.79m-8.868 0h8.868" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No se encontraron detalles para este pedido.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold text-gray-900 dark:text-white">
                                    <th scope="row" colspan="4" class="px-6 py-3 text-base text-right">Total General</th>
                                    <td class="px-6 py-3 text-right">{{ number_format($totalGeneral, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex justify-end">
                    <button wire:click="closeModal"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>