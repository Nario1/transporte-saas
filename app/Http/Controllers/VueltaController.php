<?php

namespace App\Http\Controllers;

use App\Models\Vuelta;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Ruta;
use App\Http\Requests\StoreVueltaAdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VueltaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fecha = request('fecha', today()->toDateString()); // Por defecto fecha de hoy
        $flota = request('flota');

        $vueltasQuery = Vuelta::where('empresa_id', $user->empresa_id)
            ->where('estado', 'completada')
            ->whereDate('fecha', $fecha)
            ->when($flota, function ($q) use ($flota) {
                return $q->whereHas('vehiculo', function ($vQ) use ($flota) {
                    $vQ->where('numero_flota', $flota);
                });
            })
            ->with(['vehiculo', 'conductor', 'ruta', 'paraderoSalida', 'paraderoLlegada'])
            ->orderBy('fecha', 'desc')
            ->orderBy('numero_vuelta')
            ->orderBy('created_at');

        $vueltas = $vueltasQuery->paginate(20)->withQueryString();

        // Resumen
        $resumenQuery = Vuelta::where('empresa_id', $user->empresa_id)
            ->where('estado', 'completada')
            ->whereDate('fecha', $fecha)
            ->when($flota, function ($q) use ($flota) {
                return $q->whereHas('vehiculo', function ($vQ) use ($flota) {
                    $vQ->where('numero_flota', $flota);
                });
            });

        $resumen = [
            'total'      => $vueltas->total(),
            'vehiculos'  => (clone $resumenQuery)->distinct('vehiculo_id')->count(),
            'conductores'=> (clone $resumenQuery)->distinct('conductor_id')->count(),
        ];

        return view('admin.vueltas.index', compact('vueltas', 'fecha', 'resumen'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $vehiculos = Vehiculo::where('empresa_id', $user->empresa_id)
            ->where('estado', 'activo')
            ->with('conductor')
            ->orderBy('numero_flota')
            ->orderBy('placa')
            ->get();

        $conductores = Conductor::where('empresa_id', $user->empresa_id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $rutas = Ruta::where('empresa_id', $user->empresa_id)
            ->where('estado', 'activa')
            ->orderBy('nombre')
            ->get();

        $fechaHoy = today()->toDateString();

        return view('admin.vueltas.create', compact('vehiculos', 'conductores', 'rutas', 'fechaHoy'));
    }

    public function store(StoreVueltaAdminRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        // Verificar que el vehículo pertenece a la empresa
        $this->verificarVehiculo($data['vehiculo_id'], $user->empresa_id);

        // Verificar que no exista ya esa vuelta para ese vehículo en ese día
        $existe = Vuelta::where('vehiculo_id', $data['vehiculo_id'])
            ->whereDate('fecha', $data['fecha'])
            ->where('numero_vuelta', $data['numero_vuelta'])
            ->exists();

        if ($existe) {
            return back()->withInput()
                ->with('error', "Ya existe la vuelta #{$data['numero_vuelta']} para este vehículo en esta fecha.");
        }

        $data['empresa_id'] = $user->empresa_id;
        $data['created_by'] = $user->id;

        Vuelta::create($data);

        return redirect()->route('vueltas.index')
            ->with('success', 'Vuelta registrada correctamente.');
    }

    public function completar(Vuelta $vuelta)
    {
        $this->verificarEmpresa($vuelta);

        if ($vuelta->estado !== 'activa') {
            return back()->with('error', 'Solo se pueden completar vueltas en curso.');
        }

        $vuelta->update([
            'estado' => 'completada',
            'hora_llegada' => now(),
            'latitud_fin' => $vuelta->latitud_fin ?? $vuelta->lat_actual,
            'longitud_fin' => $vuelta->longitud_fin ?? $vuelta->lng_actual,
            'lat_actual' => null,
            'lng_actual' => null,
        ]);

        return back()->with('success', 'La vuelta se marcó como completada manualmente.');
    }

    public function destroy(Vuelta $vuelta)
    {
        $this->verificarEmpresa($vuelta);

        $vuelta->delete();

        return back()->with('success', 'Vuelta eliminada correctamente.');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function verificarEmpresa(Vuelta $vuelta): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($vuelta->empresa_id !== $user->empresa_id, 403, 'Acceso no autorizado.');
    }

    private function verificarVehiculo(int $vehiculoId, int $empresaId): void
    {
        $existe = Vehiculo::where('id', $vehiculoId)
            ->where('empresa_id', $empresaId)
            ->exists();

        abort_if(!$existe, 403, 'El vehículo no pertenece a tu empresa.');
    }
}