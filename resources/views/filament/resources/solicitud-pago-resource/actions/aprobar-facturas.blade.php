<div class="space-y-3" x-data="{
    seleccionadas: $wire.entangle('data.facturas_seleccionadas').live,
    idsFiltrados: @js(collect($facturas)->pluck('id')->values()),
    seleccionarTodas() {
        const actual = new Set(this.seleccionadas ?? [])
        this.idsFiltrados.forEach(id => actual.add(id))
        this.seleccionadas = Array.from(actual)
    },
    limpiarFiltro() {
        const filtro = new Set(this.idsFiltrados)
        this.seleccionadas = (this.seleccionadas ?? []).filter(id => ! filtro.has(id))
    }
}">
    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-700">
        <div class="font-semibold">Facturas disponibles</div>
        <button type="button" @click="seleccionarTodas()" class="inline-flex items-center rounded border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            Seleccionar todas
        </button>
        <button type="button" @click="limpiarFiltro()" class="inline-flex items-center rounded border border-gray-200 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50">
            Quitar selección filtrada
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left">
                    <th class="px-3 py-2 w-12"></th>
                    <th class="px-3 py-2">Factura</th>
                    <th class="px-3 py-2">Proveedor</th>
                    <th class="px-3 py-2">Empresa / Sucursal</th>
                    <th class="px-3 py-2">Emisión</th>
                    <th class="px-3 py-2">Vencimiento</th>
                    <th class="px-3 py-2 text-right">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($facturas as $factura)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <input
                                type="checkbox"
                                class="rounded border-gray-300"
                                value="{{ $factura['id'] }}"
                                x-model="seleccionadas"
                                wire:model.live="data.facturas_seleccionadas"
                                wire:key="factura-{{ $factura['id'] }}"
                            >
                        </td>
                        <td class="px-3 py-2 font-medium text-gray-800">{{ $factura['numero'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $factura['proveedor'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $factura['empresa'] }} / {{ $factura['sucursal'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $factura['fecha_emision'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $factura['fecha_vencimiento'] }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-gray-900">{{ number_format($factura['saldo'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">No hay facturas disponibles para esta solicitud.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
