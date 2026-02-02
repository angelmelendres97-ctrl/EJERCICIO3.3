<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
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
                // Esta es la forma correcta de obtener el usuario autenticado
                $user = auth()->user();

                if (!$user) {
                    return $navigation;
                }

                // Obtener menús según el rol del usuario
                $menuItems = Menu::whereHas('roles', function ($query) use ($user) {
                    $query->whereIn('name', $user->roles->pluck('name'));
                })->orWhereDoesntHave('roles')
                    ->orderBy('grupo')
                    ->orderBy('orden')
                    ->get();

                $navigationItems = [];
                foreach ($menuItems as $menuItem) {
                    $navigationItems[] = \Filament\Navigation\NavigationItem::make($menuItem->nombre)
                        ->icon($menuItem->icono)
                        ->url($menuItem->ruta)
                        ->group($menuItem->grupo ?: 'General')
                        ->isActiveWhen(fn (): bool => request()->routeIs($menuItem->ruta));
                }

                return $navigation->items($navigationItems);
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
