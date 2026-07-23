<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Vuelta;
use App\Models\Tributo;
use App\Models\Sancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $empresaId = $user->empresa_id;
        $hoy       = today();

        // ── OFFSET para el gráfico (Navegación) ──────────────────
        $offset = (int) $request->input('offset', 0);
        $fechaFinalGrafico = $hoy->copy()->subDays($offset * 7);

        // Asegurar que los tributos existan para los indicadores
        Tributo::ensureGenerados($empresaId);

        // ── Indicadores principales (Siempre con hoy) ─────────────
        $autosActivos   = Vehiculo::where('empresa_id', $empresaId)->where('estado', 'activo')->count();
        $totalVehiculos = Vehiculo::where('empresa_id', $empresaId)->count();
        $totalConductores = Conductor::where('empresa_id', $empresaId)->where('estado', 'activo')->count();

        // ── Vueltas ───────────────────────────────────────────────
        $vueltasHoy  = Vuelta::where('empresa_id', $empresaId)->whereDate('fecha', $hoy)->count();
        $vueltasAyer = Vuelta::where('empresa_id', $empresaId)->whereDate('fecha', $hoy->copy()->subDay())->count();

        // ── Tributos del día ──────────────────────────────────────
        $tributosHoy      = Tributo::where('empresa_id', $empresaId)->whereDate('fecha', $hoy);
        $totalCobradoHoy  = (clone $tributosHoy)->where('estado', 'pagado')->sum('monto');
        $pagadosHoy       = (clone $tributosHoy)->where('estado', 'pagado')->count();
        $pendientesHoy    = (clone $tributosHoy)->where('estado', 'pendiente')->count();
        $montoPendienteHoy= (clone $tributosHoy)->where('estado', 'pendiente')->sum('monto');

        // ── Deuda acumulada ───────────────────────────────────────
        $deudaTotal    = Tributo::where('empresa_id', $empresaId)->where('estado', 'pendiente')->sum('monto');
        $autosConDeuda = Tributo::where('empresa_id', $empresaId)->where('estado', 'pendiente')
            ->distinct('vehiculo_id')->count('vehiculo_id');

        // ── Sanciones pendientes ──────────────────────────────────
        $sancionesPendientes = Sancion::where('empresa_id', $empresaId)->where('estado', 'pendiente')->count();
        $montoSanciones      = Sancion::where('empresa_id', $empresaId)->where('estado', 'pendiente')->sum('monto');

        // ── Documentos por vencer (15 días) ──────────────────────
        $limite = $hoy->copy()->addDays(15);
        $docsPorVencer = Vehiculo::where('empresa_id', $empresaId)
            ->where(fn($q) =>
                $q->whereBetween('soat_vence',         [$hoy, $limite])
                  ->orWhereBetween('rev_tecnica_vence', [$hoy, $limite])
            )->count();

        $soatVencidos = Vehiculo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('soat_vence', '<', $hoy)
            ->count();

        $revVencidos = Vehiculo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('rev_tecnica_vence', '<', $hoy)
            ->count();

        $tarjetaVencidos = Vehiculo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('tarjeta_prop_vence', '<', $hoy)
            ->count();

        // ── Tabla operativa del día (PAGINADA) ──────────────────
        $vehiculosHoy = Vehiculo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->with([
                'conductor',
                'tributos' => fn($q) => $q->whereDate('fecha', $hoy),
            ])
            ->withCount([
                'vueltas as vueltas_hoy' => fn($q) => $q->whereDate('fecha', $hoy),
            ])
            ->orderBy('numero_flota')
            ->orderBy('placa')
            ->paginate(10, ['*'], 'page_v');

        // ── Ingresos del Periodo (Gráfico con Navegación) ─────────
        $ultimos7 = collect(range(6, 0))->map(function ($i) use ($fechaFinalGrafico, $empresaId) {
            $fecha = $fechaFinalGrafico->copy()->subDays($i);
            return [
                'label' => $fecha->locale('es')->isoFormat('dd D'),
                'monto' => Tributo::where('empresa_id', $empresaId)
                    ->whereDate('fecha', $fecha)
                    ->where('estado', 'pagado')
                    ->sum('monto'),
            ];
        });

        // Rango legible para la UI
        $rangoGrafico = $fechaFinalGrafico->copy()->subDays(6)->locale('es')->isoFormat('D MMM') . ' - ' . $fechaFinalGrafico->locale('es')->isoFormat('D MMM');

        // ── Tributos pendientes para cobrar rápido ────────────────
        $pendientesCobrar = Tributo::where('empresa_id', $empresaId)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'pendiente')
            ->with(['vehiculo', 'conductor'])
            ->get();

        // ── Documentación Crítica (Vencidos) ──────────────────────
        $licenciasVencidas = Conductor::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('licencia_vence', '<', $hoy)
            ->count();

        // ── Licencias por vencer (30 días) ────────────────────────
        $licenciasPorVencer = Conductor::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->whereBetween('licencia_vence', [$hoy, $hoy->copy()->addDays(30)])
            ->count();

        // ── KPIs Rankings ────────────────────────────────────────
        // 1. Ranking de Flotas con más deuda (> 5 días, PAGINADO)
        $topDeudores = Tributo::where('empresa_id', $empresaId)
            ->where('estado', 'pendiente')
            ->select(
                'vehiculo_id', 
                DB::raw('SUM(monto) as total_deuda'), 
                DB::raw('COUNT(*) as dias_deuda'),
                DB::raw('MIN(fecha) as desde_cuando')
            )
            ->groupBy('vehiculo_id')
            ->having('dias_deuda', '>=', 5)
            ->orderByDesc('total_deuda')
            ->with(['vehiculo.conductor', 'vehiculo.propietario'])
            ->paginate(10, ['*'], 'page_d');

        $topConductores = Vuelta::where('empresa_id', $empresaId)
            ->whereDate('fecha', $hoy)
            ->select('conductor_id', DB::raw('COUNT(*) as total_vueltas'))
            ->groupBy('conductor_id')
            ->orderByDesc('total_vueltas')
            ->with('conductor')
            ->limit(5)
            ->get();

        $resumenRutas = Vuelta::where('empresa_id', $empresaId)
            ->whereDate('fecha', $hoy)
            ->select('ruta_id', DB::raw('COUNT(*) as total'))
            ->groupBy('ruta_id')
            ->with('ruta')
            ->get();

        return view('admin.dashboard.index', compact(
            'autosActivos', 'totalVehiculos', 'totalConductores',
            'vueltasHoy', 'vueltasAyer', 'totalCobradoHoy', 'pagadosHoy',
            'pendientesHoy', 'montoPendienteHoy', 'deudaTotal', 'autosConDeuda',
            'sancionesPendientes', 'montoSanciones', 'docsPorVencer', 'soatVencidos', 'revVencidos', 'tarjetaVencidos',
            'vehiculosHoy', 'ultimos7', 'pendientesCobrar', 'licenciasPorVencer', 'licenciasVencidas',
            'topDeudores', 'topConductores', 'resumenRutas', 'offset', 'rangoGrafico'
        ));
    }
}