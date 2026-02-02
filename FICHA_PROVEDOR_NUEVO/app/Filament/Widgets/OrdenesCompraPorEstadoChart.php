<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\OrdenCompra;
use Filament\Widgets\ChartWidget;

class OrdenesCompraPorEstadoChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Órdenes de compra por estado y presupuesto';

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

        $presupuestos = $this->getPresupuestos($baseQuery, 'presupuesto');
        $labels = [];
        $values = [];
        $colors = [];

        $activePalette = ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0'];
        $cancelledPalette = ['#ef4444', '#f87171', '#fca5a5', '#fecaca'];

        foreach ($presupuestos as $index => $presupuesto) {
            $labels[] = sprintf('Activas - %s', $presupuesto);
            $values[] = $this->countByEstadoYPresupuesto($baseQuery, false, 'presupuesto', $presupuesto);
            $colors[] = $activePalette[$index % count($activePalette)];
        }

        foreach ($presupuestos as $index => $presupuesto) {
            $labels[] = sprintf('Anuladas - %s', $presupuesto);
            $values[] = $this->countByEstadoYPresupuesto($baseQuery, true, 'presupuesto', $presupuesto);
            $colors[] = $cancelledPalette[$index % count($cancelledPalette)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
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
