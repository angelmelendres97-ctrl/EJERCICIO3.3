
<div class="p-4">
    <h3 class="text-lg font-semibold mb-4">Detalles de la Orden de Compra</h3>

    @php
        $totalGeneral = 0;
        $totalDescuentos = 0;
        $totalImpuesto = 0;
        $totalIva5 = 0;
        $totalIva8 = 0;
        $totalIva15 = 0;
        $baseIva0 = 0;
        $subtotal = 0;
    @endphp

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">#</th>
                    <th scope="col" class="px-6 py-3">Bodega</th>
                    <th scope="col" class="px-6 py-3">Código Producto</th>
                    <th scope="col" class="px-6 py-3">Producto</th>
                    <th scope="col" class="px-6 py-3 text-right">Cantidad</th>
                    <th scope="col" class="px-6 py-3 text-right">Costo</th>
                    <th scope="col" class="px-6 py-3 text-right">Descuento</th>
                    <th scope="col" class="px-6 py-3 text-right">Impuesto (%)</th>
                    <th scope="col" class="px-6 py-3 text-right">Valor Impuesto</th>
                    <th scope="col" class="px-6 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detalles as $index => $detalle)
                    @php
                        $cantidad = floatval($detalle->cantidad);
                        $costo = floatval($detalle->costo);
                        $descuento = floatval($detalle->descuento);
                        $impuestoPorcentaje = floatval($detalle->impuesto);
                        $subtotal_linea = $cantidad * $costo;
                        $valorImpuesto = $subtotal_linea * ($impuestoPorcentaje / 100);
                        $total = ($subtotal_linea + $valorImpuesto) - $descuento;

                        // Acumular totales
                        $subtotal += $subtotal_linea;
                        $totalGeneral += $total;
                        $totalDescuentos += $descuento;
                        $totalImpuesto += $valorImpuesto;
                        if ($impuestoPorcentaje == 5) $totalIva5 += $valorImpuesto;
                        if ($impuestoPorcentaje == 8) $totalIva8 += $valorImpuesto;
                        if ($impuestoPorcentaje == 15) $totalIva15 += $valorImpuesto;
                        if ($impuestoPorcentaje == 0) $baseIva0 += $subtotal_linea;
                    @endphp
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $index + 1 }}
                        </th>
                        <td class="px-6 py-4">{{ $detalle->bodega }}</td>
                        <td class="px-6 py-4">{{ $detalle->codigo_producto }}</td>
                        <td class="px-6 py-4">{{ $detalle->producto }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($cantidad, 2) }}</td>
                        <td class="px-6 py-4 text-right">${{ number_format($costo, 2) }}</td>
                        <td class="px-6 py-4 text-right">${{ number_format($descuento, 2) }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($impuestoPorcentaje, 2) }}%</td>
                        <td class="px-6 py-4 text-right">${{ number_format($valorImpuesto, 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold">${{ number_format($total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center">No se encontraron productos para esta orden de compra.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($detalles) > 0)
                <tfoot>
                    <tr class="font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">
                        <th scope="row" colspan="6" class="px-6 py-3 text-right text-base">Total General</th>
                        <td class="px-6 py-3 text-right font-bold">${{ number_format($totalDescuentos, 2) }}</td>
                        <td class="px-6 py-3"></td> <!-- Columna de Impuesto % -->
                        <td class="px-6 py-3 text-right font-bold">${{ number_format($totalImpuesto, 2) }}</td>
                        <td class="px-6 py-3 text-right font-bold">${{ number_format($totalGeneral, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    <br>

    @if(count($detalles) > 0)
        <div class="mt-4 flex justify-end">
            <div class="w-full max-w-sm space-y-2">
                <!-- Subtotal -->
                <div class="flex justify-between">
                    <span class="font-semibold">Subtotal:</span>
                    <span class="font-bold w-32 text-right">${{ number_format($subtotal, 2) }}</span>
                </div>

                <!-- Total Descuentos -->
                <div class="flex justify-between">
                    <span class="font-semibold">Total Descuentos:</span>
                    <span class="font-bold w-32 text-right">${{ number_format($totalDescuentos, 2) }}</span>
                </div>

                <!-- IVA 5% -->
                @if ($totalIva5 > 0)
                <div class="flex justify-between">
                    <span class="font-semibold">IVA 5%:</span>
                    <span class="font-bold w-32 text-right">${{ number_format($totalIva5, 2) }}</span>
                </div>
                @endif

                <!-- IVA 8% -->
                @if ($totalIva8 > 0)
                <div class="flex justify-between">
                    <span class="font-semibold">IVA 8%:</span>
                    <span class="font-bold w-32 text-right">${{ number_format($totalIva8, 2) }}</span>
                </div>
                @endif

                <!-- IVA 15% -->
                @if ($totalIva15 > 0)
                <div class="flex justify-between">
                    <span class="font-semibold">IVA 15%:</span>
                    <span class="font-bold w-32 text-right">${{ number_format($totalIva15, 2) }}</span>
                </div>
                @endif

                <!-- Base IVA 0% -->
                @if ($baseIva0 > 0)
                <div class="flex justify-between">
                    <span class="font-semibold">Base IVA 0%:</span>
                    <span class="font-bold w-32 text-right">${{ number_format($baseIva0, 2) }}</span>
                </div>
                @endif

                <!-- Total General -->
                <div class="flex justify-between border-t border-gray-300 dark:border-gray-700 pt-2 mt-2">
                    <span class="font-extrabold text-lg text-primary-600">Total General:</span>
                    <span class="font-extrabold text-xl text-primary-600 w-32 text-right">${{ number_format($totalGeneral, 2) }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
