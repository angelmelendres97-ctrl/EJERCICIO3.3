<x-filament::page>
    <form wire:submit.prevent="save">
        <div class="overflow-x-auto">
            <table
                class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400 border-collapse border border-gray-300">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col"
                            class="px-4 py-3 border border-gray-300 sticky left-0 z-20 bg-gray-50 shadow-md">
                            <div style="min-width: 250px; width: 250px;">Producto / Detalle</div>
                        </th>
                        @foreach($providers as $provider)
                            <th scope="col" class="px-4 py-3 border border-gray-300 text-center">
                                <div style="min-width: 450px; width: 450px;">
                                    <div class="font-bold text-base text-black">{{ $provider->nombre }}</div>
                                    <div class="text-xs font-normal normal-case">
                                        {{ $provider->correo_display }}<br>
                                        {{ $provider->contacto_display }}
                                    </div>
                                    <div class="mt-2 text-left">
                                        <label class="text-[10px] font-bold text-gray-500">Observación General:</label>
                                        @if($this->record->estado === 'Comparativo Precios')
                                            <div class="text-xs text-gray-500 italic bg-gray-50 p-2 rounded border border-gray-200">
                                                {{ $providerObservations[$provider->id] ?? 'Sin observaciones generales.' }}
                                            </div>
                                        @else
                                            <textarea wire:model.blur="providerObservations.{{ $provider->id }}"
                                                placeholder="Observación general para este proveedor..."
                                                class="w-full text-xs p-1.5 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 resize-none h-16"></textarea>
                                        @endif
                                    </div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50">
                            <td class="px-4 py-4 border border-gray-300 align-top sticky left-0 z-10 bg-white shadow-md">
                                <div style="min-width: 250px; width: 250px;">
                                    <div class="font-bold text-gray-900">{{ $product->producto }}</div>
                                    <div class="text-xs text-gray-500">
                                        Bodega: {{ $bodegas[$product->id_bodega] ?? $product->id_bodega }}<br>
                                        Código: {{ $product->codigo_producto ?? 'N/A' }}
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <span class="font-semibold">Solicitado:</span>
                                        {{ number_format($product->cantidad, 2) }}<br>
                                        <span class="font-semibold text-success-600">Aprobado:</span>
                                        {{ number_format($product->cantidad_aprobada, 2) }}
                                    </div>
                                </div>
                            </td>

                            @foreach($providers as $provider)
                                @php
                                    $pivot = $matrix[$product->id][$provider->id] ?? null;
                                    $bindings = "matrix.{$product->id}.{$provider->id}";
                                @endphp
                                <td class="px-2 py-2 border border-gray-300 align-top">
                                    @if($pivot)
                                        <div class="space-y-3" style="min-width: 450px; width: 450px;">
                                            <!-- Row 1: Cant, V.Unit, Desc -->
                                            <div class="flex gap-2 w-full">
                                                <!-- Col 1: Cantidad -->
                                                <div class="flex-1">
                                                    <label
                                                        class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Cant.</label>
                                                    @if($this->record->estado === 'Comparativo Precios')
                                                        <div class="text-xs p-1.5">
                                                            {{ number_format($pivot['cantidad_oferta'] ?? 0, 2) }}</div>
                                                    @else
                                                        <input type="number" step="0.01"
                                                            wire:model.blur="{{ $bindings }}.cantidad_oferta"
                                                            class="block w-full text-xs p-1.5 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-50 focus:bg-white transition-colors duration-200">
                                                    @endif
                                                </div>
                                                <!-- Col 2: Valor Unitario -->
                                                <div class="flex-1">
                                                    <label
                                                        class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Costo</label>
                                                    <div class="relative">
                                                        @if($this->record->estado === 'Comparativo Precios')
                                                            <div class="text-xs p-1.5">
                                                                ${{ number_format($pivot['valor_unitario_oferta'] ?? 0, 2) }}</div>
                                                        @else
                                                            <input type="number" step="0.01"
                                                                wire:model.blur="{{ $bindings }}.valor_unitario_oferta"
                                                                class="block w-full text-xs p-1.5 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-50 focus:bg-white transition-colors duration-200">
                                                        @endif
                                                    </div>
                                                </div>
                                                <!-- Col 3: Descuento % -->
                                                <div class="flex-1">
                                                    <label class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Desc
                                                        %</label>
                                                    @if($this->record->estado === 'Comparativo Precios')
                                                        <div class="text-xs p-1.5">
                                                            {{ number_format($pivot['descuento_porcentaje'] ?? 0, 2) }}%</div>
                                                    @else
                                                        <input type="number" step="0.01"
                                                            wire:model.blur="{{ $bindings }}.descuento_porcentaje"
                                                            class="block w-full text-xs p-1.5 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-50 focus:bg-white transition-colors duration-200">
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex gap-2 w-full">
                                                <!-- Col 1: IVA (Select) -->
                                                <div class="flex-1">
                                                    <label
                                                        class="text-[10px] uppercase font-bold text-gray-500 block mb-1">IVA</label>
                                                    @if($this->record->estado === 'Comparativo Precios')
                                                        <div class="text-xs p-1.5">
                                                            {{ number_format($pivot['iva_porcentaje'] ?? 0, 0) }}%</div>
                                                    @else
                                                        <select wire:model.live.debounce.500ms="{{ $bindings }}.iva_porcentaje"
                                                            class="block w-full text-xs p-1.5 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-white">
                                                            <option value="0">0%</option>
                                                            <option value="5">5%</option>
                                                            <option value="8">8%</option>
                                                            <option value="15">15%</option>
                                                            <option value="18">18%</option>
                                                        </select>
                                                    @endif
                                                </div>
                                                <!-- Col 2: Otros Cargos -->
                                                <div class="flex-1">
                                                    <label
                                                        class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Otros</label>
                                                    <div class="relative">
                                                        @if($this->record->estado === 'Comparativo Precios')
                                                            <div class="text-xs p-1.5">
                                                                ${{ number_format($pivot['otros_cargos'] ?? 0, 2) }}</div>
                                                        @else
                                                            <input type="number" step="0.01"
                                                                wire:model.blur="{{ $bindings }}.otros_cargos"
                                                                class="block w-full text-xs p-1.5 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-50 focus:bg-white transition-colors duration-200">
                                                        @endif
                                                    </div>
                                                </div>
                                                <!-- Col 3: Total Linea (Disabled) -->
                                                <div
                                                    class="flex-1 bg-gray-100 rounded-lg px-2 flex flex-col justify-center border border-gray-200">
                                                    <label
                                                        class="text-[9px] uppercase font-bold text-gray-400 block text-right">Total</label>
                                                    <div class="text-right font-bold text-sm text-black">
                                                        ${{ number_format($pivot['total_oferta'] ?? 0, 2) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Observacion -->
                                            <div class="relative">
                                                @if($this->record->estado === 'Comparativo Precios')
                                                    <div class="text-xs p-1.5 italic text-gray-600">
                                                        {{ $pivot['observacion_oferta'] ?? '-' }}</div>
                                                @else
                                                    <input type="text" placeholder="Observación..."
                                                        wire:model.live.debounce.1000ms="{{ $bindings }}.observacion_oferta"
                                                        class="block w-full text-xs p-1.5 border-0 border-b-2 border-gray-200 focus:border-primary-500 focus:ring-0 bg-transparent placeholder-gray-400 transition-colors">
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-300 italic text-xs py-4">
                                            No asignado
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>

                <!-- FOOTER TOTALS -->
                <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                    <tr>
                        <td class="px-4 py-3 font-bold text-gray-700 text-right">
                            Resumen General
                        </td>
                        @foreach($providers as $provider)
                            @php
                                $totals = $grandTotals[$provider->id] ?? [
                                    'subtotal' => 0,
                                    'descuento' => 0,
                                    'otros_cargos' => 0,
                                    'total' => 0,
                                    'iva_breakdown' => []
                                ];
                            @endphp
                            <td class="px-4 py-3 align-top">
                                <div class="text-xs space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Subtotal:</span>
                                        <span
                                            class="font-mono font-medium">${{ number_format($totals['subtotal'], 2) }}</span>
                                    </div>
                                    @if($totals['descuento'] > 0)
                                        <div class="flex justify-between text-success-700">
                                            <span>Descuento:</span>
                                            <span class="font-mono">-${{ number_format($totals['descuento'], 2) }}</span>
                                        </div>
                                    @endif

                                    <!-- IVA Breakdown -->
                                    @foreach($totals['iva_breakdown'] as $rate => $amount)
                                        @if($amount > 0)
                                            <div class="flex justify-between text-gray-600">
                                                <span>IVA {{ $rate }}%:</span>
                                                <span class="font-mono">${{ number_format($amount, 2) }}</span>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if($totals['otros_cargos'] > 0)
                                        <div class="flex justify-between text-gray-600">
                                            <span>Otros:</span>
                                            <span class="font-mono">${{ number_format($totals['otros_cargos'], 2) }}</span>
                                        </div>
                                    @endif

                                    <div
                                        class="border-t border-gray-300 pt-1 mt-1 flex justify-between font-bold text-base text-black">
                                        <span>TOTAL:</span>
                                        <span>${{ number_format($totals['total'], 2) }}</span>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <label class="block text-sm font-bold text-gray-700 mb-2">Observación General del Comparativo</label>
            @if($this->record->estado === 'Comparativo Precios')
                <div class="p-3 bg-gray-50 rounded-lg text-gray-700 text-sm border border-gray-200 min-h-[80px]">
                    {{ $globalObservation ?? 'Sin observación general.' }}
                </div>
            @else
                <textarea wire:model.blur="globalObservation"
                    placeholder="Ingrese una observación general para todo el cuadro comparativo..."
                    class="block w-full text-sm p-3 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-colors duration-200"
                    rows="3"></textarea>
            @endif
        </div>
    </form>
</x-filament::page>