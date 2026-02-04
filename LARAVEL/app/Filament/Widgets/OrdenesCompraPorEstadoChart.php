<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\OrdenCompra;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

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
                $colors[] = $this->resolveStatusColor($presupuestoLabel, $status['value']);
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

    private function resolveStatusColor(string $presupuestoLabel, bool $isCancelled): string
    {
        $normalized = Str::upper(trim($presupuestoLabel));

        if (Str::startsWith($normalized, 'AZ')) {
            return $isCancelled ? '#ef4444' : '#2563eb';
        }

        if (Str::startsWith($normalized, 'PB')) {
            return $isCancelled ? '#f59e0b' : '#22c55e';
        }

        if ($normalized === 'SIN PRESUPUESTO') {
            return '#9ca3af';
        }

        return $isCancelled ? '#f97316' : '#64748b';
    }
}
