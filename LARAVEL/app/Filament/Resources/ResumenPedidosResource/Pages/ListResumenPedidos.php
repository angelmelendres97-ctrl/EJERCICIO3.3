<?php

namespace App\Filament\Resources\ResumenPedidosResource\Pages;

use App\Filament\Resources\ResumenPedidosResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListResumenPedidos extends ListRecords
{
    protected static string $resource = ResumenPedidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'PB')),

            'presupuesto_az' => Tab::make('Presupuesto AZ')
                ->icon('heroicon-o-sparkles')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'AZ')),
        ];
    }
}
