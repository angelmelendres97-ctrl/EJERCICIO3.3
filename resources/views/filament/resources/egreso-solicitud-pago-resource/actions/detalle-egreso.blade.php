<div class="max-h-[80vh] space-y-4 overflow-y-auto p-4">
    <h3 class="text-lg font-semibold">Detalle de egreso</h3>

    <div
        class="grid grid-cols-1 gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:grid-cols-3">
        <div><span class="font-semibold">Solicitud:</span> #{{ $solicitud->id }}</div>
        <div><span class="font-semibold">Fecha:</span> {{ optional($solicitud->fecha)->format('Y-m-d') }}</div>
        <div><span class="font-semibold">Conexión:</span>
            {{ $solicitud->empresa->nombre_empresa ?? $solicitud->id_empresa }}</div>
    </div>

    @php
        $resolverTotal = fn($detalle) => (float) ($detalle->monto_factura ?? $detalle->saldo_al_crear ?? 0);
        $totalFacturado = $detalles->sum($resolverTotal);
        $totalAbonado = $detalles->sum(fn($detalle) => (float) ($detalle->abono_aplicado ?? 0));
        $totalSaldo = max(0, $totalFacturado - $totalAbonado);

        $totalesProveedor = $detalles->groupBy(function ($detalle) {
            $nombre = $detalle->proveedor_nombre;
            $ruc = $detalle->proveedor_ruc;
            $label = trim(($nombre ?: 'Proveedor') . ($ruc ? " ({$ruc})" : ''));

            return $label ?: 'Proveedor';
        })->map(function ($items) use ($resolverTotal) {
            $total = $items->sum($resolverTotal);
            $abono = $items->sum(fn($detalle) => (float) ($detalle->abono_aplicado ?? 0));

            return [
                'total' => $total,
                'abono' => $abono,
                'saldo' => max(0, $total - $abono),
            ];
        });
    @endphp

    <div
        class="grid grid-cols-1 gap-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 shadow-sm dark:border-emerald-600/40 dark:bg-emerald-900/20 dark:text-emerald-100 sm:grid-cols-3">
        <div><span class="font-semibold">Monto facturado:</span>
            ${{ number_format((float) $totalFacturado, 2) }}</div>
        <div><span class="font-semibold">Monto abonado:</span>
            ${{ number_format((float) $totalAbonado, 2) }}</div>
        <div><span class="font-semibold">Saldo pendiente:</span>
            ${{ number_format((float) $totalSaldo, 2) }}</div>
    </div>

    <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Diario generado (resumen por proveedor)</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th class="px-3 py-2">Proveedor</th>
                        <th class="px-3 py-2 text-right">Total facturado</th>
                        <th class="px-3 py-2 text-right">Total abonado</th>
                        <th class="px-3 py-2 text-right">Saldo pendiente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($totalesProveedor as $proveedor => $totales)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $proveedor }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format((float) $totales['total'], 2) }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format((float) $totales['abono'], 2) }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format((float) $totales['saldo'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-3 text-center text-gray-500">Sin proveedores registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Directorio generado (detalle de facturas)</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th class="px-3 py-2">Proveedor</th>
                        <th class="px-3 py-2">Factura</th>
                        <th class="px-3 py-2">Emisión</th>
                        <th class="px-3 py-2">Vencimiento</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-right">Abono</th>
                        <th class="px-3 py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($detalles as $detalle)
                        @php
                            $total = $resolverTotal($detalle);
                            $abono = (float) ($detalle->abono_aplicado ?? 0);
                            $saldoPendiente = max(0, $total - $abono);
                            $labelProveedor = trim(
                                ($detalle->proveedor_nombre ?: 'Proveedor') .
                                    ($detalle->proveedor_ruc ? " ({$detalle->proveedor_ruc})" : '')
                            );
                        @endphp
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $labelProveedor }}</td>
                            <td class="px-3 py-2">{{ $detalle->numero_factura }}</td>
                            <td class="px-3 py-2">{{ optional($detalle->fecha_emision)->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">{{ optional($detalle->fecha_vencimiento)->format('Y-m-d') }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format($total, 2) }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format($abono, 2) }}</td>
                            <td class="px-3 py-2 text-right">${{ number_format($saldoPendiente, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-3 text-center text-gray-500">Sin facturas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
