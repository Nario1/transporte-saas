<?php
 
// ══════════════════════════════════════════════════════════════════
// app/Http/Controllers/Auth/LoginController.php
// ══════════════════════════════════════════════════════════════════
 
namespace App\Http\Controllers\Auth;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login.index');
    }
 
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required',
            'password' => 'required',
        ], [
            'email.required'    => 'El Usuario o Placa es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);
 
        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }
 
        $request->session()->regenerate();
 
        /** @var \App\Models\User $user */
        $user = Auth::user();
 
        // ── Verificar usuario activo ──────────────────────────────
        if (!$user->activo) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }
 
        // ── Verificar empresa activa (excepto SUPER_ADMIN) ────────
        if (!$user->hasRole('SUPER_ADMIN')) {
            if (!$user->empresa || !$user->empresa->activa) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Acceso denegado: Su empresa se encuentra suspendida. Contacte al administrador.');
            }
        }
 
        // ── Redirección según rol ─────────────────────────────────
 
        // 1. Super Admin
        if ($user->hasRole('SUPER_ADMIN')) {
            return redirect()->route('superadmin.dashboard');
        }
 
        // 2. Conductor — panel propio
        if ($user->hasRole('conductor')) {
            return redirect()->route('conductor.dashboard');
        }
 
        // 3. Admin / Operador — redirigir a la primera ruta que tenga permiso
        // El orden define la prioridad: si tiene 'ver dashboard' va al dashboard,
        // si no tiene ese permiso pero sí 'ver vehiculos' va a vehículos, etc.
        $rutasPorPermiso = [
            'ver dashboard'    => 'dashboard',
            'ver vehiculos'    => 'vehiculos.index',
            'ver conductores'  => 'conductores.index',
            'ver propietarios' => 'propietarios.index',
            'ver rutas'        => 'rutas.index',
            'ver tributos'     => 'tributos.index',
            'ver vueltas'      => 'vueltas.index',
            'ver sanciones'    => 'sanciones.index',
            'ver reportes'     => 'reportes.index',
            'gestionar usuarios' => 'users.index',
            'gestionar roles'    => 'roles.index',
        ];
 
        foreach ($rutasPorPermiso as $permiso => $ruta) {
            if ($user->can($permiso)) {
                return redirect()->route($ruta);
            }
        }
 
        // 4. Fallback — sin permisos asignados
        Auth::logout();
        return redirect()->route('login')
            ->with('error', 'Tu cuenta no tiene permisos asignados. Contacta al administrador.');
    }
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect()->route('login');
    }
}
 