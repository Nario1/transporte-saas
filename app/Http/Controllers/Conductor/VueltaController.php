<?php
namespace App\Http\Controllers\Conductor;
 
use App\Http\Controllers\Controller;
use App\Models\Vuelta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class VueltaController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $conductor = $user->conductor;
 
        abort_if(!$conductor, 403, 'Sin conductor asociado.');

        $vehiculo = $conductor->vehiculos()->first();
        $vehiculoId = $vehiculo ? $vehiculo->id : 0;
 
        // 🟢 NUEVO: Si tiene una vuelta activa, redirigir a la vista de "Activa"
        $vueltaActiva = Vuelta::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'activa')
            ->exists();
 
        if ($vueltaActiva && !$request->has('fecha')) {
            return redirect()->route('conductor.vuelta.activa');
        }
 
        $fecha = $request->input('fecha', today()->toDateString());
 
        // Vueltas del día seleccionado
        $vueltas = Vuelta::where('vehiculo_id', $vehiculoId)
            ->whereDate('fecha', $fecha)
            ->with(['vehiculo', 'ruta'])
            ->orderBy('numero_vuelta')
            ->get();
 
        // Resumen del mes
        $hoy = today();
        $resumenMes = [
            'total_vueltas' => Vuelta::where('vehiculo_id', $vehiculoId)
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->count(),
            'dias_trabajados' => Vuelta::where('vehiculo_id', $vehiculoId)
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->distinct('fecha')
                ->count('fecha'),
        ];
 
        // Historial últimos 7 días
        $ultimos7 = collect(range(6, 0))->map(function ($i) use ($vehiculoId, $hoy) {
            $fechaDia = $hoy->copy()->subDays($i);
            return [
                'label'   => $fechaDia->locale('es')->isoFormat('dd D'),
                'fecha'   => $fechaDia->toDateString(),
                'vueltas' => Vuelta::where('vehiculo_id', $vehiculoId)
                    ->whereDate('fecha', $fechaDia)
                    ->count(),
            ];
        });
 
        return view('users.conductor.vueltas', compact(
            'vueltas',
            'fecha',
            'resumenMes',
            'ultimos7',
        ));
    }
}