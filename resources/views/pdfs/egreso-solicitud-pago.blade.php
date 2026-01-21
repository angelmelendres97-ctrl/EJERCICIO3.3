<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de egreso</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1, h2, h3 {
            margin: 0 0 8px 0;
        }

        .section {
            margin-bottom: 24px;
        }

        .meta {
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f3f4f6;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Reporte de egreso</h1>
    <div class="meta">
        <div><strong>Solicitud:</strong> #{{ $solicitudId }}</div>
        <div><strong>Generado:</strong> {{ $generatedAt->format('Y-m-d H:i') }}</div>
    </div>

    @foreach ($reportes as $reporte)
        <div class="section">
            <h2>Asiento diario</h2>
            @if ($reporte['asiento'])
                <div class="meta">
                    <div><strong>Empresa:</strong> {{ $reporte['context']['empresa'] ?? '' }}</div>
                    <div><strong>Sucursal:</strong> {{ $reporte['context']['sucursal'] ?? '' }}</div>
                    <div><strong>Asiento:</strong> {{ $reporte['asiento']->asto_cod_asto ?? '' }}</div>
                    <div><strong>Fecha:</strong> {{ $reporte['asiento']->asto_fec_asto ?? '' }}</div>
                    <div><strong>Beneficiario:</strong> {{ $reporte['asiento']->asto_ben_asto ?? '' }}</div>
                    <div><strong>Detalle:</strong> {{ $reporte['asiento']->asto_det_asto ?? '' }}</div>
                </div>
            @else
                <p class="muted">No se encontró información del asiento en SAE.</p>
            @endif

            <h3>Detalle diario</h3>
            <table>
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Detalle</th>
                        <th class="text-right">Débito</th>
                        <th class="text-right">Crédito</th>
                        <th class="text-right">Débito Ext.</th>
                        <th class="text-right">Crédito Ext.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reporte['diario'] as $linea)
                        <tr>
                            <td>{{ $linea->dasi_cod_cuen ?? '' }}</td>
                            <td>{{ $linea->dasi_det_asi ?? '' }}</td>
                            <td class="text-right">{{ number_format((float) ($linea->dasi_dml_dasi ?? 0), 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format((float) ($linea->dasi_cml_dasi ?? 0), 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format((float) ($linea->dasi_dme_dasi ?? 0), 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format((float) ($linea->dasi_cme_dasi ?? 0), 2, '.', ',') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Sin movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h3>Directorio</h3>
            <table>
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Factura</th>
                        <th>Detalle</th>
                        <th>Fecha Vence</th>
                        <th class="text-right">Débito</th>
                        <th class="text-right">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reporte['directorio'] as $linea)
                        <tr>
                            <td>{{ $linea->dire_nom_clpv ?? '' }}</td>
                            <td>{{ $linea->dir_num_fact ?? '' }}</td>
                            <td>{{ $linea->dir_detalle ?? '' }}</td>
                            <td>{{ $linea->dir_fec_venc ?? '' }}</td>
                            <td class="text-right">{{ number_format((float) ($linea->dir_deb_ml ?? 0), 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format((float) ($linea->dir_cre_ml ?? 0), 2, '.', ',') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Sin entradas en directorio.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
