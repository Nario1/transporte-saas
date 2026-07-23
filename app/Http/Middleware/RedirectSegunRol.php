<?php
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
 
class RedirectSegunRol
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
 
        if (!$user) {
            return $next($request);
        }
 
        // Si es conductor y está intentando acceder al panel admin
        if ($user->hasRole('conductor') && $request->is('dashboard') || $request->is('vehiculos*') || $request->is('conductores*')) {
            return redirect()->route('conductor.dashboard');
        }
 
        // Si es admin y está intentando acceder al panel conductor
        if (!$user->hasRole('conductor') && $request->is('conductor/*')) {
            return redirect()->route('dashboard');
        }
 
        return $next($request);
    }
}
 
 