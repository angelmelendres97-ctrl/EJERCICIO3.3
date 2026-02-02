<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\ResumenPedidos;
use Filament\Widgets\ChartWidget;

class ResumenPedidosPorEstadoChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Resúmenes de pedidos por estado y presupuesto';

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

        $presupuestos = $this->getPresupuestos($baseQuery, 'tipo');
        $labels = [];
        $values = [];
        $colors = [];

        $activePalette = ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd'];
        $cancelledPalette = ['#f97316', '#fb923c', '#fdba74', '#fed7aa'];

        foreach ($presupuestos as $index => $presupuesto) {
            $labels[] = sprintf('Activos - %s', $presupuesto);
            $values[] = $this->countByEstadoYPresupuesto($baseQuery, false, 'tipo', $presupuesto);
            $colors[] = $activePalette[$index % count($activePalette)];
        }

        foreach ($presupuestos as $index => $presupuesto) {
            $labels[] = sprintf('Anulados - %s', $presupuesto);
            $values[] = $this->countByEstadoYPresupuesto($baseQuery, true, 'tipo', $presupuesto);
            $colors[] = $cancelledPalette[$index % count($cancelledPalette)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Resumenes',
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getPresupuestos($baseQuery, string $column): array
    {
        $presupuestos = (clone $baseQuery)
            ->select($column)
            ->distinct()
            ->pluck($column)
            ->map(fn($value) => $value ?: 'Sin presupuesto')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $presupuestos ?: ['Sin presupuesto'];
    }

    private function countByEstadoYPresupuesto($baseQuery, bool $anulada, string $column, string $presupuesto): int
    {
        $query = (clone $baseQuery)->where('anulada', $anulada);

        if ($presupuesto === 'Sin presupuesto') {
            $query->where(function ($innerQuery) use ($column) {
                $innerQuery->whereNull($column)->orWhere($column, '');
            });
        } else {
            $query->where($column, $presupuesto);
        }

        return $query->count();
    }
}
