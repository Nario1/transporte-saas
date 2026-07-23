<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    /** Prefijo único por empresa: empresa_id=3 → "e3_" */
    private function prefijo(): string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return 'e' . $user->empresa_id . '_';
    }

    /** "SUPERVISOR" → "e3_SUPERVISOR" */
    private function nombreConPrefijo(string $nombre): string
    {
        return $this->prefijo() . strtoupper($nombre);
    }

    /** "e3_SUPERVISOR" → "SUPERVISOR" */
    private function nombreSinPrefijo(string $nombre): string
    {
        return preg_replace('/^e\d+_/', '', $nombre);
    }

    /** Permisos que no se pueden asignar desde el panel de empresa */
    private function permisosExcluidos(): array
    {
        return [
            'gestionar empresas',
            'conductor.dashboard',
            'conductor.tributos',
            'conductor.vueltas',
            'conductor.sanciones',
            'conductor.perfil',
        ];
    }

    /** Roles del sistema que nunca se muestran ni editan */
    private function rolesSistema(): array
    {
        return ['SUPER_ADMIN', 'ADMIN'];
    }

    // ══════════════════════════════════════════════════════════════
    // CRUD
    // ══════════════════════════════════════════════════════════════

    public function index()
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $prefijo = $this->prefijo();

        // Mostrar solo roles que pertenecen a esta empresa
        $roles = Role::withCount('users')
            ->where('name', 'like', $prefijo . '%')
            ->get()
            ->map(function ($role) {
                $role->nombre_visible = $this->nombreSinPrefijo($role->name);
                return $role;
            });

        // Si es SUPER_ADMIN también ve los roles base del sistema
        if ($user->hasRole('SUPER_ADMIN')) {
            $rolesSistema = Role::withCount('users')
                ->whereIn('name', ['ADMIN', 'OPERADOR'])
                ->get()
                ->map(function ($role) {
                    $role->nombre_visible = $role->name;
                    return $role;
                });

            $roles = $roles->merge($rolesSistema);
        }

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $allPermissions = Permission::whereNotIn('name', $this->permisosExcluidos())
            ->orderBy('name')
            ->pluck('name');

        return view('admin.roles.create', compact('allPermissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $request->validated();

        $nombreCompleto = $this->nombreConPrefijo($request->name);

        // Unicidad dentro de esta empresa
        if (Role::where('name', $nombreCompleto)->exists()) {
            return back()->withInput()
                ->with('error', 'Ya existe un rol con ese nombre en tu empresa.');
        }

        try {
            $role = Role::create([
                'name'       => $nombreCompleto,
                'guard_name' => 'web',
            ]);

            if ($request->filled('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return redirect()->route('roles.index')
                ->with('success', "Rol \"{$this->nombreSinPrefijo($role->name)}\" creado correctamente.");

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $role = Role::with('users')->findOrFail($id);

        $this->verificarAcceso($role);

        $nombreVisible = $this->nombreSinPrefijo($role->name);
        $permissions   = $role->permissions->pluck('name');

        return view('admin.roles.show', compact('role', 'nombreVisible', 'permissions'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $this->verificarAcceso($role);

        $allPermissions  = Permission::whereNotIn('name', $this->permisosExcluidos())
            ->orderBy('name')
            ->pluck('name');

        $permisosActivos = $role->permissions->pluck('name')->toArray();
        $nombreVisible   = $this->nombreSinPrefijo($role->name);

        return view('admin.roles.edit', compact(
            'role', 'allPermissions', 'permisosActivos', 'nombreVisible'
        ));
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);

        $this->verificarAcceso($role);

        $request->validated();

        $nombreCompleto = $this->nombreConPrefijo($request->name);

        // Unicidad excluyendo el rol actual
        if (Role::where('name', $nombreCompleto)->where('id', '!=', $id)->exists()) {
            return back()->withInput()
                ->with('error', 'Ya existe un rol con ese nombre en tu empresa.');
        }

        $role->update(['name' => $nombreCompleto]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Rol \"{$this->nombreSinPrefijo($role->name)}\" actualizado correctamente.");
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $this->verificarAcceso($role);

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    // ══════════════════════════════════════════════════════════════
    // SEGURIDAD
    // ══════════════════════════════════════════════════════════════

    private function verificarAcceso(Role $role): void
    {
        $nombreLimpio = $this->nombreSinPrefijo($role->name);

        // 1. Roles globales del sistema (sin prefijo eX_)
        if (in_array($role->name, ['SUPER_ADMIN', 'ADMIN', 'OPERADOR', 'conductor'])) {
            // Solo el SUPER_ADMIN global puede tocar estos
            if (!Auth::user()->hasRole('SUPER_ADMIN')) {
                abort(403, 'Este rol base del sistema no puede modificarse.');
            }
            return;
        }

        // 2. Roles de la empresa (con prefijo eX_)
        // Bloquear solo si el nombre base es "ADMIN"
        if ($nombreLimpio === 'ADMIN') {
            abort(403, 'El rol administrador de la empresa no puede ser modificado ni eliminado.');
        }

        // Verificar que el rol pertenece a la empresa actual
        if (!str_starts_with($role->name, $this->prefijo())) {
            abort(403, 'No tienes permiso para gestionar este rol de otra empresa.');
        }
    }
}