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
        // Limpiar caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permiso si no existe
        $perm = Permission::updateOrCreate([
            'name' => 'gestionar alertas',
            'guard_name' => 'web'
        ]);

        // Asignar al rol SUPER_ADMIN
        $superAdmin = Role::where('name', 'SUPER_ADMIN')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($perm);
        }

        // Asignar a todos los roles ADMIN de las empresas existentes (e.g. e1_ADMIN, e2_ADMIN)
        $rolesAdmin = Role::where('name', 'LIKE', '%_ADMIN')->get();
        foreach ($rolesAdmin as $role) {
            $role->givePermissionTo($perm);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpiar caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::where('name', 'gestionar alertas')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
