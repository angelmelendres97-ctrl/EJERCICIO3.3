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
        $reportes = $reportes ?? [];
        $hasReportes = count($reportes) > 0;
        $totalDebito = $hasReportes
            ? collect($reportes)->flatMap(fn($reporte) => collect($reporte['diario'] ?? []))->sum('dasi_dml_dasi')
            : 0;
        $totalCredito = $hasReportes
            ? collect($reportes)->flatMap(fn($reporte) => collect($reporte['diario'] ?? []))->sum('dasi_cml_dasi')
            : 0;
        $totalDiferencia = $hasReportes ? $totalDebito - $totalCredito : 0;
    @endphp

    <div
        class="grid grid-cols-1 gap-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 shadow-sm dark:border-emerald-600/40 dark:bg-emerald-900/20 dark:text-emerald-100 sm:grid-cols-3">
        <div><span class="font-semibold">Total débito:</span>
            ${{ number_format((float) $totalDebito, 2) }}</div>
        <div><span class="font-semibold">Total crédito:</span>
            ${{ number_format((float) $totalCredito, 2) }}</div>
        <div><span class="font-semibold">Diferencia:</span>
            ${{ number_format((float) $totalDiferencia, 2) }}</div>
    </div>

    @if ($hasReportes)
        @foreach ($reportes as $reporte)
            <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Asiento contable</h4>
                <div
                    class="grid grid-cols-1 gap-2 rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 sm:grid-cols-3">
                    <div><span class="font-semibold">Empresa:</span> {{ $reporte['context']['empresa'] ?? '' }}</div>
                    <div><span class="font-semibold">Sucursal:</span> {{ $reporte['context']['sucursal'] ?? '' }}</div>
                    <div><span class="font-semibold">Asiento:</span> {{ $reporte['asiento']->asto_cod_asto ?? '' }}</div>
                    <div><span class="font-semibold">Fecha:</span> {{ $reporte['asiento']->asto_fec_asto ?? '' }}</div>
                    <div><span class="font-semibold">Beneficiario:</span> {{ $reporte['asiento']->asto_ben_asto ?? '' }}</div>
                    <div><span class="font-semibold">Detalle:</span> {{ $reporte['asiento']->asto_det_asto ?? '' }}</div>
                </div>

                <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Diario contable</h5>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2">Cuenta</th>
                                    <th class="px-3 py-2">Descripción</th>
                                    <th class="px-3 py-2 text-right">Débito</th>
                                    <th class="px-3 py-2 text-right">Crédito</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($reporte['diario'] as $linea)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $linea->dasi_cod_cuen ?? '' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $linea->dasi_nom_ctac ?? $linea->dasi_det_asi ?? '' }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            ${{ number_format((float) ($linea->dasi_dml_dasi ?? 0), 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            ${{ number_format((float) ($linea->dasi_cml_dasi ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 text-center text-gray-500">Sin líneas de diario.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Directorio (facturas afectadas)</h5>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2">Proveedor</th>
                                    <th class="px-3 py-2">Factura</th>
                                    <th class="px-3 py-2">Vencimiento</th>
                                    <th class="px-3 py-2">Detalle</th>
                                    <th class="px-3 py-2 text-right">Débito</th>
                                    <th class="px-3 py-2 text-right">Crédito</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($reporte['directorio'] as $linea)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $linea->dire_nom_clpv ?? '' }}
                                        </td>
                                        <td class="px-3 py-2">{{ $linea->dir_num_fact ?? '' }}</td>
                                        <td class="px-3 py-2">{{ $linea->dir_fec_venc ?? '' }}</td>
                                        <td class="px-3 py-2">{{ $linea->dir_detalle ?? '' }}</td>
                                        <td class="px-3 py-2 text-right">
                                            ${{ number_format((float) ($linea->dir_deb_ml ?? 0), 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            ${{ number_format((float) ($linea->dir_cre_ml ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-3 text-center text-gray-500">Sin facturas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-600/40 dark:bg-amber-900/20 dark:text-amber-100">
            No se encontró información del asiento contable en SAE para esta solicitud.
        </div>

        @php
            $resolverTotal = fn($detalle) => (float) ($detalle->monto_factura ?? $detalle->saldo_al_crear ?? 0);
            $totalFacturado = $detalles->sum($resolverTotal);
            $totalAbonado = $detalles->sum(fn($detalle) => (float) ($detalle->abono_aplicado ?? 0));
            $totalSaldo = max(0, $totalFacturado - $totalAbonado);
        @endphp

        <div
            class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:grid-cols-3">
            <div><span class="font-semibold">Monto facturado:</span>
                ${{ number_format((float) $totalFacturado, 2) }}</div>
            <div><span class="font-semibold">Monto abonado:</span>
                ${{ number_format((float) $totalAbonado, 2) }}</div>
            <div><span class="font-semibold">Saldo pendiente:</span>
                ${{ number_format((float) $totalSaldo, 2) }}</div>
        </div>
    @endif
</div>
