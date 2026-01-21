<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resumen de Pedido</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 10px;
        }

        .page {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 0 90px;
        }

        .header-date {
            text-align: right;
            margin-bottom: 5px;
            margin-top: 20px
        }

        .header-date .title-sub {
            font-size: 13px;
            font-weight: 600;
        }

        .header-block {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 32px;
            /* s�belo a 34/36 si quieres m�s */
            font-weight: 800;
            line-height: 1.05;
            margin-bottom: 6px;
        }

        .header-block .title-main {
            font-size: 18px;
            /* antes 16px */
            font-weight: 700;
        }

        .doc-line {
            display: flex;
            justify-content: center;
            align-items: baseline;
            /* alinea bien n�meros/letras grandes */
            gap: 18px;
        }

        /* N�mero */
        .doc-number {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        /* Presupuesto (AZ / PB) */
        .doc-type {
            font-size: 40px;
            /* mismo tama�o que el n�mero */
            font-weight: 1000;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }


        .header-block .title-sub {
            font-size: 14px;
            font-weight: 700;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        td.center {
            text-align: center;
        }

        td.right {
            text-align: right;
        }

        .clearfix {
            clear: both;
        }

        .signatures {
            margin-top: 45px;
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
            padding-top: 35px;
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
            size: A4 landscape;
            margin: 20px;
        }

        /* FOOTER (USANDO ELEMENTO FIJO + CONTADORES) */
        /* Este m�todo funciona en dompdf, mpdf y wkhtmltopdf */
        .pdf-footer {
            position: fixed;
            bottom: 10px;
            /* distancia desde la parte inferior de la p�gina */
            right: 20px;
            /* alineado a la derecha (c�mbialo si quieres centrar) */
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* El contenido se genera con :before usando los contadores */
        .pdf-footer .pagenum:before {
            content: "P�g " counter(page) " / " counter(pages);
        }

        /* Si el motor no soporta counter(pages) mostrar� solo el n�mero de p�gina */
        .pdf-footer .pagenum-alt:before {
            content: "P�g " counter(page);
        }

        .signatures-fixed {
            position: fixed;
            bottom: 45px;
            /* deja espacio para el footer de p�ginas */
            left: 20px;
            right: 20px;
        }

        .signatures-fixed .sign-cell {
            padding-top: 35px;
        }


        /* L�nea n�mero + presupuesto */
        .doc-line {
            display: flex;
            justify-content: center;
            /* centra todo el bloque */
            align-items: center;
            /* centra verticalmente */
            gap: 12px;
            /* espacio entre n�mero y PB */
        }



        .doc-line span:first-child {
            font-size: 18px;
            font-weight: 700;
        }

        .group-title {
            margin-top: 16px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .group-subtitle {
            font-size: 11px;
            font-weight: 600;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <div class="page">

        <div class="header-date">
            <div class="title-main">Machala,
                {{ mb_convert_case(new IntlDateFormatter('es_ES', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'America/Guayaquil', IntlDateFormatter::GREGORIAN, "d 'de' MMMM 'del' yyyy")->format(new DateTime(date('Y-m-d'))), MB_CASE_TITLE, 'UTF-8') }}
            </div>
        </div>

        <div class="header-block">
            <div class="company-name">
                {{ $nombreEmpresaTitulo ?? 'Nombre de Empresa no disponible' }}
            </div>

            <div class="doc-line">
                <span class="doc-number">R. N° {{ str_pad($resumen->codigo_secuencial, 8, '0', STR_PAD_LEFT) }}</span>
                <span class="doc-type"> {{ $resumen->tipo }}</span>
            </div>
        </div>

        @php
            $total_oc = 0;
        @endphp

        @foreach ($groupedDetalles as $grupo)
            <div class="group-title">
                Conexión: {{ $grupo['conexion_nombre'] ?: $grupo['conexion_id'] }}
            </div>
            <div class="group-subtitle">
                Empresa: {{ $grupo['empresa_nombre'] ?: $grupo['empresa_id'] }} | Sucursal: {{ $grupo['sucursal_nombre'] ?: $grupo['sucursal_id'] }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:2px">Items</th>
                        <th style="width:8px">Fecha</th>
                        <th style="width:35px">Proveedor</th>
                        <th style="width:20px">Detalle</th>
                        <th style="width:15px">Pedido</th>
                        <th style="width:12px">N°. Factura o Proforma</th>
                        <th style="width:12px">Orden Compra</th>
                        <th style="width:5px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_grupo = 0;
                    @endphp

                    @foreach ($grupo['detalles'] as $key => $detalle)
                        <tr>
                            <td class="center">{{ $key + 1 }}</td>

                            @php
                                $data_orden_compra = $detalle->ordenCompra;
                                $total_grupo += $data_orden_compra?->total ?? 0;
                            @endphp

                            <td>{{ $data_orden_compra?->fecha_pedido ? date_format(date_create($data_orden_compra->fecha_pedido), 'Y-m-d') : '' }}</td>
                            <td class="left">{{ $data_orden_compra?->proveedor }}</td>
                            <td class="left">{{ $data_orden_compra?->observaciones ? strtoupper($data_orden_compra->observaciones) : '' }}</td>
                            <td class="left">{{ $data_orden_compra?->pedidos_importados }}</td>
                            <td class="left">{{ $data_orden_compra?->numero_factura_proforma ?? '' }}</td>
                            <td class="left">{{ $data_orden_compra?->id ? str_pad($data_orden_compra->id, 8, '0', STR_PAD_LEFT) : '' }}</td>
                            <td class="right">$ {{ number_format($data_orden_compra?->total ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row-flex" style="">
                <div class="col-8">
                    <div class="flex" style="margin-top: 5px; text-align: right !important;">
                        <div class="left-info">
                            <b>TOTAL GRUPO: $ {{ number_format($total_grupo, 2) }} </b>
                            <br>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:80%; border: none; border-collapse: collapse;" class="left"></th>
                                <td style="width:10%" class="right"><b>TOTAL $</b></td>
                                <td style="width:10%" class="right"><b>$ {{ number_format($total_grupo, 2) }}</b></td>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="clearfix"></div>
            </div>

            @php
                $total_oc += $total_grupo;
            @endphp
        @endforeach

        <div class="row-flex" style="">
          {{--   <div class="col-8">
                <div class="flex" style="margin-top: 8px; text-align: right !important;">
                    <div class="left-info">
                        <b>TOTAL GENERAL: $ {{ number_format($total_oc, 2) }} </b>
                        <br>
                    </div>
                </div>
            </div> --}}

            <div class="col-4">
                <table>
                    <thead>
                        <tr>
                            <th style="width:80%; border: none; border-collapse: collapse;" class="left"></th>
                            <td style="width:10%" class="right"><b>TOTAL GENERAL $</b></td>
                            <td style="width:10%" class="right"><b>$ {{ number_format($total_oc, 2) }}</b></td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="clearfix"></div>
        </div>

        @php
            $nombreUsuario = $resumen->usuario?->name ?? '';
            $iniciales = collect(preg_split('/\\s+/', trim($nombreUsuario)))
                ->filter()
                ->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
                ->implode('.');
            $iniciales = $iniciales ? $iniciales . '.' : '';
        @endphp

        <div class="signatures-fixed">

            <table class="sign-table">
                <tr>
                    <td class="sign-cell">
                        <div class="sign-line"></div>
                        <div class="sign-label"><b>ELABORADO POR</b></div>
                        <div class="sign-role"><b>{{ $iniciales }}</b></div>
                    </td>

                    <td class="sign-cell">
                        <div class="sign-line"></div>
                        <div class="sign-label"><b>REVISADO POR</b></div>
                    </td>
                    <td class="sign-cell">
                        <div class="sign-line"></div>
                        <div class="sign-label"><b>AUTORIZADO POR</b></div>
                    </td>

                    <td class="sign-cell">
                        <div class="sign-line"></div>
                        <div class="sign-label"><b>RECIBIDO POR</b></div>
                    </td>


                </tr>
            </table>
        </div>

    </div>

    

</body>


</html>
