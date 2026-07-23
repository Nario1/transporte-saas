<?php

namespace App\Http\Controllers;

use App\Models\Tributo;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Http\Requests\StoreTributoRequest;
use App\Http\Requests\PagarTributoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TributoController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fecha = request('fecha', today()->toDateString());

        // 1. Automatización: Asegurar que los tributos estén generados al entrar (si no es fecha futura)
        if (\Carbon\Carbon::parse($fecha)->isPast() || \Carbon\Carbon::parse($fecha)->isToday()) {
            Tributo::ensureGenerados($user->empresa_id);
        }

        // 2. Cargar tributos del día seleccionado
        $pagados = Tributo::where('empresa_id', $user->empresa_id)
            ->whereDate('fecha', $fecha)
            ->where('estado', 'pagado')
            ->with(['vehiculo', 'conductor', 'cobrador'])
            ->paginate(20, ['*'], 'pagados_page')
            ->withQueryString();

        $pendientes = Tributo::where('empresa_id', $user->empresa_id)
            ->whereDate('fecha', $fecha)
            ->where('estado', 'pendiente')
            ->with(['vehiculo', 'conductor'])
            ->paginate(20, ['*'], 'pendientes_page')
            ->withQueryString();
            
        $exonerados = Tributo::where('empresa_id', $user->empresa_id)
            ->whereDate('fecha', $fecha)
            ->where('estado', 'exonerado')
            ->with(['vehiculo', 'conductor'])
            ->paginate(20, ['*'], 'exonerados_page')
            ->withQueryString();

        // 3. Resumen del día (Calculados sobre la consulta completa para evitar errores de paginación)
        $queryBase = Tributo::where('empresa_id', $user->empresa_id)->whereDate('fecha', $fecha);

        $resumen = [
            'total_cobrado'    => $queryBase->clone()->where('estado', 'pagado')->sum('monto'),
            'total_pendiente'  => $queryBase->clone()->where('estado', 'pendiente')->sum('monto'),
            'autos_pagaron'    => $pagados->total(),
            'autos_pendientes' => $pendientes->total(),
            'autos_exonerados' => $exonerados->total(),
        ];

        $deudas = Tributo::where('empresa_id', $user->empresa_id)
            ->where('estado', 'pendiente')
            ->with(['vehiculo'])
            ->selectRaw('vehiculo_id, SUM(monto) as total_deuda, COUNT(*) as dias_deuda')
            ->groupBy('vehiculo_id')
            ->get();

        return view('admin.tributos.index', compact(
            'pagados', 'pendientes', 'exonerados', 'resumen', 'deudas', 'fecha'
        ));
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

        $fechaHoy = today()->toDateString();

        return view('admin.tributos.create', compact('vehiculos', 'fechaHoy'));
    }

    public function store(StoreTributoRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        $this->verificarVehiculo($data['vehiculo_id'], $user->empresa_id);

        Tributo::updateOrCreate(
            [
                'vehiculo_id' => $data['vehiculo_id'],
                'fecha'       => $data['fecha'],
            ],
            [
                'empresa_id'    => $user->empresa_id,
                'conductor_id'  => $data['conductor_id'],
                'monto'         => $data['monto'],
                'metodo_pago'   => $data['metodo_pago'],
                'estado'        => 'pagado',
                'cobrado_por'   => $user->id,
                'cobrado_at'    => now(),
                'observaciones' => $data['observaciones'] ?? null,
            ]
        );

        return redirect()->route('tributos.index')
            ->with('success', 'Tributo registrado correctamente.');
    }

    // Cobro rápido desde el listado (botón cobrar)
    public function cobrar(PagarTributoRequest $request, Tributo $tributo)
    {
        $this->verificarEmpresa($tributo);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $metodo = $request->validated();

        $tributo->update([
            'estado'      => 'pagado',
            'metodo_pago' => $metodo['metodo_pago'],
            'cobrado_por' => $user->id,
            'cobrado_at'  => now(),
        ]);

        return back()->with('success', "Tributo de {$tributo->vehiculo->placa} cobrado correctamente.");
    }

    // Acción para exonerar un tributo (mantenimiento, etc.)
    public function exonerar(Request $request, Tributo $tributo)
    {
        $this->verificarEmpresa($tributo);

        $request->validate([
            'motivo_exoneracion' => 'required|string|max:255',
        ]);

        $tributo->update([
            'estado'             => 'exonerado',
            'monto'              => 0, // No paga nada
            'motivo_exoneracion' => $request->motivo_exoneracion,
            'exonerado_por'      => Auth::id(),
            'exonerado_at'       => now(),
        ]);

        return back()->with('success', "Unidad {$tributo->vehiculo->placa} exonerada correctamente.");
    }

    // Generar tributos pendientes del día para todos los vehículos activos
    public function generarDelDia()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $generados = Tributo::ensureGenerados($user->empresa_id);

        return back()->with('success', "Se procesaron los tributos de hoy. Unidades nuevas: {$generados}.");
    }

    // Exonerar todos los pendientes de una fecha específica
    public function exonerarTodoHoy(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fecha = $request->input('fecha', today()->toDateString());

        $tributos = Tributo::where('empresa_id', $user->empresa_id)
            ->whereDate('fecha', $fecha)
            ->where('estado', 'pendiente')
            ->get();

        $total = $tributos->count();
        $motivo = $request->input('motivo_exoneracion', 'Exoneración Masiva');

        foreach ($tributos as $t) {
            $t->update([
                'estado'             => 'exonerado',
                'monto'              => 0,
                'motivo_exoneracion' => "MASIVO: " . $motivo,
                'exonerado_por'      => $user->id,
                'exonerado_at'       => now(),
            ]);
        }

        return redirect()->route('tributos.index', ['fecha' => $fecha])
            ->with('success', "Se han exonerado {$total} unidades para el día " . \Carbon\Carbon::parse($fecha)->format('d/m/Y') . " correctamente.");
    }

    public function detalleDeuda(Vehiculo $vehiculo)
    {
        $this->verificarVehiculo($vehiculo->id, Auth::user()->empresa_id);

        $detalles = Tributo::where('vehiculo_id', $vehiculo->id)
            ->where('estado', 'pendiente')
            ->orderBy('fecha', 'desc')
            ->get(['fecha', 'monto'])
            ->map(fn($t) => [
                'fecha' => $t->fecha->toDateString(),
                'monto' => $t->monto
            ]);

        return response()->json([
            'vehiculo' => [
                'numero_flota' => $vehiculo->numero_flota,
                'placa'        => $vehiculo->placa,
                'propietario'  => $vehiculo->propietario?->nombre ?? 'Sin Propietario',
            ],
            'detalles' => $detalles,
            'total'    => $detalles->sum('monto')
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function verificarEmpresa(Tributo $tributo): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($tributo->empresa_id !== $user->empresa_id, 403, 'Acceso no autorizado.');
    }

    private function verificarVehiculo(int $vehiculoId, int $empresaId): void
    {
        $existe = Vehiculo::where('id', $vehiculoId)
            ->where('empresa_id', $empresaId)
            ->exists();

        abort_if(!$existe, 403, 'El vehículo no pertenece a tu empresa.');
    }
}