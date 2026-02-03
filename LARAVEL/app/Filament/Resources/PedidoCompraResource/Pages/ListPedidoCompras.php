<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPedidoCompras extends ListRecords
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reporte')
                ->label('Ir al Reporte')
                ->url(static::getResource()::getUrl('reporte'))
                ->color('primary')
                ->icon('heroicon-o-document-chart-bar'),
        ];
    }
}
