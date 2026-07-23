<?php

namespace App\Http\Controllers;

use App\Models\Tributo;
use App\Models\Sancion;
use App\Models\PagoMp;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PagoMpController extends Controller
{
    public function __construct(private MercadoPagoService $mp) {}

    // ────────────────────────────────────────────────────────────────
    // Generar link de pago desde panel conductor (auth requerida)
    // POST /conductor/tributos/{tributo}/pagar-mp
    // ────────────────────────────────────────────────────────────────
    public function generarLink(Tributo $tributo)
    {
        $conductor = auth()->user()?->conductor;
        if (! $conductor || $tributo->conductor_id !== $conductor->id) {
            abort(403, 'No autorizado');
        }

        if ($tributo->estado === 'pagado') {
            return back()->with('info', 'Este tributo ya fue pagado.');
        }

        // Generar token único de pago si aún no tiene
        if (! $tributo->token_pago) {
            $tributo->update(['token_pago' => Str::random(48)]);
        }

        // Redirigir a la página pública de pago (Payment Brick se carga allí)
        return redirect()->route('pago.public', ['token' => $tributo->token_pago, 'tipo' => 'tributo']);
    }

    // ────────────────────────────────────────────────────────────────
    // Generar link de pago para SANCIÓN
    // POST /conductor/sanciones/{sancion}/pagar-mp
    // ────────────────────────────────────────────────────────────────
    public function generarLinkSancion(Sancion $sancion)
    {
        $conductor = auth()->user()?->conductor;
        if (! $conductor || $sancion->conductor_id !== $conductor->id) {
            abort(403, 'No autorizado');
        }

        if ($sancion->estado === 'pagado') {
            return back()->with('info', 'Esta sanción ya fue pagada.');
        }

        if (! $sancion->token_pago) {
            $sancion->update(['token_pago' => Str::random(48)]);
        }

        return redirect()->route('pago.public', ['token' => $sancion->token_pago, 'tipo' => 'sancion']);
    }

    // ────────────────────────────────────────────────────────────────
    // Página pública de pago — GET /pagar/{token}/{tipo}
    // ────────────────────────────────────────────────────────────────
    public function mostrarPago(string $token, string $tipo = 'tributo')
    {
        if ($tipo === 'sancion') {
            $model = Sancion::where('token_pago', $token)->with(['conductor', 'vehiculo', 'empresa'])->firstOrFail();
        } else {
            $model = Tributo::where('token_pago', $token)->with(['conductor', 'vehiculo', 'empresa'])->firstOrFail();
        }

        if ($model->estado === 'pagado') {
            return view('users.pagos.ya-pagado', ['tributo' => $model, 'tipo' => $tipo]);
        }

        $publicKey = $this->mp->getPublicKey();

        try {
            $pref = $this->mp->crearPreferencia($model, $tipo);

            $searchData = ($tipo === 'sancion') ? ['sancion_id' => $model->id] : ['tributo_id' => $model->id];
            
            PagoMp::updateOrCreate(
                $searchData,
                [
                    'preference_id' => $pref['id'],
                    'estado'        => 'pendiente',
                    'monto'         => $model->monto,
                    'payment_id'    => null,
                ]
            );

            $preferenceId = $pref['id'];
        } catch (\Throwable $e) {
            Log::error("mostrarPago ({$tipo}): error creando preferencia", ['error' => $e->getMessage()]);
            $preferenceId = null;
        }

        // Reusamos la vista 'pagar' pero pasamos el modelo genérico
        return view('users.pagos.pagar', [
            'tributo'      => $model, // El brick usa 'tributo' en la vista
            'publicKey'    => $publicKey,
            'preferenceId' => $preferenceId,
            'tipo'         => $tipo
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // Procesar pago desde el Payment Brick — POST /pago/procesar
    // ────────────────────────────────────────────────────────────────
    public function procesar(Request $request)
    {
        $tokenPago = $request->input('token_pago', '');
        $tipo      = $request->input('tipo', 'tributo');
        $formData  = $request->input('formData', []);
        
        Log::info("MP procesar iniciado", [
            'token_pago' => $tokenPago,
            'tipo'       => $tipo,
            'formData'   => $formData
        ]);

        if ($tipo === 'sancion') {
            $model = Sancion::where('token_pago', $tokenPago)->with(['conductor', 'empresa'])->first();
        } else {
            $model = Tributo::where('token_pago', $tokenPago)->with(['conductor', 'empresa'])->first();
        }

        if (! $model) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        if ($model->estado === 'pagado') {
            return response()->json([
                'status'   => 'approved',
                'redirect' => url("/pago/retorno?estado=success&token={$tokenPago}&tipo={$tipo}"),
            ]);
        }

        try {
            $resultado     = $this->mp->crearPago($model, $formData, $tipo);
            $paymentId     = (string) ($resultado['id'] ?? '');
            $estadoMp      = $resultado['status'] ?? 'pending';
            $estadoInterno = $this->mp->mapearEstado($estadoMp);
            $metodoPago    = $resultado['payment_method_id'] ?? null;

            // Actualizar BD inmediatamente
            DB::transaction(function () use ($model, $paymentId, $estadoInterno, $metodoPago, $resultado, $tipo) {
                $searchData = ($tipo === 'sancion') ? ['sancion_id' => $model->id] : ['tributo_id' => $model->id];
                
                PagoMp::updateOrCreate(
                    $searchData,
                    [
                        'payment_id'   => $paymentId,
                        'estado'       => $estadoInterno,
                        'metodo'       => $metodoPago,
                        'monto'        => $model->monto,
                        'webhook_data' => $resultado,
                    ]
                );

                if ($estadoInterno === 'aprobado' && $model->estado !== 'pagado') {
                    $model->update([
                        'estado'      => 'pagado',
                        'metodo_pago' => 'mercadopago',
                        'cobrado_at'  => now(),
                    ]);
                }
            });

            Log::info("MP procesar: {$tipo} {$model->id} → {$estadoInterno} (pid={$paymentId})");

            $redirect = url("/pago/retorno?estado={$estadoMp}&token={$tokenPago}&payment_id={$paymentId}&tipo={$tipo}");

            return response()->json([
                'status'   => $estadoMp,
                'message'  => $resultado['status_detail'] ?? '',
                'redirect' => $redirect,
            ]);

        } catch (\Throwable $e) {
            Log::error("MP procesar {$tipo} error", ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────────────
    // Webhook de Mercado Pago — POST /webhook/mercadopago
    // ────────────────────────────────────────────────────────────────
    public function webhook(Request $request)
    {
        Log::info('MP Webhook recibido', ['headers' => $request->headers->all(), 'body' => $request->all()]);

        if (! $this->mp->validarWebhook($request)) {
            Log::warning('MP Webhook: firma inválida');
            return response()->json(['error' => 'Firma inválida'], 400);
        }

        $topic = $request->query('topic') ?? $request->input('type', '');
        $id    = $request->query('id') ?? $request->input('data.id', '');

        if (! in_array($topic, ['payment', 'merchant_order'])) {
            return response()->json(['status' => 'ignorado'], 200);
        }

        try {
            $datosPago = $this->mp->verificarPago((string) $id);
            $this->procesarPago($datosPago);
        } catch (\Throwable $e) {
            Log::error('MP Webhook error', ['payment_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Error interno'], 500);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    // ────────────────────────────────────────────────────────────────
    // Consultar estado actual (Polling desde JS)
    // ────────────────────────────────────────────────────────────────
    public function consultarEstado(Request $request)
    {
        $token = $request->query('token', '');
        $tipo  = $request->query('tipo', 'tributo');

        if (!$token) return response()->json(['error' => 'Token requerido'], 400);

        $model = ($tipo === 'sancion') 
            ? Sancion::where('token_pago', $token)->first() 
            : Tributo::where('token_pago', $token)->first();

        if (!$model) return response()->json(['error' => 'No encontrado'], 404);

        // Si ya está pagado en nuestra base de datos, genial
        if ($model->estado === 'pagado') {
            return response()->json([
                'pagado'   => true,
                'redirect' => route('pago.retorno', ['token' => $token, 'tipo' => $tipo, 'estado' => 'success'])
            ]);
        }

        // Si no está pagado, buscamos si hay un pago pendiente en pagos_mp para verificar con la API
        $pagoMp = ($tipo === 'sancion')
            ? PagoMp::where('sancion_id', $model->id)->latest()->first()
            : PagoMp::where('tributo_id', $model->id)->latest()->first();

        $extRef = "{$tipo}-{$model->id}";

        if (($pagoMp && $pagoMp->payment_id) || $extRef) {
            try {
                // Primero intentamos buscarlo por referencia externa (más robusto para Yape)
                $resultados = $this->mp->buscarPorReferencia($extRef);
                $pagoEncontrado = false;

                foreach ($resultados as $datoPago) {
                    $this->procesarPago($datoPago);
                    $pagoEncontrado = true;
                }

                if (!$pagoEncontrado && $pagoMp && $pagoMp->payment_id) {
                    $datos = $this->mp->verificarPago($pagoMp->payment_id);
                    $this->procesarPago($datos);
                }

                $model->refresh();
                
                if ($model->estado === 'pagado') {
                    return response()->json([
                        'pagado'   => true,
                        'redirect' => route('pago.retorno', ['token' => $token, 'tipo' => $tipo, 'estado' => 'success'])
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error en polling MP: ' . $e->getMessage());
            }
        }

        return response()->json(['pagado' => false]);
    }

    // ────────────────────────────────────────────────────────────────
    // Retorno desde MP (Success / Pending / Failure)
    // ────────────────────────────────────────────────────────────────
    public function retorno(Request $request)
    {
        $estado    = $request->query('estado') ?? $request->query('status', 'pending');
        $token     = $request->query('token', '');
        $tipo      = $request->query('tipo', 'tributo');
        $paymentId = $request->query('payment_id') ?? $request->query('collection_id', '');

        $model = null;

        // 1. Intentar cargar por token (lo más común)
        if ($token) {
            $model = ($tipo === 'sancion') 
                ? Sancion::where('token_pago', $token)->with(['conductor', 'empresa', 'pagoMp'])->first()
                : Tributo::where('token_pago', $token)->with(['conductor', 'empresa', 'pagoMp'])->first();
        }

        // 2. Si tenemos payment_id, procesamos y actualizamos
        if ($paymentId) {
            try {
                $datosPago = $this->mp->verificarPago((string) $paymentId);
                $this->procesarPago($datosPago);
                
                // Si aún no tenemos modelo, intentamos por external_reference
                if (!$model && isset($datosPago['external_reference'])) {
                    if (preg_match('/(tributo|sancion)-(\d+)/', $datosPago['external_reference'], $matches)) {
                        $mTipo = $matches[1];
                        $mId   = (int) $matches[2];
                        $model = ($mTipo === 'sancion') ? Sancion::find($mId) : Tributo::find($mId);
                        $tipo  = $mTipo;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Retorno MP: error verificando pago', ['error' => $e->getMessage()]);
            }
        }

        if ($model) {
            $model->refresh();
            if ($model->estado === 'pagado') $estado = 'success';
        }

        return view('users.pagos.retorno', [
            'tributo'   => $model, 
            'estado'    => $estado, 
            'paymentId' => $paymentId, 
            'tipo'      => $tipo
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // Método privado: actualizar BD con datos de pago de MP
    // ────────────────────────────────────────────────────────────────
    private function procesarPago(array $datosPago): void
    {
        $paymentId   = (string) ($datosPago['id'] ?? '');
        $estadoMp    = $datosPago['status'] ?? 'pending';
        $externalRef = $datosPago['external_reference'] ?? '';
        $metodoPago  = $datosPago['payment_method_id'] ?? null;

        // Detectar tipo y ID de external_reference: "{tipo}-{id}"
        if (preg_match('/(tributo|sancion)-(\d+)/', $externalRef, $matches)) {
            $tipo      = $matches[1];
            $idInterno = (int) $matches[2];
        } else {
            Log::warning('MP procesarPago: external_reference inválido', ['ref' => $externalRef]);
            return;
        }

        $model         = ($tipo === 'sancion') ? Sancion::find($idInterno) : Tributo::find($idInterno);
        $estadoInterno = $this->mp->mapearEstado($estadoMp);

        if (! $model) {
            Log::warning("MP procesarPago: {$tipo} {$idInterno} no encontrado");
            return;
        }

        DB::transaction(function () use ($model, $paymentId, $estadoInterno, $metodoPago, $datosPago, $tipo) {
            $searchData = ($tipo === 'sancion') ? ['sancion_id' => $model->id] : ['tributo_id' => $model->id];
            
            PagoMp::updateOrCreate(
                $searchData,
                [
                    'payment_id'   => $paymentId,
                    'estado'       => $estadoInterno,
                    'metodo'       => $metodoPago,
                    'monto'        => $model->monto,
                    'webhook_data' => $datosPago,
                ]
            );

            if ($estadoInterno === 'aprobado' && $model->estado !== 'pagado') {
                $model->update([
                    'estado'      => 'pagado',
                    'metodo_pago' => 'mercadopago',
                    'cobrado_at'  => now(),
                ]);
            }
        });

        Log::info("MP procesarPago: {$tipo} {$model->id} → {$estadoInterno} (pid={$paymentId})");
    }
}
