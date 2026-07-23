<?php
namespace App\Http\Controllers\Conductor;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 
class PerfilController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $conductor = $user->conductor?->load(['empresa', 'vehiculos']);
 
        return view('users.conductor.perfil', compact('conductor'));
    }
 
    public function cambiarPassword()
    {
        return view('users.conductor.cambiar-password');
    }
 
    public function guardarPassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
 
        $request->validate([
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.required'   => 'La contraseña es obligatoria.',
            'password.min'        => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
        ]);
 
        $user->update([
            'password' => Hash::make($request->password),
        ]);
 
        // Marcar que ya no es primer ingreso
        $conductor = $user->conductor;
        if ($conductor) $conductor->update(['primer_ingreso' => false]);
 
        // Redirigir a registro facial si es obligatorio y no lo tiene
        if ($conductor && $conductor->requiere_facial && !$conductor->rostro()->exists()) {
            return redirect()->route('conductor.rostro.index')
                ->with('warning', 'Contraseña actualizada. Ahora registra tu rostro.');
        }
 
        return redirect()->route('conductor.dashboard')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}