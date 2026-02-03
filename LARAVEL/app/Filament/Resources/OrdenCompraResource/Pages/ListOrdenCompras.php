<?php

namespace App\Filament\Resources\OrdenCompraResource\Pages;

use App\Filament\Resources\OrdenCompraResource;
use App\Filament\Resources\ResumenPedidosResource;
use App\Filament\Resources\OrdenCompraResource\Widgets\ResumenPedidosTableWidget;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrdenCompras extends ListRecords
{
    protected static string $resource = OrdenCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('crear_resumen')
                ->label('Crear resumen')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn() => ResumenPedidosResource::getUrl('create'))
                ->openUrlInNewTab(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todas' => Tab::make('Todas'),
            'anuladas' => Tab::make('Anuladas')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('anulada', true)),
            'presupuesto_pb' => Tab::make('Presupuesto PB')
                ->icon('heroicon-o-banknotes')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('presupuesto', 'PB')),
            'presupuesto_az' => Tab::make('Presupuesto AZ')
                ->icon('heroicon-o-sparkles')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('presupuesto', 'AZ')),
        ];
    }

   /*  protected function getFooterWidgets(): array
    {
        return [
            ResumenPedidosTableWidget::class,
        ];
    } */
}
