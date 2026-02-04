<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMonthYearFilters;
use App\Models\Empresa;
use App\Models\OrdenCompra;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OrdenesCompraPorEmpresaSucursalFechaChart extends ChartWidget
{
    use HasMonthYearFilters;

    protected static ?string $heading = 'Órdenes de compra por empresa, sucursal y fecha';

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
            ->selectRaw('id_empresa, amdg_id_sucursal, DATE(COALESCE(fecha_pedido, created_at)) as fecha, COUNT(*) as total')
            ->groupBy('id_empresa', 'amdg_id_sucursal', 'fecha');

        $this->applyMonthYearFilter($query, 'fecha_pedido', true);

        $rows = $query
            ->orderByDesc('fecha')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $empresas = Empresa::query()
            ->whereIn('id', $rows->pluck('id_empresa'))
            ->pluck('nombre_empresa', 'id');

        $labels = $rows->map(function ($row) use ($empresas) {
            $empresa = $empresas[$row->id_empresa] ?? ('Empresa ' . $row->id_empresa);
            $sucursal = $row->amdg_id_sucursal ? ('Sucursal ' . $row->amdg_id_sucursal) : 'Sucursal N/D';
            $fecha = $row->fecha ? Carbon::parse($row->fecha)->format('d/m/Y') : 'Sin fecha';

            return "{$empresa} · {$sucursal} · {$fecha}";
        });

        $data = $rows->map(fn($row) => (int) $row->total);

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
                    'data' => $data->all(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels->all(),
        ];
    }
}
