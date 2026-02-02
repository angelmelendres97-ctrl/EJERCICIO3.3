<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Reporte consolidado de productos
            </x-slot>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-600">
                    Revise el inventario disponible por conexión, empresa, sucursal y bodega.
                </div>
                <div class="text-sm font-semibold text-amber-600">
                    Productos encontrados: {{ $this->productosCount }}
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por código, nombre, descripción o ubicación..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-3 text-sm focus:border-amber-500 focus:ring-amber-500" />
                    </div>
                    <button type="button" wire:click="$set('search','')"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white w-full">
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-[12%] px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('producto_codigo')"
                                            class="flex items-center gap-1">
                                            Código
                                            @if ($sortField === 'producto_codigo')
                                                <span class="text-xs text-amber-600">
                                                    {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                                </span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="w-[20%] px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('producto_nombre')"
                                            class="flex items-center gap-1">
                                            Producto
                                            @if ($sortField === 'producto_nombre')
                                                <span class="text-xs text-amber-600">
                                                    {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                                </span>
                                            @endif
                                        </button>
                                    </th>

                                    <th class="w-[10%] px-4 py-2 text-right font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('stock_total')"
                                            class="flex items-center gap-1 float-right">
                                            Stock total
                                            @if ($sortField === 'stock_total')
                                                <span class="text-xs text-amber-600">
                                                    {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                                </span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="w-[24%] px-4 py-2 text-left font-semibold text-gray-700">Ubicaciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white"
                                wire:key="productos-page-{{ $this->getPage() }}">
                                @forelse ($this->productosPaginated as $producto)
                                    <tr class="align-top" wire:key="prod-{{ $producto['key'] }}">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-800">
                                            {{ $producto['producto_codigo'] ?? 'N/D' }}
                                            @if (!empty($producto['producto_barra']))
                                                <div class="text-xs text-gray-500">Barra: {{ $producto['producto_barra'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-800">
                                                {{ $producto['producto_nombre'] ?? 'Sin nombre' }}
                                            </div>
                                            @if (!empty($producto['unidad']))
                                                <div class="text-xs text-gray-500">Unidad: {{ $producto['unidad'] }}</div>
                                            @endif
                                        </td>


                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                            {{ number_format((float) ($producto['stock_total'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <details class="rounded-md border border-gray-200 bg-gray-50 p-3">
                                                <summary class="cursor-pointer text-sm font-semibold text-gray-700">
                                                    {{ count($producto['ubicaciones'] ?? []) }} ubicación(es) registradas
                                                </summary>
                                                <div class="mt-3 overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                                        <thead class="bg-white">
                                                            <tr>
                                                                <th class="px-3 py-1 text-left font-semibold text-gray-700">Conexión</th>
                                                                <th class="px-3 py-1 text-left font-semibold text-gray-700">Empresa</th>
                                                                <th class="px-3 py-1 text-left font-semibold text-gray-700">Sucursal</th>
                                                                <th class="px-3 py-1 text-left font-semibold text-gray-700">Bodega</th>
                                                                <th class="px-3 py-1 text-right font-semibold text-gray-700">Stock</th>
                                                                <th class="px-3 py-1 text-right font-semibold text-gray-700">Precio</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            @foreach ($producto['ubicaciones'] ?? [] as $ubicacion)
                                                                <tr>
                                                                    <td class="px-3 py-1 text-gray-700">
                                                                        {{ $ubicacion['conexion_nombre'] ?? $ubicacion['conexion_id'] }}
                                                                    </td>
                                                                    <td class="px-3 py-1 text-gray-700">
                                                                        {{ $ubicacion['empresa_nombre'] ?? $ubicacion['empresa_codigo'] }}
                                                                    </td>
                                                                    <td class="px-3 py-1 text-gray-700">
                                                                        {{ $ubicacion['sucursal_nombre'] ?? $ubicacion['sucursal_codigo'] }}
                                                                    </td>
                                                                    <td class="px-3 py-1 text-gray-700">
                                                                        {{ $ubicacion['bodega_nombre'] ?? $ubicacion['bodega_codigo'] }}
                                                                    </td>
                                                                    <td class="px-3 py-1 text-right font-semibold text-gray-800">
                                                                        {{ number_format((float) ($ubicacion['stock'] ?? 0), 2, '.', ',') }}
                                                                    </td>
                                                                    <td class="px-3 py-1 text-right text-gray-700">
                                                                        ${{ number_format((float) ($ubicacion['precio'] ?? 0), 2, '.', ',') }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-600">
                                            Seleccione filtros y cargue el reporte para visualizar los productos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 bg-white px-4 py-3">
                        {{ $this->productosPaginated->links() }}
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
