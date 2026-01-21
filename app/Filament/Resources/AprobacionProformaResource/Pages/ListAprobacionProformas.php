<?php

namespace App\Filament\Resources\AprobacionProformaResource\Pages;

use App\Filament\Resources\AprobacionProformaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAprobacionProformas extends ListRecords
{
    protected static string $resource = AprobacionProformaResource::class;

    protected ?string $heading = 'Aprobación de Proformas';

    public function getTabs(): array
    {
        return [
            'pendientes' => \Filament\Resources\Components\Tab::make('Pendientes')
                ->modifyQueryUsing(fn($query) => $query->where('estado', 'Pendiente')),
            'historial' => \Filament\Resources\Components\Tab::make('Historial')
                ->modifyQueryUsing(fn($query) => $query->where('estado', '!=', 'Pendiente')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // No create action
        ];
    }
}
