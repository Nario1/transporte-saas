<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use App\Models\ConductorRostro;
use App\Http\Requests\StoreRostroRequest;
use App\Services\ReconocimientoFacialService;
use Illuminate\Http\Request;

class RostroController extends Controller
{
    public function __construct(private ReconocimientoFacialService $rostroService) {}
    /**
     * Muestra la página de captura/gestión de rostro para un conductor (admin).
     */
    public function show(Conductor $conductor)
    {
        $this->authorize('view', $conductor);

        $rostro = ConductorRostro::where('conductor_id', $conductor->id)
            ->where('activo', true)
            ->latest()
            ->first();

        return view('admin.conductores.rostro', compact('conductor', 'rostro'));
    }

    /**
     * Guarda o actualiza el rostro del conductor.
     * Recibe: foto (base64) + embedding (JSON array de 128 floats)
     */
    public function store(StoreRostroRequest $request, Conductor $conductor)
    {
        $this->authorize('update', $conductor);

        $request->validated();

        // Decodificar embedding
        $embedding = json_decode($request->embedding, true);
        if (! is_array($embedding) || count($embedding) < 64) {
            return response()->json([
                'ok'    => false,
                'error' => 'Embedding facial inválido.',
            ], 422);
        }

        $rostro = $this->rostroService->guardarPerfilFacial($conductor, $embedding, $request->foto_b64);

        return response()->json([
            'ok'       => true,
            'mensaje'  => 'Rostro registrado exitosamente.',
            'foto_url' => $rostro->foto_url,
        ]);
    }

    /**
     * Elimina (desactiva) el rostro del conductor.
     */
    public function destroy(Conductor $conductor)
    {
        $this->authorize('update', $conductor);

        $this->rostroService->eliminarPerfilFacial($conductor);

        return back()->with('success', 'Registro facial eliminado.');
    }

    /**
     * Muestra la página de captura para el conductor (Autogestión).
     */
    public function showConductor()
    {
        $conductor = auth()->user()->conductor;
        if (!$conductor) abort(403);

        $rostro = ConductorRostro::where('conductor_id', $conductor->id)
            ->where('activo', true)
            ->latest()
            ->first();

        return view('users.conductor.rostro', compact('conductor', 'rostro'));
    }

    /**
     * Guarda el rostro del conductor (Autogestión).
     */
    public function storeConductor(Request $request)
    {
        $conductor = auth()->user()->conductor;
        if (!$conductor) abort(403);

        $embedding = json_decode($request->embedding, true);
        if (! is_array($embedding) || count($embedding) < 64) {
            return response()->json(['ok' => false, 'error' => 'Embedding facial inválido.'], 422);
        }

        $this->rostroService->guardarPerfilFacial($conductor, $embedding, $request->foto_b64);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Rostro registrado exitosamente.',
            'redirect' => route('conductor.dashboard')
        ]);
    }
}
