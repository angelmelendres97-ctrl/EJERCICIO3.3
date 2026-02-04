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
                $colors[] = $this->resolvePresupuestoColor($presupuestoValue, $status['value']);
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

    private function resolvePresupuestoColor(?string $presupuesto, bool $anulada): string
    {
        if ($presupuesto === 'AZ') {
            return $anulada ? '#ef4444' : '#2563eb';
        }

        if ($presupuesto === 'PB') {
            return $anulada ? '#f59e0b' : '#22c55e';
        }

        return $anulada ? '#f97316' : '#9ca3af';
    }
}
