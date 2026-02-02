<?php

namespace App\Filament\Widgets;

use App\Models\Empresa;
use App\Models\OrdenCompra;
use App\Models\Proveedores;
use App\Models\ResumenPedidos;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $ordenes = OrdenCompra::query();

        return [
            Stat::make('Usuarios registrados', User::query()->count())
                ->description('Usuarios activos en el sistema')
                ->icon('heroicon-o-users'),
            Stat::make('Órdenes de compra', OrdenCompra::query()->count())
                ->description('Total de órdenes creadas')
                ->icon('heroicon-o-clipboard-document-check'),
            Stat::make('Órdenes anuladas', (clone $ordenes)->where('anulada', true)->count())
                ->description('Órdenes marcadas como anuladas')
                ->icon('heroicon-o-x-circle'),
            Stat::make('Resumenes de pedidos', ResumenPedidos::query()->count())
                ->description('Resumenes registrados')
                ->icon('heroicon-o-document-text'),
            Stat::make('Proveedores registrados', Proveedores::query()->count())
                ->description('Total de proveedores registrados')
                ->icon('heroicon-o-building-storefront'),
            Stat::make('Empresas activas', Empresa::query()->count())
                ->description('Empresas en la plataforma')
                ->icon('heroicon-o-building-office-2'),
        ];
    }
}
