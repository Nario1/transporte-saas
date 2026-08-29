<?php

namespace App\Http\Controllers\Conductor;

use App\Http\Controllers\Controller;
use App\Models\AlertaOperativo;
use App\Events\AlertaOperativoCreada;
use App\Events\AlertaOperativoFinalizada;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AlertaOperativoController extends Controller
{
    /**
     * ==========================================
     * CONDUCTOR ACTIONS (JSON API)
     * ==========================================
     */

    /**
     * Reportar un operativo (Conductor).
     */
    public function store(Request $request)
    {
        $request->validate([
            'punto' => 'required|in:Punto A,Punto B,Punto C',
        ]);

        $user = auth()->user();
        $conductor = $user->conductor;

        if (!$conductor) {
            return response()->json(['error' => 'No tienes un perfil de conductor asociado.'], 403);
        }

        // Evitar duplicados activos en el mismo punto para la misma empresa
        $existeActivo = AlertaOperativo::where('empresa_id', $conductor->empresa_id)
            ->where('punto', $request->punto)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existeActivo) {
            return response()->json(['message' => 'Ya existe un reporte activo para este punto.'], 200);
        }

        $alerta = AlertaOperativo::create([
            'empresa_id'   => $conductor->empresa_id,
            'conductor_id' => $conductor->id,
            'user_id'      => null,
            'punto'        => $request->punto,
            'estado'       => 'activo',
            'expires_at'   => now()->addHour(),
        ]);

        // Transmitir evento en tiempo real
        broadcast(new AlertaOperativoCreada($alerta))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "Operativo reportado en el {$alerta->punto} correctamente.",
            'alerta'  => $alerta
        ]);
    }

    /**
     * Finalizar un operativo (Conductor).
     */
    public function finalizar(AlertaOperativo $alerta)
    {
        $user = auth()->user();
        $conductor = $user->conductor;

        if (!$conductor || $alerta->empresa_id !== $conductor->empresa_id) {
            return response()->json(['error' => 'Acceso no autorizado.'], 403);
        }

        if ($alerta->estado === 'activo') {
            $alerta->update(['estado' => 'finalizado']);
            broadcast(new AlertaOperativoFinalizada($alerta))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'El operativo se marcó como finalizado.'
        ]);
    }


    /**
     * ==========================================
     * ADMINISTRATOR ACTIONS (Web Views & Redirects)
     * ==========================================
     */

    /**
     * Vista de control y listado (Administrador).
     */
    public function adminIndex()
    {
        $this->middleware('permission:gestionar alertas');

        $empresaId = auth()->user()->empresa_id;

        // Alertas Activas (estado activo y sin expirar)
        $activas = AlertaOperativo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->with(['conductor', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Alertas Pasadas (finalizadas o ya expiradas)
        $historial = AlertaOperativo::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->where('estado', 'finalizado')
                  ->orWhere('expires_at', '<=', now());
            })
            ->with(['conductor', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.alertas.index', compact('activas', 'historial'));
    }

    /**
     * Reportar un operativo (Administrador).
     */
    public function adminStore(Request $request)
    {
        $this->middleware('permission:gestionar alertas');

        $request->validate([
            'punto' => 'required|in:Punto A,Punto B,Punto C',
        ]);

        $empresaId = auth()->user()->empresa_id;

        // Evitar duplicados activos
        $existeActivo = AlertaOperativo::where('empresa_id', $empresaId)
            ->where('punto', $request->punto)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existeActivo) {
            return back()->with('error', 'Ya existe un reporte activo para este punto.');
        }

        $alerta = AlertaOperativo::create([
            'empresa_id'   => $empresaId,
            'conductor_id' => null,
            'user_id'      => auth()->id(),
            'punto'        => $request->punto,
            'estado'       => 'activo',
            'expires_at'   => now()->addHour(),
        ]);

        broadcast(new AlertaOperativoCreada($alerta))->toOthers();

        return back()->with('success', "Se reportó el operativo en el {$alerta->punto} correctamente.");
    }

    /**
     * Finalizar un operativo (Administrador).
     */
    public function adminFinalizar(AlertaOperativo $alerta)
    {
        $this->middleware('permission:gestionar alertas');

        if ($alerta->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acceso no autorizado.');
        }

        if ($alerta->estado === 'activo') {
            $alerta->update(['estado' => 'finalizado']);
            broadcast(new AlertaOperativoFinalizada($alerta))->toOthers();
        }

        return back()->with('success', 'El operativo se marcó como finalizado.');
    }
}
