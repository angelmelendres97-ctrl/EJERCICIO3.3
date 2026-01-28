<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuadro Comparativo</title>
    <style>
        @page {
            margin: 0.5cm;
            font-family: Arial, sans-serif;
        }

        body {
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 3px;
            vertical-align: top;
            word-wrap: break-word;
        }

        /* Header Red Bar */
        .header-bar {
            background-color: #DE1B1B;
            /* Adjust exact red from image */
            color: white;
            text-align: center;
            font-weight: bold;
            padding: 5px;
            border: 1px solid black;
        }

        .section-header {
            background-color: #DE1B1B;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-xs {
            font-size: 9px;
        }

        /* Specific widths matching the image logic */
        .col-id {
            width: 4%;
        }

        .col-qty {
            width: 6%;
        }

        .col-desc {
            width: 20%;
        }

        /* Remaining width divided by providers */

        .no-border-top {
            border-top: none;
        }

        .no-border-bottom {
            border-bottom: none;
        }

        .no-border-left {
            border-left: none;
        }

        .no-border-right {
            border-right: none;
        }

        .bg-gray {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>

    <!-- Main Header -->
    @php
        // 3 Left Cols (Nro Item, Cant Aprobada, Producto)
        // + (Providers * 3 Cols Each: Cant, Costo, Total)
        $totalCols = 3 + (count($providers) * 3);
    @endphp
    <table>
        <tr>
            <td colspan="{{ $totalCols }}" class="header-bar">
                Cuadro Comparativo de Compras
            </td>
        </tr>
        <tr>
            <td colspan="3" class="text-bold">Lugar y Fecha: {{ now()->format('Y-m-d H:i') }}</td>
            <td colspan="{{ count($providers) * 3 }}" class="text-center text-bold">PROFORMA:
                {{ $record->numero_secuencia ?? $record->id }}
            </td>
        </tr>

        <!-- Provider Names Row -->
        <tr>
            <td colspan="3" class="section-header"></td> <!-- Placeholder left -->
            @foreach($providers as $index => $provider)
                <td colspan="3" class="section-header">Proveedor No. {{ $index + 1 }}</td>
            @endforeach
        </tr>

        <!-- Provider Details Rows -->
        <tr>
            <td colspan="3" rowspan="2" class="align-top">
                <!-- Empty for alignment -->
            </td>

            @foreach($providers as $provider)
                <td colspan="3" class="text-xs">
                    <span class="text-bold">Nombre:</span><br>
                    {{ $provider->nombre }}
                </td>
            @endforeach
        </tr>
        <tr>
            @foreach($providers as $provider)
                <td colspan="3" class="text-xs">
                    <span class="text-bold">e-mail:</span> {{ $provider->correo_display }}<br>
                    <span class="text-bold">Contacto:</span> {{ $provider->contacto_display }}<br>
                    @if(isset($providerObservations[$provider->id]) && $providerObservations[$provider->id])
                        <div style="margin-top: 4px; border-top: 1px dashed #ccc; padding-top: 2px;">
                            <span class="text-bold">Obs:</span> {{ $providerObservations[$provider->id] }}
                        </div>
                    @endif
                </td>
            @endforeach
        </tr>

        <!-- Products Header -->
        <tr class="text-center text-bold bg-gray">
            <td class="col-id">Nro Item</td>
            <td class="col-qty">Cant. Aprobada</td>
            <td class="col-desc">Producto</td>

            @foreach($providers as $provider)
                <td>Cant.</td>
                <td>Costo</td>
                <td>Total</td>
            @endforeach
        </tr>

        <!-- Products List -->
        @foreach($products as $index => $product)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ number_format($product->cantidad_aprobada, 2) }}</td>
                <td>
                    {{ $product->producto }}
                </td>

                @foreach($providers as $provider)
                    @php
                        $pivot = $matrix[$product->id][$provider->id] ?? null;
                    @endphp
                    <td class="text-center">
                        {{ $pivot ? number_format($pivot['cantidad_oferta'] ?? 0, 2) : '0.00' }}
                    </td>
                    <td class="text-right">
                        {{ $pivot ? number_format($pivot['valor_unitario_oferta'] ?? 0, 2) : '0.00' }}
                    </td>
                    <td class="text-right">
                        {{ $pivot ? number_format($pivot['total_oferta'] ?? 0, 2) : '0.00' }}
                    </td>
                @endforeach
            </tr>
        @endforeach

        <!-- Totals Section -->
        <tr>
            <td colspan="3" class="text-right text-bold no-border-right"></td>

            @foreach($providers as $id => $provider)
                @php $totals = $grandTotals[$provider->id] ?? ['subtotal' => 0, 'descuento' => 0, 'iva_total' => 0, 'otros_cargos' => 0, 'total' => 0]; @endphp

                <!-- Subtotal Span 3 cols -->
                <td colspan="2" class="text-right text-bold bg-gray">Subtotal</td>
                <td class="text-right">{{ number_format($totals['subtotal'] ?? 0, 2) }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="3" class="no-border-right"></td>
            @foreach($providers as $id => $provider)
                @php $totals = $grandTotals[$provider->id] ?? ['subtotal' => 0, 'descuento' => 0, 'iva_total' => 0, 'otros_cargos' => 0, 'total' => 0]; @endphp
                <td colspan="2" class="text-right text-xs">Descuento %</td>
                <td class="text-right">{{ number_format($totals['descuento'] ?? 0, 2) }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="3" class="no-border-right"></td>
            @foreach($providers as $id => $provider)
                @php $totals = $grandTotals[$provider->id] ?? ['iva_total' => 0]; @endphp
                <td colspan="2" class="text-right text-xs">IVA</td>
                <td class="text-right">{{ number_format($totals['iva_total'] ?? 0, 2) }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="3" class="no-border-right"></td>
            @foreach($providers as $id => $provider)
                @php $totals = $grandTotals[$provider->id] ?? ['otros_cargos' => 0]; @endphp
                <td colspan="2" class="text-right text-xs">Otros Cargos</td>
                <td class="text-right">{{ number_format($totals['otros_cargos'] ?? 0, 2) }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="3" class="no-border-right"></td>
            @foreach($providers as $id => $provider)
                @php $totals = $grandTotals[$provider->id] ?? ['total' => 0]; @endphp
                <td colspan="2" class="text-right text-bold">Total</td>
                <td class="text-right text-bold">{{ number_format($totals['total'] ?? 0, 2) }}</td>
            @endforeach
        </tr>

        <!-- Product Observations -->
        <tr>
            <td colspan="3" class="section-header">Observaciones por Producto</td>
            @foreach($providers as $provider)
                <td colspan="3" class="text-xs align-top">
                    @foreach($products as $product)
                        @php
                            $pivot = $matrix[$product->id][$provider->id] ?? null;
                            $obs = $pivot['observacion_oferta'] ?? null;
                        @endphp
                        @if($obs)
                            <div style="margin-bottom: 4px;">
                                <span class="text-bold">{{ $product->producto }}:</span> {{ $obs }}
                            </div>
                        @endif
                    @endforeach
                </td>
            @endforeach
        </tr>

    </table>



    @if($record->observacion_comparativo)
        <div style="margin-top: 10px; border: 1px solid black; padding: 5px; font-size: 10px;">
            <span class="text-bold">Observación General:</span><br>
            {{ $record->observacion_comparativo }}
        </div>
    @endif

    <br>

    <!-- Signatures -->
    <table style="border: 1px solid black;">
        <tr class="section-header">
            <td style="width: 33%;">Elaborado por:</td>
            <td style="width: 33%;">Autorizado por:</td>
            <td style="width: 34%;">Orden de Compra generada por:</td>
        </tr>
        <tr style="height: 80px;">
            <td style="text-align:center; vertical-align: bottom; height: 80px;">
                <!-- Space for signature -->
                <div style="border-top: 1px solid black; margin: 0 40px;"></div>
                {{ auth()->user()->name ?? 'Usuario' }}<br>
                {{ now()->format('Y-m-d H:i:s') }}<br>
                Gestor de compras
            </td>
            <td style="text-align:center; vertical-align: bottom;">
                <div style="border-top: 1px solid black; margin: 0 40px;"></div>
                Logistica
            </td>
            <td style="text-align:center; vertical-align: bottom;">
                <div style="border-top: 1px solid black; margin: 0 40px;"></div>
                Gerencia
            </td>
        </tr>
    </table>

</body>

</html>
