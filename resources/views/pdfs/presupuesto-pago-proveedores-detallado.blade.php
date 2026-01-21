<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto de pago a proveedores - Detallado</title>
    <style>
        html,
        body,
        .page,
        div,
        span,
        p,
        table,
        thead,
        tbody,
        tfoot,
        tr,
        th,
        td {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h2,
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

        .section-title {
            margin-top: 18px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .provider-card {
            margin-top: 10px;
            border: 1px solid #e5e7eb;
            padding: 8px;
        }

        .provider-header {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .totals-row {
            background: #e5e7eb;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <h1>GRUPO EMPRESARIAL ADMG</h1>
    <h2>SALDOS PENDIENTES A PAGAR A PROVEEDORES</h2>
    <h3>{{ $descripcionReporte }}</h3>
    <p class="text-center" style="margin-top:4px;">Elaborado por: {{ $usuario ?? 'N/D' }}</p>

    @forelse ($empresas as $empresa)
        <div class="section-title">{{ $empresa['conexion_nombre'] }} - {{ $empresa['empresa_nombre'] }}</div>

        @forelse ($empresa['proveedores'] as $proveedor)
            <div class="provider-card">
                <div class="provider-header">
                    {{ $proveedor['nombre'] }}
                    @if (!empty($proveedor['ruc']))
                        · RUC: {{ $proveedor['ruc'] }}
                    @endif
                </div>
                <div style="font-size:11px; color:#4b5563;">
                    {{ $proveedor['descripcion'] }} · Área: {{ $proveedor['area'] }}
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 18%">Sucursal</th>
                            <th style="width: 16%">Factura</th>
                            <th style="width: 16%">Emisión</th>
                            <th style="width: 16%">Vencimiento</th>
                            <th style="width: 16%" class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proveedor['facturas'] as $factura)
                            <tr>
                                <td>{{ $factura['sucursal'] ?? '' }}</td>
                                <td>{{ $factura['numero'] ?? '' }}</td>
                                <td>{{ $factura['fecha_emision'] ?? '' }}</td>
                                <td>{{ $factura['fecha_vencimiento'] ?? '' }}</td>
                                <td class="text-right">
                                    ${{ number_format((float) ($factura['saldo'] ?? 0), 2, '.', ',') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No existen facturas registradas.</td>
                            </tr>
                        @endforelse
                        <tr class="totals-row">
                            <td colspan="4" class="text-right">Subtotal proveedor</td>
                            <td class="text-right">
                                ${{ number_format((float) ($proveedor['subtotal'] ?? 0), 2, '.', ',') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @empty
            <table>
                <tr>
                    <td class="text-center">No existen proveedores seleccionados.</td>
                </tr>
            </table>
        @endforelse

        <table>
            <tr class="totals-row">
                <td class="text-right" style="width:80%;">Subtotal empresa</td>
                <td class="text-right" style="width:20%;">
                    ${{ number_format((float) ($empresa['subtotal'] ?? 0), 2, '.', ',') }}
                </td>
            </tr>
        </table>
    @empty
        <table>
            <tr>
                <td class="text-center">No existen proveedores seleccionados.</td>
            </tr>
        </table>
    @endforelse

    <div style="margin-top:14px; font-weight:700; text-align:right;">
        Total general: ${{ number_format((float) ($total ?? 0), 2, '.', ',') }}
    </div>
</body>

</html>
