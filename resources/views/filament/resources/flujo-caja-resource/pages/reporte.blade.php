<x-filament-panels::page>
    {{ $this->form }}

    <div x-data="{ bankModalOpen: @entangle('showBankModal').live }">

        {{-- Report Table --}}
        <div
            class="overflow-x-auto bg-white rounded-xl shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10 mt-6">
            @if(empty($reportHeader))
                <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-presentation-chart-line class="w-16 h-16 mx-auto mb-4 text-gray-400 opacity-50" />
                    <h3 class="text-lg font-medium">Generar Reporte</h3>
                    <p class="mt-1 text-sm">Seleccione los filtros y haga clic en "Consultar" para ver el flujo de caja.</p>
                </div>
            @else
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead
                        class="text-xs uppercase bg-gray-50/50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-semibold tracking-wider">Concepto</th>
                            @foreach($reportHeader as $date)
                                <th @class([
                                    'px-6 py-4 text-center font-semibold',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ])
                                    style="{{ $loop->first ? 'background-color: #fef3c7;' : '' }}">
                                    {{ $date }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- 1. Banco --}}
                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors cursor-pointer group"
                            wire:click="$set('showBankModal', true)">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-2 border-r-2 border-gray-200 dark:border-gray-600"
                                style="background-color: #f3f4f6;">
                                <span
                                    class="bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 p-1.5 rounded-lg group-hover:scale-110 transition-transform">
                                    <x-heroicon-o-building-library class="w-4 h-4" />
                                </span>
                                BANCO
                            </td>
                            @foreach($reportData['banco']['values'] ?? [] as $val)
                                <td @class([
                                    'px-6 py-4 text-right font-bold text-gray-700 dark:text-gray-300',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ])
                                    style="{{ $loop->first ? 'background-color: #fef3c7;' : '' }}">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- 2. Cuentas Cobrar --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2 opacity-80 border-r-2 border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-indigo-50"
                                style="background-color: #f3f4f6;" wire:click="openCxcTotal()">
                                <span
                                    class="bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 p-1.5 rounded-lg group-hover:scale-110 transition-transform">
                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                </span>
                                CUENTAS COBRAR
                            </td>
                            @foreach($reportData['cxc']['values'] ?? [] as $val)
                                <td @class([
                                    'px-6 py-4 text-right text-gray-600 dark:text-gray-400 cursor-pointer hover:text-indigo-600 hover:bg-indigo-50',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ]) wire:click="openCxcDetails({{ $loop->index }})"
                                    wire:loading.class="opacity-50"
                                    style="{{ $loop->first ? 'background-color: #fef3c7;' : '' }}">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- 3. Total Ingreso --}}
                        <tr class="bg-green-50 dark:bg-green-900/20 font-semibold">
                            <td class="px-6 py-4 text-green-800 dark:text-green-300 flex items-center gap-2 border-r-2 border-green-200 dark:border-green-800"
                                style="background-color: #d5f9e0ff;">
                                <span
                                    class="bg-green-200 text-green-700 dark:bg-green-800 dark:text-green-200 p-1.5 rounded-lg">
                                    <x-heroicon-o-arrow-trending-up class="w-4 h-4" />
                                </span>
                                TOTAL INGRESO
                            </td>
                            @foreach($reportData['total_ingreso']['values'] ?? [] as $val)
                                <td @class([
                                    'px-6 py-4 text-right text-green-700 dark:text-green-400',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ])
                                    style="{{ $loop->first ? 'background-color: #d5f9e0ff;' : 'background-color: #d5f9e0ff;' }}">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- 4. Cuentas Pagar --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2 opacity-80 border-r-2 border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-red-50"
                                style="background-color: #f3f4f6;" wire:click="openCxpTotal()">
                                <span
                                    class="bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-1.5 rounded-lg group-hover:scale-110 transition-transform">
                                    <x-heroicon-o-banknotes class="w-4 h-4" />
                                </span>
                                CUENTAS PAGAR
                            </td>
                            @foreach($reportData['cxp']['values'] ?? [] as $val)
                                <td @class([
                                    'px-6 py-4 text-right text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600 hover:bg-red-50',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ]) style="{{ $loop->first ? 'background-color: #fef3c7;' : '' }}"
                                    wire:click="openCxpDetails({{ $loop->index }})" wire:loading.class="opacity-50">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- 5. Nomina --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2 opacity-80 border-r-2 border-gray-200 dark:border-gray-700"
                                style="background-color: #f3f4f6;">
                                <span
                                    class="bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 p-1.5 rounded-lg">
                                    <x-heroicon-o-user-group class="w-4 h-4" />
                                </span>
                                NOMINA
                            </td>
                            @foreach($reportData['nomina']['values'] ?? [] as $val)
                                <td @class([
                                    'px-6 py-4 text-right text-gray-600 dark:text-gray-400',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ])
                                    style="{{ $loop->first ? 'background-color: #fef3c7;' : '' }}">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- 6. Total Pagar --}}
                        <tr class="bg-red-50 dark:bg-red-900/20 font-semibold">
                            <td class="px-6 py-4 text-red-800 dark:text-red-300 flex items-center gap-2 border-r-2 border-red-200 dark:border-red-800"
                                style="background-color: #ffe1e1ff;">
                                <span class="bg-red-200 text-red-700 dark:bg-red-800 dark:text-red-200 p-1.5 rounded-lg">
                                    <x-heroicon-o-arrow-trending-down class="w-4 h-4" />
                                </span>
                                TOTAL A PAGAR
                            </td>
                            @foreach($reportData['total_pagar']['values'] ?? [] as $val)
                                <td @class([
                                    'px-6 py-4 text-right text-red-700 dark:text-red-400',
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first
                                ])
                                    style="{{ $loop->first ? 'background-color: #ffe1e1ff;' : 'background-color: #ffe1e1ff;' }}">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- 7. Flujo de Caja --}}
                        <tr class="bg-white dark:bg-gray-900 border-t-2 border-gray-200 dark:border-gray-600">
                            <td
                                class="px-6 py-4 font-black text-gray-900 dark:text-white flex items-center gap-2 text-base border-r-2 border-gray-200 dark:border-gray-600">
                                <span class="bg-gray-800 text-white dark:bg-white dark:text-black p-1.5 rounded-lg">
                                    <x-heroicon-o-currency-dollar class="w-4 h-4" />
                                </span>
                                FLUJO DE CAJA
                            </td>
                            @foreach($reportData['flujo']['values'] ?? [] as $loopIndex => $val)
                                {{-- Determine color based on In vs Out --}}
                                @php
                                    // Parse clean number for comparison (remove commas)
                                    $in = (float) str_replace(',', '', $reportData['total_ingreso']['values'][$loopIndex] ?? 0);
                                    $out = (float) str_replace(',', '', $reportData['total_pagar']['values'][$loopIndex] ?? 0);
                                    $isPositive = $in >= $out;
                                @endphp
                                <td @class([
                                    'px-6 py-4 text-right font-black text-base',
                                    'text-green-600 dark:text-green-400' => $isPositive,
                                    'text-red-600 dark:text-red-400' => !$isPositive,
                                    'bg-green-50/50 dark:bg-green-900/10' => $isPositive && !$loop->first,
                                    'bg-red-50/50 dark:bg-red-900/10' => !$isPositive && !$loop->first,
                                    'border-r-2 border-yellow-400 dark:border-yellow-600' => $loop->first,
                                ]) style="{{ $loop->first ? 'background-color: #fef3c7;' : '' }}">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Bank Details Modal --}}
        {{-- Bank Details Modal (Premium Design) --}}
        <template x-teleport="body">
            <div x-show="bankModalOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="bankModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"
                    @click="bankModalOpen = false"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="bankModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl ring-1 ring-gray-900/5"
                        @click.stop>
                        <!-- Header -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span
                                    class="bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 p-2 rounded-lg">
                                    <x-heroicon-o-building-library class="w-5 h-5" />
                                </span>
                                SALDOS EN BANCOS
                            </h3>
                            <button @click="bankModalOpen = false"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-6">
                            <div
                                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Sucursal</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Banco</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Cuenta</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @forelse($bankData as $bank)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $bank['sucursal'] ?? 'MATRIZ' }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $bank['banco'] }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400">
                                                    {{ $bank['cuenta'] }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                                    $ {{ number_format($bank['saldo'], 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4"
                                                    class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                                                    No hay datos bancarios disponibles.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <!-- Total Row -->
                                    <tfoot class="bg-blue-50 dark:bg-blue-900/20">
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-4 text-right text-sm font-bold text-blue-900 dark:text-blue-100">
                                                TOTAL GENERAL
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right text-base font-black text-blue-700 dark:text-blue-300">
                                                $ {{ number_format(array_sum(array_column($bankData, 'saldo')), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex flex-row-reverse sm:px-6 border-t border-gray-100 dark:border-gray-700">
                            <button type="button"
                                class="inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors"
                                @click="bankModalOpen = false">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- CXC Modal --}}
        <template x-teleport="body">
            <div x-show="showCxcModal" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true"
                x-data="{ showCxcModal: @entangle('showCxcModal').live }">
                <div x-show="showCxcModal" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
                    @click="showCxcModal = false" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"></div>

                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showCxcModal"
                        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl ring-1 ring-gray-900/5"
                        @click.stop x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span
                                    class="bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 p-2 rounded-lg">
                                    <x-heroicon-o-document-text class="w-5 h-5" />
                                </span>
                                DETALLE CUENTAS POR COBRAR
                            </h3>
                            <button @click="showCxcModal = false"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="px-6 py-6">
                            <div
                                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm max-h-[60vh] overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                                    <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Código</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Cliente</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @forelse($cxcData as $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $item['codigo'] }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $item['nombre'] }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                                    $ {{ number_format($item['saldo'], 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3"
                                                    class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No hay datos disponibles.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-indigo-50 dark:bg-indigo-900/20 sticky bottom-0 z-10">
                                        <tr>
                                            <td colspan="2"
                                                class="px-6 py-4 text-right text-sm font-bold text-indigo-900 dark:text-indigo-100">
                                                TOTAL GENERAL
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right text-base font-black text-indigo-700 dark:text-indigo-300">
                                                $ {{ number_format(array_sum(array_column($cxcData, 'saldo')), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- CXP Modal --}}
        <template x-teleport="body">
            <div x-show="showCxpModal" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true"
                x-data="{ showCxpModal: @entangle('showCxpModal').live }">
                <div x-show="showCxpModal" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
                    @click="showCxpModal = false" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"></div>

                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showCxpModal"
                        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl ring-1 ring-gray-900/5"
                        @click.stop x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span
                                    class="bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 p-2 rounded-lg">
                                    <x-heroicon-o-banknotes class="w-5 h-5" />
                                </span>
                                {{ $cxpModalTitle }}
                            </h3>
                            <button @click="showCxpModal = false"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="px-6 py-6">
                            <div
                                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm max-h-[60vh] overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                                    <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Código</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Proveedor</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @forelse($cxpData as $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $item['codigo'] }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $item['nombre'] }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                                    $ {{ number_format($item['saldo'], 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3"
                                                    class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No hay datos disponibles.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-red-50 dark:bg-red-900/20 sticky bottom-0 z-10">
                                        <tr>
                                            <td colspan="2"
                                                class="px-6 py-4 text-right text-sm font-bold text-red-900 dark:text-red-100">
                                                TOTAL GENERAL
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right text-base font-black text-red-700 dark:text-red-300">
                                                $ {{ number_format(array_sum(array_column($cxpData, 'saldo')), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-filament-panels::page>