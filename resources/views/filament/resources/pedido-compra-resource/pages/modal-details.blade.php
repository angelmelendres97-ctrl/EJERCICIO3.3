<div class="p-4">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Código Producto</th>
                <th scope="col" class="px-6 py-3">Nombre Producto</th>
                <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                <th scope="col" class="px-6 py-3 text-right">Costo</th>
                <th scope="col" class="px-6 py-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $detail)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4">{{ $detail->dped_cod_prod }}</td>
                    <td class="px-6 py-4">{{ $detail->dped_prod_nom }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detail->dped_can_ped, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detail->dped_prc_dped, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detail->dped_can_ped * $detail->dped_prc_dped, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-semibold text-gray-900 dark:text-white">
                <th scope="row" colspan="4" class="px-6 py-3 text-base text-right">Total General</th>
                <td class="px-6 py-3 text-right">{{ number_format($totalGeneral, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
