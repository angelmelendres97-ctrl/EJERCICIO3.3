<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\ProductoSyncService; // <-- Nuevo Import

use Exception;

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the local record
            $record = static::getModel()::create($data);

            // 2. Attach related data (lineasNegocio)
            $lineasNegocioIds = $this->data['lineasNegocio'] ?? [];
            $record->lineasNegocio()->attach($lineasNegocioIds);

            ProductoSyncService::sincronizar($record, $this->data);

            return $record;
        });
    }

    public function fillTreeSelection($linea, $grupo, $categoria, $marca)
    {
        $this->form->fill([
            'linea' => $linea,
            'grupo' => $grupo,
            'categoria' => $categoria,
            'marca' => $marca,
        ]);
    }
}
