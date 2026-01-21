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

            <table class="fi-ta-table w-full text-left table-auto divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th
                            class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white">
                            Empresa</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">RUC</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Proveedor</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Factura</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Detalle</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Emisión</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white">Vencimiento</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white text-right">Total</th>
                        <th class="px-3 py-3.5 font-semibold text-gray-950 dark:text-white text-right">Abono</th>
                        <th
                            class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white text-right">
                            Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse($this->paginatedResults as $row)
                        @if(($row['type'] ?? 'data') === 'summary')
                            <tr class="bg-gray-50 dark:bg-white/5 font-bold">
                                <td colspan="7" class="px-3 py-4 sm:first-of-type:ps-6 text-right">{{ $row['proveedor'] }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['total_factura'], 2) }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['abono'], 2) }}</td>
                                <td class="px-3 py-4 sm:last-of-type:pe-6 text-right">{{ number_format($row['saldo'], 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">{{ $row['empresa_origen'] }}</td>
                                <td class="px-3 py-4">{{ $row['ruc'] }}</td>
                                <td class="px-3 py-4">{{ $row['proveedor'] }}</td>
                                <td class="px-3 py-4">{{ $row['numero_factura'] }}</td>
                                <td class="px-3 py-4">{{ $row['detalle'] }}</td>
                                <td class="px-3 py-4">{{ $row['emision'] }}</td>
                                <td class="px-3 py-4">{{ $row['vencimiento'] }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['total_factura'], 2) }}</td>
                                <td class="px-3 py-4 text-right">{{ number_format($row['abono'], 2) }}</td>
                                <td class="px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6 text-right font-bold">
                                    {{ number_format($row['saldo'], 2) }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($this->resultados) > 0)
                    <tfoot class="bg-gray-50 dark:bg-white/5 font-bold border-t-2 border-gray-300 dark:border-white/10">
                        <tr>
                            <td colspan="7" class="px-3 py-3.5 sm:first-of-type:ps-6 text-right">Totales (General):</td>
                            <td class="px-3 py-3.5 text-right">
                                {{ number_format(collect($this->resultados)->where('type', 'data')->sum('total_factura'), 2) }}
                            </td>
                            <td class="px-3 py-3.5 text-right">
                                {{ number_format(collect($this->resultados)->where('type', 'data')->sum('abono'), 2) }}
                            </td>
                            <td class="px-3 py-3.5 sm:last-of-type:pe-6 text-right">
                                {{ number_format(collect($this->resultados)->where('type', 'data')->sum('saldo'), 2) }}
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