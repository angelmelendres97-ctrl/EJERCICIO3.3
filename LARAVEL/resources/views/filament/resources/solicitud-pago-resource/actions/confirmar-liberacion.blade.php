@if ($requiereConfirmacion ?? false)
    <div class="rounded-md border border-amber-200 bg-amber-50 p-4">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 text-amber-500">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
            </div>
            <div class="space-y-1">
                <p class="text-sm font-semibold text-amber-800">Liberarás las facturas que no estén seleccionadas.</p>
                <p class="text-xs text-amber-700">Estas facturas volverán a estar disponibles para una nueva solicitud de pago.</p>
                <button
                    type="button"
                    x-data="{ confirmado: $wire.entangle('data.confirmar_liberacion').live }"
                    @click="confirmado = confirm('¿Deseas aprobar solo las facturas seleccionadas y liberar el resto?')"
                    class="inline-flex items-center rounded border border-amber-300 bg-white px-3 py-1 text-xs font-semibold text-amber-800 shadow-sm hover:bg-amber-100"
                >
                    {{ $confirmado ? 'Confirmado' : 'Confirmar liberación' }}
                </button>
            </div>
        </div>
    </div>
@endif
