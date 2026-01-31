<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Reporte consolidado de productos
            </x-slot>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Visualice el stock disponible por conexión, empresa, sucursal y bodega.
                </div>
                <div class="text-sm font-semibold text-gray-700">
                    Total registros: {{ $this->productosPaginated->total() }}
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                            🔍
                        </span>

                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por código, nombre, empresa o bodega..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:ring-amber-500"
                        />
                    </div>
                    <button type="button" wire:click="$set('search','')" class="text-sm text-gray-500 hover:text-gray-700">
                        Limpiar
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('conexion')" class="flex items-center gap-1">
                                            Conexión
                                            @if ($sortField === 'conexion')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('empresa')" class="flex items-center gap-1">
                                            Empresa
                                            @if ($sortField === 'empresa')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('sucursal')" class="flex items-center gap-1">
                                            Sucursal
                                            @if ($sortField === 'sucursal')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('bodega')" class="flex items-center gap-1">
                                            Bodega
                                            @if ($sortField === 'bodega')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('codigo')" class="flex items-center gap-1">
                                            Código
                                            @if ($sortField === 'codigo')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('producto_nombre')" class="flex items-center gap-1">
                                            Producto
                                            @if ($sortField === 'producto_nombre')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-right font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('precio')" class="flex items-center justify-end gap-1 w-full">
                                            Precio
                                            @if ($sortField === 'precio')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-right font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('stock')" class="flex items-center justify-end gap-1 w-full">
                                            Stock
                                            @if ($sortField === 'stock')
                                                <span class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-right font-semibold text-gray-700">Mín.</th>
                                    <th class="px-4 py-2 text-right font-semibold text-gray-700">Máx.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($this->productosPaginated as $producto)
                                    <tr class="align-top">
                                        <td class="px-4 py-3 text-xs text-gray-700">
                                            {{ $producto['conexion_nombre'] }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-700">
                                            {{ $producto['empresa_nombre'] }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-700">
                                            {{ $producto['sucursal_nombre'] }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-700">
                                            {{ $producto['bodega_nombre'] }}
                                        </td>
                                        <td class="px-4 py-3 text-xs font-semibold text-gray-800">
                                            {{ $producto['producto_codigo'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-800">
                                                {{ $producto['producto_nombre'] }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $producto['producto_descripcion'] ?: 'Sin descripción registrada' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-gray-700">
                                            ${{ number_format((float) ($producto['precio'] ?? 0), 4, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-800">
                                            {{ number_format((float) ($producto['stock'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-gray-700">
                                            {{ number_format((float) ($producto['stock_minimo'] ?? 0), 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs text-gray-700">
                                            {{ number_format((float) ($producto['stock_maximo'] ?? 0), 2, '.', ',') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-600">
                                            {{ empty($this->productos) ? 'Seleccione filtros y cargue el reporte para visualizar productos.' : 'No se encontraron productos con los filtros actuales.' }}
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
