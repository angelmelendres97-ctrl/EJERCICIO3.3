<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\Empresa;
use App\Models\OrdenCompra;
use Filament\Widgets\ChartWidget;

class OrdenesCompraTotalPorEmpresaChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Total monetario de órdenes de compra por empresa';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        return $this->getMonthYearFilterOptions(18);
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $query = OrdenCompra::query()
            ->selectRaw('id_empresa, SUM(total) as total')
            ->groupBy('id_empresa');

        $this->applyMonthYearFilter($query, 'fecha_pedido', true);

        $totales = $query->orderByDesc('total')->get();
        $empresas = Empresa::query()
            ->whereIn('id', $totales->pluck('id_empresa'))
            ->pluck('nombre_empresa', 'id');

        $labels = $totales->map(fn($row) => $empresas[$row->id_empresa] ?? ('Empresa ' . $row->id_empresa));
        $data = $totales->map(fn($row) => (float) $row->total);

        return [
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => $data->all(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels->all(),
        ];
    }
}
