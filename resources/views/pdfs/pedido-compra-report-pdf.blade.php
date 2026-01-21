<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pedidos de Compra</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 6px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Pedidos de Compra</h1>
        <p>Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nro</th>
                <th>Secuencial</th>
                <th>Responsable</th>
                <th>Fecha Pedido</th>
                <th>Fecha Entrega</th>
                <th>Lugar Entrega</th>
                <th>Para Uso De</th>
                <th>Tipo Pedido</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ str_pad($record->pedi_cod_pedi, 8, "0", STR_PAD_LEFT) }}</td>
                    <td>{{ strtoupper($record->pedi_res_pedi) }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->pedi_fec_pedi)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->pedi_fec_entr)->format('d/m/Y') }}</td>
                    <td>{{ strtoupper($record->pedi_lug_entr) }}</td>
                    <td>{{ strtoupper($record->pedi_uso_pedi) }}</td>
                    <td>{{ strtoupper($record->pedi_tipo_pedi) }}</td>
                    <td>{{ strtoupper($record->pedi_est_prof) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
