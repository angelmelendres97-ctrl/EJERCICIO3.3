<?php

namespace App\Filament\Resources\AsignacionProveedorResource\Pages;

use App\Filament\Resources\AsignacionProveedorResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditAsignacionProveedor extends EditRecord
{
    protected static string $resource = AsignacionProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Proforma N°: ' . $this->record->id;
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('regresar')
                ->label('Regresar al Reporte')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
