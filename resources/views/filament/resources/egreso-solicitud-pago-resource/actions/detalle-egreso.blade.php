<div class="max-h-[80vh] space-y-4 overflow-y-auto p-4">
    <h3 class="text-lg font-semibold">Detalle de egreso</h3>

    @php
        $conexiones = collect($reportes)
            ->map(function ($reporte) {
                $empresa = $reporte['context']['empresa'] ?? '';
                $sucursal = $reporte['context']['sucursal'] ?? '';

                return trim($empresa . ($sucursal ? " / {$sucursal}" : ''));
            })
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    @endphp

    @php
        $resumenGeneral = [
            'debito' => 0.0,
            'credito' => 0.0,
        ];

        foreach ($reportes as $reporte) {
            $debito = collect($reporte['diario'] ?? [])->sum(fn($linea) => (float) ($linea->dasi_dml_dasi ?? 0));
            $credito = collect($reporte['diario'] ?? [])->sum(fn($linea) => (float) ($linea->dasi_cml_dasi ?? 0));
            $resumenGeneral['debito'] += $debito;
            $resumenGeneral['credito'] += $credito;
        }

        $resumenGeneral['saldo'] = $resumenGeneral['debito'] - $resumenGeneral['credito'];
        $resumenGeneral['saldo_pendiente'] = abs($resumenGeneral['saldo']);
    @endphp

    <div
        class="grid grid-cols-1 gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:grid-cols-3">
        <div><span class="font-semibold">Solicitud:</span> #{{ $solicitud->id }}</div>
        <div><span class="font-semibold">Fecha:</span> {{ optional($solicitud->fecha)->format('Y-m-d') }}</div>
        <div><span class="font-semibold">Conexión:</span>
            {{ $conexiones ?: ($solicitud->empresa->nombre_empresa ?? $solicitud->id_empresa) }}</div>
    </div>

    @forelse ($reportes as $reporte)
        @php
            $totalDebito = collect($reporte['diario'] ?? [])->sum(fn($linea) => (float) ($linea->dasi_dml_dasi ?? 0));
            $totalCredito = collect($reporte['diario'] ?? [])->sum(fn($linea) => (float) ($linea->dasi_cml_dasi ?? 0));
            $saldoFinal = $totalDebito - $totalCredito;
            $saldoPendiente = abs($saldoFinal);
        @endphp

        <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Asiento contable generado</h4>
                <span class="text-xs text-gray-500 dark:text-gray-300">
                    Empresa {{ $reporte['context']['empresa'] ?? '' }}
                    @if (! empty($reporte['context']['sucursal']))
                        · Sucursal {{ $reporte['context']['sucursal'] }}
                    @endif
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 rounded-lg border border-slate-100 bg-slate-50 p-3 text-xs text-slate-700 dark:border-gray-700 dark:bg-gray-800/40 dark:text-gray-200 sm:grid-cols-4">
                <div>
                    <div class="text-[11px] uppercase text-slate-500">Total débito</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($totalDebito, 2) }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase text-slate-500">Total crédito</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($totalCredito, 2) }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase text-slate-500">Saldo pendiente</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($saldoPendiente, 2) }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase text-slate-500">Saldo final</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($saldoFinal, 2) }}</div>
                </div>
            </div>

            @if ($reporte['asiento'])
                <div class="grid grid-cols-1 gap-2 rounded-lg border border-emerald-100 bg-emerald-50 p-3 text-xs text-emerald-900 dark:border-emerald-600/40 dark:bg-emerald-900/20 dark:text-emerald-100 sm:grid-cols-3">
                    <div><span class="font-semibold">Asiento:</span> {{ $reporte['asiento']->asto_cod_asto ?? '' }}</div>
                    <div><span class="font-semibold">Fecha:</span> {{ $reporte['asiento']->asto_fec_asto ?? '' }}</div>
                    <div><span class="font-semibold">Beneficiario:</span> {{ $reporte['asiento']->asto_ben_asto ?? '' }}</div>
                    <div class="sm:col-span-3"><span class="font-semibold">Detalle:</span> {{ $reporte['asiento']->asto_det_asto ?? '' }}</div>
                </div>
            @else
                <p class="text-sm text-gray-500">No se encontró el asiento contable en SAE para esta solicitud.</p>
            @endif

            <div>
                <h5 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">Detalle del diario</h5>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 text-[11px] uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2">Cuenta</th>
                                <th class="px-3 py-2">Detalle</th>
                                <th class="px-3 py-2 text-right">Débito</th>
                                <th class="px-3 py-2 text-right">Crédito</th>
                                <th class="px-3 py-2 text-right">Débito Ext.</th>
                                <th class="px-3 py-2 text-right">Crédito Ext.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($reporte['diario'] as $linea)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">
                                        {{ $linea->dasi_cod_cuen ?? '' }}
                                    </td>
                                    <td class="px-3 py-2">{{ $linea->dasi_det_asi ?? '' }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) ($linea->dasi_dml_dasi ?? 0), 2) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) ($linea->dasi_cml_dasi ?? 0), 2) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) ($linea->dasi_dme_dasi ?? 0), 2) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) ($linea->dasi_cme_dasi ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-center text-gray-500">Sin movimientos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h5 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">Directorio</h5>
                <div class="mt-2 overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 text-[11px] uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2">Proveedor</th>
                                <th class="px-3 py-2">Factura</th>
                                <th class="px-3 py-2">Detalle</th>
                                <th class="px-3 py-2">Vence</th>
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
                                    <td class="px-3 py-2">{{ $linea->dir_detalle ?? '' }}</td>
                                    <td class="px-3 py-2">{{ $linea->dir_fec_venc ?? '' }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) ($linea->dir_deb_ml ?? 0), 2) }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format((float) ($linea->dir_cre_ml ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-center text-gray-500">Sin entradas en directorio.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-500 dark:border-gray-700">
            No se encontraron asientos contables relacionados con esta solicitud.
        </div>
    @endforelse

    @if (! empty($reportes))
        <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Resumen general</h4>
            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
                    <div class="text-[11px] uppercase text-slate-500">Total débito</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format((float) ($resumenGeneral['debito'] ?? 0), 2) }}</div>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
                    <div class="text-[11px] uppercase text-slate-500">Total crédito</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format((float) ($resumenGeneral['credito'] ?? 0), 2) }}</div>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
                    <div class="text-[11px] uppercase text-slate-500">Saldo pendiente</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format((float) ($resumenGeneral['saldo_pendiente'] ?? 0), 2) }}</div>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-gray-700 dark:bg-gray-800/40">
                    <div class="text-[11px] uppercase text-slate-500">Saldo final</div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format((float) ($resumenGeneral['saldo'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
