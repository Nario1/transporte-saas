<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_nombre' => 'required|string|max:120',
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|min:8|confirmed',
        ]);

        return DB::transaction(function () use ($request) {

            // 1. Crear la Empresa (Tenant)
            $empresa = Empresa::create([
                'nombre'         => $request->empresa_nombre,
                'activa'         => 1,
                'plan'           => 'basico',
                'tributo_diario' => 0.00,
            ]);

            // 2. Crear el Usuario Admin de esa empresa
            $user = User::create([
                'empresa_id' => $empresa->id,
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'activo'     => true,
            ]);

            // 3. Obtener el rol ADMIN (Ya creado y configurado por EmpresaObserver)
            $prefijo = 'e' . $empresa->id . '_';
            $adminRole = Role::where('name', $prefijo . 'ADMIN')->first();
            
            if ($adminRole) {
                $user->assignRole($adminRole);
            }

            // 5. Autologin y entrada directa
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Bienvenido, se ha registrado la empresa y tu cuenta de administrador.');
        });
    }
}