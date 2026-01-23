<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de pago de facturas - General</title>
    <style>
        @page {
            margin: 20px 24px 24px 24px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1f2937;
            position: relative;
            margin: 0;
            padding: 10px;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h2 {
            font-size: 14px;
            margin: 0;
            text-align: center;
            font-weight: normal;
        }

        h3 {
            font-size: 12px;
            margin: 0;
            text-align: center;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 12px;
            text-align: center;
            color: #4b5563;
        }

        .section-title {
            margin-top: 18px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .totals-table th {
            background: #1f2937;
            color: #f9fafb;
        }

        /* Columna Abono resaltada */
        .abono-col {
            background: #e0f2fe;
            font-weight: 700;
            color: #0f172a;
        }

        /* Resaltado amarillo final */
        .highlight-yellow {
            background: #fde68a;
            font-weight: 900;
            color: #111827;
        }

        .pay-row td {
            font-weight: 800;
            font-size: 13px;
            border-top: 2px solid #111827 !important;
            background: #fff;
        }

        .pay-highlight {
            background: #fde047;
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 140px;
            text-align: right;
        }

        .signatures-wrap {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .signatures-table td {
            border: none !important;
            padding: 0 18px;
            vertical-align: top;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #111827;
            margin: 0 auto;
            width: 90%;
            height: 1px;
        }

        /* (2) Firmas y roles en MAYÚSCULAS */
        .signature-name {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
        }

        .signature-role {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 400;
            color: #374151;
            text-transform: uppercase;
        }

        .logo {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 90px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="content">
    <img src="{{ public_path('images/LOGOADMG.png') }}" alt="Logo ADMG" class="logo">
    <h1>GRUPO EMPRESARIAL ADMG</h1>
    <h2>REPORTE GENERAL DE SOLICITUD DE PAGO DE FACTURAS</h2>
    <h3>{{ $descripcionReporte }}</h3>
    <p class="subtitle">Elaborado por: {{ $usuario ?? 'N/D' }}</p>

    @php
        // (3) Totales calculados: empresas + compras
        $totalEmpresasValor = 0;
        $totalEmpresasAbono = 0;
        $totalEmpresasSaldo = 0;

        $totalComprasValor = 0;
        $totalComprasAbono = 0;
        $totalComprasSaldo = 0;
    @endphp

    @forelse ($empresas as $empresa)
        @php
            $empresaValor = (float) ($empresa['totales']['valor'] ?? 0);
            $empresaAbono = (float) ($empresa['totales']['abono'] ?? 0);
            $empresaSaldo = (float) ($empresa['totales']['saldo'] ?? 0);

            $totalEmpresasValor += $empresaValor;
            $totalEmpresasAbono += $empresaAbono;
            $totalEmpresasSaldo += $empresaSaldo;
        @endphp

        <div class="section-title">{{ $empresa['conexion_nombre'] }} - {{ $empresa['empresa_nombre'] }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 25%">Proveedor</th>
                    <th style="width: 25%">Descripción</th>
                    <th style="width: 15%">Área</th>
                    <th style="width: 12%" class="text-right">Valor</th>
                    <th style="width: 11%" class="text-right abono-col">Abono</th>
                    <th style="width: 12%" class="text-right">Saldo pendiente</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($empresa['proveedores'] as $proveedor)
                    <tr>
                        <td>
                            {{ $proveedor['nombre'] }}
                            @if (!empty($proveedor['ruc']))
                                <br><span style="font-size:11px; color:#4b5563">RUC: {{ $proveedor['ruc'] }}</span>
                            @endif
                        </td>
                        <td>{{ $proveedor['descripcion'] }}</td>
                        <td>{{ $proveedor['area'] ?? '' }}</td>
                        <td class="text-right">
                            ${{ number_format((float) ($proveedor['totales']['valor'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right abono-col">
                            ${{ number_format((float) ($proveedor['totales']['abono'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right">
                            ${{ number_format((float) ($proveedor['totales']['saldo'] ?? 0), 2, '.', ',') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No existen proveedores seleccionados.</td>
                    </tr>
                @endforelse

                <!-- SUBTOTAL EMPRESA -->
                <tr>
                    <td colspan="3" class="text-right" style="font-weight:700; background:#e5e7eb">
                        SUBTOTAL EMPRESA
                    </td>
                    <td class="text-right" style="font-weight:700; background:#e5e7eb">
                        ${{ number_format($empresaValor, 2, '.', ',') }}
                    </td>
                    <td class="text-right abono-col" style="font-weight:700;">
                        ${{ number_format($empresaAbono, 2, '.', ',') }}
                    </td>
                    <td class="text-right" style="font-weight:700; background:#e5e7eb">
                        ${{ number_format($empresaSaldo, 2, '.', ',') }}
                    </td>
                </tr>
            </tbody>
        </table>
    @empty
        <table>
            <tr>
                <td class="text-center">No existen proveedores seleccionados.</td>
            </tr>
        </table>
    @endforelse

    @if (!empty($compras ?? []))
        @php
            foreach ($compras ?? [] as $grupo) {
                $totalComprasValor += (float) ($grupo['totales']['valor'] ?? 0);
                $totalComprasAbono += (float) ($grupo['totales']['abono'] ?? 0);
                $totalComprasSaldo += (float) ($grupo['totales']['saldo'] ?? 0);
            }
        @endphp

        <div class="section-title text-xl">---------------------------------COMPRAS-------------------------------------</div>
        @foreach ($compras as $grupo)
            <div class="section-title">{{ $grupo['conexion_nombre'] }} - {{ $grupo['empresa_nombre'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%">Descripción del proveedor</th>
                        <th style="width: 16%" class="text-right">Valor</th>
                        <th style="width: 17%" class="text-right abono-col">Abono</th>
                        <th style="width: 17%" class="text-right">Saldo pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grupo['compras'] as $compra)
                        <tr>
                            <td>{{ $compra['descripcion'] ?: 'Compra adicional' }}</td>
                            <td class="text-right">
                                ${{ number_format((float) ($compra['valor'] ?? 0), 2, '.', ',') }}
                            </td>
                            <td class="text-right abono-col">
                                ${{ number_format((float) ($compra['abono'] ?? 0), 2, '.', ',') }}</td>
                            <td class="text-right">
                                ${{ number_format((float) ($compra['saldo'] ?? 0), 2, '.', ',') }}</td>
                        </tr>
                    @endforeach

                    <!-- SUBTOTAL COMPRAS POR EMPRESA -->
                    <tr>
                        <td class="text-right" style="font-weight:700; background:#e5e7eb">SUBTOTAL COMPRAS</td>
                        <td class="text-right" style="font-weight:700; background:#e5e7eb">
                            ${{ number_format((float) ($grupo['totales']['valor'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right abono-col" style="font-weight:700;">
                            ${{ number_format((float) ($grupo['totales']['abono'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right" style="font-weight:700; background:#e5e7eb">
                            ${{ number_format((float) ($grupo['totales']['saldo'] ?? 0), 2, '.', ',') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endif

    @php
        // Gran total = empresas + compras
        $granTotalValor = $totalEmpresasValor + $totalComprasValor;
        $granTotalAbono = $totalEmpresasAbono + $totalComprasAbono; // VALOR/ABONO A PAGAR
        $granTotalSaldo = $totalEmpresasSaldo + $totalComprasSaldo;
    @endphp

    <div class="section-title">Resumen general</div>
    <table class="totals-table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="text-right">Valor</th>
                <th class="text-right abono-col">Abono</th>
                <th class="text-right">Saldo pendiente</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight:700">SUBTOTAL EMPRESAS</td>
                <td class="text-right">${{ number_format($totalEmpresasValor, 2, '.', ',') }}</td>
                <td class="text-right abono-col">${{ number_format($totalEmpresasAbono, 2, '.', ',') }}</td>
                <td class="text-right">${{ number_format($totalEmpresasSaldo, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td style="font-weight:700">SUBTOTAL COMPRAS</td>
                <td class="text-right">${{ number_format($totalComprasValor, 2, '.', ',') }}</td>
                <td class="text-right abono-col">${{ number_format($totalComprasAbono, 2, '.', ',') }}</td>
                <td class="text-right">${{ number_format($totalComprasSaldo, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td style="font-weight:900">TOTAL GENERAL</td>
                <td class="text-right" style="font-weight:900">${{ number_format($granTotalValor, 2, '.', ',') }}</td>
                <td class="text-right abono-col" style="font-weight:900">
                    ${{ number_format($granTotalAbono, 2, '.', ',') }}</td>
                <td class="text-right" style="font-weight:900">${{ number_format($granTotalSaldo, 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- (4) Línea final individual resaltada: ABONO/VALOR A PAGAR = suma de abonos -->
    <table style="margin-top:18px;">
        <tbody>
            <tr class="pay-row">
                <td colspan="3" class="text-right">VALOR A PAGAR:</td>
                <td class="text-right">
                    <span class="pay-highlight">${{ number_format($granTotalAbono, 2, '.', ',') }}</span>
                </td>
            </tr>
        </tbody>
    </table>
        </div>

    <div class="signatures-wrap">
        <table class="signatures-table">
            <tr>
                <td style="width:25%;">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $usuario ?? 'N/D' }}</div>
                    <div class="signature-role">Elaborado por</div>
                </td>

                <td style="width:25%;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Ing. Janeth Machuca</div>
                    <div class="signature-role">Gerente Financiera</div>
                </td>

                <td style="width:25%;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Abg. Jhinson Macucha</div>
                    <div class="signature-role">Gerente General</div>
                </td>

                <td style="width:25%;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Dr. ADMG</div>
                    <div class="signature-role">Presidente Ejecutivo</div>
                </td>
            </tr>
        </table>
    </div>
    </div>
</body>

</html>
