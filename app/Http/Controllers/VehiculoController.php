<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Propietario;
use App\Models\Ruta;
use App\Models\Tributo;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $query = Vehiculo::where('empresa_id', $user->empresa_id)
            ->with([
                'conductor.user',
                'propietario',
                'rutas' => fn($q) => $q->where('vehiculo_rutas.activo', true),
            ]);
    
        // ── Búsqueda libre ────────────────────────────────────────
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('placa', 'like', "%{$q}%")
                    ->orWhere('numero_flota', 'like', "%{$q}%");
            });
        }
    
        // ── Filtro por estado ─────────────────────────────────────
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
    
        // ── Filtro por ruta ───────────────────────────────────────
        if ($request->filled('ruta_id')) {
            $query->whereHas('rutas', fn($r) =>
                $r->where('rutas.id', $request->ruta_id)
                ->where('vehiculo_rutas.activo', true)
            );
        }
    
        // Para el select de rutas en la vista
        $rutas = \App\Models\Ruta::where('empresa_id', $user->empresa_id)
            ->where('estado', 'activa')
            ->orderBy('nombre')
            ->get();
    
        $soat_vencidos = Vehiculo::where('empresa_id', $user->empresa_id)->where('estado', 'activo')->where('soat_vence', '<', now())->count();
        $rev_vencidos = Vehiculo::where('empresa_id', $user->empresa_id)->where('estado', 'activo')->where('rev_tecnica_vence', '<', now())->count();
        $tar_vencidos = Vehiculo::where('empresa_id', $user->empresa_id)->where('estado', 'activo')->where('tarjeta_prop_vence', '<', now())->count();
        $lic_vencidas = Vehiculo::where('empresa_id', $user->empresa_id)->where('estado', 'activo')->whereHas('conductor', function($q) {
            $q->where('licencia_vence', '<', now());
        })->count();

        $resumen = [
            'total' => $query->clone()->count(),
            'activos' => $query->clone()->where('estado', 'activo')->count(),
            'docs_vencidos' => $soat_vencidos + $rev_vencidos + $tar_vencidos + $lic_vencidas,
            'soat_vencidos' => $soat_vencidos,
            'rev_vencidos' => $rev_vencidos,
            'tar_vencidos' => $tar_vencidos,
            'lic_vencidas' => $lic_vencidas,
        ];

        $vehiculos = $query
            ->orderBy('numero_flota')
            ->orderBy('placa')
            ->paginate(20)
            ->withQueryString();
    
        return view('admin.vehiculos.index', compact('vehiculos', 'rutas', 'resumen'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        [$conductores, $propietarios, $rutas] = $this->datosFormulario($user->empresa_id);

        return view('admin.vehiculos.create', compact('conductores', 'propietarios', 'rutas'));
    }

    public function store(StoreVehiculoRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        $this->verificarRelaciones($data, $user->empresa_id);

        $data['empresa_id'] = $user->empresa_id;

        $vehiculo = Vehiculo::create($data);

        // Asignar rutas seleccionadas
        if ($request->filled('rutas')) {
            $vehiculo->rutas()->sync(
                collect($request->rutas)->mapWithKeys(fn($id) => [
                    $id => ['activo' => true, 'fecha_asignacion' => today()]
                ])
            );
        }

        // Crear tributo pendiente de hoy si el vehículo es activo
        if ($vehiculo->estado === 'activo') {
            Tributo::firstOrCreate(
                ['vehiculo_id' => $vehiculo->id, 'fecha' => today()],
                [
                    'empresa_id'   => $vehiculo->empresa_id,
                    'conductor_id' => $vehiculo->conductor_id,
                    'monto'        => $user->empresa->tributo_diario ?? 0.00,
                    'estado'       => 'pendiente',
                ]
            );
        }

        return redirect()->route('vehiculos.index')
            ->with('success', "Vehículo {$vehiculo->placa_form} registrado correctamente.");
    }

    public function show(Vehiculo $vehiculo)
    {
        $this->verificarEmpresa($vehiculo);

        $vehiculo->load([
            'conductor',
            'propietario',
            'rutas'     => fn($q) => $q->where('vehiculo_rutas.activo', true),
            'vueltas'   => fn($q) => $q->latest('fecha')->limit(10),
            'tributos'  => fn($q) => $q->latest('fecha')->limit(10),
            'sanciones' => fn($q) => $q->latest('fecha')->limit(5),
            'audits'    => fn($q) => $q->where('event', 'updated')->orderBy('created_at', 'desc'),
        ]);

        return view('admin.vehiculos.show', compact('vehiculo'));
    }

    public function edit(Vehiculo $vehiculo)
    {
        $this->verificarEmpresa($vehiculo);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        [$conductores, $propietarios, $rutas] = $this->datosFormulario($user->empresa_id);

        $rutasAsignadas = $vehiculo->rutas()
            ->where('vehiculo_rutas.activo', true)
            ->pluck('rutas.id')
            ->toArray();

        return view('admin.vehiculos.edit', compact(
            'vehiculo', 'conductores', 'propietarios', 'rutas', 'rutasAsignadas'
        ));
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo)
    {
        $this->verificarEmpresa($vehiculo);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        $this->verificarRelaciones($data, $user->empresa_id);

        $vehiculo->update($data);

        // Sincronizar rutas
        if ($request->has('rutas')) {
            $vehiculo->rutas()->sync(
                collect($request->rutas)->mapWithKeys(fn($id) => [
                    $id => ['activo' => true, 'fecha_asignacion' => today()]
                ])
            );
        } else {
            // Si no mandaron rutas, desactivar todas
            $idsActuales = $vehiculo->rutas()->pluck('rutas.id')->toArray();
            foreach ($idsActuales as $rutaId) {
                $vehiculo->rutas()->updateExistingPivot($rutaId, ['activo' => false]);
            }
        }

        return redirect()->route('vehiculos.index')
            ->with('success', "Vehículo {$vehiculo->placa_form} actualizado correctamente.");
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $this->verificarEmpresa($vehiculo);

        if ($vehiculo->vueltas()->count() > 0 || $vehiculo->tributos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un vehículo que tiene vueltas o tributos registrados.');
        }

        $vehiculo->rutas()->detach();
        $vehiculo->delete();

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function datosFormulario(int $empresaId): array
    {
        $conductores = Conductor::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $propietarios = Propietario::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $rutas = Ruta::where('empresa_id', $empresaId)
            ->where('estado', 'activa')
            ->orderBy('nombre')
            ->get();

        return [$conductores, $propietarios, $rutas];
    }

    private function verificarEmpresa(Vehiculo $vehiculo): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($vehiculo->empresa_id !== $user->empresa_id, 403, 'Acceso no autorizado.');
    }

    private function verificarRelaciones(array $data, int $empresaId): void
    {
        if (!empty($data['conductor_id'])) {
            $existe = Conductor::where('id', $data['conductor_id'])
                ->where('empresa_id', $empresaId)
                ->exists();
            abort_if(!$existe, 403, 'El conductor no pertenece a tu empresa.');
        }

        if (!empty($data['propietario_id'])) {
            $existe = Propietario::where('id', $data['propietario_id'])
                ->where('empresa_id', $empresaId)
                ->exists();
            abort_if(!$existe, 403, 'El propietario no pertenece a tu empresa.');
        }
    }
}