<?php
namespace App\Http\Controllers\Conductor;
 
use App\Http\Controllers\Controller;
use App\Models\Tributo;
use Illuminate\Support\Facades\Auth;
 
class TributoController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $conductor = $user->conductor;
        $hoy       = today();
 
        abort_if(!$conductor, 403, 'Sin conductor asociado.');
 
        $vehiculo = $conductor->vehiculos()->first();
        $vehiculoId = $vehiculo ? $vehiculo->id : 0;
 
        // Tributo del día
        $tributoHoy = Tributo::where('vehiculo_id', $vehiculoId)
            ->whereDate('fecha', $hoy)
            ->with(['vehiculo.empresa', 'empresa'])
            ->first();
 
        // Historial últimos 30 días
        $historial = Tributo::where('vehiculo_id', $vehiculoId)
            ->orderByDesc('fecha')
            ->with(['vehiculo.empresa', 'empresa'])
            ->limit(30)
            ->get();
 
        // Deuda acumulada
        $deudaTotal = Tributo::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'pendiente')
            ->sum('monto');
 
        $diasDeuda = Tributo::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'pendiente')
            ->count();
 
        // Resumen del mes
        $resumenMes = [
            'pagado'    => Tributo::where('vehiculo_id', $vehiculoId)
                ->where('estado', 'pagado')
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->sum('monto'),
            'pendiente' => Tributo::where('vehiculo_id', $vehiculoId)
                ->where('estado', 'pendiente')
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->sum('monto'),
        ];
 
        return view('users.conductor.tributos', compact(
            'tributoHoy',
            'historial',
            'deudaTotal',
            'diasDeuda',
            'resumenMes',
        ));
    }
}