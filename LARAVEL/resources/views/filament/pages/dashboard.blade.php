<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Panel estadístico</x-slot>
            <x-slot name="description">
                Métricas principales y gráficos dinámicos del sistema.
            </x-slot>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">Usuarios registrados</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ number_format($this->dashboardTotals['usuarios']) }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">Órdenes de compra</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ number_format($this->dashboardTotals['ordenes']) }}
                        </p>
                        <p class="text-xs text-gray-500">Periodo: {{ $this->selectedFilterLabel }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">Total monetario OC</p>
                        <p class="mt-2 text-2xl font-semibold text-amber-600">
                            ${{ number_format($this->dashboardTotals['total_ordenes'], 2, '.', ',') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">Resúmenes</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ number_format($this->dashboardTotals['resumenes']) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Anulados: {{ number_format($this->dashboardTotals['resumenes_anulados']) }}
                        </p>
                    </div>
                </div>

                <div class="w-full max-w-md rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-700">Filtros de periodo</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="text-xs font-semibold text-gray-600">
                            Mes
                            <select wire:model.live="selectedMonth"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                <option value="">Todos</option>
                                @foreach ($this->monthOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs font-semibold text-gray-600">
                            Año
                            <select wire:model.live="selectedYear"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                <option value="">Todos</option>
                                @foreach ($this->yearOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Periodo activo: {{ $this->selectedFilterLabel }}</p>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-2">
           {{--  <x-filament::section>
                <x-slot name="heading">Usuarios por rol</x-slot>
                <div class="relative h-72" wire:key="usuarios-roles-{{ $selectedMonth }}-{{ $selectedYear }}">
                    <div x-data="chartRenderer(@js($this->userRoleChartData), 'bar')" x-init="init()" class="h-full">
                        <canvas x-ref="canvas" class="h-full w-full"></canvas>
                    </div>
                </div>
            </x-filament::section> --}}

            <x-filament::section>
                <x-slot name="heading">Órdenes de compra por estado y presupuesto</x-slot>
                <x-slot name="description">Filtrado por el periodo seleccionado.</x-slot>
                <div class="relative h-72" wire:key="ordenes-estado-{{ $selectedMonth }}-{{ $selectedYear }}">
                    <div x-data="chartRenderer(@js($this->ordenCompraStatusChartData), 'doughnut')" x-init="init()"
                        class="h-full">
                        <canvas x-ref="canvas" class="h-full w-full"></canvas>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">Total monetario de OC por empresa</x-slot>
                <div class="relative h-72" wire:key="ordenes-empresa-{{ $selectedMonth }}-{{ $selectedYear }}">
                    <div x-data="chartRenderer(@js($this->ordenCompraEmpresaChartData), 'bar')" x-init="init()"
                        class="h-full">
                        <canvas x-ref="canvas" class="h-full w-full"></canvas>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Resúmenes por estado y presupuesto</x-slot>
                <div class="relative h-72" wire:key="resumenes-estado-{{ $selectedMonth }}-{{ $selectedYear }}">
                    <div x-data="chartRenderer(@js($this->resumenStatusChartData), 'doughnut')" x-init="init()"
                        class="h-full">
                        <canvas x-ref="canvas" class="h-full w-full"></canvas>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Órdenes de compra por empresa, sucursal y fecha</x-slot>
            <x-slot name="description">Agrupadas por empresa, sucursal y fecha según el periodo seleccionado.</x-slot>
            <div class="relative h-72"
                wire:key="ordenes-empresa-sucursal-fecha-{{ $selectedMonth }}-{{ $selectedYear }}">
                <div x-data="chartRenderer(@js($this->ordenCompraEmpresaSucursalFechaChartData), 'bar')"
                    x-init="init()" class="h-full">
                    <canvas x-ref="canvas" class="h-full w-full"></canvas>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Resúmenes por empresa</x-slot>
            <div class="relative h-72" wire:key="resumenes-empresa-{{ $selectedMonth }}-{{ $selectedYear }}">
                <div x-data="chartRenderer(@js($this->resumenEmpresaChartData), 'bar')" x-init="init()" class="h-full">
                    <canvas x-ref="canvas" class="h-full w-full"></canvas>
                </div>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('chartRenderer', (payload, type) => ({
                    chart: null,
                    init() {
                        const ctx = this.$refs.canvas.getContext('2d');

                        this.chart = new Chart(ctx, {
                            type,
                            data: {
                                labels: payload.labels ?? [],
                                datasets: [{
                                    label: payload.label ?? '',
                                    data: payload.values ?? [],
                                    backgroundColor: payload.colors ?? [],
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                    },
                                },
                            },
                        });
                    },
                }));
            });
        </script>
    @endpush
</x-filament-panels::page>
