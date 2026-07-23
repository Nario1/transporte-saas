<?php

namespace App\Http\Controllers;

use App\Models\Sancion;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Http\Requests\StoreSancionRequest;
use App\Http\Requests\PagarSancionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SancionController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $from = $request->get('from');
        $to   = $request->get('to');

        // Consultas base
        $queryPendientes = Sancion::where('empresa_id', $user->empresa_id)->where('estado', 'pendiente');
        $queryPagadas    = Sancion::where('empresa_id', $user->empresa_id)->where('estado', 'pagado');
        $queryExoneradas = Sancion::where('empresa_id', $user->empresa_id)->where('estado', 'exonerado');

        // Aplicar filtros si existen
        if ($from) {
            $queryPendientes->whereDate('fecha', '>=', $from);
            $queryPagadas->whereDate('fecha', '>=', $from);
            $queryExoneradas->whereDate('fecha', '>=', $from);
        }
        if ($to) {
            $queryPendientes->whereDate('fecha', '<=', $to);
            $queryPagadas->whereDate('fecha', '<=', $to);
            $queryExoneradas->whereDate('fecha', '<=', $to);
        }

        $pendientes = $queryPendientes->with(['vehiculo', 'conductor', 'registrador', 'pagoMp'])
            ->latest('fecha')
            ->paginate(20, ['*'], 'pendientes_page');

        $pagadas    = $queryPagadas->with(['vehiculo', 'conductor', 'cobrador', 'pagoMp'])
            ->latest('fecha')
            ->paginate(20, ['*'], 'pagadas_page');

        $exoneradas = $queryExoneradas->with(['vehiculo', 'conductor', 'registrador'])
            ->latest('fecha')
            ->paginate(20, ['*'], 'exoneradas_page');

        $resumen = [
            'total_pendiente' => $queryPendientes->clone()->sum('monto'),
            'cantidad_pendiente' => $pendientes->total(),
            'total_cobrado_rango' => $queryPagadas->clone()->sum('monto'),
        ];

        return view('admin.sanciones.index', compact('pendientes', 'pagadas', 'exoneradas', 'resumen', 'from', 'to'));
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

        $fechaHoy = today()->toDateString();

        return view('admin.sanciones.create', compact('vehiculos', 'conductores', 'fechaHoy'));
    }

    public function store(StoreSancionRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        $this->verificarVehiculo($data['vehiculo_id'], $user->empresa_id);

        if (!empty($data['conductor_id'])) {
            $this->verificarConductor($data['conductor_id'], $user->empresa_id);
        }

        $data['empresa_id']     = $user->empresa_id;
        $data['registrado_por'] = $user->id;
        $data['estado']         = 'pendiente';

        Sancion::create($data);

        return redirect()->route('sanciones.index')
            ->with('success', 'Sanción registrada correctamente.');
    }

    public function pagar(PagarSancionRequest $request, Sancion $sancion)
    {
        $this->verificarEmpresa($sancion);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validated();

        $sancion->update([
            'estado'      => 'pagado',
            'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
            'cobrado_por' => $user->id,
            'cobrado_at'  => now(),
        ]);

        return back()->with('success', "Sanción de {$sancion->vehiculo->placa} marcada como pagada.");
    }

    public function exonerar(Request $request, Sancion $sancion)
    {
        $this->verificarEmpresa($sancion);

        $request->validate([
            'motivo_exoneracion' => 'required|string|max:255',
        ]);

        $sancion->update([
            'estado'             => 'exonerado',
            'motivo_exoneracion' => $request->motivo_exoneracion,
            'exonerado_por'      => Auth::id(),
            'exonerado_at'       => now(),
        ]);

        return back()->with('success', 'Sanción exonerada correctamente.');
    }

    public function destroy(Sancion $sancion)
    {
        $this->verificarEmpresa($sancion);

        if ($sancion->estado === 'pagado') {
            return back()->with('error', 'No se puede eliminar una sanción que ya fue pagada.');
        }

        $sancion->delete();

        return back()->with('success', 'Sanción eliminada correctamente.');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function verificarEmpresa(Sancion $sancion): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($sancion->empresa_id !== $user->empresa_id, 403, 'Acceso no autorizado.');
    }

    private function verificarVehiculo(int $vehiculoId, int $empresaId): void
    {
        $existe = Vehiculo::where('id', $vehiculoId)
            ->where('empresa_id', $empresaId)
            ->exists();

        abort_if(!$existe, 403, 'El vehículo no pertenece a tu empresa.');
    }

    private function verificarConductor(int $conductorId, int $empresaId): void
    {
        $existe = Conductor::where('id', $conductorId)
            ->where('empresa_id', $empresaId)
            ->exists();

        abort_if(!$existe, 403, 'El conductor no pertenece a tu empresa.');
    }
}