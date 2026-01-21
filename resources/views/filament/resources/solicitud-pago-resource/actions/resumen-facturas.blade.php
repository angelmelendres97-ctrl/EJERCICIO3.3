<div class="rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-gray-800">Resumen de facturas seleccionadas</p>
            <p class="text-xs text-gray-600">Mantén visible cuántas facturas incluirás y cómo se compara con el monto a pagar.</p>
        </div>

        <div class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
            {{ $accionSeleccionada === 'aprobar_liberar' ? 'Liberará facturas no seleccionadas' : 'Aprobación total' }}
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-md bg-white p-3 shadow">
            <p class="text-xs uppercase tracking-wide text-gray-500">Facturas seleccionadas</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $facturasSeleccionadas }}</p>
            <p class="text-xs text-gray-500">de {{ $totalFacturas }} disponibles</p>
        </div>

        <div class="rounded-md bg-white p-3 shadow">
            <p class="text-xs uppercase tracking-wide text-gray-500">Total seleccionado</p>
            <p class="text-2xl font-semibold text-gray-900">
                $ {{ number_format((float) $montoSeleccionado, 2, '.', ',') }}
            </p>
            <p class="text-xs text-gray-500">Monto acumulado de facturas</p>
        </div>

        <div class="rounded-md bg-white p-3 shadow">
            <p class="text-xs uppercase tracking-wide text-gray-500">Monto a pagar</p>
            <p class="text-2xl font-semibold text-gray-900">
                $ {{ number_format((float) $montoIngresado, 2, '.', ',') }}
            </p>
            <p class="text-xs text-gray-500">Ingresado en el formulario</p>
        </div>

        <div class="rounded-md bg-white p-3 shadow">
            <p class="text-xs uppercase tracking-wide text-gray-500">Diferencia</p>

            @php
                $diff = round((float) $diferencia, 2);
                $diffAbs = abs($diff);
                $diffOk = $diffAbs < 0.01; // tolerancia por decimales
            @endphp

            <p class="text-2xl font-semibold {{ $diffOk ? 'text-green-700' : 'text-red-700' }}">
                $ {{ number_format($diffAbs, 2, '.', ',') }}
            </p>

            <p class="text-xs text-gray-500">
                @if ($diffOk)
                    Monto alineado
                @elseif ($diff > 0)
                    Sobra monto (excedente)
                @else
                    Falta monto
                @endif
            </p>
        </div>
    </div>
</div>
