<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de pago de facturas</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            position: relative;
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

        .summary {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            padding: 8px;
            margin-top: 12px;
            font-weight: 700;
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
    <h2>REPORTE DE SOLICITUD DE PAGO DE FACTURAS</h2>
    <h3>{{ $descripcion }}</h3>
    <p style="text-align:center; margin-top: 2px; font-size: 12px; color: #4b5563;">Elaborado por: {{ $usuario ?? 'N/D' }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 25%">Proveedor</th>
                <th style="width: 25%">Descripción</th>
                <th style="width: 15%">Área</th>
                <th style="width: 12%" class="text-right">Valor</th>
                <th style="width: 11%" class="text-right">Abono</th>
                <th style="width: 12%" class="text-right">Saldo pendiente</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['proveedor'] }}</td>
                    <td>{{ $row['descripcion'] }}</td>
                    <td>{{ $row['area'] ?? '' }}</td>
                    <td class="text-right">${{ number_format((float) ($row['valor'] ?? 0), 2, '.', ',') }}</td>
                    <td class="text-right">${{ number_format((float) ($row['abono'] ?? 0), 2, '.', ',') }}</td>
                    <td class="text-right">${{ number_format((float) ($row['saldo'] ?? 0), 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No existen facturas seleccionadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div>Total valor: ${{ number_format((float) ($totales['valor'] ?? 0), 2, '.', ',') }}</div>
        <div>Total abono: ${{ number_format((float) ($totales['abono'] ?? 0), 2, '.', ',') }}</div>
        <div>Total saldo pendiente: ${{ number_format((float) ($totales['saldo'] ?? 0), 2, '.', ',') }}</div>
    </div>
</body>

</html>
