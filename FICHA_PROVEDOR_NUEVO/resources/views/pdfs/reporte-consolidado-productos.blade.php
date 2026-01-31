<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte consolidado de productos</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h2 {
            font-size: 13px;
            margin: 0;
            text-align: center;
            font-weight: normal;
        }

        p.subtitle {
            font-size: 11px;
            text-align: center;
            color: #4b5563;
            margin: 6px 0 0;
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

        .section-title {
            margin-top: 16px;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }

        .summary {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            padding: 8px;
            margin-top: 12px;
            font-weight: 700;
            text-align: right;
        }

        .location-table th {
            background: #1f2937;
            color: #f9fafb;
        }
    </style>
</head>

<body>
    <h1>REPORTE CONSOLIDADO DE PRODUCTOS</h1>
    <h2>{{ $descripcionReporte }}</h2>
    <p class="subtitle">Elaborado por: {{ $usuario ?? 'N/D' }}</p>

    @forelse ($productos as $producto)
        <div class="section-title">
            {{ $producto['producto_nombre'] ?? 'Sin nombre' }}
            @if (!empty($producto['producto_codigo']))
                ({{ $producto['producto_codigo'] }})
            @endif
        </div>
        <table>
            <tbody>
                <tr>
                    <th style="width: 18%">Descripción</th>
                    <td style="width: 42%">{{ $producto['producto_descripcion'] ?? 'Sin descripción' }}</td>
                    <th style="width: 14%" class="text-right">Precio prom.</th>
                    <td style="width: 10%" class="text-right">
                        ${{ number_format((float) ($producto['precio_promedio'] ?? 0), 2, '.', ',') }}
                    </td>
                    <th style="width: 8%" class="text-right">Stock total</th>
                    <td style="width: 8%" class="text-right">
                        {{ number_format((float) ($producto['stock_total'] ?? 0), 2, '.', ',') }}
                    </td>
                </tr>
                <tr>
                    <th>Unidad</th>
                    <td>{{ $producto['unidad'] ?? 'N/D' }}</td>
                    <th>Código barra</th>
                    <td colspan="3">{{ $producto['producto_barra'] ?? 'N/D' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="location-table">
            <thead>
                <tr>
                    <th style="width: 14%">Conexión</th>
                    <th style="width: 16%">Empresa</th>
                    <th style="width: 16%">Sucursal</th>
                    <th style="width: 16%">Bodega</th>
                    <th style="width: 10%" class="text-right">Stock</th>
                    <th style="width: 10%" class="text-right">Precio</th>
                    <th style="width: 8%" class="text-right">IVA</th>
                    <th style="width: 10%" class="text-right">Mín/Máx</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($producto['ubicaciones'] ?? [] as $ubicacion)
                    <tr>
                        <td>{{ $ubicacion['conexion_nombre'] ?? $ubicacion['conexion_id'] }}</td>
                        <td>{{ $ubicacion['empresa_nombre'] ?? $ubicacion['empresa_codigo'] }}</td>
                        <td>{{ $ubicacion['sucursal_nombre'] ?? $ubicacion['sucursal_codigo'] }}</td>
                        <td>{{ $ubicacion['bodega_nombre'] ?? $ubicacion['bodega_codigo'] }}</td>
                        <td class="text-right">
                            {{ number_format((float) ($ubicacion['stock'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right">
                            ${{ number_format((float) ($ubicacion['precio'] ?? 0), 2, '.', ',') }}
                        </td>
                        <td class="text-right">
                            {{ number_format((float) ($ubicacion['iva'] ?? 0), 2, '.', ',') }}%
                        </td>
                        <td class="text-right">
                            {{ number_format((float) ($ubicacion['stock_minimo'] ?? 0), 0, '.', ',') }} /
                            {{ number_format((float) ($ubicacion['stock_maximo'] ?? 0), 0, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>No se encontraron productos con los filtros aplicados.</p>
    @endforelse
</body>

</html>
