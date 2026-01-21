<?php

namespace App\Filament\Resources\SolicitudPagoResource\Pages;

use App\Filament\Pages\SolicitudPagoFacturas;
use App\Filament\Resources\SolicitudPagoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSolicitudPago extends CreateRecord
{
    protected static string $resource = SolicitudPagoResource::class;

    public function mount(): void
    {
        $this->redirect(SolicitudPagoFacturas::getUrl());
    }
}
