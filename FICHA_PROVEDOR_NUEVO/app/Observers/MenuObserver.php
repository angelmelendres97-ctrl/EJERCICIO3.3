<?php

namespace App\Observers;

use App\Services\MenuService;

class MenuObserver
{
    /**
     * Manejar el evento "created" del modelo Menu.
     */
    public function created(): void
    {
        // Un menú se ha creado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "updated" del modelo Menu.
     */
    public function updated(): void
    {
        // Un menú se ha actualizado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "deleted" del modelo Menu.
     */
    public function deleted(): void
    {
        // Un menú se ha eliminado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "restored" del modelo Menu.
     */
    public function restored(): void
    {
        // Un menú se ha restaurado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "forceDeleted" del modelo Menu.
     */
    public function forceDeleted(): void
    {
        // Un menú se ha eliminado permanentemente
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