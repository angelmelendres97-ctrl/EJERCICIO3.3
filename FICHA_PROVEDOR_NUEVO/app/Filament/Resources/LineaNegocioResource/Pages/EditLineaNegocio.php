<?php

namespace App\Filament\Resources\LineaNegocioResource\Pages;

use App\Filament\Resources\LineaNegocioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLineaNegocio extends EditRecord
{
    protected static string $resource = LineaNegocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
