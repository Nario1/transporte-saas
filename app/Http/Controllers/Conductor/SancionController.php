<?php
namespace App\Http\Controllers\Conductor;
 
use App\Http\Controllers\Controller;
use App\Models\Sancion;
use Illuminate\Support\Facades\Auth;
 
class SancionController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $conductor = $user->conductor;
 
        abort_if(!$conductor, 403, 'Sin conductor asociado.');
 
        $vehiculo = $conductor->vehiculos()->first();
        $vehiculoId = $vehiculo ? $vehiculo->id : 0;
 
        $hoy = today();
 
        // Sanciones pendientes
        $pendientes = Sancion::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'pendiente')
            ->with('vehiculo')
            ->orderByDesc('fecha')
            ->get();
 
        // Historial pagadas
        $pagadas = Sancion::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'pagado')
            ->with('vehiculo')
            ->orderByDesc('fecha')
            ->limit(20)
            ->get();
 
        // Resumen
        $resumen = [
            'total_pendiente' => $pendientes->sum('monto'),
            'cantidad_pendiente' => $pendientes->count(),
            'pagado_mes' => Sancion::where('vehiculo_id', $vehiculoId)
                ->where('estado', 'pagado')
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->sum('monto'),
        ];
 
        return view('users.conductor.sanciones', compact(
            'pendientes',
            'pagadas',
            'resumen',
        ));
    }
}