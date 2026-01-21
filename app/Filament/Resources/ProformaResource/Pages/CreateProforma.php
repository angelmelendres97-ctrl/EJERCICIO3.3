<?php

namespace App\Filament\Resources\ProformaResource\Pages;

use App\Filament\Resources\ProformaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProforma extends CreateRecord
{
    protected static string $resource = ProformaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_usuario'] = $data['id_usuario'] ?? auth()->id();

        $subtotalGeneral = 0;
        $impuestoGeneral = 0;
        $descuentoGeneral = 0;

        $newDetalles = [];
        $rawDetalles = $data['detalles'] ?? [];

        \Illuminate\Support\Facades\Log::info('Proforma Raw Details Count:', ['count' => count($rawDetalles)]);

        if (is_array($rawDetalles) && count($rawDetalles) > 0) {
            foreach ($rawDetalles as $detalle) {
                // Ensure numeric values
                $cantidad = floatval(str_replace(',', '', $detalle['cantidad'] ?? 0));
                $costo = floatval(str_replace(',', '', $detalle['costo'] ?? 0));
                $descuento = floatval(str_replace(',', '', $detalle['descuento'] ?? 0));
                $porcentajeIva = floatval(str_replace(',', '', $detalle['impuesto'] ?? 0));

                // Calculation
                $subtotalItem = $cantidad * $costo;
                $valorIva = $subtotalItem * ($porcentajeIva / 100);
                $totalItem = ($subtotalItem + $valorIva) - $descuento;

                // Update detail fields
                $detalle['valor_impuesto'] = round($valorIva, 2);
                $detalle['total'] = round($totalItem, 2);

                // Default approved qty
                $detalle['cantidad_aprobada'] = $detalle['cantidad_aprobada'] ?? $cantidad;

                $newDetalles[] = $detalle;

                // Accumulate globals
                $subtotalGeneral += $subtotalItem;
                $impuestoGeneral += $valorIva;
                $descuentoGeneral += $descuento;
            }
            $data['detalles'] = $newDetalles;

            // Set global totals from calculation
            $data['subtotal'] = round($subtotalGeneral, 2);
            $data['total_impuesto'] = round($impuestoGeneral, 2);
            $data['total_descuento'] = round($descuentoGeneral, 2);
            $data['total'] = round(($subtotalGeneral + $impuestoGeneral) - $descuentoGeneral, 2);
        } else {
            // Fallback: If no details are accessible (e.g. stripped by Filament relationship logic in some contexts),
            // rely on the values sent from the frontend hidden fields.
            \Illuminate\Support\Facades\Log::warning('Proforma Creation: No details found for calculation. Using frontend totals.');

            // Ensure they are floats
            $data['subtotal'] = floatval(str_replace(',', '', $data['subtotal'] ?? 0));
            $data['total_impuesto'] = floatval(str_replace(',', '', $data['total_impuesto'] ?? 0));
            $data['total_descuento'] = floatval(str_replace(',', '', $data['total_descuento'] ?? 0));
            $data['total'] = floatval(str_replace(',', '', $data['total'] ?? 0));
        }

        \Illuminate\Support\Facades\Log::info('Proforma Calculated/Fallback Totals:', [
            'subtotal' => $data['subtotal'],
            'total_impuesto' => $data['total_impuesto'],
            'total_descuento' => $data['total_descuento'],
            'total' => $data['total'],
        ]);

        return $data;
    }
}
