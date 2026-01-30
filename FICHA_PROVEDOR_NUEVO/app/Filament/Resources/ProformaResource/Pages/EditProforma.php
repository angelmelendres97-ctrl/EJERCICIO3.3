<?php

namespace App\Filament\Resources\ProformaResource\Pages;

use App\Filament\Resources\ProformaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProforma extends EditRecord
{
    protected static string $resource = ProformaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => ProformaResource::userIsAdmin())
                ->authorize(fn() => ProformaResource::userIsAdmin()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subtotalGeneral = 0;
        $impuestoGeneral = 0;
        $descuentoGeneral = 0;

        $newDetalles = [];
        if (isset($data['detalles']) && is_array($data['detalles'])) {
            foreach ($data['detalles'] as $detalle) {
                // Asegurar tipos numéricos
                // Asegurar tipos numéricos (eliminar comas si existen)
                $cantidad = floatval(str_replace(',', '', $detalle['cantidad'] ?? 0));
                $costo = floatval(str_replace(',', '', $detalle['costo'] ?? 0));
                $descuento = floatval(str_replace(',', '', $detalle['descuento'] ?? 0));
                $porcentajeIva = floatval(str_replace(',', '', $detalle['impuesto'] ?? 0));

                // Cálculos por ítem
                $subtotalItem = $cantidad * $costo;
                $valorIva = $subtotalItem * ($porcentajeIva / 100);
                $totalItem = ($subtotalItem + $valorIva) - $descuento;

                // Actualizar detalle
                $detalle['valor_impuesto'] = number_format($valorIva, 6, '.', '');
                $detalle['total'] = number_format($totalItem, 6, '.', '');

                $newDetalles[] = $detalle;

                // Acumuladores
                $subtotalGeneral += $subtotalItem;
                $impuestoGeneral += $valorIva;
                $descuentoGeneral += $descuento;
            }
            $data['detalles'] = $newDetalles;
        }

        // Actualizar totales de cabecera
        $data['subtotal'] = round($subtotalGeneral, 2);
        $data['total_impuesto'] = round($impuestoGeneral, 2);
        $data['total_descuento'] = round($descuentoGeneral, 2);
        $data['total'] = round(($subtotalGeneral + $impuestoGeneral) - $descuentoGeneral, 2);

        \Illuminate\Support\Facades\Log::info('Proforma Editing Data:', [
            'raw_detalles' => $data['detalles'] ?? [],
            'calc_subtotal' => $data['subtotal'],
            'calc_total' => $data['total']
        ]);

        return $data;
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
