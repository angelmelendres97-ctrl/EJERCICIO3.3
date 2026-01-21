<x-filament-panels::page>

    {{-- CSS DIRECTO --}}
    <style>
        /* Tabs */
        .tabs {
            display: flex;
            gap: 6px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }

        .tab-btn {
            border: 1px solid transparent;
            background: #f8fafc;
            color: #475569;
            padding: 8px 14px;
            font-weight: 600;
            font-size: 13px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            cursor: pointer;
        }

        .tab-btn:hover {
            background: #ffffff;
            color: #0f172a;
            border-color: #e5e7eb;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #0f172a;
            border-color: #e5e7eb;
            border-bottom-color: #ffffff;
        }

        /* Resaltado BANCO/CHEQUE */
        .row-bank td {
            background: #fff7d6 !important;
        }

        .row-bank td:first-child {
            border-left: 4px solid #f59e0b;
        }

        .row-bank:hover td {
            background: #ffefb0 !important;
        }

        .badge-bank {
            display: inline-block;
            margin-left: 6px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 999px;
            background: #fcd34d;
            color: #78350f;
            vertical-align: middle;
        }

        /* Footer totales / saldo */
        .tfoot-row td {
            background: #f8fafc;
            font-weight: 700;
        }

        .tfoot-saldo td {
            background: #fff1f2;
            font-weight: 800;
        }
    </style>

    @php
        // Helpers
        $money = fn($v) => '$' . number_format((float) ($v ?? 0), 2, '.', ',');
        $rate2 = fn($v) => number_format((float) ($v ?? 0), 2, '.', ','); // Cotización a 2 decimales
    @endphp

    <div class="space-y-6">

        {{-- =========================
             1) REGISTRO DE EGRESO
           ========================= --}}
        <x-filament::section>
            <x-slot name="heading">
                Registro de egreso
            </x-slot>

            @php
                $estadoSolicitud = strtoupper($this->solicitud->estado ?? '');
                $estadoLabel =
                    $estadoSolicitud === 'APROBADA'
                        ? 'Aprobada y pendiente de egreso'
                        : $this->solicitud->estado ?? 'N/D';

                // Colores (solo clases)
                $estadoClass = $estadoSolicitud === 'APROBADA' ? 'text-amber-700' : 'text-emerald-700';
            @endphp

            <div class="flex flex-wrap items-stretch justify-between gap-4">
                {{-- Solicitud --}}
                <div class="flex-1 min-w-[200px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Solicitud</div>
                        <div class="mt-1 text-2xl font-extrabold text-slate-900">#{{ $this->solicitud->id }}</div>
                        <div class="mt-1 text-sm text-slate-600">
                            {{ $this->solicitud->motivo ?? 'Sin motivo' }}
                        </div>
                    </div>
                </div>

                {{-- Estado --}}
                <div class="flex-1 min-w-[220px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Estado</div>
                        <div class="mt-1 text-lg font-extrabold {{ $estadoClass }}">
                            {{ $estadoLabel }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500">Solicitud</div>
                    </div>
                </div>

                {{-- Total facturas --}}
                <div class="flex-1 min-w-[180px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total facturas
                        </div>
                        <div class="mt-1 text-3xl font-extrabold text-slate-900">{{ $this->totalFacturas }}</div>
                        <div class="mt-1 text-xs text-slate-500">registradas</div>
                    </div>
                </div>

                {{-- Total a pagar --}}
                <div class="flex-1 min-w-[200px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total a pagar
                        </div>
                        <div class="mt-1 text-2xl font-extrabold text-amber-700">
                            {{ $this->totalAbonoHtml }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500">monto total</div>
                    </div>
                </div>
            </div>
        </x-filament::section>


        {{-- =====================================
             2) FACTURAS AGRUPADAS POR PROVEEDOR
           ===================================== --}}
        <x-filament::section>
            <x-slot name="heading">
                Facturas agrupadas por proveedor
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

        {{-- =====================================
             3) DIRECTORIO Y DIARIO GENERADO
           ===================================== --}}
        <x-filament::section>
            <x-slot name="heading">
                Directorio y Diario generado
            </x-slot>

            {{-- Totales generales --}}
            {{-- Totales Directorio / Diario --}}
            <div class="mt-4 flex flex-wrap items-stretch justify-between gap-4">

                {{-- Total débito --}}
                <div class="flex-1 min-w-[220px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Total débito
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-slate-900">
                            ${{ number_format($this->totalDebito, 2, '.', ',') }}
                        </div>
                    </div>
                </div>

                {{-- Total crédito --}}
                <div class="flex-1 min-w-[220px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Total crédito
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-slate-900">
                            ${{ number_format($this->totalCredito, 2, '.', ',') }}
                        </div>
                    </div>
                </div>

                {{-- Diferencia --}}
                <div class="flex-1 min-w-[220px] rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <div class="flex h-full flex-col justify-center">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            Diferencia
                        </div>
                        <div
                            class="mt-2 text-2xl font-extrabold {{ $this->totalDiferencia == 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            ${{ number_format($this->totalDiferencia, 2, '.', ',') }}
                        </div>
                    </div>
                </div>

            </div>

            {{-- Tabs --}}
            <div class="mt-6" x-data="{ tab: 'directorio' }" x-cloak>
                <div class="tabs">
                    <button type="button" class="tab-btn" :class="tab === 'directorio' ? 'active' : ''"
                        @click="tab = 'directorio'">
                        Directorio
                    </button>
                    <button type="button" class="tab-btn" :class="tab === 'diario' ? 'active' : ''"
                        @click="tab = 'diario'">
                        Diario
                    </button>
                </div>

                <div class="rounded-b-xl border border-slate-200 bg-white p-4">

                    {{-- =================
                         DIRECTORIO
                       ================= --}}
                    <div x-show="tab === 'directorio'">
                        @if (empty($this->directorioEntries))
                            <div
                                class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                Aún no se han generado entradas de directorio para esta solicitud.
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($this->directorioEntries as $providerKey => $entries)
                                    @php
                                        $totDebML = collect($entries)->sum(fn($e) => (float) ($e['debito_local'] ?? 0));
                                        $totCreML = collect($entries)->sum(
                                            fn($e) => (float) ($e['credito_local'] ?? 0),
                                        );
                                        $totDebME = collect($entries)->sum(
                                            fn($e) => (float) ($e['debito_extranjera'] ?? 0),
                                        );
                                        $totCreME = collect($entries)->sum(
                                            fn($e) => (float) ($e['credito_extranjera'] ?? 0),
                                        );
                                        $saldoML = $totDebML - $totCreML;
                                        $saldoME = $totDebME - $totCreME;
                                    @endphp

                                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                                        <div class="text-sm font-semibold text-slate-800">
                                            Proveedor
                                            {{ $entries[0]['proveedor'] ?? (explode('|', $providerKey)[0] ?? 'N/D') }}
                                        </div>

                                        <div class="mt-3 overflow-auto rounded-lg border border-slate-200">
                                            <table class="w-full text-xs text-slate-600">
                                                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">Proveedor</th>
                                                        <th class="px-3 py-2 text-left">Tipo</th>
                                                        <th class="px-3 py-2 text-left">Factura</th>
                                                        <th class="px-3 py-2 text-left">Vence</th>
                                                        <th class="px-3 py-2 text-left">Detalle</th>
                                                        <th class="px-3 py-2 text-right">Cotización</th>
                                                        <th class="px-3 py-2 text-right">Débito ML</th>
                                                        <th class="px-3 py-2 text-right">Crédito ML</th>
                                                        <th class="px-3 py-2 text-right">Débito ME</th>
                                                        <th class="px-3 py-2 text-right">Crédito ME</th>
                                                        <th class="px-3 py-2 text-center">Diario</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach ($entries as $entry)
                                                        @php $vence = $entry['fecha_vencimiento'] ?? null; @endphp
                                                        <tr>
                                                            <td class="px-3 py-2 font-semibold text-slate-700">
                                                                {{ $entry['proveedor'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">{{ $entry['tipo'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">{{ $entry['factura'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ $vence ? \Illuminate\Support\Carbon::parse($vence)->format('Y-m-d') : 'N/D' }}
                                                            </td>
                                                            <td class="px-3 py-2 text-slate-500">
                                                                {{ $entry['detalle'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $rate2($entry['cotizacion'] ?? 1) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['debito_local'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['credito_local'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['debito_extranjera'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['credito_extranjera'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-center text-emerald-700">
                                                                {{ $entry['diario_generado'] ?? false ? 'Sí' : 'No' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>

                                                <tfoot>
                                                    <tr class="tfoot-row">
                                                        <td class="px-3 py-2 text-left" colspan="6">TOTALES</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totDebML) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totCreML) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totDebME) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totCreME) }}</td>
                                                        <td class="px-3 py-2"></td>
                                                    </tr>
                                                    <tr class="tfoot-saldo">
                                                        <td class="px-3 py-2 text-left" colspan="6">SALDO</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($saldoML) }}</td>
                                                        <td class="px-3 py-2 text-right"></td>
                                                        <td class="px-3 py-2 text-right">{{ $money($saldoME) }}</td>
                                                        <td class="px-3 py-2 text-right"></td>
                                                        <td class="px-3 py-2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- =================
                         DIARIO
                       ================= --}}
                    <div x-show="tab === 'diario'">
                        @if (empty($this->diarioEntries))
                            <div
                                class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                Aún no se han generado movimientos en el diario para esta solicitud.
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($this->diarioEntries as $providerKey => $entries)
                                    @php
                                        $totDebML = collect($entries)->sum(fn($e) => (float) ($e['debito'] ?? 0));
                                        $totCreML = collect($entries)->sum(fn($e) => (float) ($e['credito'] ?? 0));
                                        $totDebME = collect($entries)->sum(
                                            fn($e) => (float) ($e['debito_extranjera'] ?? 0),
                                        );
                                        $totCreME = collect($entries)->sum(
                                            fn($e) => (float) ($e['credito_extranjera'] ?? 0),
                                        );
                                        $saldoML = $totDebML - $totCreML;
                                        $saldoME = $totDebME - $totCreME;
                                    @endphp

                                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                                        <div class="text-sm font-semibold text-slate-800">
                                            Proveedor
                                            {{ $entries[0]['beneficiario'] ?? (explode('|', $providerKey)[0] ?? 'N/D') }}
                                        </div>

                                        <div class="mt-3 overflow-auto rounded-lg border border-slate-200">
                                            <table class="w-full text-xs text-slate-600">
                                                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">Fila</th>
                                                        <th class="px-3 py-2 text-left">Cuenta contable</th>
                                                        <th class="px-3 py-2 text-left">Nombre</th>
                                                        <th class="px-3 py-2 text-left">Documento</th>
                                                        <th class="px-3 py-2 text-right">Cotización</th>
                                                        <th class="px-3 py-2 text-right">Débito ML</th>
                                                        <th class="px-3 py-2 text-right">Crédito ML</th>
                                                        <th class="px-3 py-2 text-right">Débito ME</th>
                                                        <th class="px-3 py-2 text-right">Crédito ME</th>
                                                        <th class="px-3 py-2 text-left">Beneficiario</th>
                                                        <th class="px-3 py-2 text-left">Cuenta bancaria</th>
                                                        <th class="px-3 py-2 text-left">Banco/Cheque</th>
                                                        <th class="px-3 py-2 text-left">Fecha venc.</th>
                                                        <th class="px-3 py-2 text-left">Formato cheque</th>
                                                        <th class="px-3 py-2 text-left">Código contable</th>
                                                        <th class="px-3 py-2 text-left">Detalle</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach ($entries as $entry)
                                                        @php
                                                            $vence = $entry['fecha_vencimiento'] ?? null;

                                                            $cuenta = (string) ($entry['cuenta'] ?? '');
                                                            $nombre = strtoupper(
                                                                trim(
                                                                    (string) ($entry['cuenta_nombre'] ??
                                                                        ($entry['nombre'] ?? '')),
                                                                ),
                                                            );

                                                            // Banco por prefijo contable (ajusta si tu plan cambia) o por nombre
                                                            $porCuenta = str_starts_with($cuenta, '1.1.01.04.');
                                                            $porNombre =
                                                                str_contains($nombre, 'BANCO') ||
                                                                str_contains($nombre, 'CHEQUE');

                                                            $isBancoCheque = $porCuenta || $porNombre;
                                                        @endphp

                                                        <tr class="{{ $isBancoCheque ? 'row-bank' : '' }}">
                                                            <td class="px-3 py-2 font-semibold text-slate-700">

                                                                @if ($isBancoCheque)
                                                                    <span class="badge-bank">BANCO/CHEQUE</span>
                                                                @endif
                                                            </td>

                                                            <td class="px-3 py-2">{{ $entry['cuenta'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2 text-slate-500">
                                                                {{ $entry['cuenta_nombre'] ?? ($entry['nombre'] ?? 'N/D') }}
                                                            </td>
                                                            <td class="px-3 py-2">{{ $entry['documento'] ?? 'N/D' }}
                                                            </td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $rate2($entry['cotizacion'] ?? 1) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['debito'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['credito'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['debito_extranjera'] ?? 0) }}</td>
                                                            <td class="px-3 py-2 text-right">
                                                                {{ $money($entry['credito_extranjera'] ?? 0) }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ $entry['beneficiario'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ $entry['cuenta_bancaria'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ $entry['banco_cheque'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ $vence ? \Illuminate\Support\Carbon::parse($vence)->format('Y-m-d') : 'N/D' }}
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                {{ $entry['formato_cheque'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2">
                                                                {{ $entry['codigo_contable'] ?? 'N/D' }}</td>
                                                            <td class="px-3 py-2 text-slate-500">
                                                                {{ $entry['detalle'] ?? 'N/D' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>

                                                <tfoot>
                                                    <tr class="tfoot-row">
                                                        <td class="px-3 py-2 text-left" colspan="5">TOTALES</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totDebML) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totCreML) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totDebME) }}</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($totCreME) }}</td>
                                                        <td class="px-3 py-2" colspan="7"></td>
                                                    </tr>

                                                    <tr class="tfoot-saldo">
                                                        <td class="px-3 py-2 text-left" colspan="5">SALDO</td>
                                                        <td class="px-3 py-2 text-right">{{ $money($saldoML) }}</td>
                                                        <td class="px-3 py-2 text-right"></td>
                                                        <td class="px-3 py-2 text-right">{{ $money($saldoME) }}</td>
                                                        <td class="px-3 py-2 text-right"></td>
                                                        <td class="px-3 py-2" colspan="7"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
