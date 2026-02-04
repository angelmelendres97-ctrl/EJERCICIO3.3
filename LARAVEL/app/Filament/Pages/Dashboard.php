<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrdenesCompraPorEstadoChart;
use App\Filament\Widgets\OrdenesCompraPorEmpresaSucursalFechaChart;
use App\Filament\Widgets\OrdenesCompraTotalPorEmpresaChart;
use App\Filament\Widgets\ResumenPedidosPorEstadoChart;
use App\Filament\Widgets\ResumenPedidosPorEmpresaChart;
use App\Filament\Widgets\UsuariosPorRolChart;
use App\Filament\Widgets\DashboardStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            /* UsuariosPorRolChart::class, */
            OrdenesCompraPorEstadoChart::class,
            OrdenesCompraPorEmpresaSucursalFechaChart::class,
            OrdenesCompraTotalPorEmpresaChart::class,
            ResumenPedidosPorEstadoChart::class,
            ResumenPedidosPorEmpresaChart::class,
        ];
    }
}
