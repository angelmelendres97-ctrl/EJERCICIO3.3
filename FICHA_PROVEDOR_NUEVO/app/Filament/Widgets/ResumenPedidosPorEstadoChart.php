<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\ResumenPedidos;
use Filament\Widgets\ChartWidget;

class ResumenPedidosPorEstadoChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Resumenes de pedidos por estado';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        return $this->getMonthYearFilterOptions(18);
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $baseQuery = ResumenPedidos::query();
        $this->applyMonthYearFilter($baseQuery, 'created_at');

        $activos = (clone $baseQuery)->where('anulada', false)->count();
        $anulados = (clone $baseQuery)->where('anulada', true)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Resumenes',
                    'data' => [$activos, $anulados],
                    'backgroundColor' => ['#2563eb', '#f97316'],
                ],
            ],
            'labels' => ['Activos', 'Anulados'],
        ];
    }
}
