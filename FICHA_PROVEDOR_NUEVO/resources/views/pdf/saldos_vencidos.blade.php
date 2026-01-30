<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Saldos Vencidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1,
        .header h2,
        .header h3 {
            margin: 0;
            padding: 2px;
            font-weight: bold;
        }

        .header h1 {
            font-size: 14px;
        }

        .header h2 {
            font-size: 12px;
        }

        .header h3 {
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Company Header */
        .company-header td {
            background-color: #dbeafe;
            /* Blue 100 */
            color: #1e3a8a;
            /* Blue 900 */
            font-weight: bold;
            text-align: center;
        }

        /* Company Summary */
        .company-summary td {
            background-color: #ccfbf1;
            /* Teal 100 */
            color: #134e4a;
            /* Teal 900 */
            font-weight: bold;
        }

        .subtotal-row td {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .total-row td {
            background-color: #d0d0d0;
            font-weight: bold;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ mb_strtoupper($nombresEmpresas) }}</h1>
        <h2>REPORTE SALDOS VENCIDOS - {{ mb_strtoupper($tipoReporte === 'global' ? 'GLOBAL' : 'DETALLADO') }}</h2>
        <h3>Fecha Reporte: {{ now()->format('d-m-Y') }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                @if($tipoReporte === 'global')
                    <th style="width: 50%;">Proveedor</th>
                    <th style="width: 15%;">Total</th>
                    <th style="width: 15%;">Abono</th>
                    <th style="width: 20%;">Saldo</th>
                @else
                    <th style="width: 25%;">Proveedor</th>
                    <th style="width: 12%;">No. Factura</th>
                    <th style="width: 8%;">Fecha Emisión</th>
                    <th style="width: 8%;">Fecha Vence</th>
                    <th style="width: 35%;">Detalle</th>
                    <th style="width: 12%;">Saldo</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($resultados as $row)
                @php
                    $type = $row['type'] ?? 'data';
                @endphp

                @if($type === 'company_header')
                    <tr class="company-header">
                        <td colspan="{{ $tipoReporte === 'global' ? 4 : 6 }}">
                            {{ $row['proveedor'] }}
                        </td>
                    </tr>
                @elseif($type === 'company_summary')
                    <tr class="company-summary">
                        @if($tipoReporte === 'global')
                            <td class="text-right">TOTAL POR PAGAR</td>
                            <td class="text-right">{{ number_format($row['total_factura'], 2) }}</td>
                            <td class="text-right">{{ number_format($row['abono'], 2) }}</td>
                            <td class="text-right">{{ number_format($row['saldo'], 2) }}</td>
                        @else
                            <td colspan="5" class="text-right">TOTAL POR PAGAR (SALDO):</td>
                            <td class="text-right">{{ number_format($row['saldo'], 2) }}</td>
                        @endif
                    </tr>
                @elseif($type === 'summary')
                    {{-- Provider Summary in Detailed View --}}
                    @if($tipoReporte !== 'global')
                        <tr class="subtotal-row">
                            <td colspan="5" class="text-right">{{ $row['proveedor'] }}:</td>
                            <td class="text-right">{{ number_format($row['saldo'], 2) }}</td>
                        </tr>
                    @endif
                @elseif($type === 'data' || $type === 'data_global')
                    <tr>
                        @if($tipoReporte === 'global')
                            <td>{{ $row['proveedor'] }}</td>
                            <td class="text-right">{{ number_format($row['total_factura'], 2) }}</td>
                            <td class="text-right">{{ number_format($row['abono'], 2) }}</td>
                            <td class="text-right">{{ number_format($row['saldo'], 2) }}</td>
                        @else
                            <td>{{ $row['proveedor'] }}</td>
                            <td class="text-center">{{ $row['numero_factura'] }}</td>
                            <td class="text-center">
                                {{ $row['emision'] ? \Carbon\Carbon::parse($row['emision'])->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $row['vencimiento'] ? \Carbon\Carbon::parse($row['vencimiento'])->format('d/m/Y') : '-' }}
                            </td>
                            <td>{{ $row['detalle'] }}</td>
                            <td class="text-right">{{ number_format($row['saldo'], 2) }}</td>
                        @endif
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="{{ $tipoReporte === 'global' ? 3 : 5 }}" class="text-right">TOTAL GENERAL:</td>
                <td class="text-right">
                    {{ number_format(collect($resultados)->whereIn('type', ['data', 'data_global'])->sum('saldo'), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>

</html>