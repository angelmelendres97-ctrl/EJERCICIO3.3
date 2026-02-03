<?php

namespace App\Filament\Resources\AsignacionProveedorResource\Pages;

use App\Filament\Resources\AsignacionProveedorResource;
use Filament\Resources\Pages\ListRecords;

class ListAsignacionProveedors extends ListRecords
{
    protected static string $resource = AsignacionProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
