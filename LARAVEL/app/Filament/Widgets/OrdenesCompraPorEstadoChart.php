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

        $presupuestos = (clone $baseQuery)
            ->select('presupuesto')
            ->distinct()
            ->pluck('presupuesto')
            ->map(fn($presupuesto) => $presupuesto ?: 'Sin presupuesto')
            ->unique()
            ->values();

        if ($presupuestos->isEmpty()) {
            $presupuestos = collect(['Sin presupuesto']);
        }

        $statuses = [
            ['label' => 'Activas', 'value' => false],
            ['label' => 'Anuladas', 'value' => true],
        ];

        $labels = [];
        $values = [];
        $colors = [];
        $palette = ['#10b981', '#ef4444', '#3b82f6', '#f59e0b', '#8b5cf6', '#14b8a6', '#f97316', '#ec4899'];
        $colorIndex = 0;

        foreach ($presupuestos as $presupuestoLabel) {
            $presupuestoValue = $presupuestoLabel === 'Sin presupuesto' ? null : $presupuestoLabel;

            foreach ($statuses as $status) {
                $query = clone $baseQuery;
                if ($presupuestoValue === null) {
                    $query->whereNull('presupuesto');
                } else {
                    $query->where('presupuesto', $presupuestoValue);
                }

                $labels[] = $presupuestoLabel . ' · ' . $status['label'];
                $values[] = $query->where('anulada', $status['value'])->count();
                $colors[] = $palette[$colorIndex % count($palette)];
                $colorIndex++;
            }
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
}
