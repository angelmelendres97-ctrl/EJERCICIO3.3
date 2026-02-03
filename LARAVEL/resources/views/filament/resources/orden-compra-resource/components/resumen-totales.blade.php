@php
    $fmtRate = fn($rate) => rtrim(rtrim(number_format((float) $rate, 2, '.', ''), '0'), '.');
    $subtotalGeneral = $subtotalGeneral ?? 0;
    $descuentoGeneral = $descuentoGeneral ?? 0;
    $totalGeneral = $totalGeneral ?? 0;
@endphp

<table class="w-full text-sm">
    <tbody>
        <tr>
            <th class="text-right font-semibold pr-4">Subtotal</th>
            <td class="text-right font-bold w-32">$ {{ number_format($subtotalGeneral, 2, '.', ',') }}</td>
        </tr>
        <tr>
            <th class="text-right font-semibold pr-4">Total Descuento</th>
            <td class="text-right font-bold w-32">$ {{ number_format($descuentoGeneral, 2, '.', ',') }}</td>
        </tr>
        @foreach ($tarifas ?? [] as $rate)
            @php
                $rateKey = (string) $rate;
                $base = $basePorIva[$rateKey] ?? 0;
                $iva = $ivaPorIva[$rateKey] ?? 0;
            @endphp
            @if (round($base, 6) > 0)
                <tr>
                    <th class="text-right font-semibold pr-4">Tarifa {{ $fmtRate($rate) }} %</th>
                    <td class="text-right font-bold w-32">$ {{ number_format($base, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <th class="text-right font-semibold pr-4">IVA {{ $fmtRate($rate) }} %</th>
                    <td class="text-right font-bold w-32">$ {{ number_format($iva, 2, '.', ',') }}</td>
                </tr>
            @endif
        @endforeach
        <tr class="border-t border-gray-300 dark:border-gray-700">
            <th class="text-right font-extrabold text-lg text-primary-600 pr-4">Total</th>
            <td class="text-right font-extrabold text-xl text-primary-600 w-32">$ {{ number_format($totalGeneral, 2, '.', ',') }}</td>
        </tr>
    </tbody>
</table>
