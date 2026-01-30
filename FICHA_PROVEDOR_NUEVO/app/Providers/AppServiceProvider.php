<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use App\Observers\MenuObserver;
use App\Models\DetalleOrdenCompra;
use App\Observers\DetalleOrdenCompraObserver;
use App\Models\ResumenPedidos;
use App\Observers\ResumenPedidosObserver;
use Spatie\Permission\Models\Role;
use App\Observers\RoleObserver;
use App\Models\MenuRole;
use App\Observers\MenuRoleObserver;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Config::set('database.connections.sqlite_memory', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Menu::observe(MenuObserver::class);
        Role::observe(RoleObserver::class);
        MenuRole::observe(MenuRoleObserver::class);
        DetalleOrdenCompra::observe(DetalleOrdenCompraObserver::class);
        ResumenPedidos::observe(ResumenPedidosObserver::class);
    }
}