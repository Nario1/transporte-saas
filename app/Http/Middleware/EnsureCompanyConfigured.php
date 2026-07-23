<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
 
class EnsureCompanyConfigured
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
 
        // 1. Si no hay usuario o es Super Admin, dejamos pasar
        if (!$user || $user->hasRole('SUPER_ADMIN')) {
            return $next($request);
        }
 
        // 2. Si es conductor, no le aplica este bloqueo (ya tienen su propio panel)
        if ($user->hasRole('conductor')) {
            return $next($request);
        }
 
        // 3. Verificar si la empresa tiene el tributo configurado
        $empresa = $user->empresa;
        $tributoConfigurado = ($empresa && (float)$empresa->tributo_diario > 0);
 
        if ($tributoConfigurado) {
            return $next($request);
        }
 
        // 4. Excepciones: Rutas de ajustes, cerrar sesión y archivos estáticos
        $excepciones = [
            'admin/ajustes*',
            'logout',
            'api/*',
            '_debugbar/*',
        ];
 
        foreach ($excepciones as $excepcion) {
            if ($request->is($excepcion)) {
                return $next($request);
            }
        }
 
        // 5. Redirigir al módulo de ajustes con advertencia e instrucciones interactivas
        return redirect()->route('ajustes.index')
            ->with('config_tutorial', true);
    }
}
