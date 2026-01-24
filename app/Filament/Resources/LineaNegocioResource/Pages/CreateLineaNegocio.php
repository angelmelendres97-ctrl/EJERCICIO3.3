<?php

namespace App\Filament\Resources\LineaNegocioResource\Pages;

use App\Filament\Resources\LineaNegocioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLineaNegocio extends CreateRecord
{
    protected static string $resource = LineaNegocioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['id']);

        return $data;
    }
}
