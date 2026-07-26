<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Permission::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $permisos = $query->orderBy('name')->paginate(15)->withQueryString();
        
        return view('superadmin.permisos.index', compact('permisos', 'search'));
    }

    public function create()
    {
        return view('superadmin.permisos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
        ]);

        $permiso = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        // Asegurar que SUPER_ADMIN obtenga el permiso automáticamente
        $superAdminRole = Role::where('name', 'SUPER_ADMIN')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permiso);
        }

        // Limpiar caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('superadmin.permisos.index')
            ->with('success', 'Permiso global creado y asignado al rol SUPER_ADMIN correctamente.');
    }

    public function show($id)
    {
        $permiso = Permission::findOrFail($id);
        $roles = $permiso->roles;

        return view('superadmin.permisos.show', compact('permiso', 'roles'));
    }

    public function edit($id)
    {
        $permiso = Permission::findOrFail($id);
        return view('superadmin.permisos.edit', compact('permiso'));
    }

    public function update(Request $request, $id)
    {
        $permiso = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permiso->id,
        ]);

        $permiso->update([
            'name' => $request->name,
        ]);

        // Limpiar caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('superadmin.permisos.index')
            ->with('success', 'Permiso global actualizado correctamente.');
    }

    public function destroy($id)
    {
        $permiso = Permission::findOrFail($id);
        
        $permiso->delete();

        // Limpiar caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('superadmin.permisos.index')
            ->with('success', 'Permiso global eliminado correctamente.');
    }
}
