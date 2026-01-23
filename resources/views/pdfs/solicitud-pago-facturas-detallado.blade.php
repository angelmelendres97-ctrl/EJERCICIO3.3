<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de pago de facturas - Detallado</title>
    <style>
        @page {
            margin: 20px 24px 120px 24px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1f2937;
            position: relative;
            padding-bottom: 120px;
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
            margin-top: 10px;
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
            margin-top: 16px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .provider-box {
            margin-top: 12px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f8fafc;
        }

        .provider-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            font-weight: 700;
            color: #111827;
        }

        .provider-meta {
            font-weight: 500;
            color: #374151;
        }

        .signatures-wrap {
            position: fixed;
            bottom: 24px;
            left: 0;
            right: 0;
            margin-top: 0;
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

        .signature-name {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            /* (2) Nombres en mayúsculas */
        }

        .signature-role {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 400;
            color: #374151;
            text-transform: uppercase;
            /* (2) Roles en mayúsculas */
        }

        /* (4) Línea final resaltada */
        .pay-row td {
            font-weight: 800;
            font-size: 13px;
            border-top: 2px solid #111827 !important;
            background: #fff;
        }

        .pay-highlight {
            background: #fde047;
            /* amarillo */
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 140px;
            text-align: right;
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
    <img src="{{ public_path('images/LOGOADMG.png') }}" alt="Logo ADMG" class="logo">
    <h1>GRUPO EMPRESARIAL ADMG</h1>
    <h2>REPORTE DETALLADO DE SOLICITUD DE PAGO DE FACTURAS</h2>
    <h3>{{ $descripcionReporte }}</h3>
    <p class="subtitle">Elaborado por: {{ $usuario ?? 'N/D' }}</p>

    @php
        /**
         * (3) Subtotales y totales con empresas + compras
         * Sumamos:
         *  - Total empresas (acumulado de subtotales empresa)
         *  - Total compras (si existen)
         *  - Gran total final = empresas + compras
         *  - Valor a pagar = suma de abonos (empresas + compras)
         */
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

        @forelse ($empresa['proveedores'] as $proveedor)
            <div class="provider-box">
                <div class="provider-header">
                    <div>
                        {{ $proveedor['nombre'] }}
                        @if (!empty($proveedor['ruc']))
                            <span class="provider-meta">· RUC: {{ $proveedor['ruc'] }}</span>
                        @endif
                    </div>
                    <div class="provider-meta">Descripción: {{ $proveedor['descripcion'] ?: 'Sin descripción' }}</div>
                    <div class="provider-meta">Área: {{ $proveedor['area'] ?: 'Sin área' }}</div>
                    <div class="provider-meta">
                        Totales · Valor:
                        ${{ number_format((float) ($proveedor['totales']['valor'] ?? 0), 2, '.', ',') }} ·
                        Abono: ${{ number_format((float) ($proveedor['totales']['abono'] ?? 0), 2, '.', ',') }} ·
                        Saldo: ${{ number_format((float) ($proveedor['totales']['saldo'] ?? 0), 2, '.', ',') }}
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 20%">Sucursal</th>
                            <th style="width: 20%">Factura</th>
                            <th style="width: 20%">Emisión</th>
                            <!-- (1) Se quita Vencimiento -->
                            <th style="width: 20%" class="text-right">Valor</th>
                            <th style="width: 20%" class="text-right">Abono / Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $facturasProveedor = 0;
                        @endphp
                        @foreach ($proveedor['sucursales'] ?? [] as $sucursal)
                            @foreach ($sucursal['facturas'] ?? [] as $factura)
                                @php $facturasProveedor++; @endphp
                                <tr>
                                    <td>{{ $sucursal['sucursal_nombre'] ?? $sucursal['sucursal_codigo'] }}</td>
                                    <td>{{ $factura['numero'] }}</td>
                                    <td>{{ $factura['fecha_emision'] }}</td>
                                    <td class="text-right">
                                        ${{ number_format((float) ($factura['valor'] ?? 0), 2, '.', ',') }}</td>
                                    <td class="text-right">
                                        ${{ number_format((float) ($factura['abono'] ?? 0), 2, '.', ',') }}<br>
                                        <span style="font-size:11px; color:#4b5563">Saldo:
                                            ${{ number_format((float) ($factura['saldo'] ?? 0), 2, '.', ',') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach

                        @if ($facturasProveedor === 0)
                            <tr>
                                <td colspan="5" class="text-center">No hay facturas para este proveedor.</td>
                            </tr>
                        @endif

                        <tr>
                            <td colspan="3" class="text-right" style="font-weight:700; background:#e5e7eb">Subtotal
                                proveedor</td>
                            <td class="text-right" style="font-weight:700; background:#e5e7eb">
                                ${{ number_format((float) ($proveedor['totales']['valor'] ?? 0), 2, '.', ',') }}
                            </td>
                            <td class="text-right" style="font-weight:700; background:#e5e7eb">
                                ${{ number_format((float) ($proveedor['totales']['abono'] ?? 0), 2, '.', ',') }}<br>
                                <span style="font-size:11px; color:#111827">Saldo:
                                    ${{ number_format((float) ($proveedor['totales']['saldo'] ?? 0), 2, '.', ',') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @empty
            <table>
                <tr>
                    <td class="text-center">No existen proveedores para esta empresa.</td>
                </tr>
            </table>
        @endforelse

        <!-- (3) Subtotal por empresa (ya existe, lo ajustamos a 3 columnas numéricas + etiqueta) -->
        <table>
            <tr>
                <td style="width:60%; font-weight:700; background:#f3f4f6">SUBTOTAL EMPRESA</td>
                <td class="text-right" style="width:13%; font-weight:700; background:#f3f4f6">
                    ${{ number_format($empresaValor, 2, '.', ',') }}
                </td>
                <td class="text-right" style="width:13%; font-weight:700; background:#f3f4f6">
                    ${{ number_format($empresaAbono, 2, '.', ',') }}
                </td>
                <td class="text-right" style="width:14%; font-weight:700; background:#f3f4f6">
                    ${{ number_format($empresaSaldo, 2, '.', ',') }}
                </td>
            </tr>
        </table>
    @empty
        <table>
            <tr>
                <td class="text-center">No existen facturas seleccionadas.</td>
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

        <div class="section-title">Compras</div>
        @foreach ($compras as $grupo)
            <div class="section-title">{{ $grupo['conexion_nombre'] }} - {{ $grupo['empresa_nombre'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%">Descripción del proveedor</th>
                        <th style="width: 16%">Factura</th>
                        <th style="width: 17%" class="text-right">Valor</th>
                        <th style="width: 17%" class="text-right">Abono / Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grupo['compras'] as $compra)
                        <tr>
                            <td>{{ $compra['descripcion'] ?: 'Compra adicional' }}</td>
                            <td>{{ $compra['numero'] }}</td>
                            <td class="text-right">
                                ${{ number_format((float) ($compra['valor'] ?? 0), 2, '.', ',') }}
                            </td>
                            <td class="text-right">
                                ${{ number_format((float) ($compra['abono'] ?? 0), 2, '.', ',') }}<br>
                                <span style="font-size:11px; color:#4b5563">Saldo:
                                    ${{ number_format((float) ($compra['saldo'] ?? 0), 2, '.', ',') }}</span>
                            </td>
                        </tr>
                    @endforeach

                    <!-- (3) Subtotal de compras por empresa -->
                    <tr>
                        <td colspan="2" class="text-right" style="font-weight:700; background:#e5e7eb">SUBTOTAL COMPRAS
                        </td>
                        <td class="text-right" style="font-weight:700; background:#e5e7eb">
                            ${{ number_format((float) ($grupo['totales']['valor'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right" style="font-weight:700; background:#e5e7eb">
                            ${{ number_format((float) ($grupo['totales']['abono'] ?? 0), 2, '.', ',') }}<br>
                            <span style="font-size:11px; color:#111827">Saldo:
                                ${{ number_format((float) ($grupo['totales']['saldo'] ?? 0), 2, '.', ',') }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endif

    @php
        $granTotalValor = $totalEmpresasValor + $totalComprasValor;
        $granTotalAbono = $totalEmpresasAbono + $totalComprasAbono; // (4) VALOR A PAGAR = suma de abonos
        $granTotalSaldo = $totalEmpresasSaldo + $totalComprasSaldo;
    @endphp

    <table style="margin-top:18px;">
        <thead>
            <tr>
                <th>Resumen general</th>
                <th class="text-right">Valor</th>
                <th class="text-right">Abono</th>
                <th class="text-right">Saldo pendiente</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight:700">SUBTOTAL EMPRESAS</td>
                <td class="text-right">${{ number_format($totalEmpresasValor, 2, '.', ',') }}</td>
                <td class="text-right">${{ number_format($totalEmpresasAbono, 2, '.', ',') }}</td>
                <td class="text-right">${{ number_format($totalEmpresasSaldo, 2, '.', ',') }}</td>
            </tr>

            <tr>
                <td style="font-weight:700">SUBTOTAL COMPRAS</td>
                <td class="text-right">${{ number_format($totalComprasValor, 2, '.', ',') }}</td>
                <td class="text-right">${{ number_format($totalComprasAbono, 2, '.', ',') }}</td>
                <td class="text-right">${{ number_format($totalComprasSaldo, 2, '.', ',') }}</td>
            </tr>

            <tr>
                <td style="font-weight:800">TOTALES ACUMULADOS</td>
                <td class="text-right" style="font-weight:800">${{ number_format($granTotalValor, 2, '.', ',') }}</td>
                <td class="text-right" style="font-weight:800">${{ number_format($granTotalAbono, 2, '.', ',') }}</td>
                <td class="text-right" style="font-weight:800">${{ number_format($granTotalSaldo, 2, '.', ',') }}</td>
            </tr>

            <!-- (4) Línea final individual: VALOR A PAGAR = total de abonos -->
            <tr class="pay-row">
                <td colspan="3" class="text-right">VALOR A PAGAR:</td>
                <td class="text-right">
                    <span class="pay-highlight">${{ number_format($granTotalAbono, 2, '.', ',') }}</span>
                </td>
            </tr>
        </tbody>
    </table>

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
                    <div class="signature-name">Darwin Santamaria</div>
                    <div class="signature-role">Gerente Administrador</div>
                </td>

                <td style="width:25%;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Cristhian Santamaria</div>
                    <div class="signature-role">Gerente General</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
