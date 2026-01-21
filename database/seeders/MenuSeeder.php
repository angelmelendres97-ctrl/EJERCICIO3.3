<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        
        // Crear menús de ejemplo
        $dashboard = Menu::firstOrCreate([
            'nombre' => 'Dashboard',
            'icono' => 'heroicon-o-home',
            'ruta' => '/admin',
            'orden' => 1,
            'activo' => true,
        ]);
        
        $users = Menu::firstOrCreate([
            'nombre' => 'Usuarios',
            'icono' => 'heroicon-o-users',
            'ruta' => '/admin/users',
            'orden' => 2,
            'activo' => true,
        ]);
        
        $roles = Menu::firstOrCreate([
            'nombre' => 'Roles',
            'icono' => 'heroicon-o-shield-check',
            'ruta' => '/admin/roles',
            'orden' => 3,
            'activo' => true,
        ]);
        
        $permissions = Menu::firstOrCreate([
            'nombre' => 'Permisos',
            'icono' => 'heroicon-o-key',
            'ruta' => '/admin/permissions',
            'orden' => 4,
            'activo' => true,
        ]);
        
        // Asignar roles a menús
        // Dashboard visible para todos
        // Usuarios, Roles y Permisos solo para admin
        $users->roles()->sync([$adminRole->id]);
        $roles->roles()->sync([$adminRole->id]);
        $permissions->roles()->sync([$adminRole->id]);
    }
}