<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use App\Models\Conductor;
use App\Http\Requests\StorePropietarioRequest;
use App\Http\Requests\UpdatePropietarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PropietarioController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $query = Propietario::where('empresa_id', $user->empresa_id)
            ->with(['conductor.user'])
            ->withCount('vehiculos');
    
        // ── Búsqueda libre ────────────────────────────────────────
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%");
            });
        }
    
        // ── Filtro por estado ─────────────────────────────────────
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === 'activo' ? true : false);
        }
    
        $resumen = [
            'total' => $query->clone()->count(),
            'activos' => $query->clone()->where('activo', true)->count()
        ];

        $propietarios = $query
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();
    
        return view('admin.propietarios.index', compact('propietarios', 'resumen'));
    }

    public function create()
    {
        return view('admin.propietarios.create');
    }

    public function store(StorePropietarioRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $request->validated();

        return DB::transaction(function () use ($user, $data) {
            try {
                // 1. Crear Propietario
                $data['empresa_id'] = $user->empresa_id;
                $data['activo']     = true;
                $propietario = Propietario::create($data);

                // 2. ¿Es también conductor?
                if (!empty($data['es_conductor'])) {
                    Conductor::create([
                        'empresa_id'     => $user->empresa_id,
                        'propietario_id' => $propietario->id,
                        'nombre'         => $data['nombre'],
                        'apellidos'      => $data['apellidos'],
                        'dni'            => $data['dni'] ?? null,
                        'telefono'       => $data['telefono'] ?? null,
                        'email'          => $data['email'] ?? null,
                        'direccion'      => $data['direccion'] ?? null,
                        'tipo_licencia'  => $data['tipo_licencia'] ?? null,
                        'licencia_vence' => $data['licencia_vence'] ?? null,
                        'estado'         => $data['conductor_estado'] ?? 'activo',
                    ]);
                }

                $msg = !empty($data['es_conductor']) 
                    ? 'Propietario y Conductor creados correctamente.'
                    : 'Propietario "' . $propietario->nombre . '" registrado con éxito.';

                return redirect()->route('propietarios.index')->with('success', $msg);

            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
            }
        });
    }

    public function edit(Propietario $propietario)
    {
        $this->verificarEmpresa($propietario);
        return view('admin.propietarios.edit', compact('propietario'));
    }

    public function show(Propietario $propietario)
    {
        // 1. Verificamos que el propietario sea de la empresa del usuario
        $this->verificarEmpresa($propietario);

        // 2. Cargamos las relaciones necesarias
        $propietario->load(['conductor', 'vehiculos' => function($query) {
            $query->orderBy('placa');
        }]);

        return view('admin.propietarios.show', compact('propietario'));
    }

    public function update(UpdatePropietarioRequest $request, Propietario $propietario)
    {
        $this->verificarEmpresa($propietario);

        $data = $request->validated();

        try {
            $propietario->update($data);
            return redirect()->route('propietarios.index')
                ->with('success', 'Datos actualizados correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Propietario $propietario)
    {
        $this->verificarEmpresa($propietario);

        if ($propietario->vehiculos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: tiene vehículos asignados.');
        }

        $propietario->delete();
        return redirect()->route('propietarios.index')
            ->with('success', 'Propietario eliminado.');
    }

    private function verificarEmpresa(Propietario $propietario): void
    {
        abort_if($propietario->empresa_id !== Auth::user()->empresa_id, 403, 'No autorizado.');
    }
}