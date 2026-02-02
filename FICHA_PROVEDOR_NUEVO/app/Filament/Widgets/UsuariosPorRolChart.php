<?php

namespace App\Filament\Widgets;

use App\Models\Role;
use Filament\Widgets\ChartWidget;

class UsuariosPorRolChart extends ChartWidget
{
    protected static ?string $heading = 'Usuarios por rol';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Usuarios',
                    'data' => $roles->pluck('users_count')->all(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $roles->pluck('name')->all(),
        ];
    }
}
