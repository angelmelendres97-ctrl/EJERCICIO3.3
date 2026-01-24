<x-filament::page>
    <form wire:submit.prevent="save">
        <div class="space-y-6">
            <!-- Global Observations -->
            @if($globalObservation)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Observación General</h3>
                            <div class="text-sm text-blue-700">
                                <p>{{ $globalObservation }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs text-gray-700 uppercase">
                            <tr>
                                <th
                                    class="px-3 py-3 sticky left-0 bg-gray-50 z-10 w-[220px] border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                    Producto
                                </th>
                                @foreach($providers as $provider)
                                    <th
                                        class="px-2 py-2 text-center min-w-[220px] border-r border-gray-100 last:border-0 align-top">
                                        <div class="flex flex-col h-full justify-between">
                                            <div>
                                                <div class="font-bold text-sm text-gray-900 leading-tight mb-1">
                                                    {{ $provider->nombre }}
                                                </div>
                                                <div
                                                    class="text-[10px] text-gray-500 font-normal normal-case leading-tight">
                                                    {{ $provider->correo }}
                                                </div>
                                                <div
                                                    class="text-[10px] text-gray-500 font-normal normal-case leading-tight">
                                                    {{ $provider->telefono }}
                                                </div>
                                            </div>
                                            @if(!empty($providerObservations[$provider->id]))
                                                <div
                                                    class="mt-1.5 p-1 bg-yellow-50 text-yellow-800 text-[10px] border border-yellow-100 rounded text-center normal-case leading-tight">
                                                    {{ $providerObservations[$provider->id] }}
                                                </div>
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50 group">
                                    <td
                                        class="px-3 py-2 sticky left-0 bg-white z-10 w-[220px] border-r border-gray-200 align-top shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] group-hover:bg-gray-50">
                                        <div class="font-medium text-gray-900 text-xs leading-tight mb-0.5">
                                            {{ $product->producto }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 mb-2">{{ $product->codigo_producto }}</div>

                                        <div class="space-y-1 text-[10px]">
                                            <div
                                                class="flex justify-between items-center text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded">
                                                <span>Bodega:</span>
                                                <span
                                                    class="font-medium text-gray-800">{{ $bodegas[$product->id_bodega] ?? 'N/A' }}</span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">
                                                <span>Solicitado:</span>
                                                <span class="font-bold">{{ round($product->cantidad, 6) }}</span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center text-green-700 bg-green-50 px-1.5 py-0.5 rounded">
                                                <span>Aprobado:</span>
                                                <span
                                                    class="font-bold">{{ round($approvedQuantities[$product->id] ?? 0, 6) }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    @foreach($providers as $provider)
                                        @php
                                            $pivot = $matrix[$product->id][$provider->id] ?? null;
                                            $isSelected = ($selectedProviders[$product->id] ?? null) == $provider->id;
                                        @endphp
                                        <td
                                            class="px-2 py-2 align-top border-r border-gray-100 last:border-0 {{ ($selectedProviders[$product->id][$provider->id] ?? false) ? 'bg-blue-50/30' : '' }}">
                                            @if($pivot)
                                                <div class="flex gap-2">
                                                    <div class="pt-0.5">
                                                        @if($isReadOnly)
                                                            @if($selectedProviders[$product->id][$provider->id] ?? false)
                                                                <x-heroicon-s-check-circle class="w-4 h-4 text-green-600" />
                                                            @else
                                                                <span class="block w-3.5 h-3.5 border border-gray-300 rounded bg-gray-50"></span>
                                                            @endif
                                                        @else
                                                            <input type="checkbox"
                                                                wire:model.live="selectedProviders.{{ $product->id }}.{{ $provider->id }}"
                                                                class="w-3.5 h-3.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-1 cursor-pointer">
                                                        @endif
                                                    </div>

                                                    <div class="flex-1 w-full min-w-0">
                                                        @if(!($selectedProviders[$product->id][$provider->id] ?? false))
                                                        <div class="text-xs space-y-0.5">
                                                            <div class="flex justify-between items-baseline">
                                                                <span class="text-gray-500 text-[10px]">Cant:</span>
                                                                <span
                                                                    class="font-medium text-gray-900">{{ round($pivot['cantidad_oferta'], 6) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-baseline">
                                                                <span class="text-gray-500 text-[10px]">P.Unit:</span>
                                                                <span
                                                                    class="font-medium text-gray-900">${{ number_format($pivot['valor_unitario_oferta'], 2, '.', '') }}</span>
                                                            </div>

                                                            <div
                                                                class="flex justify-between items-baseline font-bold text-gray-900 pt-1 border-t border-gray-200 mt-1">
                                                                <span>Total:</span>
                                                                <span>${{ number_format($pivot['total_oferta'], 2, '.', '') }}</span>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <!-- Offer Observation -->
                                                        @if(!empty($pivot['observacion_oferta']))
                                                            <div
                                                                class="mt-1.5 text-[10px] text-gray-500 italic leading-tight bg-gray-50 px-1.5 py-1 rounded">
                                                                {{ $pivot['observacion_oferta'] }}
                                                            </div>
                                                        @endif

                                                        <!-- Editable Fields -->
                                                        @if($selectedProviders[$product->id][$provider->id] ?? false)
                                                            <div class="mt-2 p-2 bg-white rounded border border-blue-200 shadow-sm">
                                                                <div
                                                                    class="text-[10px] font-bold text-blue-800 mb-1.5 uppercase border-b border-blue-100 pb-0.5">
                                                                    Aprobación</div>
                                                                <div class="grid grid-cols-2 gap-2 mb-2">
                                                                    @if($isReadOnly)
                                                                        <div class="col-span-2 space-y-1">
                                                                            <div class="flex justify-between text-xs border-b border-gray-100 pb-1">
                                                                                <span class="text-gray-500 font-medium">Cantidad:</span>
                                                                                <span class="text-gray-900 font-bold">{{ $approvalValues[$product->id][$provider->id]['cantidad'] ?? 0 }}</span>
                                                                            </div>
                                                                            <div class="flex justify-between text-xs border-b border-gray-100 pb-1">
                                                                                <span class="text-gray-500 font-medium">Precio:</span>
                                                                                <span class="text-gray-900 font-bold">${{ number_format($approvalValues[$product->id][$provider->id]['precio'] ?? 0, 4) }}</span>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div>
                                                                            <label class="block text-[9px] text-gray-500 font-semibold mb-0.5">CANT.</label>
                                                                            <input type="number" step="0.01"
                                                                                wire:model.live.debounce.500ms="approvalValues.{{ $product->id }}.{{ $provider->id }}.cantidad"
                                                                                class="block w-full text-xs py-0.5 px-1 border-gray-300 rounded focus:border-blue-500 focus:ring-blue-500 h-6">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-[9px] text-gray-500 font-semibold mb-0.5">PRECIO</label>
                                                                            <input type="number" step="0.0001"
                                                                                wire:model.live.debounce.500ms="approvalValues.{{ $product->id }}.{{ $provider->id }}.precio"
                                                                                class="block w-full text-xs py-0.5 px-1 border-gray-300 rounded focus:border-blue-500 focus:ring-blue-500 h-6">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    @if($isReadOnly)
                                                                        @if(!empty($approvalValues[$product->id][$provider->id]['observacion']))
                                                                            <div class="mt-2 text-[10px] text-gray-600 italic bg-gray-50 p-1.5 rounded border border-gray-100">
                                                                                <span class="font-semibold not-italic text-gray-500">Obs:</span> {{ $approvalValues[$product->id][$provider->id]['observacion'] }}
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        <textarea rows="1"
                                                                            wire:model.blur="approvalValues.{{ $product->id }}.{{ $provider->id }}.observacion"
                                                                            placeholder="Obs."
                                                                            class="block w-full text-[10px] py-1 px-1.5 border-gray-300 rounded focus:border-blue-500 focus:ring-blue-500 resize-none leading-tight min-h-[28px]"></textarea>
                                                                    @endif
                                                                </div>
                                                                
                                                                @php
                                                                    $apQty = floatval($approvalValues[$product->id][$provider->id]['cantidad'] ?? 0);
                                                                    $apPrice = floatval($approvalValues[$product->id][$provider->id]['precio'] ?? 0);
                                                                    $apSubtotal = round($apQty, 6) * $apPrice;
                                                                    $apIvaPercent = floatval($pivot['iva_porcentaje'] ?? 0);
                                                                    $apIva = $apSubtotal * ($apIvaPercent / 100);
                                                                    $apTotal = $apSubtotal + $apIva;
                                                                @endphp
                                                                <div class="mt-2 pt-1 border-t border-blue-100 space-y-0.5">
                                                                    <div class="flex justify-between items-center text-gray-500 text-[10px]">
                                                                        <span>Subtotal:</span>
                                                                        <span>${{ number_format($apSubtotal, 2, '.', '') }}</span>
                                                                    </div>
                                                                    @if($apIva > 0)
                                                                    <div class="flex justify-between items-center text-gray-500 text-[10px]">
                                                                        <span>IVA ({{ $apIvaPercent }}%):</span>
                                                                        <span>${{ number_format($apIva, 2, '.', '') }}</span>
                                                                    </div>
                                                                    @endif
                                                                    <div class="flex justify-between items-center font-bold text-blue-900 text-[10px]">
                                                                        <span>Total:</span>
                                                                        <span>${{ number_format($apTotal, 2, '.', '') }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <span
                                                        class="text-[10px] text-gray-400 italic bg-gray-50 px-2 py-0.5 rounded-full">
                                                        -
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <!-- Table Footer for Totals -->
                        @if(count($providers) > 0)
                            <tfoot class="bg-gray-100 font-semibold text-gray-900 border-t-2 border-gray-200 text-xs">
                                <tr>
                                    <td
                                        class="px-3 py-3 text-right sticky left-0 bg-gray-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                        TOTALES OFERTA
                                    </td>
                                    @foreach($providers as $provider)
                                        @php
                                            $totals = $providerTotals[$provider->id] ?? ['subtotal' => 0, 'iva' => 0, 'total' => 0];
                                        @endphp
                                        <td class="px-2 py-3 border-r border-gray-200 last:border-0 align-top">
                                            <div class="space-y-1">
                                                <div class="flex justify-between items-center text-[10px] text-gray-600">
                                                    <span>Subtotal:</span>
                                                    <span>${{ number_format($totals['subtotal'], 2) }}</span>
                                                </div>
                                                <div class="flex justify-between items-center text-[10px] text-gray-600">
                                                    <span>IVA:</span>
                                                    <span>${{ number_format($totals['iva'], 2) }}</span>
                                                </div>
                                                <div
                                                    class="flex justify-between items-center text-sm font-bold text-gray-900 border-t border-gray-300 pt-1 mt-1">
                                                    <span>Total:</span>
                                                    <span>${{ number_format($totals['total'], 2) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>


        </div>
    </form>
</x-filament::page>