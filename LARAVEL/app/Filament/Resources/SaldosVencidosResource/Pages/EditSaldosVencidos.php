<?php

namespace App\Filament\Resources\SaldosVencidosResource\Pages;

use App\Filament\Resources\SaldosVencidosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaldosVencidos extends EditRecord
{
    protected static string $resource = SaldosVencidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
