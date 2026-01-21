<?php

namespace App\Filament\Resources\EgresoSolicitudPagoResource\Pages;

use App\Filament\Resources\EgresoSolicitudPagoResource;
use Filament\Resources\Pages\ListRecords;

class ListEgresoSolicitudPagos extends ListRecords
{
    protected static string $resource = EgresoSolicitudPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
