<?php

namespace App\Filament\Resources\SolicitudPagoResource\Pages;

use App\Filament\Pages\SolicitudPagoFacturas;
use App\Filament\Resources\SolicitudPagoResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSolicitudPago extends ViewRecord
{
    protected static string $resource = SolicitudPagoResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->redirect(
            SolicitudPagoFacturas::getUrl([
                'record' => $this->record,
                'mode' => 'view',
            ]),
        );
    }
}
