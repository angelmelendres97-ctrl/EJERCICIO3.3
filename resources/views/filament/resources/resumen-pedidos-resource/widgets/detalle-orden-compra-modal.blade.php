@php
    $subtotal = 0;
    $totalDescuento = 0;
    $granTotal = 0;
    $impuestosPorTasa = [];

    foreach ($detalles as $detalle) {
        $subtotal += $detalle->cantidad * $detalle->costo;
        $totalDescuento += $detalle->descuento;
        $granTotal += $detalle->total;

        $tasa = (string) $detalle->impuesto;
        if (!isset($impuestosPorTasa[$tasa])) {
            $impuestosPorTasa[$tasa] = 0;
        }
        $impuestosPorTasa[$tasa] += $detalle->valor_impuesto;
    }
    ksort($impuestosPorTasa);
@endphp

<div class="p-4">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Producto</th>
                <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                <th scope="col" class="px-6 py-3 text-right">Costo</th>
                <th scope="col" class="px-6 py-3 text-right">Descuento</th>
                <th scope="col" class="px-6 py-3 text-right">Impuesto %</th>
                <th scope="col" class="px-6 py-3 text-right">Valor Impuesto</th>
                <th scope="col" class="px-6 py-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detalles as $detalle)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4">{{ $detalle->producto ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detalle->costo, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detalle->descuento, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detalle->impuesto, 2) }}%</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detalle->valor_impuesto, 2) }}</td>
                    <td class="px-6 py-4 text-right">{{ number_format($detalle->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No hay detalles para esta orden de compra.</td>
                </tr>
            @endforelse
        </tbody>
        @if($detalles->isNotEmpty())
            <tfoot class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right font-bold">Subtotal:</td>
                    <td class="px-6 py-3 text-right font-bold">{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right font-bold">Total Descuento:</td>
                    <td class="px-6 py-3 text-right font-bold">{{ number_format($totalDescuento, 2) }}</td>
                </tr>
                @foreach($impuestosPorTasa as $tasa => $valor)
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right font-bold">Total IVA ({{ number_format((float)$tasa, 2) }}%):</td>
                    <td class="px-6 py-3 text-right font-bold">{{ number_format($valor, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right font-bold">Total General:</td>
                    <td class="px-6 py-3 text-right font-bold">{{ number_format($granTotal, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
