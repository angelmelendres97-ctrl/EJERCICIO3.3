<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Gestion de Solicitud de pago
            </x-slot>

            @php
                $motivoSolicitud = $this->filters['motivo'] ?? ($this->solicitud?->motivo ?? 'N/D');
                $estadoSolicitud = $this->solicitud?->estado ?? 'Borrador';
                $creadorSolicitud =
                    $this->solicitud?->aprobador?->name ?? (\Illuminate\Support\Facades\Auth::user()?->name ?? 'N/D');
                $fechaCreacion = optional($this->solicitud?->created_at ?? now())->format('Y-m-d');
            @endphp


            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-2">
                <div class="text-sm text-gray-600">
                    Ingreso los valores de abono para cada factura que desea pagar en esta solicitud.
                </div>

            </div>


            <div
    class="mt-4 flex flex-wrap items-center gap-1 sm:justify-center justify-between"
    x-data
    x-on:refresh-resumen.window="$wire.$refresh()"
>
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 min-w-[220px]" >
                    <div class="font-semibold uppercase tracking-wide text-amber-800">
                        Elaborado por:
                    </div>
                    <div class="mt-1 text-sm font-extrabold text-amber-900">
                        {{ $creadorSolicitud }}

                    </div>
                    <div class="font-semibold uppercase tracking-wide text-amber-800 mt-1">
                        Motivo:
                    </div>
                    <div class="mt-1 text-sm font-extrabold text-amber-900">
                        {{ $motivoSolicitud }}

                    </div>

                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 min-w-[220px]">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">
                        Estado
                    </div>
                    <div class="mt-1 text-xl font-extrabold text-amber-900">
                        {{ $estadoSolicitud }}
                    </div>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 min-w-[220px]">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">
                        Fecha de creación:
                    </div>
                    <div class="mt-1 text-xl font-extrabold text-amber-900">
                        {{ $fechaCreacion }}

                    </div>
                </div>


                {{-- Card 3: Total de todas las facturas --}}
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 min-w-[220px]">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">
                        Total de todas las facturas
                    </div>
                    <div class="mt-1 text-2xl font-extrabold text-amber-900">
                        ${{ number_format((float) $this->totalFacturas, 2, '.', ',') }}
                    </div>
                </div>

                {{-- Card 4: Monto aprobado --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4 min-w-[220px]">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                        Monto aprobado
                    </div>
                    <div class="mt-1 text-2xl font-extrabold text-slate-900">
                        ${{ number_format($this->montoAprobadoValue(), 2, '.', ',') }}
                    </div>
                </div>

                {{-- Card 5: Monto disponible --}}
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 min-w-[220px]">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                        Monto Disponible
                    </div>
                    <div class="mt-1 text-2xl font-extrabold text-emerald-900">
                        ${{ number_format((float) $this->presupuestoDisponible, 2, '.', ',') }}
                    </div>
                </div>
            </div>




            <div class="mt-4 space-y-4">
                @php
                    $allowSelection = true;
                @endphp

                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full">

                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Buscar proveedor, factura o RUC…"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-amber-500 focus:ring-amber-500" />
                    </div>



                    <button type="button" wire:click="$set('search','')"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Limpiar
                    </button>

                    <button type="button" wire:click="openCompraModal"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        + Agregar Compra
                    </button>
                    @if ($this->solicitud)
                        <button type="button" wire:click="openAgregarFacturasModal"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            + Agregar proveedores/facturas
                        </button>
                    @endif
                </div>


                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    @php
                        $columnsCount = $allowSelection ? 7 : 6;
                    @endphp

                    @if ($allowSelection)
                        @php
                            $allFacturasSeleccionadas = $this->allFacturasSelected();
                            $hayFacturasSeleccionadas = $this->anyFacturasSelected();
                        @endphp
                        <div
                            class="flex items-center justify-end gap-2 border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">
                            <label class="flex items-center gap-2">
                                <input type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                    wire:click="toggleAllFacturasSelection"
                                    @checked($allFacturasSeleccionadas)
                                    @disabled($this->presupuestoDisponible <= 0 && !$hayFacturasSeleccionadas) />
                                Seleccionar todas las facturas
                            </label>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('proveedor_nombre')"
                                            class="flex items-center gap-1">
                                            Proveedor
                                            @if ($sortField === 'proveedor_nombre')
                                                <span
                                                    class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Descripción</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Área</th>
                                    <th class="px-4 py-2 text-right font-semibold text-gray-700">
                                        <button type="button" wire:click="sortBy('total')"
                                            class="flex items-center gap-1 float-right">
                                            Total
                                            @if ($sortField === 'total')
                                                <span
                                                    class="text-xs text-amber-600">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    </th>

                                    <th class="px-4 py-2 text-right font-semibold text-gray-700">Total asignado
                                    </th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Facturas</th>
                                    {{--  --}}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($this->providersPaginated as $proveedor)
                                    <tr class="align-top">
                                        <td class="px-4 py-3">
                                            @php
                                                $descripcionProveedor =
                                                    $this->providerDescriptions[$proveedor['key']] ??
                                                    ($proveedor['proveedor_actividad'] ?? '');
                                                $nombreProveedor = !empty($proveedor['es_compra'])
                                                    ? ($descripcionProveedor ?:
                                                    $proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'])
                                                    : $proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'];
                                            @endphp
                                            <div class="font-semibold text-gray-800">
                                                {{ $nombreProveedor }}
                                                @if (!empty($proveedor['es_compra']))
                                                    <span
                                                        class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Compra</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Código: {{ $proveedor['proveedor_codigo'] }}
                                                @if (!empty($proveedor['proveedor_ruc']))
                                                    · RUC: {{ $proveedor['proveedor_ruc'] }}
                                                @endif
                                                <span
                                                    class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">{{ $proveedor['facturas_count'] ?? 0 }}
                                                    factura(s)</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                wire:model.live.debounce.500ms="providerDescriptions.{{ $proveedor['key'] }}"
                                                placeholder="Descripción del proveedor"
                                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $this->providerAreas[$proveedor['key']] ?? ($proveedor['area'] ?? '—') }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                            ${{ number_format((float) ($proveedor['total'] ?? 0), 2, '.', ',') }}</td>

                                        <td class="px-4 py-3 text-right align-top">
                                            @php
                                                $totalAsignado =
                                                    (float) ($this->providerAbonos[$proveedor['key']] ?? 0);
                                            @endphp
                                            <div class="text-right font-semibold text-gray-800">
                                                ${{ number_format($totalAsignado, 2, '.', ',') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <details wire:key="prov-{{ $proveedor['key'] }}" x-data="{ open: $wire.entangle('openProviders.{{ $proveedor['key'] }}').live }"
                                                :open="open" @toggle="open = $event.target.open"
                                                class="rounded-md border border-gray-200 bg-slate-50 p-3 w-full">

                                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                                                    Ver detalle agrupado
                                                </summary>
                                                <div class="mt-2 space-y-3 w-full">
                                                    @foreach ($proveedor['empresas'] ?? [] as $empresa)
                                                        <div class="rounded-lg border border-slate-200 bg-white">
                                                            <div
                                                                class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                                                                @php
                                                                    $empresaKey =
                                                                        ($empresa['conexion_id'] ?? '') .
                                                                        '|' .
                                                                        ($empresa['empresa_codigo'] ?? '');
                                                                    $empresaSeleccionada = $this->empresaHasAllSelected(
                                                                        $proveedor['key'],
                                                                        $empresaKey,
                                                                    );
                                                                    $empresaTieneSeleccion = $this->empresaHasAnySelected(
                                                                        $proveedor['key'],
                                                                        $empresaKey,
                                                                    );
                                                                @endphp
                                                                <span>{{ $empresa['conexion_nombre'] ?? 'Conexión' }} ·
                                                                    {{ $empresa['empresa_nombre'] ?? $empresa['empresa_codigo'] }}</span>
                                                                <div class="flex items-center gap-3">
                                                                    <span
                                                                        class="text-[11px] font-medium text-slate-500">{{ count($empresa['sucursales'] ?? []) }}
                                                                        sucursal(es)</span>
                                                                    @if ($allowSelection)
                                                                        <label
                                                                            class="flex items-center gap-1 text-[11px] font-medium text-slate-600">
                                                                            <input type="checkbox"
                                                                                class="h-3.5 w-3.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                                                                wire:click="toggleEmpresaSelection({{ \Illuminate\Support\Js::from($proveedor['key']) }}, {{ \Illuminate\Support\Js::from($empresaKey) }})"
                                                                                @checked($empresaSeleccionada)
                                                                                @disabled($this->presupuestoDisponible <= 0 && !$empresaTieneSeleccion) />
                                                                            Seleccionar
                                                                        </label>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="space-y-2 p-3 w-full">
                                                                @foreach ($empresa['sucursales'] ?? [] as $sucursal)
                                                                    <div class="rounded-md border border-slate-200">
                                                                        <div
                                                                            class="flex items-center justify-between bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-700">
                                                                            @php
                                                                                $sucursalKey =
                                                                                    $empresaKey .
                                                                                    '|' .
                                                                                    ($sucursal['sucursal_codigo'] ??
                                                                                        '');
                                                                                $sucursalSeleccionada = $this->sucursalHasAllSelected(
                                                                                    $proveedor['key'],
                                                                                    $empresaKey,
                                                                                    $sucursalKey,
                                                                                );
                                                                                $sucursalTieneSeleccion = $this->sucursalHasAnySelected(
                                                                                    $proveedor['key'],
                                                                                    $empresaKey,
                                                                                    $sucursalKey,
                                                                                );
                                                                            @endphp
                                                                            <span>{{ $sucursal['sucursal_nombre'] ?? $sucursal['sucursal_codigo'] }}</span>
                                                                            @if ($allowSelection)
                                                                                <label
                                                                                    class="flex items-center gap-1 text-[11px] font-medium text-slate-600">
                                                                                    <input type="checkbox"
                                                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                                                                        wire:click="toggleSucursalSelection({{ \Illuminate\Support\Js::from($proveedor['key']) }}, {{ \Illuminate\Support\Js::from($empresaKey) }}, {{ \Illuminate\Support\Js::from($sucursalKey) }})"
                                                                                        @checked($sucursalSeleccionada)
                                                                                        @disabled($this->presupuestoDisponible <= 0 && !$sucursalTieneSeleccion) />
                                                                                    Seleccionar
                                                                                </label>
                                                                            @endif

                                                                        </div>
                                                                        <div
                                                                            class="overflow-x-auto lg:overflow-visible">
                                                                            <table
                                                                                class="w-full table-auto divide-y divide-gray-200 text-xs">
                                                                                <thead class="bg-white">
                                                                                    <tr>
                                                                                        <th
                                                                                            class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                            Factura</th>
                                                                                        <th
                                                                                            class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                            Emisión</th>

                                                                                        <th
                                                                                            class="px-3 py-1 text-right font-semibold text-gray-700">
                                                                                            Saldo</th>
                                                                                        <th
                                                                                            class="px-3 py-1 text-right font-semibold text-gray-700">
                                                                                            Abono</th>
                                                                                        @if ($allowSelection)
                                                                                            <th
                                                                                                class="px-3 py-1 text-center font-semibold text-gray-700">
                                                                                                Seleccionar</th>
                                                                                        @endif
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody
                                                                                    class="divide-y divide-gray-100">
                                                                                    @foreach ($sucursal['facturas'] ?? [] as $factura)
                                                                                        <tr
                                                                                            wire:key="fac-{{ $factura['key'] }}">

                                                                                            <td
                                                                                                class="px-3 py-1 text-gray-700">
                                                                                                {{ $factura['numero'] ?? '' }}
                                                                                                @if (($factura['tipo'] ?? null) === 'compra')
                                                                                                    <span
                                                                                                        class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Compra</span>
                                                                                                @endif
                                                                                            </td>
                                                                                            <td
                                                                                                class="px-3 py-1 text-danger-700">
                                                                                                {{ $factura['fecha_emision'] ?? '' }}
                                                                                            </td>

                                                                                            <td
                                                                                                class="px-3 py-1 text-right font-semibold text-gray-800">
                                                                                                ${{ number_format((float) ($factura['saldo'] ?? 0), 2, '.', ',') }}
                                                                                            </td>
                                                                                            <td
                                                                                                class="px-3 py-1 text-right">
                                                                                                @php

                                                                                                    $key =
                                                                                                        $factura['key'];
                                                                                                    $saldoFactura =
                                                                                                        (float) ($factura[
                                                                                                            'saldo'
                                                                                                        ] ?? 0);
                                                                                                    $abonoActual =
                                                                                                        (float) ($this
                                                                                                            ->invoiceAbonos[
                                                                                                            $key
                                                                                                        ] ?? 0);

                                                                                                    $abonoActual =
                                                                                                        (float) ($this
                                                                                                            ->invoiceAbonos[
                                                                                                            $key
                                                                                                        ] ?? 0);

                                                                                                    // Disponible real para ESTA factura (le sumamos su propio abono)
                                                                                                    $disponibleParaFactura = max(
                                                                                                        0,
                                                                                                        $this->presupuestoDisponible +
                                                                                                            $abonoActual,
                                                                                                    );

                                                                                                    // Máximo permitido = saldo factura VS disponible
                                                                                                    $maximoPermitido = min(
                                                                                                        (float) ($factura[
                                                                                                            'saldo'
                                                                                                        ] ?? 0),
                                                                                                        $disponibleParaFactura,
                                                                                                    );
                                                                                                @endphp


                                                                                                <div x-data="{
                                                                                                    key: @js($key),
                                                                                                    aprobado: {{ $this->montoAprobadoValue() }},
                                                                                                    saldo: {{ $saldoFactura }},
                                                                                                    draft: @js(number_format($abonoActual, 2, '.', '')),

                                                                                                    sanitize(val) {
                                                                                                        val = (val ?? '').toString();
                                                                                                        val = val.replace(/[^0-9.,]/g, '');

                                                                                                        const d = val.indexOf('.');
                                                                                                        const c = val.indexOf(',');
                                                                                                        const sep = (d === -1) ? c : (c === -1 ? d : Math.min(d, c));

                                                                                                        if (sep !== -1) {
                                                                                                            const head = val.slice(0, sep);
                                                                                                            const tail = val.slice(sep + 1).replace(/[.,]/g, '');
                                                                                                            val = head + '.' + tail;
                                                                                                        }

                                                                                                        return val;
                                                                                                    },

                                                                                                    toNumber(val) {
                                                                                                        const s = this.sanitize(val);
                                                                                                        if (s === '') return 0;
                                                                                                        const n = parseFloat(s);
                                                                                                        return isNaN(n) ? 0 : n;
                                                                                                    },

                                                                                                    maxPermitido() {
                                                                                                        const aprobado = this.aprobado || 0;

                                                                                                        const abonos = $wire.invoiceAbonos ?? {};
                                                                                                        let totalSinEsta = 0;

                                                                                                        for (const [k, v] of Object.entries(abonos)) {
                                                                                                            if (k === this.key) continue;
                                                                                                            const n = parseFloat(String(v).replace(',', '.')) || 0;
                                                                                                            totalSinEsta += Math.max(0, n);
                                                                                                        }

                                                                                                        const disponible = Math.max(0, aprobado - totalSinEsta);
                                                                                                        return Math.max(0, Math.min(this.saldo, disponible));
                                                                                                    },

                                                                                                    clamp(n) {
                                                                                                        n = Math.max(0, n);
                                                                                                        n = Math.min(this.maxPermitido(), n);
                                                                                                        return Math.round(n * 100) / 100;
                                                                                                    },

                                                                                                    commit() {
                                                                                                        const n = this.clamp(this.toNumber(this.draft));
                                                                                                        this.draft = n.toFixed(2);
                                                                                                        $wire.set(`invoiceAbonos.${this.key}`, n)
$wire.$refresh()
                                                                                                    },

                                                                                                    init() {
                                                                                                        // cuando Livewire cambie invoiceAbonos[key] (por abono proveedor), refresca el input
                                                                                                        this.$watch(() => $wire.invoiceAbonos?.[this.key], (v) => {
                                                                                                            const n = parseFloat(String(v ?? 0).replace(',', '.')) || 0;
                                                                                                            this.draft = n.toFixed(2);
                                                                                                        });
                                                                                                    }
                                                                                                }"
                                                                                                    x-init="init()"
                                                                                                    x-on:click.stop
                                                                                                    x-on:keydown.stop>
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        inputmode="decimal"
                                                                                                        x-model="draft"
                                                                                                        x-on:keydown="if ($event.key === '-' || $event.key === 'e' || $event.key === 'E') $event.preventDefault();"
                                                                                                        x-on:input="draft = sanitize(draft)"
                                                                                                        x-on:input.debounce.400ms="commit()"
                                                                                                        x-on:blur="commit()"
                                                                                                        class="w-24 md:w-28 max-w-[7rem] rounded border border-gray-300 px-2 py-1 text-right text-sm focus:border-amber-500 focus:ring-amber-500"
                                                                                                        @disabled($allowSelection && !in_array($key, $this->selectedInvoices)) />
                                                                                                </div>


                                                                                                @php
                                                                                                    $estadoAbono =
                                                                                                        'No se ha abonado';

                                                                                                    if (
                                                                                                        $abonoActual >
                                                                                                            0 &&
                                                                                                        $abonoActual <
                                                                                                            $saldoFactura
                                                                                                    ) {
                                                                                                        $estadoAbono =
                                                                                                            'Falta abonar';
                                                                                                    } elseif (
                                                                                                        $abonoActual >=
                                                                                                            $saldoFactura &&
                                                                                                        $saldoFactura >
                                                                                                            0
                                                                                                    ) {
                                                                                                        $estadoAbono =
                                                                                                            'Está abonado';
                                                                                                    }
                                                                                                @endphp

                                                                                                <div
                                                                                                    class="mt-1 text-[11px] font-semibold text-slate-700">
                                                                                                    {{ $estadoAbono }}
                                                                                                </div>





                                                                                            </td>
                                                                                            @if ($allowSelection)
                                                                                                <td
                                                                                                    class="px-3 py-1 text-center">
                                                                                                    <input
                                                                                                        type="checkbox"
                                                                                                        value="{{ $factura['key'] }}"
                                                                                                        wire:model.live="selectedInvoices"
                                                                                                        class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                                                                                        @disabled(!in_array($factura['key'], $this->selectedInvoices) && $this->presupuestoDisponible <= 0) />

                                                                                                </td>
                                                                                            @endif
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $columnsCount }}"
                                            class="px-4 py-4 text-center text-sm text-gray-600">
                                            Seleccione filtros para visualizar las facturas disponibles.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 bg-white px-4 py-3">
                        {{ $this->providersPaginated->links() }}
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    <div x-data="{ open: @entangle('showCompraModal').live }" x-cloak>
        <div x-show="open" class="fixed inset-0 z-40 flex items-center justify-center px-4" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>

            <div class="relative z-50 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Agregar compra</h2>
                        <p class="text-sm text-slate-500">Registre una factura adicional como compra.</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="open = false">
                        ✕
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Conexión</label>
                            <select wire:model.live="compraForm.conexion_id"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                <option value="">Seleccione una conexión</option>
                                @foreach ($this->compraConexionesOptions as $conexionId => $conexionNombre)
                                    <option value="{{ $conexionId }}">{{ $conexionNombre }}</option>
                                @endforeach
                            </select>
                            @error('compraForm.conexion_id')
                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Empresa destino</label>
                            <select wire:model.live="compraForm.empresa_codigo"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
                                @disabled(empty($this->compraForm['conexion_id']))>
                                <option value="">Seleccione una empresa</option>
                                @foreach ($this->compraEmpresasOptions as $empresaCodigo => $empresaNombre)
                                    <option value="{{ $empresaCodigo }}">{{ $empresaNombre }}</option>
                                @endforeach
                            </select>
                            @error('compraForm.empresa_codigo')
                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Descripción del proveedor</label>
                        <input type="text" wire:model.defer="compraForm.descripcion_proveedor"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
                            placeholder="Ingrese la descripción del proveedor" />
                        @error('compraForm.descripcion_proveedor')
                            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Valor a pagar</label>
                            <input type="number" step="0.01" wire:model.defer="compraForm.valor_pagar"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="0.00" />
                            @error('compraForm.valor_pagar')
                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Abono</label>
                            <input type="number" step="0.01" wire:model.defer="compraForm.abono"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
                                placeholder="0.00" />
                            @error('compraForm.abono')
                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeCompraModal"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardarCompra"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Guardar compra
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ open: @entangle('showAgregarFacturasModal').live }" x-cloak>
        <div x-show="open" class="fixed inset-0 z-40 flex items-center justify-center px-4" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>

            <div class="relative z-50 w-full max-w-6xl rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Agregar proveedores y facturas</h2>
                        <p class="text-sm text-slate-500">Seleccione proveedores y facturas para agregarlos a la
                            solicitud.</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="open = false">
                        ✕
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Conexiones</label>
                            <select wire:model.live="modalFilters.conexiones" multiple
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                @foreach (\App\Models\Empresa::query()->pluck('nombre_empresa', 'id') as $conexionId => $conexionNombre)
                                    <option value="{{ $conexionId }}">{{ $conexionNombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Empresas</label>
                            <select wire:model.live="modalFilters.empresas" multiple
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                @foreach ($this->getEmpresasOptionsByConnections($this->modalFilters['conexiones'] ?? []) as $empresaKey => $empresaNombre)
                                    <option value="{{ $empresaKey }}">{{ $empresaNombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Sucursales</label>
                            <select wire:model.live="modalFilters.sucursales" multiple
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                                @foreach ($this->getSucursalesOptionsByConnections($this->modalFilters['conexiones'] ?? [], $this->groupOptionsByConnection($this->modalFilters['empresas'] ?? [])) as $sucursalKey => $sucursalNombre)
                                    <option value="{{ $sucursalKey }}">{{ $sucursalNombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full">
                            <input type="text" wire:model.live.debounce.300ms="modalSearch"
                                placeholder="Buscar proveedor, factura o RUC…"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-3 text-sm focus:border-amber-500 focus:ring-amber-500" />
                        </div>
                        <button type="button" wire:click="loadModalFacturas"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Cargar proveedores
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                        @php
                            $modalColumnsCount = 3;
                        @endphp

                        <div
                            class="flex items-center justify-end gap-2 border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">
                            <label class="flex items-center gap-2">
                                <input type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                    wire:click="toggleModalAllFacturasSelection"
                                    @checked($this->modalAllFacturasSelected()) />
                                Seleccionar todas las facturas
                            </label>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">
                                            <button type="button" wire:click="sortModalBy('proveedor_nombre')"
                                                class="flex items-center gap-1">
                                                Proveedor
                                                @if ($this->modalSortField === 'proveedor_nombre')
                                                    <span
                                                        class="text-xs text-amber-600">{{ $this->modalSortDirection === 'asc' ? '▲' : '▼' }}</span>
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-4 py-2 text-right font-semibold text-gray-700">
                                            <button type="button" wire:click="sortModalBy('total')"
                                                class="flex items-center gap-1 float-right">
                                                Total
                                                @if ($this->modalSortField === 'total')
                                                    <span
                                                        class="text-xs text-amber-600">{{ $this->modalSortDirection === 'asc' ? '▲' : '▼' }}</span>
                                                @endif
                                            </button>
                                        </th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Facturas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($this->modalProvidersPaginated as $proveedor)
                                        <tr class="align-top">
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $proveedor['proveedor_nombre'] ?? $proveedor['proveedor_codigo'] }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Código: {{ $proveedor['proveedor_codigo'] }}
                                                    @if (!empty($proveedor['proveedor_ruc']))
                                                        · RUC: {{ $proveedor['proveedor_ruc'] }}
                                                    @endif
                                                    <span
                                                        class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">{{ $proveedor['facturas_count'] ?? 0 }}
                                                        factura(s)</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                                ${{ number_format((float) ($proveedor['total'] ?? 0), 2, '.', ',') }}
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <details wire:key="modal-prov-{{ $proveedor['key'] }}"
                                                    x-data="{ open: $wire.entangle('modalOpenProviders.{{ $proveedor['key'] }}').live }"
                                                    :open="open" @toggle="open = $event.target.open"
                                                    class="rounded-md border border-gray-200 bg-slate-50 p-3 w-full">
                                                    <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                                                        Ver detalle agrupado
                                                    </summary>
                                                    <div class="mt-2 space-y-3 w-full">
                                                        @foreach ($proveedor['empresas'] ?? [] as $empresa)
                                                            <div class="rounded-lg border border-slate-200 bg-white">
                                                                <div
                                                                    class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                                                                    @php
                                                                        $empresaKey =
                                                                            ($empresa['conexion_id'] ?? '') .
                                                                            '|' .
                                                                            ($empresa['empresa_codigo'] ?? '');
                                                                        $empresaSeleccionada = $this->modalEmpresaHasAllSelected(
                                                                            $proveedor['key'],
                                                                            $empresaKey,
                                                                        );
                                                                    @endphp
                                                                    <span>{{ $empresa['conexion_nombre'] ?? 'Conexión' }}
                                                                        ·
                                                                        {{ $empresa['empresa_nombre'] ?? $empresa['empresa_codigo'] }}</span>
                                                                    <div class="flex items-center gap-3">
                                                                        <span
                                                                            class="text-[11px] font-medium text-slate-500">{{ count($empresa['sucursales'] ?? []) }}
                                                                            sucursal(es)</span>
                                                                        <label
                                                                            class="flex items-center gap-1 text-[11px] font-medium text-slate-600">
                                                                            <input type="checkbox"
                                                                                class="h-3.5 w-3.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                                                                wire:click="toggleModalEmpresaSelection({{ \Illuminate\Support\Js::from($proveedor['key']) }}, {{ \Illuminate\Support\Js::from($empresaKey) }})"
                                                                                @checked($empresaSeleccionada) />
                                                                            Seleccionar
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="space-y-2 p-3 w-full">
                                                                    @foreach ($empresa['sucursales'] ?? [] as $sucursal)
                                                                        <div
                                                                            class="rounded-md border border-slate-200">
                                                                            <div
                                                                                class="flex items-center justify-between bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-700">
                                                                                @php
                                                                                    $sucursalKey =
                                                                                        $empresaKey .
                                                                                        '|' .
                                                                                        ($sucursal['sucursal_codigo'] ?? '');
                                                                                    $sucursalSeleccionada = $this->modalSucursalHasAllSelected(
                                                                                        $proveedor['key'],
                                                                                        $empresaKey,
                                                                                        $sucursalKey,
                                                                                    );
                                                                                @endphp
                                                                                <span>{{ $sucursal['sucursal_nombre'] ?? $sucursal['sucursal_codigo'] }}</span>
                                                                                <label
                                                                                    class="flex items-center gap-1 text-[11px] font-medium text-slate-600">
                                                                                    <input type="checkbox"
                                                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                                                                        wire:click="toggleModalSucursalSelection({{ \Illuminate\Support\Js::from($proveedor['key']) }}, {{ \Illuminate\Support\Js::from($empresaKey) }}, {{ \Illuminate\Support\Js::from($sucursalKey) }})"
                                                                                        @checked($sucursalSeleccionada) />
                                                                                    Seleccionar
                                                                                </label>
                                                                            </div>
                                                                            <div
                                                                                class="overflow-x-auto lg:overflow-visible">
                                                                                <table
                                                                                    class="w-full table-auto divide-y divide-gray-200 text-xs">
                                                                                    <thead class="bg-white">
                                                                                        <tr>
                                                                                            <th
                                                                                                class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                                Factura</th>
                                                                                            <th
                                                                                                class="px-3 py-1 text-left font-semibold text-gray-700">
                                                                                                Emisión</th>
                                                                                            <th
                                                                                                class="px-3 py-1 text-right font-semibold text-gray-700">
                                                                                                Saldo</th>
                                                                                            <th
                                                                                                class="px-3 py-1 text-center font-semibold text-gray-700">
                                                                                                Seleccionar</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody
                                                                                        class="divide-y divide-gray-100">
                                                                                        @foreach ($sucursal['facturas'] ?? [] as $factura)
                                                                                            <tr
                                                                                                wire:key="modal-fac-{{ $factura['key'] }}">
                                                                                                <td
                                                                                                    class="px-3 py-1 text-gray-700">
                                                                                                    {{ $factura['numero'] ?? '' }}
                                                                                                </td>
                                                                                                <td
                                                                                                    class="px-3 py-1 text-gray-700">
                                                                                                    {{ $factura['fecha_emision'] ?? '' }}
                                                                                                </td>
                                                                                                <td
                                                                                                    class="px-3 py-1 text-right font-semibold text-gray-800">
                                                                                                    ${{ number_format((float) ($factura['saldo'] ?? 0), 2, '.', ',') }}
                                                                                                </td>
                                                                                                <td
                                                                                                    class="px-3 py-1 text-center">
                                                                                                    <input
                                                                                                        type="checkbox"
                                                                                                        value="{{ $factura['key'] }}"
                                                                                                        wire:model.live="modalSelectedInvoices"
                                                                                                        class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                                                                                </td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $modalColumnsCount }}"
                                                class="px-4 py-4 text-center text-sm text-gray-600">
                                                Seleccione filtros para visualizar las facturas disponibles.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-gray-200 bg-white px-4 py-3">
                            {{ $this->modalProvidersPaginated->links() }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeAgregarFacturasModal"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Cancelar
                    </button>
                    <button type="button" wire:click="agregarFacturasSeleccionadas"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Agregar seleccionadas
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
