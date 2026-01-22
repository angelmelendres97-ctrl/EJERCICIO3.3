<?php

namespace App\Filament\Resources\SolicitudPagoResource\Pages;

use App\Filament\Resources\SolicitudPagoResource;
use App\Filament\Pages\PresupuestoPagoProveedores;
use App\Models\SolicitudPago;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSolicitudPagos extends ListRecords
{
    protected static string $resource = SolicitudPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('nuevo')
                ->label('Nueva Solicitud de Pago')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->url(PresupuestoPagoProveedores::getUrl()),
        ];
    }

    public function getTabs(): array
    {
        $model = SolicitudPagoResource::getModel();

        return [
            'borrador' => Tab::make('Borrador y Pendiente asignar')
                ->icon('heroicon-o-pencil-square')
                ->badge(fn() => $model::where('estado', 'BORRADOR')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', 'BORRADOR')),

            'aprobada' => Tab::make('Aprobada y enviada')
                ->icon('heroicon-o-check-circle')
                ->badge(fn() => $model::whereIn('estado', ['APROBADA', SolicitudPago::ESTADO_APROBADA_ANULADA])->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('estado', ['APROBADA', SolicitudPago::ESTADO_APROBADA_ANULADA])),

            'anulada' => Tab::make('Anulada')
                ->icon('heroicon-o-x-circle')
                ->badge(fn() => $model::where('estado', 'ANULADA')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', 'ANULADA')),
        ];
    }
}
