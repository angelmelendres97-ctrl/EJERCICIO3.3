<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MenuService;

class ClearMenuCacheCommand extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'menu:clear-cache';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Limpiar la caché de menús';

    /**
     * Ejecutar el comando.
     */
    public function handle(MenuService $menuService): void
    {
        $menuService->clearAllMenuCache();
        
        $this->info('Caché de menús limpiada exitosamente.');
    }
}