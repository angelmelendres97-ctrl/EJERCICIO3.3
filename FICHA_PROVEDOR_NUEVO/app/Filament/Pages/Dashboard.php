<?php

namespace App\Filament\Pages;

use App\Models\Empresa;
use App\Models\OrdenCompra;
use App\Models\ResumenPedidos;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string $view = 'filament.pages.dashboard';

    public ?int $selectedMonth = null;

    public ?int $selectedYear = null;

    public function mount(): void
    {
        $now = now();
        $this->selectedMonth = (int) $now->month;
        $this->selectedYear = (int) $now->year;
    }

    public function getMonthOptionsProperty(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    public function getYearOptionsProperty(): array
    {
        $ordenYears = OrdenCompra::query()
            ->selectRaw('YEAR(COALESCE(fecha_pedido, created_at)) as year')
            ->distinct()
            ->pluck('year')
            ->filter()
            ->all();

        $resumenYears = ResumenPedidos::query()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->filter()
            ->all();

        $years = collect($ordenYears)
            ->merge($resumenYears)
            ->unique()
            ->sortDesc()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int) now()->year]);
        }

        return $years->mapWithKeys(fn(int $year) => [$year => $year])->all();
    }

    public function getUserRoleChartDataProperty(): array
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return $this->buildChartPayload(
            $roles->pluck('name')->all(),
            $roles->pluck('users_count')->all(),
            'Usuarios por rol'
        );
    }

    public function getOrdenCompraStatusChartDataProperty(): array
    {
        $query = OrdenCompra::query();
        $this->applyOrdenCompraDateFilters($query);

        $stats = $query
            ->selectRaw("CASE WHEN anulada = 1 THEN 'Anuladas' ELSE 'Vigentes' END as estado, COUNT(*) as total")
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        return $this->buildChartPayload(
            $stats->pluck('estado')->all(),
            $stats->pluck('total')->all(),
            'Órdenes de compra por estado'
        );
    }

    public function getOrdenCompraEmpresaChartDataProperty(): array
    {
        $query = OrdenCompra::query()
            ->selectRaw('id_empresa, SUM(total) as total_monto')
            ->groupBy('id_empresa');
        $this->applyOrdenCompraDateFilters($query);

        $totales = $query->get();
        $empresas = Empresa::query()->pluck('nombre_empresa', 'id');

        $labels = $totales->map(function ($row) use ($empresas) {
            return $empresas[$row->id_empresa] ?? 'Sin empresa';
        })->all();

        $values = $totales->pluck('total_monto')->map(fn($value) => (float) $value)->all();

        return $this->buildChartPayload($labels, $values, 'Total monetario por empresa');
    }

    public function getResumenStatusChartDataProperty(): array
    {
        $query = ResumenPedidos::query();
        $this->applyResumenDateFilters($query);

        $stats = $query
            ->selectRaw("CASE WHEN anulada = 1 THEN 'Anulados' ELSE 'Vigentes' END as estado, COUNT(*) as total")
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        return $this->buildChartPayload(
            $stats->pluck('estado')->all(),
            $stats->pluck('total')->all(),
            'Resúmenes por estado'
        );
    }

    public function getResumenEmpresaChartDataProperty(): array
    {
        $query = ResumenPedidos::query()
            ->selectRaw('id_empresa, COUNT(*) as total')
            ->groupBy('id_empresa');
        $this->applyResumenDateFilters($query);

        $totales = $query->get();
        $empresas = Empresa::query()->pluck('nombre_empresa', 'id');

        $labels = $totales->map(function ($row) use ($empresas) {
            return $empresas[$row->id_empresa] ?? 'Sin empresa';
        })->all();

        $values = $totales->pluck('total')->map(fn($value) => (int) $value)->all();

        return $this->buildChartPayload($labels, $values, 'Resúmenes por empresa');
    }

    public function getDashboardTotalsProperty(): array
    {
        $ordenQuery = OrdenCompra::query();
        $this->applyOrdenCompraDateFilters($ordenQuery);

        $resumenQuery = ResumenPedidos::query();
        $this->applyResumenDateFilters($resumenQuery);

        return [
            'usuarios' => (int) DB::table('users')->count(),
            'ordenes' => (int) (clone $ordenQuery)->count(),
            'total_ordenes' => (float) (clone $ordenQuery)->sum('total'),
            'resumenes' => (int) (clone $resumenQuery)->count(),
            'resumenes_anulados' => (int) (clone $resumenQuery)->where('anulada', true)->count(),
        ];
    }

    public function getSelectedFilterLabelProperty(): string
    {
        $month = $this->selectedMonth;
        $year = $this->selectedYear;

        if (! $month && ! $year) {
            return 'Todos los periodos';
        }

        $mesLabel = $month ? ($this->monthOptions[$month] ?? '') : 'Todos los meses';
        $anioLabel = $year ?: 'Todos los años';

        return trim($mesLabel . ' ' . $anioLabel);
    }

    protected function applyOrdenCompraDateFilters(Builder $query): void
    {
        $dateExpression = DB::raw('COALESCE(fecha_pedido, created_at)');

        if ($this->selectedYear) {
            $query->whereYear($dateExpression, $this->selectedYear);
        }

        if ($this->selectedMonth) {
            $query->whereMonth($dateExpression, $this->selectedMonth);
        }
    }

    protected function applyResumenDateFilters(Builder $query): void
    {
        if ($this->selectedYear) {
            $query->whereYear('created_at', $this->selectedYear);
        }

        if ($this->selectedMonth) {
            $query->whereMonth('created_at', $this->selectedMonth);
        }
    }

    protected function buildChartPayload(array $labels, array $values, string $label): array
    {
        $colors = $this->buildChartColors(count($labels));

        return [
            'labels' => $labels,
            'values' => $values,
            'label' => $label,
            'colors' => $colors,
        ];
    }

    protected function buildChartColors(int $count): array
    {
        $palette = [
            '#F59E0B',
            '#10B981',
            '#3B82F6',
            '#6366F1',
            '#EC4899',
            '#14B8A6',
            '#F97316',
            '#84CC16',
            '#0EA5E9',
            '#A855F7',
        ];

        if ($count <= 0) {
            return [];
        }

        return collect(range(0, $count - 1))
            ->map(fn(int $index) => $palette[$index % count($palette)])
            ->all();
    }
}
