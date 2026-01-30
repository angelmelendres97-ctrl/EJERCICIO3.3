<?php

namespace App\Filament\Resources\EgresoSolicitudPagoResource\Pages;

use App\Filament\Resources\EgresoSolicitudPagoResource;
use App\Models\SolicitudPago;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEgresoSolicitudPagos extends ListRecords
{
    protected static string $resource = EgresoSolicitudPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $model = EgresoSolicitudPagoResource::getModel();

        return [
            'todas' => Tab::make('Todas')
                ->icon('heroicon-o-rectangle-stack')
                ->badge(fn() => $model::whereIn('estado', [
                    'APROBADA',
                    SolicitudPago::ESTADO_APROBADA_ANULADA,
                    SolicitudPago::ESTADO_SOLICITUD_COMPLETADA,
                ])->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('estado', [
                    'APROBADA',
                    SolicitudPago::ESTADO_APROBADA_ANULADA,
                    SolicitudPago::ESTADO_SOLICITUD_COMPLETADA,
                ])),
            'aprobadas' => Tab::make('Solicitudes Aprobadas Anuladas')
                ->icon('heroicon-o-exclamation-triangle')
                ->badge(fn() => $model::whereIn('estado', ['APROBADA', SolicitudPago::ESTADO_APROBADA_ANULADA])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('estado', ['APROBADA', SolicitudPago::ESTADO_APROBADA_ANULADA])),
            'completada' => Tab::make('Solicitud Completada')
                ->icon('heroicon-o-check-circle')
                ->badge(fn() => $model::where('estado', SolicitudPago::ESTADO_SOLICITUD_COMPLETADA)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', SolicitudPago::ESTADO_SOLICITUD_COMPLETADA)),
        ];
    }
}
