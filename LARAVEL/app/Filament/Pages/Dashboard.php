<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\OrdenesCompraPorEstadoChart;
use App\Filament\Widgets\OrdenesCompraTotalPorEmpresaChart;
use App\Filament\Widgets\ResumenPedidosPorEstadoChart;
use App\Filament\Widgets\ResumenPedidosPorEmpresaChart;
use App\Filament\Widgets\UsuariosPorRolChart;
use App\Models\Empresa;
use App\Models\OrdenCompra;
use App\Models\ResumenPedidos;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public ?string $selectedMonth = null;
    public ?string $selectedYear = null;

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            /* UsuariosPorRolChart::class, */
            OrdenesCompraPorEstadoChart::class,
            OrdenesCompraTotalPorEmpresaChart::class,
            ResumenPedidosPorEstadoChart::class,
            ResumenPedidosPorEmpresaChart::class,
        ];
    }

    public function getMonthOptionsProperty(): array
    {
        $options = [];

        for ($month = 1; $month <= 12; $month++) {
            $options[$month] = Carbon::createFromDate(2000, $month, 1)->translatedFormat('F');
        }

        return $options;
    }

    public function getYearOptionsProperty(): array
    {
        $years = OrdenCompra::query()
            ->selectRaw('YEAR(COALESCE(fecha_pedido, created_at)) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->all();

        if (empty($years)) {
            return [now()->year => (string) now()->year];
        }

        return collect($years)
            ->mapWithKeys(fn($year) => [(int) $year => (string) $year])
            ->all();
    }

    public function getSelectedFilterLabelProperty(): string
    {
        $month = $this->selectedMonth ? (int) $this->selectedMonth : null;
        $year = $this->selectedYear ? (int) $this->selectedYear : null;

        if ($month && $year) {
            return Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        }

        if ($year) {
            return 'Año ' . $year;
        }

        if ($month) {
            return 'Mes ' . Carbon::createFromDate(2000, $month, 1)->translatedFormat('F');
        }

        return 'Todos';
    }

    public function getDashboardTotalsProperty(): array
    {
        $ordenesQuery = OrdenCompra::query();
        $this->applyDateFilter($ordenesQuery, 'fecha_pedido', true);

        $resumenesQuery = ResumenPedidos::query();
        $this->applyDateFilter($resumenesQuery, 'created_at');

        return [
            'usuarios' => User::query()->count(),
            'ordenes' => (clone $ordenesQuery)->count(),
            'total_ordenes' => (float) (clone $ordenesQuery)->sum('total'),
            'resumenes' => (clone $resumenesQuery)->count(),
            'resumenes_anulados' => (clone $resumenesQuery)->where('anulada', true)->count(),
        ];
    }

    public function getOrdenCompraStatusChartDataProperty(): array
    {
        $baseQuery = OrdenCompra::query();
        $this->applyDateFilter($baseQuery, 'fecha_pedido', true);

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
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    public function getOrdenCompraEmpresaChartDataProperty(): array
    {
        $query = OrdenCompra::query()
            ->selectRaw('id_empresa, SUM(total) as total')
            ->groupBy('id_empresa');

        $this->applyDateFilter($query, 'fecha_pedido', true);

        $totales = $query->orderByDesc('total')->get();
        $empresas = Empresa::query()
            ->whereIn('id', $totales->pluck('id_empresa'))
            ->pluck('nombre_empresa', 'id');

        $labels = $totales->map(fn($row) => $empresas[$row->id_empresa] ?? ('Empresa ' . $row->id_empresa));
        $values = $totales->map(fn($row) => (float) $row->total);

        return [
            'label' => 'Total',
            'labels' => $labels->all(),
            'values' => $values->all(),
            'colors' => array_fill(0, $values->count(), '#f59e0b'),
        ];
    }

    public function getResumenStatusChartDataProperty(): array
    {
        $baseQuery = ResumenPedidos::query();
        $this->applyDateFilter($baseQuery, 'created_at');

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
                $colors[] = $this->resolvePresupuestoColor($presupuestoValue, $status['value']);
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    public function getResumenEmpresaChartDataProperty(): array
    {
        $query = ResumenPedidos::query()
            ->selectRaw('id_empresa, SUM(total) as total')
            ->groupBy('id_empresa');

        $this->applyDateFilter($query, 'created_at');

        $totales = $query->orderByDesc('total')->get();
        $empresas = Empresa::query()
            ->whereIn('id', $totales->pluck('id_empresa'))
            ->pluck('nombre_empresa', 'id');

        $labels = $totales->map(fn($row) => $empresas[$row->id_empresa] ?? ('Empresa ' . $row->id_empresa));
        $values = $totales->map(fn($row) => (float) $row->total);

        return [
            'label' => 'Total',
            'labels' => $labels->all(),
            'values' => $values->all(),
            'colors' => array_fill(0, $values->count(), '#64748b'),
        ];
    }

    public function getOrdenCompraEmpresaSucursalFechaChartDataProperty(): array
    {
        $query = OrdenCompra::query()
            ->selectRaw('id_empresa, amdg_id_sucursal, DATE(COALESCE(fecha_pedido, created_at)) as fecha, COUNT(*) as total')
            ->groupBy('id_empresa', 'amdg_id_sucursal', 'fecha');

        $this->applyDateFilter($query, 'fecha_pedido', true);

        $rows = $query
            ->orderByDesc('fecha')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $empresas = Empresa::query()
            ->whereIn('id', $rows->pluck('id_empresa'))
            ->pluck('nombre_empresa', 'id');

        $labels = $rows->map(function ($row) use ($empresas) {
            $empresa = $empresas[$row->id_empresa] ?? ('Empresa ' . $row->id_empresa);
            $sucursal = $row->amdg_id_sucursal ? ('Sucursal ' . $row->amdg_id_sucursal) : 'Sucursal s/d';
            $fecha = $row->fecha ? Carbon::parse($row->fecha)->format('d/m/Y') : 'Fecha s/d';

            return "{$empresa} · {$sucursal} · {$fecha}";
        });

        $values = $rows->pluck('total')->map(fn($value) => (int) $value);

        return [
            'label' => 'Órdenes',
            'labels' => $labels->all(),
            'values' => $values->all(),
            'colors' => array_fill(0, $values->count(), '#0ea5e9'),
        ];
    }

    private function applyDateFilter(Builder $query, string $column, bool $fallbackToCreatedAt = false): void
    {
        $month = $this->selectedMonth ? (int) $this->selectedMonth : null;
        $year = $this->selectedYear ? (int) $this->selectedYear : null;

        if (! $month && ! $year) {
            return;
        }

        $columnExpression = $fallbackToCreatedAt
            ? DB::raw("COALESCE({$column}, created_at)")
            : $column;

        if ($month && $year) {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $query->whereBetween($columnExpression, [$start, $end]);

            return;
        }

        if ($year) {
            $query->whereYear($columnExpression, $year);
        }

        if ($month) {
            $query->whereMonth($columnExpression, $month);
        }
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
