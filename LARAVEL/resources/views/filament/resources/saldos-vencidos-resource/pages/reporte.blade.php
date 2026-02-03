<x-filament-panels::page>
    {{ $this->form }}

    @if($this->consultado)
        <div
            class="fi-ta-ctn overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-6 relative">
            {{-- Loading Indicator Overlay --}}
            <div wire:loading wire:target="consultar, gotoPage, nextPage, previousPage"
                class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 flex items-center justify-center">
                <x-filament::loading-indicator class="h-10 w-10 text-primary-500" />
            </div>

            <div class="p-4 border-b border-gray-200 dark:border-white/5 bg-gray-50/50">
                <input type="text" wire:model.live.debounce.500ms="search"
                    placeholder="Buscar por Proveedor, RUC, Factura o Detalle..."
                    class="block w-full max-w-sm text-sm border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 shadow-sm" />
            </div>

            <table class="fi-ta-table w-full text-left table-auto divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th
                            class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white">
                            Empresa</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">RUC</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Proveedor</th>
                        @if(($this->data['tipo_reporte'] ?? 'detallado') === 'detallado')
                            <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Factura</th>
                            <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Detalle</th>
                            <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Emisión</th>
                            <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Vencimiento</th>
                        @endif
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white text-right">Total</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white text-right">Abono</th>
                        <th
                            class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white text-right">
                            Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse($this->paginatedResults as $row)
                        @if(($row['type'] ?? 'data') === 'company_header')
                            <tr class="bg-blue-100 dark:bg-blue-900/20 font-bold border-y-2 border-blue-200 dark:border-blue-700">
                                <td colspan="{{ ($this->data['tipo_reporte'] ?? 'detallado') === 'detallado' ? 10 : 6 }}"
                                    class="px-3 py-4 sm:first-of-type:ps-6 text-blue-900 dark:text-blue-100 uppercase tracking-wider text-center">
                                    {{ $row['proveedor'] }}
                                </td>
                            </tr>
                        @elseif(($row['type'] ?? 'data') === 'company_summary')
                            <tr
                                class="bg-teal-100 dark:bg-teal-900/20 font-bold border-t-2 border-teal-200 dark:border-teal-700 text-teal-900 dark:text-teal-100">
                                <td colspan="{{ ($this->data['tipo_reporte'] ?? 'detallado') === 'detallado' ? 7 : 3 }}"
                                    class="px-3 py-4 sm:first-of-type:ps-6 text-right uppercase">
                                    {{ $row['proveedor'] }}
                                </td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['total_factura'], 2) }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['abono'], 2) }}</td>
                                <td class="px-3 py-4 sm:last-of-type:pe-6 text-right">{{ number_format($row['saldo'], 2) }}</td>
                            </tr>
                        @elseif(($row['type'] ?? 'data') === 'summary')
                            <tr class="bg-gray-50 dark:bg-white/5 font-bold">
                                <td colspan="{{ ($this->data['tipo_reporte'] ?? 'detallado') === 'detallado' ? 7 : 3 }}"
                                    class="px-3 py-4 sm:first-of-type:ps-6 text-right">{{ $row['proveedor'] }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['total_factura'], 2) }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['abono'], 2) }}</td>
                                <td class="px-3 py-4 sm:last-of-type:pe-6 text-right">{{ number_format($row['saldo'], 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ $row['empresa_origen'] }}
                                </td>
                                <td class="px-3 py-4">{{ $row['ruc'] }}</td>
                                <td class="px-3 py-4">{{ $row['proveedor'] }}</td>
                                @if(($this->data['tipo_reporte'] ?? 'detallado') === 'detallado')
                                    <td class="px-3 py-4">{{ $row['numero_factura'] }}</td>
                                    <td class="px-3 py-4">{{ $row['detalle'] }}</td>
                                    <td class="px-3 py-4">{{ $row['emision'] }}</td>
                                    <td class="px-3 py-4">{{ $row['vencimiento'] }}</td>
                                @endif
                                <td class="px-3 py-4 text-right">{{ number_format($row['total_factura'], 2) }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['abono'], 2) }}</td>
                                <td class="px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6 text-right font-bold">
                                    {{ number_format($row['saldo'], 2) }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ ($this->data['tipo_reporte'] ?? 'detallado') === 'detallado' ? 10 : 6 }}"
                                class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($this->resultados) > 0)
                    <tfoot class="bg-gray-50 dark:bg-white/5 font-bold border-t-2 border-gray-300 dark:border-white/10">
                        <tr>
                            <td colspan="{{ ($this->data['tipo_reporte'] ?? 'detallado') === 'detallado' ? 7 : 3 }}"
                                class="px-3 py-3.5 sm:first-of-type:ps-6 text-right">Totales (General):</td>
                            <td class="px-3 py-3.5 text-right">
                                {{ number_format(collect($this->resultados)->whereIn('type', ['data', 'data_global'])->sum('total_factura'), 2) }}
                            </td>
                            <td class="px-3 py-3.5 text-right">
                                {{ number_format(collect($this->resultados)->whereIn('type', ['data', 'data_global'])->sum('abono'), 2) }}
                            </td>
                            <td class="px-3 py-3.5 sm:last-of-type:pe-6 text-right">
                                {{ number_format(collect($this->resultados)->whereIn('type', ['data', 'data_global'])->sum('saldo'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>

            <div class="p-4 border-t border-gray-200 dark:border-white/5">
                {{ $this->paginatedResults->links() }}
            </div>
        </div>
    @endif
</x-filament-panels::page>