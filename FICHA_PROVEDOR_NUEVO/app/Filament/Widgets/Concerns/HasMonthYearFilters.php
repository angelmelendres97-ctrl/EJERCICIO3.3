<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasMonthYearFilters
{
    protected function getMonthYearFilterOptions(int $monthsBack = 12): array
    {
        $options = [];
        $current = now()->startOfMonth();

        for ($offset = 0; $offset < $monthsBack; $offset++) {
            $date = $current->copy()->subMonths($offset);
            $options[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        return $options;
    }

    protected function resolveSelectedMonthYear(): Carbon
    {
        $filter = $this->filter ?? '';
        [$year, $month] = array_pad(explode('-', $filter, 2), 2, null);

        if (! $year || ! $month) {
            return now()->startOfMonth();
        }

        return Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
    }

    protected function applyMonthYearFilter(
        Builder $query,
        string $column,
        bool $fallbackToCreatedAt = false
    ): void {
        $selectedMonth = $this->resolveSelectedMonthYear();
        $start = $selectedMonth->copy()->startOfMonth();
        $end = $selectedMonth->copy()->endOfMonth();

        if ($fallbackToCreatedAt) {
            $query->whereBetween(DB::raw("COALESCE({$column}, created_at)"), [$start, $end]);

            return;
        }

        $query->whereBetween($column, [$start, $end]);
    }
}
