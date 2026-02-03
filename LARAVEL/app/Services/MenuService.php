<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Obtiene los menús disponibles para un usuario según sus roles
     *
     * @param User $user
     * @return array
     */
    public function getMenuItemsForUser(User $user): array
    {
        // Usamos cache para mejorar el rendimiento
        $cacheKey = "user_menu_" . $user->id;
        
        return Cache::remember($cacheKey, 3600, function () use ($user) {
            // Obtener los menús activos ordenados
            $menus = Menu::where('activo', true)
                ->with('roles')
                ->orderBy('grupo')
                ->orderBy('orden')
                ->get();
            
            // Filtrar menús según los roles del usuario
            $userRoles = $user->roles->pluck('id')->toArray();
            
            $filteredMenus = $menus->filter(function ($menu) use ($userRoles) {
                // Si el menú no tiene roles asignados, es visible para todos
                if ($menu->roles->isEmpty()) {
                    return true;
                }
                
                // Verificar si el usuario tiene alguno de los roles asignados al menú
                return $menu->roles->pluck('id')->intersect($userRoles)->isNotEmpty();
            });
            
            return $filteredMenus->toArray();
        });
    }
    
    /**
     * Limpia la caché de menús para un usuario específico
     *
     * @param User $user
     * @return void
     */
    public function clearUserMenuCache(User $user): void
    {
        Cache::forget("user_menu_" . $user->id);
    }
    
    /**
     * Limpia todas las cachés de menús
     *
     * @return void
     */
    public function clearAllMenuCache(): void
    {
        // Este método puede requerir una implementación más compleja
        // dependiendo de cómo esté configurada la caché en tu aplicación
        Cache::flush();
    }
}
