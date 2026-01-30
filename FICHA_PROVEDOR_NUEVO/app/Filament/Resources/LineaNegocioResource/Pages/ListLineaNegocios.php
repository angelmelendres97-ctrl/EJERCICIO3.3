<?php

namespace App\Filament\Resources\LineaNegocioResource\Pages;

use App\Filament\Resources\LineaNegocioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLineaNegocios extends ListRecords
{
    protected static string $resource = LineaNegocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
