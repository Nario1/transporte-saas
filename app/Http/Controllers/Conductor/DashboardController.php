<?php

namespace App\Http\Controllers\Conductor;
 
use App\Http\Controllers\Controller;
use App\Models\Tributo;
use App\Models\Vuelta;
use App\Models\Sancion;
use Illuminate\Support\Facades\Auth;
 
class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $conductor = $user->conductor;
        $hoy       = today();
 
        abort_if(!$conductor, 403, 'Sin conductor asociado.');
 
        // FORZAR REGISTRO DE ROSTRO (Si requiere facial y no tiene)
        if ($conductor->requiere_facial && !$conductor->rostro()->exists()) {
            return redirect()->route('conductor.rostro.index')
                ->with('warning', '⚠️ ACCESO RESTRINGIDO: Debes registrar tu rostro para habilitar tu cuenta.');
        }

        Tributo::ensureGenerados($user->empresa_id);

        $vehiculo = $conductor->vehiculos()->first();
        $vehiculoId = $vehiculo ? $vehiculo->id : 0;

        // Tributo del día
        $tributoHoy = Tributo::where('vehiculo_id', $vehiculoId)
            ->whereDate('fecha', $hoy)
            ->first();
 
        // Vueltas del día
        $vueltasHoy = Vuelta::where('vehiculo_id', $vehiculoId)
            ->whereDate('fecha', $hoy)
            ->with('ruta', 'vehiculo')
            ->orderBy('numero_vuelta')
            ->get();
 
        // Sanciones pendientes
        $sancionesPendientes = Sancion::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'pendiente')
            ->get();
 
        // Deuda acumulada de tributos
        $tributosPendientes = Tributo::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'pendiente')
            ->whereDate('fecha', '<', $hoy)
            ->orderBy('fecha', 'desc')
            ->get();

        $deudaTributos = $tributosPendientes->sum('monto');
 
        // Alertas de documentos
        $alertas = [];
 
        if ($conductor->licencia_vence_alerta) {
            $alertas[] = "Tu licencia vence el {$conductor->licencia_vence->format('d/m/Y')}";
        }
 
        if ($conductor->examen_medico_alerta) {
            $alertas[] = "Tu examen médico vence el {$conductor->vigencia_examen_medico->format('d/m/Y')}";
        }
 
        return view('users.conductor.dashboard', compact(
            'conductor',
            'tributoHoy',
            'vueltasHoy',
            'sancionesPendientes',
            'tributosPendientes',
            'deudaTributos',
            'alertas',
        ));
    }
}
 