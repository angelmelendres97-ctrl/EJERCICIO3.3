<?php

namespace App\Filament\Resources\SaldosVencidosResource\Pages;

use App\Filament\Resources\SaldosVencidosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSaldosVencidos extends ListRecords
{
    protected static string $resource = SaldosVencidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
