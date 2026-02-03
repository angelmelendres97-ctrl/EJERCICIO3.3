<?php

namespace App\Observers;

use App\Services\MenuService;

class MenuRoleObserver
{
    /**
     * Manejar el evento "created" del modelo MenuRole.
     */
    public function created(): void
    {
        // Una relación menú-rol se ha creado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "updated" del modelo MenuRole.
     */
    public function updated(): void
    {
        // Una relación menú-rol se ha actualizado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "deleted" del modelo MenuRole.
     */
    public function deleted(): void
    {
        // Una relación menú-rol se ha eliminado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "restored" del modelo MenuRole.
     */
    public function restored(): void
    {
        // Una relación menú-rol se ha restaurado
        $this->clearMenuCache();
    }

    /**
     * Manejar el evento "forceDeleted" del modelo MenuRole.
     */
    public function forceDeleted(): void
    {
        // Una relación menú-rol se ha eliminado permanentemente
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