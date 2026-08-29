<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar caché de permisos Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buscar el permiso
        $perm = Permission::where('name', 'gestionar alertas')->first();
        if (!$perm) {
            $perm = Permission::create([
                'name' => 'gestionar alertas',
                'guard_name' => 'web'
            ]);
        }

        // Asignar a todos los roles ADMIN (e.g. e2_ADMIN, e1_ADMIN, SUPER_ADMIN)
        $rolesAdmin = Role::where('name', 'LIKE', '%_ADMIN')->get();
        foreach ($rolesAdmin as $role) {
            if (!$role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario reversar
    }
};
