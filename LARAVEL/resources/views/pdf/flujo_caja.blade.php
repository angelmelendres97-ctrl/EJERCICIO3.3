<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportHeader['title'] ?? 'Flujo de Caja' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            /* Reduced from 9px */
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1,
        .header h2,
        .header h3 {
            margin: 2px 0;
            font-weight: bold;
        }

        .header h1 {
            font-size: 14px;
        }

        .header h2 {
            font-size: 12px;
        }

        .header h3 {
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #777;
            padding: 3px 2px;
            /* Reduced padding */
            vertical-align: middle;
            text-align: right;
            overflow: hidden;
            white-space: nowrap;
        }

        td.label-col {
            text-align: left;
            background-color: #f7f7f7;
            width: 100px;
            /* Reduced width */
            white-space: normal;
            /* Allow wrapping for labels */
        }

        th {
            background-color: #eee;
            text-align: center;
            font-weight: bold;
            font-size: 7px;
            /* Smaller header font */
            white-space: normal;
            /* Allow wrapping for dates */
        }

        /* Row Styles */
        .row-banco td {
            background-color: #f3f4f6;
        }

        /* Enforce bold on all cells for these rows */
        .row-total-ingreso td {
            background-color: #f0fdf4;
            font-weight: bold;
            color: #15803d;
        }

        .row-total-pagar td {
            background-color: #fef2f2;
            font-weight: bold;
            color: #b91c1c;
        }

        .row-flujo td {
            background-color: #eff6ff;
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ mb_strtoupper($reportHeader['empresa'] ?? 'EMPRESA') }}</h1>
        <h2>FLUJO DE CAJA - {{ mb_strtoupper($reportHeader['sucursal'] ?? 'SUCURSAL') }}</h2>
        <h3>Periodo: {{ $reportHeader['periodo'] ?? '' }}</h3>
        <h3>Fecha Reporte: {{ $reportHeader['fecha'] ?? now()->format('d-m-Y') }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th class="label-col" style="text-align: center;">CONCEPTO</th>
                @foreach($dates as $date)
                    <th>
                        @php
                            // Split date ranges "YYYY-MM-DD AL YYYY-MM-DD"
                            // Also split "FECHA YYYY-MM-DD"
                            $formattedDate = str_replace([' AL ', 'FECHA '], ['<br>AL<br>', 'FECHA<br>'], $date);
                        @endphp
                        {!! $formattedDate !!}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <!-- 1. BANCO -->
            <tr class="row-banco">
                <td class="label-col font-bold">BANCO</td>
                @foreach($reportData['banco']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>

            <!-- 2. CUENTAS COBRAR -->
            <tr>
                <td class="label-col">CUENTAS COBRAR</td>
                @foreach($reportData['cxc']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>

            <!-- 3. TOTAL INGRESO -->
            <tr class="row-total-ingreso">
                <td class="label-col">TOTAL INGRESO</td>
                @foreach($reportData['total_ingreso']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>

            <!-- Spacer -->
            <tr>
                <td colspan="{{ count($dates) + 1 }}" style="border:none; height: 5px;"></td>
            </tr>

            <!-- 4. CUENTAS PAGAR -->
            <tr>
                <td class="label-col">CUENTAS PAGAR</td>
                @foreach($reportData['cxp']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>

            <!-- 5. NOMINA -->
            <tr>
                <td class="label-col">NOMINA</td>
                @foreach($reportData['nomina']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>

            <!-- 6. TOTAL A PAGAR -->
            <tr class="row-total-pagar">
                <td class="label-col">TOTAL A PAGAR</td>
                @foreach($reportData['total_pagar']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>

            <!-- Spacer -->
            <tr>
                <td colspan="{{ count($dates) + 1 }}" style="border:none; height: 5px;"></td>
            </tr>

            <!-- 7. FLUJO DE CAJA -->
            <tr class="row-flujo">
                <td class="label-col">FLUJO DE CAJA</td>
                @foreach($reportData['flujo']['values'] ?? [] as $val)
                    <td>{{ $val }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

</body>

</html>