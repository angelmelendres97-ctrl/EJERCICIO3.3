<?php

namespace App\Filament\Resources\AprobacionProformaResource\Pages;

use App\Filament\Resources\AprobacionProformaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditAprobacionProforma extends EditRecord
{
    protected static string $resource = AprobacionProformaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No delete action desired for approval workflow
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Aprobar')
                ->submit('save'),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. Set Status to Aprobado
        $data['estado'] = 'Aprobado';

        // 2. Default Approved Quantity if empty
        $newDetalles = [];
        $rawDetalles = $data['detalles'] ?? [];

        if (is_array($rawDetalles)) {
            foreach ($rawDetalles as $detalle) {
                $cantidad = floatval(str_replace(',', '', $detalle['cantidad'] ?? 0));

                // If cantidad_aprobada is null or empty, assume we approve the requested amount
                $aprobadaKey = $detalle['cantidad_aprobada'] ?? null;
                if ($aprobadaKey === null || $aprobadaKey === '') {
                    $aprobada = $cantidad;
                } else {
                    $aprobada = floatval(str_replace(',', '', $aprobadaKey));
                }

                $detalle['cantidad_aprobada'] = $aprobada;

                // PRESERVE original calculated values if possible, 
                // or recalculate ONLY if we have valid inputs.
                // Since fields like 'costo' are disabled in the form, they might come as 0 or null if not dehydrated.
                // We should NOT overwrite the detail totals if we don't have the cost.
                // However, the detail total is not as critical to save here as the Header Total.
                // Let's just update the approval quantity.

                $newDetalles[] = $detalle;
            }
            $data['detalles'] = $newDetalles;
        }

        // 3. Header Totals
        // CRITICAL FIX: Do NOT overwrite totals with Recalculated 0s if we don't have the data.
        // We trust the existing totals in the database unless we have valid data to change them.
        // Since this is just an Approval step, we shouldn't be changing the Financial Totals (Price * Requested Qty) anyway.
        // So we explicitly UNSET them from $data if they are present but might be wrong, 
        // OR we just return $data without touching them (if they are not in $data, they won't be updated).

        // However, the form has hidden fields for them. If those hidden fields dehydrate to 0, they will overwrite.
        // We should check if they are valid.

        $subtotal = floatval(str_replace(',', '', $data['subtotal'] ?? 0));

        // If the incoming subtotal is 0 but we know the proforma likely has a value,
        // we should prevent overwriting it. 
        // But how do we distinguish "Real 0" from "Missing Data 0"?
        // A proforma usually has >0 total.

        // Better strategy: Remove total fields from the array if they are 0, 
        // assuming we don't want to zero them out during approval.
        // But what if it really is 0? (Unlikely for a valid proforma).

        if ($subtotal == 0) {
            unset($data['subtotal']);
            unset($data['total_impuesto']);
            unset($data['total_descuento']);
            unset($data['total']);
        }

        return $data;
    }
}
