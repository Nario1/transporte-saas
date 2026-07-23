<?php

namespace App\Observers;

use App\Models\Empresa;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmpresaObserver
{
    /**
     * Handle the Empresa "created" event.
     */
    public function created(Empresa $empresa): void
    {
        // 1. Limpiar caché de permisos para asegurar que los nuevos roles funcionen de inmediato
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir prefijo para los roles de la empresa
        $prefijo = 'e' . $empresa->id . '_';

        // 3. Crear Roles con el guard 'web' explícito
        $adminRole = Role::firstOrCreate(
            ['name' => $prefijo . 'ADMIN', 'guard_name' => 'web']
        );

        $operadorRole = Role::firstOrCreate(
            ['name' => $prefijo . 'OPERADOR', 'guard_name' => 'web']
        );

        // 4. Asignar Permisos a los roles
        // Lista completa de permisos que un Admin de empresa debe tener
        $adminRole->syncPermissions([
            'ver dashboard',
            'ver vehiculos',
            'ver conductores',
            'ver propietarios',
            'ver rutas',
            'ver vueltas',
            'ver tributos',
            'ver sanciones',
            'ver reportes',
            'gestionar usuarios',
            'gestionar roles',
            'gestionar ajustes de empresa', // El permiso solicitado
        ]);

        $operadorRole->syncPermissions([
            'ver vehiculos',
            'ver conductores',
            'ver vueltas',
            'ver tributos',
            'ver sanciones',
            'ver reportes',
        ]);
    }
}
