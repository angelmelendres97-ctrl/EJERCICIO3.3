<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\Empresa;
use App\Models\ResumenPedidos;
use Filament\Widgets\ChartWidget;

class ResumenPedidosPorEmpresaChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Resumenes por empresa';

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
        $query = ResumenPedidos::query()
            ->selectRaw('id_empresa, COUNT(*) as total')
            ->groupBy('id_empresa');

        $this->applyMonthYearFilter($query, 'created_at');

        $totales = $query->orderByDesc('total')->get();
        $empresas = Empresa::query()
            ->whereIn('id', $totales->pluck('id_empresa'))
            ->pluck('nombre_empresa', 'id');

        $labels = $totales->map(fn($row) => $empresas[$row->id_empresa] ?? ('Empresa ' . $row->id_empresa));
        $data = $totales->map(fn($row) => (int) $row->total);

        return [
            'datasets' => [
                [
                    'label' => 'Resumenes',
                    'data' => $data->all(),
                    'backgroundColor' => '#14b8a6',
                ],
            ],
            'labels' => $labels->all(),
        ];
    }
}
