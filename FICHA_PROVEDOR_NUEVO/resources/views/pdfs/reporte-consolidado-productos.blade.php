<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte consolidado de productos</title>
    <style>
        html, body,
        .page,
        div, span, p,
        table, thead, tbody, tfoot, tr, th, td {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            font-size: 16px;
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

        h3 {
            font-size: 11px;
            margin: 0;
            text-align: center;
            font-weight: normal;
            color: #4b5563;
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
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 11px;
            text-align: center;
            color: #4b5563;
        }

        .summary {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            padding: 8px;
            margin-top: 12px;
            font-weight: 700;
            text-align: right;
        }

        .logo {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 80px;
            height: auto;
        }
    </style>
</head>

<body>
    <img src="{{ public_path('images/LOGOADMG.png') }}" alt="Logo ADMG" class="logo">

    <h1>Grupo Empresarial ADMG</h1>
    <h2>Reporte consolidado de productos</h2>
    <h3>{{ $descripcionReporte }}</h3>
    <p class="subtitle">Elaborado por: {{ $usuario ?? 'N/D' }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 10%">Conexión</th>
                <th style="width: 10%">Empresa</th>
                <th style="width: 10%">Sucursal</th>
                <th style="width: 10%">Bodega</th>
                <th style="width: 8%">Código</th>
                <th style="width: 20%">Producto</th>
                <th style="width: 18%">Descripción</th>
                <th style="width: 6%" class="text-right">Precio</th>
                <th style="width: 4%" class="text-right">Stock</th>
                <th style="width: 4%" class="text-right">Mín.</th>
                <th style="width: 4%" class="text-right">Máx.</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $producto)
                <tr>
                    <td>{{ $producto['conexion_nombre'] ?? '' }}</td>
                    <td>{{ $producto['empresa_nombre'] ?? $producto['empresa_codigo'] ?? '' }}</td>
                    <td>{{ $producto['sucursal_nombre'] ?? $producto['sucursal_codigo'] ?? '' }}</td>
                    <td>{{ $producto['bodega_nombre'] ?? $producto['bodega_codigo'] ?? '' }}</td>
                    <td>{{ $producto['producto_codigo'] ?? '' }}</td>
                    <td>{{ $producto['producto_nombre'] ?? '' }}</td>
                    <td>{{ $producto['producto_descripcion'] ?? 'Sin descripción registrada' }}</td>
                    <td class="text-right">${{ number_format((float) ($producto['precio'] ?? 0), 4, '.', ',') }}</td>
                    <td class="text-right">{{ number_format((float) ($producto['stock'] ?? 0), 2, '.', ',') }}</td>
                    <td class="text-right">{{ number_format((float) ($producto['stock_minimo'] ?? 0), 2, '.', ',') }}</td>
                    <td class="text-right">{{ number_format((float) ($producto['stock_maximo'] ?? 0), 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">No hay datos disponibles.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total de registros: {{ number_format($totalProductos ?? 0) }}
    </div>
</body>

</html>
