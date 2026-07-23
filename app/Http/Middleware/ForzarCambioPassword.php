<?php
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
 
class ForzarCambioPassword
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
 
        if (!$user || !$user->hasRole('conductor')) {
            return $next($request);
        }
 
        $conductor = $user->conductor;
 
        // Si es primer ingreso y no está ya en la ruta de cambio
        if ($conductor?->primer_ingreso && !$request->is('conductor/cambiar-password*')) {
            return redirect()->route('conductor.cambiar-password');
        }
 
        return $next($request);
    }
}
