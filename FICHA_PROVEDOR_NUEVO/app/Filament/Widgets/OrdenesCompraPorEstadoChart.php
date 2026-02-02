<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\OrdenCompra;
use Filament\Widgets\ChartWidget;

class OrdenesCompraPorEstadoChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Órdenes de compra por estado';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        return $this->getMonthYearFilterOptions(18);
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $baseQuery = OrdenCompra::query();
        $this->applyMonthYearFilter($baseQuery, 'fecha_pedido', true);

        $ordenesActivas = (clone $baseQuery)->where('anulada', false)->count();
        $ordenesAnuladas = (clone $baseQuery)->where('anulada', true)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
                    'data' => [$ordenesActivas, $ordenesAnuladas],
                    'backgroundColor' => ['#10b981', '#ef4444'],
                ],
            ],
            'labels' => ['Activas', 'Anuladas'],
        ];
    }
}
