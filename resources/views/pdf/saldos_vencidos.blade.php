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
        <h2>REPORTE SALDOS VENCIDOS</h2>
        <h3>Fecha Reporte: {{ now()->format('d-m-Y') }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Proveedor</th>
                <th style="width: 12%;">No. Factura</th>
                <th style="width: 8%;">Fecha Emisión</th>
                <th style="width: 8%;">Fecha Vence</th>
                <th style="width: 35%;">Detalle</th>
                <th style="width: 12%;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
            @endphp

            @foreach($groupedResults as $proveedor => $rows)
                @php
                    $subTotal = collect($rows)->sum('saldo');
                    $grandTotal += $subTotal;
                    $rowCount = count($rows);
                @endphp

                @foreach($rows as $index => $row)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $rowCount }}" style="vertical-align: middle; font-weight: bold;">
                                {{ $proveedor }}
                            </td>
                        @endif
                        <td class="text-center">{{ $row['numero_factura'] }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row['emision'])->format('d/m/Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row['vencimiento'])->format('d/m/Y') }}</td>
                        <td>{{ $row['detalle'] }}</td>
                        <td class="text-right">{{ number_format($row['saldo'], 2) }}</td>
                    </tr>
                @endforeach

                <!-- Subtotal Row -->
                <tr class="subtotal-row">
                    <td colspan="5" class="text-right">SALDO:</td>
                    <td class="text-right">{{ number_format($subTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL:</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>