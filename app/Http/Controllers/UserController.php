<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        
        // Iniciamos la consulta cargando los roles
        $query = User::with('roles')
                    ->whereNull('conductor_id'); // <--- ESTA LÍNEA FILTRA A LOS CONDUCTORES

        // Si no es súper admin, filtramos por empresa
        if (!$authUser->hasRole('SUPER_ADMIN')) {
            $query->where('empresa_id', $authUser->empresa_id);
        }

        $users = $query->get()->map(function($user) {
            $prefijo = 'e' . $user->empresa_id . '_';
            $user->roles_limpios = $user->roles->map(function($role) use ($prefijo) {
                return strtoupper(str_replace($prefijo, '', $role->name));
            })->implode(', ');
            return $user;
        });

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $empresaId = $request->query('empresa_id') ?? $authUser->empresa_id;
        $prefijo = 'e' . $empresaId . '_';

        $roles = Role::where(function ($q) use ($prefijo) {
                $q->where('name', 'LIKE', $prefijo . '%')
                  ->orWhere('name', 'conductor');
            })
            ->get()
            ->map(function ($role) use ($prefijo) {
                $role->nombre_limpio = strtoupper(str_replace($prefijo, '', $role->name));
                return $role;
            });

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $request->validated();

        /** @var User $authUser */
        $authUser = Auth::user();

        $empresaId = $request->empresa_id ?? $authUser->empresa_id;

        $user = User::create([
            'empresa_id' => $empresaId,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'activo'     => true
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'Usuario ' . $user->name . ' creado exitosamente.');
    }

    public function edit(User $user)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if ($user->empresa_id !== $authUser->empresa_id) abort(403);

        $prefijo = 'e' . $authUser->empresa_id . '_';

        $roles = Role::where(function ($q) use ($prefijo) {
                $q->where('name', 'LIKE', $prefijo . '%')
                  ->orWhere('name', 'conductor');
            })
            ->get()
            ->map(function ($role) use ($prefijo) {
                $role->nombre_limpio = strtoupper(str_replace($prefijo, '', $role->name));
                return $role;
            });

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Actualiza el usuario asegurando el scope de empresa.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // 1. Seguridad: Verificar que el usuario pertenezca a la misma empresa
        /** @var User $authUser */
        $authUser = Auth::user();
        if ($user->empresa_id !== $authUser->empresa_id) abort(403);

        // 2. Validación
        $request->validated();

        // 3. Preparar datos para actualizar
        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // Solo actualizar password si se escribió algo
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // 4. Sincronizar Roles (Spatie quita los anteriores y pone el nuevo)
        // Evitamos que un Admin normal intente asignarse el rol SUPER_ADMIN mediante request
        if ($request->role !== 'SUPER_ADMIN') {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')
            ->with('success', "Usuario {$user->name} actualizado correctamente.");
    }

    public function destroy($id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $user = User::where('empresa_id', $authUser->empresa_id)->findOrFail($id);

        if ($user->id === $authUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($user->hasRole('SUPER_ADMIN') && !$authUser->hasRole('SUPER_ADMIN')) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar a un Súper Administrador.');
        }

        // Evitar eliminar al admin de la empresa
        $prefijo = 'e' . $authUser->empresa_id . '_';
        if ($user->hasRole($prefijo . 'ADMIN')) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar el usuario administrador principal de la empresa.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "El usuario {$userName} ha sido eliminado.");
    }
}