<div class="max-h-[80vh] space-y-4 overflow-y-auto p-4">
    <h3 class="text-lg font-semibold">Facturas de la Solicitud de Pago</h3>

    <div
        class="grid grid-cols-1 gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:grid-cols-3">
        <div><span class="font-semibold">Conexión:</span>
            {{ $solicitud->empresa->nombre_empresa ?? $solicitud->id_empresa }}</div>
        <div><span class="font-semibold">Fecha:</span> {{ optional($solicitud->fecha)->format('Y-m-d') }}</div>
        <div><span class="font-semibold">Tipo:</span> {{ $solicitud->tipo_solicitud }}</div>
    </div>

    <div
        class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-white p-3 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:grid-cols-3">
        <div><span class="font-semibold">Elaborado por:</span> {{ $solicitud->creador->name ?? 'N/A' }}</div>
        <div><span class="font-semibold">Monto aprobado:</span>
            ${{ number_format((float) ($solicitud->monto_aprobado ?? 0), 2) }}</div>
        <div><span class="font-semibold">Estado:</span> {{ $solicitud->estado ?? 'N/D' }}</div>
    </div>

    @php
        $empresaNombres = collect($detalles)
            ->mapWithKeys(function ($detalle) use ($empresaOptions) {
                $conexionId = (int) ($detalle->erp_conexion ?? 0);
                $empresaCodigo = $detalle->erp_empresa_id;
                $nombre = $empresaOptions[$conexionId][$empresaCodigo] ?? null;

                return [$conexionId . '|' . $empresaCodigo => $nombre];
            })
            ->filter()
            ->all();

        $sucursalNombres = collect($detalles)
            ->mapWithKeys(function ($detalle) use ($sucursalOptions) {
                $conexionId = (int) ($detalle->erp_conexion ?? 0);
                $empresaCodigo = (string) ($detalle->erp_empresa_id ?? '');
                $sucursalCodigo = $detalle->erp_sucursal;
                $nombre = $sucursalOptions[$conexionId][$empresaCodigo][$sucursalCodigo] ?? null;

                return [$conexionId . '|' . $sucursalCodigo => $nombre];
            })
            ->filter()
            ->all();

        $resolverTotal = fn($detalle) => (float) ($detalle->monto_factura ?? $detalle->saldo_al_crear ?? 0);

        $agrupado = $detalles
            ->groupBy(function ($d) use ($empresaNombres) {
                $key = ($d->erp_conexion ?? '') . '|' . ($d->erp_empresa_id ?? '');
                return $empresaNombres[$key] ?? $d->erp_empresa_id;
            })
            ->map(function ($porEmpresa) use ($sucursalNombres) {
                return $porEmpresa
                    ->groupBy(function ($d) use ($sucursalNombres) {
                        $key = ($d->erp_conexion ?? '') . '|' . ($d->erp_sucursal ?? '');
                        return $sucursalNombres[$key] ?? $d->erp_sucursal;
                    })
                    ->map(function ($porSucursal) {
                        return $porSucursal->groupBy('proveedor_ruc');
                    });
            });

        $totalesPorEmpresa = $detalles
            ->groupBy(function ($d) use ($empresaNombres) {
                $key = ($d->erp_conexion ?? '') . '|' . ($d->erp_empresa_id ?? '');
                return $empresaNombres[$key] ?? $d->erp_empresa_id;
            })
            ->map(fn($items) => $items->sum($resolverTotal));

        $totalGeneral = $detalles->sum($resolverTotal);
    @endphp

    <div
        class="space-y-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-900 shadow-sm dark:border-yellow-600/40 dark:bg-yellow-900/20 dark:text-yellow-100">
        <p class="font-semibold text-gray-900 dark:text-white">Resumen de totales</p>
        <ul class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-200">
            @foreach ($totalesPorEmpresa as $empresa => $total)
                <li class="flex items-center justify-between">
                    <span>{{ $empresa }}</span>
                    <span class="font-semibold">${{ number_format((float) $total, 2) }}</span>
                </li>
            @endforeach
        </ul>
        <div
            class="mt-2 flex items-center justify-between rounded-md bg-yellow-200 px-3 py-2 text-sm font-bold text-yellow-900 shadow-inner dark:bg-yellow-500/30 dark:text-yellow-50">
            <span>Total general</span>
            <span>${{ number_format((float) $totalGeneral, 2) }}</span>
        </div>
    </div>

    @forelse ($agrupado as $empresa => $sucursales)
        <details class="mb-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700" open>
            <summary
                class="flex cursor-pointer items-center justify-between bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-900 dark:bg-gray-900 dark:text-white">
                <span>Empresa: {{ $empresa }}</span>
                <span class="text-xs text-gray-700 dark:text-gray-300">Subtotal:
                    ${{ number_format((float) $totalesPorEmpresa[$empresa], 2) }}</span>
            </summary>
            <div class="space-y-3 bg-white p-4 dark:bg-gray-800">
                @foreach ($sucursales as $sucursal => $proveedores)
                    @php
                        $subtotalSucursal = $proveedores->flatten()->sum($resolverTotal);
                    @endphp

                    <details class="rounded border border-gray-100 dark:border-gray-700" open>
                        <summary
                            class="flex cursor-pointer items-center justify-between bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-800 dark:bg-gray-900/40 dark:text-gray-200">
                            <span>Sucursal: {{ $sucursal ?? 'N/A' }}</span>
                            <span class="text-xs">Subtotal:
                                ${{ number_format((float) $subtotalSucursal, 2) }}</span>
                        </summary>
                        <div class="space-y-3 p-3">
                            @foreach ($proveedores as $proveedor => $facturas)
                                @php
                                    $subtotalProveedor = $facturas->sum($resolverTotal);
                                    $firstConNombre = $facturas->first(fn($f) => filled($f->proveedor_nombre));
                                    $firstConRuc = $facturas->first(fn($f) => filled($f->proveedor_ruc ?? null));

                                    $nombreProveedor =
                                        $firstConNombre->proveedor_nombre ??
                                        ($facturas->first()->proveedor_nombre ?? null);

                                    $rucProveedor =
                                        $firstConRuc->proveedor_ruc ?? ($facturas->first()->proveedor_ruc ?? null);

                                    $labelProveedor = trim(
                                        ($nombreProveedor ?: 'Proveedor') .
                                            ($rucProveedor ? " ({$rucProveedor})" : '')
                                    );
                                @endphp

                                <details class="rounded border border-gray-100 dark:border-gray-700" open>
                                    <summary
                                        class="flex cursor-pointer items-center justify-between bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-800 dark:bg-gray-900/60 dark:text-gray-200">
                                       <span>Proveedor: {{ $labelProveedor }}</span>

                                        <span class="text-xs">Subtotal:
                                            ${{ number_format((float) $subtotalProveedor, 2) }}</span>
                                    </summary>
                                    <div class="rounded-b bg-white p-3 dark:bg-gray-800">
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                                                <thead
                                                    class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                                    <tr>
                                                        <th scope="col" class="px-4 py-2">#</th>
                                                        <th scope="col" class="px-4 py-2">N° Factura</th>
                                                        <th scope="col" class="px-4 py-2">Emisión</th>
                                                        <th scope="col" class="px-4 py-2">Vencimiento</th>
                                                        <th scope="col" class="px-4 py-2 text-right">Total</th>
                                                        <th scope="col" class="px-4 py-2 text-right">Monto abonado</th>
                                                        <th scope="col" class="px-4 py-2 text-right">Saldo pendiente</th>
                                                        <th scope="col" class="px-4 py-2">Estado</th>
                                                        <th scope="col" class="px-4 py-2">Creado por</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($facturas as $index => $detalle)
                                                        @php
                                                            $total = (float) ($detalle->monto_factura ?? $detalle->saldo_al_crear ?? 0);
                                                            $abono = (float) ($detalle->abono_aplicado ?? 0);
                                                            $saldoPendiente = max(0, $total - $abono);
                                                            $estadoTexto = $abono <= 0
                                                                ? 'Sin abonos realizados'
                                                                : ($saldoPendiente <= 0
                                                                    ? 'Abonado'
                                                                    : 'Pendiente de abono');
                                                        @endphp
                                                        <tr
                                                            class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                                                            <th scope="row"
                                                                class="whitespace-nowrap px-4 py-2 font-medium text-gray-900 dark:text-white">
                                                                {{ $index + 1 }}
                                                            </th>
                                                            <td class="px-4 py-2">{{ $detalle->numero_factura }}
                                                            </td>
                                                            <td class="px-4 py-2">
                                                                {{ optional($detalle->fecha_emision)->format('Y-m-d') }}
                                                            </td>
                                                            <td class="px-4 py-2">
                                                                {{ optional($detalle->fecha_vencimiento)->format('Y-m-d') }}
                                                            </td>
                                                            <td class="px-4 py-2 text-right">
                                                                ${{ number_format($total, 2) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-right">
                                                                ${{ number_format($abono, 2) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-right">
                                                                ${{ number_format($saldoPendiente, 2) }}
                                                            </td>
                                                            <td class="px-4 py-2">{{ $estadoTexto }}</td>
                                                            <td class="px-4 py-2">{{ $solicitud->creador->name ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </details>
    @empty
        <p>No se encontraron facturas para esta solicitud.</p>
    @endforelse
</div>
