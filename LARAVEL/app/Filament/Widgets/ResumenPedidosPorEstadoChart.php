<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\ResumenPedidos;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

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

        $presupuestos = (clone $baseQuery)
            ->select('tipo')
            ->distinct()
            ->pluck('tipo')
            ->map(fn($presupuesto) => $presupuesto ?: 'Sin presupuesto')
            ->unique()
            ->values();

        if ($presupuestos->isEmpty()) {
            $presupuestos = collect(['Sin presupuesto']);
        }

        $statuses = [
            ['label' => 'Activos', 'value' => false],
            ['label' => 'Anulados', 'value' => true],
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($presupuestos as $presupuestoLabel) {
            $presupuestoValue = $presupuestoLabel === 'Sin presupuesto' ? null : $presupuestoLabel;

            foreach ($statuses as $status) {
                $query = clone $baseQuery;
                if ($presupuestoValue === null) {
                    $query->whereNull('tipo');
                } else {
                    $query->where('tipo', $presupuestoValue);
                }

                $labels[] = $presupuestoLabel . ' · ' . $status['label'];
                $values[] = $query->where('anulada', $status['value'])->count();
                $colors[] = $this->resolveStatusColor($presupuestoLabel, $status['value']);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Resúmenes',
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
