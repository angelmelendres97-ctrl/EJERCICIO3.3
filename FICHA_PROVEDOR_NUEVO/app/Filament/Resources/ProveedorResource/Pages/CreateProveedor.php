<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\ProveedorSyncService; // <-- Nuevo Import
use App\Services\UafeService;


class CreateProveedor extends CreateRecord
{
    protected static string $resource = ProveedorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the local record
            $record = static::getModel()::create($data);

            // 2. Attach related data (lineasNegocio)
            $lineasNegocioIds = $this->data['lineasNegocio'] ?? [];
            $record->lineasNegocio()->attach($lineasNegocioIds);

            ProveedorSyncService::sincronizar($record, $this->data);

            if (! empty($this->data['uafe_documentos'])) {
                UafeService::syncDocumentos($record, $this->data['uafe_documentos'], auth()->id());
            }

            UafeService::enviarNotificacion($record);

            return $record;
        });
    }
}
