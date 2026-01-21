<div class="rounded-md border border-amber-200 bg-amber-50 p-4">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 text-amber-500">
            <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
        </div>
        <div class="space-y-1">
            <p class="text-sm font-semibold text-amber-800">El monto ingresado es mayor al total seleccionado.</p>
            <p class="text-xs text-amber-700">Confirma para ajustar el monto automáticamente antes de aprobar.</p>
            <button
                type="button"
                x-data="{ confirmado: $wire.entangle('data.confirmar_ajuste_monto').live }"
                @click="if (confirm('¿Deseas ajustar el monto automáticamente al total seleccionado?')) { confirmado = true; }"
                class="inline-flex items-center rounded border border-amber-300 bg-white px-3 py-1 text-xs font-semibold text-amber-800 shadow-sm hover:bg-amber-100"
            >
                {{ ($confirmado ?? false) ? 'Ajuste confirmado' : 'Confirmar ajuste' }}
            </button>
        </div>
    </div>
</div>
