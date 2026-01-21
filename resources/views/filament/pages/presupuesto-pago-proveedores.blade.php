<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Selección de proveedores
            </x-slot>

            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Seleccione proveedores para consolidar el presupuesto.
                </div>
                <div class="text-lg font-semibold text-amber-600">
                    Total seleccionado: ${{ number_format($this->totalSeleccionado, 2, '.', ',') }}
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <div class="mt-3 flex items-center gap-3">
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <!-- icono simple -->

                        </span>

                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar proveedor, factura o RUC…"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-amber-500 focus:ring-amber-500" />

                    </div>

                    <button type="button" wire:click="$set('search','')" class="...">
                        Limpiar
                    </button>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white w-full">
                    <div class="overflow-x-auto">


                        <table class="w-full table-fixed divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-[26%] px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('proveedor_nombre')"
                                            class="flex items-center gap-1">
                                            Proveedor
                                            @if ($sortField === 'proveedor_nombre')
                                                <span
                                                    class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="w-[18%] px-4 py-2 text-left font-semibold text-gray-700">Descripción</th>
                                    <th class="w-[8%] px-4 py-2 text-center font-semibold text-gray-700">Área</th>
                                    <th class="w-[10%] px-4 py-2 text-right font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('total')"
                                            class="flex items-center gap-1 float-right">
                                            Total
                                            @if ($sortField === 'total')
                                                <span
                                                    class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="w-[38%] px-4 py-2 text-left font-semibold text-gray-700">Facturas</th>
                                    <th class="w-[10%] px-4 py-2 text-center font-semibold text-gray-700">
                                        <div class="flex items-center justify-center gap-2">
                                            <input type="checkbox"
                                                wire:change="toggleSelectAllProviders($event.target.checked)"
                                                @checked(!empty($this->facturasDisponibles) && count($this->selectedProviders) >= count($this->facturasDisponibles))
                                                class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                            <button type="button" wire:click="sortBy('selected')"
                                                class="flex items-center justify-center gap-1">
                                                Seleccionar
                                                @if ($sortField === 'selected')
                                                    <span
                                                        class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white"
                                wire:key="providers-page-{{ $this->getPage() }}">

                                @forelse ($this->providersPaginated as $proveedor)
                                    <tr class="align-top" wire:key="prov-{{ $proveedor['key'] }}">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-800">
                                                {{ $proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'] }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Código: {{ implode(', ', $proveedor['proveedor_codigos'] ?? []) }}

                                                @if (!empty($proveedor['proveedor_ruc']))
                                                    · RUC: {{ $proveedor['proveedor_ruc'] }}
                                                @endif
                                                <span
                                                    class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">{{ $proveedor['facturas_count'] ?? 0 }}
                                                    factura(s)</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                wire:model.live.debounce.500ms="providerDescriptions.{{ $proveedor['key'] }}"
                                                placeholder="Descripción del proveedor"
                                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-2 text-left">
                                                @foreach (['Planta', 'Mina', 'Servicio'] as $area)
                                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                                        <input type="checkbox" value="{{ $area }}"
                                                            wire:model.live="providerAreas.{{ $proveedor['key'] }}"
                                                            class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                                        <span>{{ $area }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                            ${{ number_format((float) ($proveedor['total'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <details wire:ignore.self x-data="{ isOpen: {{ json_encode(in_array($proveedor['key'], $this->openProviders, true)) }} }" x-init="$el.open = isOpen"
                                                @toggle="
        isOpen = $event.target.open;
        $wire.setOpenProvider('{{ $proveedor['key'] }}', isOpen);
    "
                                                class="rounded-md border border-gray-200 bg-slate-50 p-3">
                                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                                                    Ver facturas agrupadas
                                                </summary>
                                                <div class="mt-2 space-y-3">
                                                    @foreach ($proveedor['empresas'] ?? [] as $empresa)
                                                        <div class="rounded-lg border border-slate-200 bg-white">
                                                            <div
                                                                class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                                                                <span>{{ $empresa['conexion_nombre'] ?? 'Conexión' }} ·
                                                                    {{ $empresa['empresa_nombre'] ?? $empresa['empresa_codigo'] }}</span>

                                                            </div>
                                                            <div class="space-y-2 p-3">
                                                                @foreach ($empresa['sucursales'] ?? [] as $sucursal)
                                                                    <div class="rounded-md border border-slate-200">
                                                                        <div
                                                                            class="flex items-center justify-between bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-700">
                                                                            <span>{{ $sucursal['sucursal_nombre'] ?? $sucursal['sucursal_codigo'] }}</span>

                                                                        </div>
                                                                        <div class="overflow-x-auto">
                                                                            <table
                                                                                class="min-w-full divide-y divide-gray-200 text-xs">
                                                                                <thead class="bg-white">
                                                                                    <tr>
                                                                                        <th
                                                                                            class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                            Factura</th>
                                                                                        <th
                                                                                            class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                            Emisión</th>
                                                                                        <th
                                                                                            class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                            Vencimiento</th>
                                                                                        <th
                                                                                            class="px-3 py-1 text-right font-semibold text-gray-700">
                                                                                            Saldo</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody class="divide-y divide-gray-100">
                                                                                    @foreach ($sucursal['facturas'] ?? [] as $factura)
                                                                                        <tr>
                                                                                            <td
                                                                                                class="px-3 py-1 text-gray-700">
                                                                                                {{ $factura['numero'] ?? '' }}
                                                                                            </td>
                                                                                            <td
                                                                                                class="px-3 py-1 text-gray-700">
                                                                                                {{ $factura['fecha_emision'] ?? '' }}
                                                                                            </td>
                                                                                            <td
                                                                                                class="px-3 py-1 text-gray-700">
                                                                                                {{ $factura['fecha_vencimiento'] ?? '' }}
                                                                                            </td>
                                                                                            <td
                                                                                                class="px-3 py-1 text-right font-semibold text-gray-800">
                                                                                                ${{ number_format((float) ($factura['saldo'] ?? 0), 2, '.', ',') }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div wire:key="chk-{{ $proveedor['key'] }}" wire:click.stop>
                                                <input type="checkbox" value="{{ $proveedor['key'] }}"
                                                    wire:model.live="selectedProviders"
                                                    class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-600">
                                            Seleccione filtros para visualizar el presupuesto de pagos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 bg-white px-4 py-3">
                        {{ $this->providersPaginated->links() }}
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
