<?php

namespace App\Filament\Resources\ResumenPedidosResource\Pages;

use App\Filament\Resources\ResumenPedidosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditResumenPedidos extends EditRecord
{
    protected static string $resource = ResumenPedidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => ResumenPedidosResource::canDelete($this->record))
                ->authorize(fn() => ResumenPedidosResource::canDelete($this->record)),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->hidden();
    }
}
