<?php

namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
 
class CheckEmpresaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Sin sesión → dejar pasar
        if (!Auth::check()) {
            return $next($request);
        }
 
        /** @var \App\Models\User $user */
        $user = Auth::user();
 
        // 2. SUPER_ADMIN → siempre pasa
        if ($user->hasRole('SUPER_ADMIN')) {
            return $next($request);
        }
 
        // 3. Conductor → solo verificar que esté activo
        if ($user->hasRole('conductor')) {
            if (!$user->activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
            }
            return $next($request);
        }
 
        // 4. Admin / Operador → verificar empresa activa
        $empresa = $user->empresa;
 
        if (!$empresa || !$empresa->activa) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->with('error', 'Su empresa se encuentra suspendida. Contacte con el soporte técnico de TransJunín.');
        }
 
        return $next($request);
    }
}
 
 