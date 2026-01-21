<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\ProductoSyncService; // <-- Nuevo Import

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => auth()->user()->can('Borrar')),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // 1. Actualizar el registro local
            $record->update($data);

            // 2. Sincronizar datos relacionados (lineasNegocio)
            $lineasNegocioIds = $this->data['lineasNegocio'] ?? [];
            $record->lineasNegocio()->sync($lineasNegocioIds);

            ProductoSyncService::sincronizar($record, $this->data);

            return $record;
        });
    }
}
