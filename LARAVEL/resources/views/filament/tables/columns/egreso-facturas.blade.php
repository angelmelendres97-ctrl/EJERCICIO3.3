@php
    $facturas = $getState() ?? [];
    $facturasCount = count($facturas);
@endphp

@if (empty($facturas))
    <span class="text-xs text-slate-500">Sin facturas registradas.</span>
@else
    <div x-data="{ open: false }" class="flex justify-center">
        <x-filament::button size="sm" color="info" x-on:click="open = true">
            Ver facturas ({{ $facturasCount }})
        </x-filament::button>

        <div
            x-cloak
            x-show="open"
            x-on:keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center shadow-2xl border p-4"
        >
            <div class="absolute inset-0 bg-slate-900/50" x-on:click="open = false"></div>
            <div class="relative max-h-[80vh] w-[92vw] max-w-5xl overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">Facturas del proveedor</div>
                        <div class="text-xs text-slate-500">{{ $facturasCount }} factura(s)</div>
                    </div>
                    <button type="button" class="text-slate-400 transition hover:text-slate-600" x-on:click="open = false">
                        <x-heroicon-o-x-mark style="color: red; padding: 10%;" class="h-5 w-5" />
                    </button>
                </div>
                <div class="max-h-[65vh] overflow-auto p-4">
                    <div class="overflow-auto rounded-lg border border-slate-200">
                        <table class="w-full text-xs text-slate-600">
                            <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Tipo</th>
                                    <th class="px-3 py-2 text-left">Factura</th>
                                    <th class="px-3 py-2 text-left">Vence</th>
                                    <th class="px-3 py-2 text-left">Detalle</th>
                                    <th class="px-3 py-2 text-right">Cotización</th>
                                    <th class="px-3 py-2 text-right">Débito ML</th>
                                    <th class="px-3 py-2 text-right">Crédito ML</th>
                                    <th class="px-3 py-2 text-right">Débito ME</th>
                                    <th class="px-3 py-2 text-right">Crédito ME</th>
                                    <th class="px-3 py-2 text-right">Abono</th>
                                    <th class="px-3 py-2 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($facturas as $factura)
                                    @php
                                        $vence = $factura['fecha_vencimiento'] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-700">{{ $factura['tipo'] ?? 'N/D' }}</td>
                                        <td class="px-3 py-2">{{ $factura['numero'] ?? 'N/D' }}</td>
                                        <td class="px-3 py-2">{{ $vence ? \Illuminate\Support\Carbon::parse($vence)->format('Y-m-d') : 'N/D' }}</td>
                                        <td class="px-3 py-2 text-slate-500">{{ $factura['detalle'] ?? 'N/D' }}</td>
                                        <td class="px-3 py-2 text-right">
                                            {{ isset($factura['cotizacion']) ? number_format((float) $factura['cotizacion'], 4, '.', ',') : 'N/D' }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ number_format((float) ($factura['debito_local'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ number_format((float) ($factura['credito_local'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ number_format((float) ($factura['debito_extranjera'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ number_format((float) ($factura['credito_extranjera'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-emerald-700">
                                            {{ number_format((float) ($factura['abono_total'] ?? $factura['abono'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-3 py-2 text-right text-slate-500">
                                            {{ number_format((float) ($factura['saldo_pendiente'] ?? 0), 2, '.', ',') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-4 py-3">
                    <x-filament::button size="sm" color="gray" x-on:click="open = false">
                        Cerrar
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
@endif
