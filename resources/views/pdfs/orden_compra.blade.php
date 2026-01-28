<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Orden de Compra - Formato</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            line-height: 1.35;
            padding: 20px;
        }

        .page {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            /* Deja espacio para el footer fijo (políticas + firmas) */
            padding: 0px 0px 260px;
            position: relative;
        }

        .header-block {
            text-align: center;
            margin-bottom: 10px;
        }

        .header-block .title-main {
            font-size: 16px;
            font-weight: 700;
        }

        .header-block .title-sub {
            font-size: 14px;
            font-weight: 700;
        }

        .header-block .title-insub {
            font-size: 12px;
            font-weight: 700;
        }

        .stamp {
            position: absolute;
            top: 10px;
            right: 10px;
            border: 2px solid #000;
            padding: 6px 10px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Cabecera info (2 columnas) */
        .row {
            width: 100%;
        }

        .flex {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .left-info {
            width: 100%;
            font-size: 11px;
        }

        .col-7 {
            width: 60%;
            float: left;
        }

        .col-5 {
            width: 40%;
            float: left;
        }

        .clearfix {
            clear: both;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            /* más espacio arriba */
            margin-bottom: 25px;
            /* más espacio abajo */
            font-size: 13px;
        }

        table th,
        table td {
            border: 1px solid #333;
            padding: 4px;
            font-size: 11px;
        }

        table th {
            background: #f2f2f2;
            font-weight: 700;
            font-size: 11px;
        }

        th.left {
            text-align: left;
        }

        td.center {
            text-align: center;
        }

        td.right {
            text-align: right;
        }

        /* ======= BLOQUE RESUMEN (Observaciones + mini-info + totales) ======= */
        .resume-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .resume-wrap td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .resume-left {
            width: 66%;
            padding-right: 8px;
        }

        .resume-right {
            width: 34%;
        }

        .obs-box {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11px;
            min-height: 35px;
        }

        .obs-text {
            margin-top: 4px;
            white-space: pre-line;
        }

        .mini-info {
            width: 65%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 11px;
        }

        .mini-info th,
        .mini-info td {
            border: 1px solid #333;
            padding: 4px;
        }

        .mini-info th {
            background: #f2f2f2;
            text-align: left;
            width: 50%;
        }

        .totales {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 0;
        }

        .totales th,
        .totales td {
            border: 1px solid #333;
            padding: 4px;
        }

        .totales th {
            background: #f2f2f2;
            text-align: left;
        }

        /* ======= FOOTER FIJO ======= */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }

        .policies {
            margin-top: 0;
            font-size: 11px;
        }

        /* Firmas: SIN “marco contenedor” */
        .signatures {
            margin-top: 40px;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .sign-table td {
            border: none;
            padding: 0;
        }

        .sign-cell {
            width: 50%;
            text-align: center;
            padding-top: 55px;
            /* MÁS ESPACIO PARA FIRMAR */
        }

        .sign-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 6px auto;
            height: 1px;
        }

        .sign-label {
            font-size: 11px;
            margin-bottom: 3px;
        }

        .sign-role {
            font-size: 11px;
        }

        @page {
            size: A4 portrait;
            margin: 20px;
        }

        .detalle-table {
            margin-top: 30px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="page">

        <div class="stamp">{{ $ordenCompra->presupuesto }}</div>

        <div class="header-block">
            <div class="title-main">{{ $nombreEmpresaTitulo ?? 'Nombre de Empresa no disponible' }}</div>
            <div class="title-sub">ORDEN DE COMPRA N° {{ str_pad($ordenCompra->id, 8, '0', STR_PAD_LEFT) }}</div>

            @php
                $nombre_formato_oc = $ordenCompra->formato == 'P' ? 'Proforma' : 'Factura';
                $numero_formato_oc = $ordenCompra->numero_factura_proforma ?? '';
            @endphp

            <div class="title-sub">{{ $nombre_formato_oc }} N° {{ $numero_formato_oc }}</div>
        </div>

        <!-- CABECERA INFO -->
        <div class="row">
            <div class="col-7">
                <div class="flex">
                    <div class="left-info">
                        <b>Ciudad y fecha: </b> MACHALA {{ $ordenCompra->fecha_pedido->format('d/m/Y') }}<br>
                        <b>Proveedor: </b> {{ $ordenCompra->proveedor }}<br>
                        <b>Para uso de: </b> {{ $ordenCompra->uso_compra }}<br>
                        <b>Solicitado por: </b> {{ $ordenCompra->solicitado_por }}<br>
                        {{-- <b>Lugar de entrega: </b> IMBUESA<br> --}}
                    </div>
                </div>
            </div>

            <div class="col-5">
                <div class="flex">
                    <div class="left-info">
                        <b>Plazo de entrega: </b> {{ $ordenCompra->fecha_entrega->format('d/m/Y') }}<br>
                        <b>Dirección: </b> {{ $ordenCompra->direccion ?? '' }}<br>
                        <b>Teléfono: </b> {{ $ordenCompra->telefono ?? '0998612034' }}<br>
                        <b>Forma de pago: </b> {{ $ordenCompra->forma_pago ?? '' }}<br>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>
        </div>

        <!-- DETALLE -->
        <table class="detalle-table">
            <thead>
                <tr>
                    <th style="width:20px">#</th>
                    <th style="width:100px">Código</th>
                    <th>Descripción</th>
                    <th style="width:40px">Unid.</th>
                    <th style="width:60px">Cant.</th>
                    <th style="width:60px">Precio U.</th>
                    <th style="width:60px">Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($ordenCompra->detalles as $key => $detalle)
                    @php
                        $auxiliarData = null;
                        if (!empty($detalle->detalle)) {
                            $decodedDetalle = json_decode($detalle->detalle, true);
                            if (
                                is_array($decodedDetalle) &&
                                (isset($decodedDetalle['codigo']) || isset($decodedDetalle['descripcion']))
                            ) {
                                $auxiliarData = $decodedDetalle;
                            }
                        }

                        $codigoMostrar = $auxiliarData['codigo'] ?? $detalle->codigo_producto;
                        $descripcionBase = $auxiliarData['descripcion'] ?? $detalle->producto;
                        $nombreProducto = $auxiliarData['descripcion_auxiliar']
                            ?? ($productoNombres[$detalle->codigo_producto] ?? null);

                        if ($nombreProducto && $descripcionBase) {
                            $descripcionMostrar = trim($nombreProducto . ' - ' . $descripcionBase);
                        } else {
                            $descripcionMostrar = $nombreProducto ?: $descripcionBase;
                        }
                    @endphp
                    <tr>
                        <td class="center">{{ $key + 1 }}</td>
                        <td>{{ $codigoMostrar }}</td>
                        <td>{{ $descripcionMostrar }}</td>
                        <td class="center">{{ $detalle->unidad ?? 'UN' }}</td>
                        <td class="center">{{ $detalle->cantidad }}</td>
                        @php
                            // Asegura números válidos
                            $cantidadImp = (float) ($detalle->cantidad ?? 0);
                            $precioUnitImp = (float) ($detalle->costo ?? 0);

                            // Total calculado: precio unitario * cantidad
                            $totalImp = $cantidadImp * $precioUnitImp;
                        @endphp

                        <td class="right">${{ number_format($precioUnitImp, 6, '.', '') }}</td>
                        <td class="right">${{ number_format($totalImp, 2, '.', '') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $nombre_tipo_oc = '';
            if ($ordenCompra->tipo_oc == 'REEMB') {
                $nombre_tipo_oc = 'REEMBOLSO';
            } elseif ($ordenCompra->tipo_oc == 'COMPRA') {
                $nombre_tipo_oc = 'COMPRA';
            } elseif ($ordenCompra->tipo_oc == 'PAGO') {
                $nombre_tipo_oc = 'PAGO';
            } elseif ($ordenCompra->tipo_oc == 'REGUL') {
                $nombre_tipo_oc = 'REGULARIZACIÓN';
            } elseif ($ordenCompra->tipo_oc == 'CAJAC') {
                $nombre_tipo_oc = 'CAJA CHICA';
            }

            $numero_pedidos = $ordenCompra->pedidos_importados ?? '';
            $txt_pedidos = '';

            if (!empty($numero_pedidos)) {
                if (str_contains($numero_pedidos, ',')) {
                    $array_pedidos = explode(',', $numero_pedidos);
                    foreach ($array_pedidos as $numero_pedi) {
                        $txt_pedidos .= trim($numero_pedi) . ' - ';
                    }
                    $txt_pedidos = rtrim($txt_pedidos, ' - ');
                } else {
                    $txt_pedidos = trim($numero_pedidos);
                }
            }
        @endphp

        @php
            // ==============================
            // RESUMEN POR TARIFA (BASE/DESC/IVA) - SOLO TARIFAS EXISTENTES
            // ==============================
            $basePorIva = [];
            $descPorIva = [];
            $ivaPorIva = [];

            foreach ($ordenCompra->detalles as $d) {
                $rate = (float) ($d->impuesto ?? 0);

                $cantidad = (float) ($d->cantidad ?? 0);
                $costo = (float) ($d->costo ?? 0);
                $descuento = (float) ($d->descuento ?? 0);

                $base = $cantidad * $costo;

                $basePorIva[$rate] = ($basePorIva[$rate] ?? 0) + $base;
                $descPorIva[$rate] = ($descPorIva[$rate] ?? 0) + $descuento;

                // IVA sobre base neta (base - descuento)
                $baseNeta = max(0, $base - $descuento);
                $ivaPorIva[$rate] = ($ivaPorIva[$rate] ?? 0) + $baseNeta * ($rate / 100);
            }

            // Tarifas existentes: SOLO las que tienen base > 0
            $tarifas = collect($basePorIva)
                ->filter(fn($base) => round((float) $base, 6) > 0)
                ->keys()
                ->map(fn($r) => (float) $r)
                ->values();

            // Orden personalizado (como tu formato): 15, 0, 5, 8, 18, ... (si existen)
            $ordenPreferido = collect([15, 0, 5, 8, 18]);
            $tarifas = $ordenPreferido
                ->intersect($tarifas)
                ->merge($tarifas->diff($ordenPreferido)->sort())
                ->values();

            // Totales generales
            $subtotalGeneral = array_sum($basePorIva);
            $descuentoGeneral = array_sum($descPorIva);
            $ivaGeneral = array_sum($ivaPorIva);
            $totalGeneral = $subtotalGeneral - $descuentoGeneral + $ivaGeneral;

            // Helpers
            $fmtRate = fn($r) => rtrim(rtrim(number_format((float) $r, 2, '.', ''), '0'), '.');
            $fmtMoney = fn($n) => number_format((float) $n, 2, '.', ',');
        @endphp


        <!-- RESUMEN (OBS + MINI INFO + TOTALES) -->
        <table class="resume-wrap">
            <tr>
                <!-- IZQUIERDA -->
                <td class="resume-left">
                    <div class="obs-box">
                        <b>Observaciones:</b>
                        <div class="obs-text">{{ $ordenCompra->observaciones }}</div>
                    </div>

                    <table class="mini-info">
                        <tr>
                            <th>Tipo de orden de compra</th>
                            <td>{{ $nombre_tipo_oc }}</td>
                        </tr>

                        @if ($ordenCompra->tipo_oc == 'REEMB')
                            <tr>
                                <th>Nombre del reembolso</th>
                                <td>{{ $ordenCompra->nombre_reembolso ?? '' }}</td>
                            </tr>
                        @endif

                        <tr>
                            <th>Números de pedido</th>
                            <td>{{ $txt_pedidos }}</td>
                        </tr>
                    </table>
                </td>

                <!-- DERECHA -->
                <td class="resume-right">
                    <table class="totales">

                        {{-- SUBTOTAL GENERAL --}}
                        <tr>
                            <th class="left">SUBTOTAL</th>
                            <td class="right">$ {{ $fmtMoney($subtotalGeneral) }}</td>
                        </tr>
                        <tr>
                            <th class="left">TOTAL DESCUENTO</th>
                            <td class="right">$ {{ $fmtMoney($descuentoGeneral) }}</td>
                        </tr>

                        {{-- TARIFA + IVA (intercalados, como factura real) --}}
                        @foreach ($basePorIva as $rate => $base)
                            @if (round($base, 6) > 0)
                                <tr>
                                    <th class="left">TARIFA {{ $fmtRate($rate) }} %</th>
                                    <td class="right">$ {{ number_format($base, 2) }}</td>
                                </tr>

                                <tr>
                                    <th class="left">IVA {{ $fmtRate($rate) }} %</th>
                                    <td class="right">$ {{ number_format($ivaPorIva[$rate] ?? 0, 2) }}</td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- TOTAL --}}
                        <tr>
                            <th class="left">TOTAL</th>
                            <td class="right">$ {{ $fmtMoney($totalGeneral) }}</td>
                        </tr>

                    </table>



                </td>
            </tr>
        </table>

        <!-- FOOTER FIJO -->
        <div class="footer">
            @php
                $empresaNombre = $nombreEmpresaTitulo ?? ($ordenCompra->empresa->nombre_empresa ?? 'LA EMPRESA');
                $empresaNombreUpper = mb_strtoupper($empresaNombre, 'UTF-8');
            @endphp

            <div class="policies">
                <b>POLÍTICAS PARA LA ORDEN DE COMPRA:</b><br>
                A) Este documento es válido solamente si está firmado por la persona autorizada para aprobar
                compras.<br>
                B) El proveedor será responsable de revisar los precios establecidos en la presente orden de compra,
                que estén acordes a los cotizados, y no podrán variar según el tiempo de vigencia establecido en la
                cotización.<br>
                C) El proveedor será responsable de cumplir con las especificaciones y el tiempo ofrecido y acordado con
                <b>{{ $empresaNombreUpper }}</b>. En caso de modificar las especificaciones, deberá informar a
                <b>{{ $empresaNombreUpper }}</b> para que esta resuelva si aprueba o no tal modificación.<br>
                D) En caso de incumplimiento de los tiempos de entrega, <b>{{ $empresaNombreUpper }}</b> decidirá si
                acepta o no el pedido y, en caso de recibirlo, podrá multar al proveedor, descontando costos de
                afectación
                por la no recepción de la mercadería en la fecha acordada.<br>
            </div>

            <div class="signatures">
                @php
                    $nombreUsuario = $ordenCompra->usuario?->name ?? '';
                    $iniciales = collect(preg_split('/\\s+/', trim($nombreUsuario)))
                        ->filter()
                        ->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
                        ->implode('.');
                    $iniciales = $iniciales ? $iniciales . '.' : '';
                @endphp
                <table class="sign-table">
                    <tr>
                        <td class="sign-cell">
                            <div class="sign-line"></div>
                            <div class="sign-label"><b>Elaborado por</b></div>
                            <div class="sign-role"><b>{{ $iniciales ?: 'COMPRAS' }}</b></div>
                        </td>

                        <td class="sign-cell">
                            <div class="sign-line"></div>
                            <div class="sign-label"><b>Aprobado por</b></div>
                            <div class="sign-role"><b>GERENCIA</b></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</body>

</html>
