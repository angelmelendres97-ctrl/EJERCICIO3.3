<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Models\Menu;
use App\Filament\Pages\Dashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigation(function (NavigationBuilder $navigation) {
                $user = auth()->user();

                if (! $user) {
                    return $navigation;
                }

                $menuItems = Menu::query()
                    ->where(function ($q) use ($user) {
                        $q->whereHas('roles', function ($query) use ($user) {
                            $query->whereIn('name', $user->roles->pluck('name'));
                        })->orWhereDoesntHave('roles');
                    })
                    ->where('activo', true) // si tienes este campo
                    ->orderByRaw("COALESCE(NULLIF(TRIM(grupo), ''), 'General') ASC")
                    ->orderBy('orden')
                    ->get();

                // Agrupar por "grupo" (normalizando espacios)
                $grouped = $menuItems->groupBy(function ($item) {
                    $g = trim((string) $item->grupo);
                    return $g !== '' ? $g : 'General';
                });

                $navigationGroups = $grouped->map(function ($items, $grupo) {
                    $navItems = $items->map(function ($menuItem) {
                        $ruta = $menuItem->ruta; // ej: /admin/reporte...

                        return \Filament\Navigation\NavigationItem::make($menuItem->nombre)
                            ->icon($menuItem->icono)
                            ->url(url($ruta))
                            ->isActiveWhen(fn(): bool => request()->is(ltrim($ruta, '/') . '*'));
                    })->values()->all();

                    return NavigationGroup::make($grupo)
                        ->items($navItems);
                })->values()->all();

                // OJO: aquí NO mandamos ->items(), solo grupos con items dentro
                return $navigation->groups($navigationGroups);
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
