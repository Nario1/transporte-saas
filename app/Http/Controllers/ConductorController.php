<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Propietario;
use App\Http\Requests\StoreConductorRequest;
use App\Http\Requests\UpdateConductorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConductorController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $query = Conductor::where('empresa_id', $user->empresa_id)
            ->with(['propietario', 'vehiculos.rutas', 'user'])
            ->withCount('vehiculos');
    
        // ── Búsqueda libre ────────────────────────────────────────
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%")
                    ->orWhereHas('vehiculos', fn($v) =>
                        $v->where('numero_flota', 'like', "%{$q}%")
                    );
            });
        }
    
        // ── Filtro por estado ─────────────────────────────────────
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
    
        $vencer_mes_count = Conductor::where('empresa_id', $user->empresa_id)
            ->whereNotNull('licencia_vence')
            ->whereMonth('licencia_vence', now()->month)
            ->whereYear('licencia_vence', now()->year)
            ->count();

        $resumen = [
            'total' => $query->clone()->count(),
            'activos' => $query->clone()->where('estado', 'activo')->count(),
            'vencer_mes' => $vencer_mes_count
        ];

        $conductores = $query
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();
    
        return view('admin.conductores.index', compact('conductores', 'resumen'));
    }
 

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $propietarios = Propietario::where('empresa_id', $user->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.conductores.create', compact('propietarios'));
    }

    public function store(StoreConductorRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        // Verificar que el propietario_id pertenezca a la misma empresa
        if (!empty($data['propietario_id'])) {
            $this->verificarPropietario($data['propietario_id'], $user->empresa_id);
        }

        $data['empresa_id'] = $user->empresa_id;

        Conductor::create($data);

        return redirect()->route('conductores.index')
            ->with('success', 'Conductor registrado correctamente.');
    }

    public function show(Conductor $conductor)
    {
        $this->verificarEmpresa($conductor);

        $conductor->load([
            'propietario',
            'vehiculos',
            'vueltas'  => fn($q) => $q->latest('fecha')->limit(10),
            'tributos' => fn($q) => $q->latest('fecha')->limit(10),
            'sanciones'=> fn($q) => $q->latest('fecha')->limit(5),
        ]);

        return view('admin.conductores.show', compact('conductor'));
    }

    public function edit(Conductor $conductor)
    {
        $this->verificarEmpresa($conductor);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $propietarios = Propietario::where('empresa_id', $user->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $conductor->load('vehiculos');

        return view('admin.conductores.edit', compact('conductor', 'propietarios'));
    }

    public function update(UpdateConductorRequest $request, Conductor $conductor)
    {
        $this->verificarEmpresa($conductor);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        if (!empty($data['propietario_id'])) {
            $this->verificarPropietario($data['propietario_id'], $user->empresa_id);
        }

        $conductor->update($data);

        return redirect()->route('conductores.index')
            ->with('success', 'Conductor actualizado correctamente.');
    }

    public function destroy(Conductor $conductor)
    {
        $this->verificarEmpresa($conductor);

        if ($conductor->vehiculos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un conductor que tiene vehículos asignados.');
        }

        $conductor->delete();

        return redirect()->route('conductores.index')
            ->with('success', 'Conductor eliminado correctamente.');
    }

    public function toggleFacial(Request $request, Conductor $conductor)
    {
        $this->verificarEmpresa($conductor);

        $request->validate(['requiere_facial' => 'required|boolean']);

        $conductor->update(['requiere_facial' => $request->requiere_facial]);

        return response()->json(['ok' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function verificarEmpresa(Conductor $conductor): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($conductor->empresa_id !== $user->empresa_id, 403, 'Acceso no autorizado.');
    }

    private function verificarPropietario(int $propietarioId, int $empresaId): void
    {
        $existe = Propietario::where('id', $propietarioId)
            ->where('empresa_id', $empresaId)
            ->exists();

        abort_if(!$existe, 403, 'El propietario no pertenece a tu empresa.');
    }
}