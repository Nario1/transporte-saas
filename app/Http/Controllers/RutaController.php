<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Models\RutaParadero;
use App\Http\Requests\StoreRutaRequest;
use App\Http\Requests\UpdateRutaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RutaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rutas = Ruta::where('empresa_id', $user->empresa_id)
            ->with(['paraderos'])
            ->withCount([
                'vehiculos as vehiculos_activos_count' => fn($q) => $q->where('vehiculo_rutas.activo', true),
                'vueltas as vueltas_hoy_count'         => fn($q) => $q->whereDate('fecha', today()),
            ])
            ->orderBy('nombre')
            ->paginate(20);

        $resumen = [
            'total' => $rutas->total(),
            'activas' => Ruta::where('empresa_id', $user->empresa_id)->where('estado', 'activa')->count()
        ];

        return view('admin.rutas.index', compact('rutas', 'resumen'));
    }

    public function create()
    {
        return view('admin.rutas.create');
    }

    public function store(StoreRutaRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        $data['empresa_id'] = $user->empresa_id;

        $paraderos = $data['paraderos'] ?? [];
        unset($data['paraderos']);

        $ruta = Ruta::create($data);

        // Guardar paraderos si vienen
        $this->guardarParaderos($ruta, $paraderos);

        return redirect()->route('rutas.index')
            ->with('success', "Ruta {$ruta->nombre} registrada correctamente.");
    }

    public function show(Ruta $ruta)
    {
        $this->verificarEmpresa($ruta);

        $ruta->load([
            'paraderos',
            // Cargamos la relación 'vehiculos' filtrando por el pivot 'activo'
            'vehiculos' => fn($q) => $q->wherePivot('activo', true)->with('conductor'),
            'vueltas'   => fn($q) => $q->latest('fecha')->limit(10)->with(['vehiculo', 'conductor']),
        ]);

        return view('admin.rutas.show', compact('ruta'));
    }

    public function edit(Ruta $ruta)
    {
        $this->verificarEmpresa($ruta);

        $ruta->load('paraderos');

        return view('admin.rutas.edit', compact('ruta'));
    }

    public function update(UpdateRutaRequest $request, Ruta $ruta)
    {
        $this->verificarEmpresa($ruta);

        $data = $request->validated();

        $paraderos = $data['paraderos'] ?? [];
        unset($data['paraderos']);

        $ruta->update($data);

        // Reemplazar paraderos: eliminar los anteriores y crear los nuevos
        $ruta->paraderos()->delete();
        $this->guardarParaderos($ruta, $paraderos);

        return redirect()->route('rutas.index')
            ->with('success', "Ruta {$ruta->nombre} actualizada correctamente.");
    }

    public function destroy(Ruta $ruta)
    {
        $this->verificarEmpresa($ruta);

        try {
            if ($ruta->vueltas()->count() > 0) {
                return back()->with('error', 'No se puede eliminar una ruta que tiene vueltas registradas.');
            }

            // Fix: No bloquear por vehículos, solo advertir en el frontend (detach lo limpia)
            /* 
            if ($ruta->vehiculos()->wherePivot('activo', true)->count() > 0) {
                return back()->with('error', 'No se puede eliminar una ruta que tiene vehículos asignados.');
            }
            */

            // Desasignar vehículos del pivot y eliminar paraderos
            $ruta->vehiculos()->detach();
            $ruta->paraderos()->delete();
            $ruta->delete();

            return redirect()->route('rutas.index')
                ->with('success', 'Ruta eliminada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al intentar eliminar la ruta: ' . $e->getMessage());
        }
    }

    // ── Paraderos (acciones individuales desde el show) ───────────

    public function storeParadero(Request $request, Ruta $ruta)
    {
        $this->verificarEmpresa($ruta);

        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'tipo'   => 'required|in:origen,intermedio,destino',
        ]);

        $orden = $ruta->paraderos()->max('orden') + 1;

        $ruta->paraderos()->create([
            'nombre' => $data['nombre'],
            'tipo'   => $data['tipo'],
            'orden'  => $orden,
        ]);

        return back()->with('success', 'Paradero agregado correctamente.');
    }

    public function destroyParadero(Ruta $ruta, RutaParadero $paradero)
    {
        $this->verificarEmpresa($ruta);

        abort_if($paradero->ruta_id !== $ruta->id, 403, 'El paradero no pertenece a esta ruta.');

        $paradero->delete();

        return back()->with('success', 'Paradero eliminado.');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function guardarParaderos(Ruta $ruta, array $paraderos): void
    {
        foreach ($paraderos as $orden => $paradero) {
            $ruta->paraderos()->create([
                'nombre' => $paradero['nombre'],
                'tipo'   => $paradero['tipo'],
                'orden'  => $orden + 1,
            ]);
        }
    }

    private function verificarEmpresa(Ruta $ruta): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($ruta->empresa_id !== $user->empresa_id, 403, 'Acceso no autorizado.');
    }
}