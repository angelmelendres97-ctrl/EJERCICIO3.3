<?php

namespace App\Observers;

use App\Services\MenuService;

class RoleObserver
{
    /**
     * Manejar el evento "created" del modelo Role.
     */
    public function created(): void
    {
        // Un rol se ha creado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "updated" del modelo Role.
     */
    public function updated(): void
    {
        // Un rol se ha actualizado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "deleted" del modelo Role.
     */
    public function deleted(): void
    {
        // Un rol se ha eliminado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "restored" del modelo Role.
     */
    public function restored(): void
    {
        // Un rol se ha restaurado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "forceDeleted" del modelo Role.
     */
    public function forceDeleted(): void
    {
        // Un rol se ha eliminado permanentemente
        $this->clearMenuCache();
    }
    
    /**
     * Limpiar la caché de menús
     */
    protected function clearMenuCache(): void
    {
        $menuService = new MenuService();
        $menuService->clearAllMenuCache();
    }
}