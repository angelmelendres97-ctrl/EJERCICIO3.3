<div class="p-4">
    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    @elseif($details->isEmpty())
        <p>No se encontraron detalles para este pedido.</p>
    @else
        @php
            $totalGeneral = $details->sum(function($detail) {
                return $detail->cantidad_pendiente * $detail->dped_prc_dped;
            });
        @endphp
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Producto</th>
                    <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                    <th scope="col" class="px-6 py-3 text-right">Costo</th>
                    <th scope="col" class="px-6 py-3 text-right">Total</th>
                    <th scope="col" class="px-6 py-3 text-right">Cantidad Importada</th>
                    <th scope="col" class="px-6 py-3 text-right">Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $detail)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">{{ $detail->dped_prod_nom }} ({{$detail->dped_cod_prod}})</td>
                        <td class="px-6 py-4 text-right">{{ number_format($detail->dped_can_ped, 2) }}</td>
                        <td class="px-6 py-4 text-right">${{ number_format($detail->dped_prc_dped, 2) }}</td>
                        <td class="px-6 py-4 text-right">${{ number_format($detail->cantidad_pendiente * $detail->dped_prc_dped, 2) }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($detail->cantidad_importada, 2) }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($detail->cantidad_pendiente, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold text-gray-900 dark:text-white">
                    <th scope="row" colspan="3" class="px-6 py-3 text-base text-right">Total General</th>
                    <td class="px-6 py-3 text-right">${{ number_format($totalGeneral, 2) }}</td>
                    <th scope="row" colspan="2" class="px-6 py-3 text-base text-right"></th>
                </tr>
            </tfoot>
        </table>
    @endif
</div>
