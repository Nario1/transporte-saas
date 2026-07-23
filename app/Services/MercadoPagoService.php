<?php

namespace App\Services;

use App\Models\Tributo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoService
{
    private string $accessToken;
    private string $publicKey;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.mercadopago.com';

    public function __construct()
    {
        $this->accessToken   = config('services.mercadopago.access_token', '');
        $this->publicKey     = config('services.mercadopago.public_key', '');
        $this->webhookSecret = config('services.mercadopago.webhook_secret', '');
    }

    /**
     * Crea una preferencia de pago (mínimo absoluto para sandbox).
     *
     * Solo incluimos 'items'. Cualquier campo adicional (payment_methods,
     * back_urls con localhost, auto_return, etc.) causa 400 en sandbox Perú.
     */
    /**
     * Crea una preferencia de pago (mínimo absoluto para sandbox).
     */
    public function crearPreferencia(mixed $model, string $type = 'tributo'): array
    {
        $id      = (string) $model->id;
        $monto   = (float) $model->monto;
        $title   = ($type === 'tributo') ? 'Tributo TransJunin' : 'Sanción TransJunin';
        $extRef  = "{$type}-{$id}";

        $body = [
            'items' => [
                [
                    'id'          => $id,
                    'title'       => $title,
                    'quantity'    => 1,
                    'unit_price'  => $monto,
                    'currency_id' => 'PEN',
                ],
            ],
            // Allow all payment methods (no exclusions) – necesario para sandbox Perú
            'back_urls' => [
                'success' => route('pago.retorno', ['token' => $model->token_pago, 'tipo' => $type, 'estado' => 'success']),
                'failure' => route('pago.retorno', ['token' => $model->token_pago, 'tipo' => $type, 'estado' => 'failure']),
                'pending' => route('pago.retorno', ['token' => $model->token_pago, 'tipo' => $type, 'estado' => 'pending']),
            ],
            'payment_methods' => [
                'excluded_payment_types' => [],
                'installments' => 1,
            ],
            'external_reference' => $extRef,
        ];


        Log::info('MP crearPreferencia request', ['type' => $type, 'id' => $id, 'body' => $body]);

        $response = Http::withToken($this->accessToken)
            ->post("{$this->baseUrl}/checkout/preferences", $body);

        Log::info('MP crearPreferencia response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if ($response->failed()) {
            Log::error('MP error creando preferencia', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            throw new \RuntimeException('Error MP ' . $response->status() . ': ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Crea un pago directo con el token del Payment Brick.
     */
    public function crearPago(mixed $model, array $formData, string $type = 'tributo'): array
    {
        $id    = (string) $model->id;
        $monto = (float) $model->monto;
        $desc  = ($type === 'tributo') ? "Tributo TransJunin #{$id}" : "Sanción TransJunin #{$id}";
        $extRef = "{$type}-{$id}";

        $body = [
            'transaction_amount' => (float) $monto,
            'token'              => $formData['token'] ?? '',
            'description'        => 'Pago TransJunin #' . $id,
            'installments'       => 1,
            'payment_method_id'  => $formData['payment_method_id'] ?? '',
            'payer' => [
                'email' => $formData['payer']['email'] ?? 'test@test.com',
            ],
            'external_reference' => $extRef,
        ];

        $idempotencyKey = Str::uuid()->toString();

        Log::info('MP crearPago', [
            'type'            => $type,
            'id'              => $id,
            'method'          => $body['payment_method_id'],
            'idempotency_key' => $idempotencyKey
        ]);

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post("{$this->baseUrl}/v1/payments", $body);

        if ($response->failed()) {
            Log::error('MP error pago directo', ['status' => $response->status(), 'body' => $response->json()]);
            throw new \RuntimeException('Error MP ' . $response->status() . ': ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Consulta el estado de un pago.
     */
    public function verificarPago(string $paymentId): array
    {
        $response = Http::withToken($this->accessToken)
            ->get("{$this->baseUrl}/v1/payments/{$paymentId}");

        if ($response->failed()) {
            throw new \RuntimeException('Error verificando pago: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Busca pagos por external_reference.
     */
    public function buscarPorReferencia(string $externalReference): array
    {
        $response = Http::withToken($this->accessToken)
            ->get("{$this->baseUrl}/v1/payments/search", [
                'external_reference' => $externalReference
            ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['results'] ?? [];
    }

    /**
     * Valida firma HMAC del webhook.
     */
    public function validarWebhook(Request $request): bool
    {
        $xSignature = $request->header('x-signature', '');
        if (empty($xSignature) || empty($this->webhookSecret)) {
            return empty($this->webhookSecret);
        }

        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            [$k, $v] = explode('=', trim($part), 2);
            $parts[$k] = $v;
        }
        $ts = $parts['ts'] ?? '';
        $v1 = $parts['v1'] ?? '';
        if (empty($ts) || empty($v1)) return false;

        $dataId   = $request->query('data_id', '');
        $xReqId   = $request->header('x-request-id', '');
        $manifest = "id:{$dataId};request-id:{$xReqId};ts:{$ts};";
        return hash_equals(hash_hmac('sha256', $manifest, $this->webhookSecret), $v1);
    }

    public function mapearEstado(string $estadoMp): string
    {
        return match ($estadoMp) {
            'approved'                              => 'aprobado',
            'rejected'                              => 'rechazado',
            'cancelled'                             => 'cancelado',
            'in_process', 'pending', 'authorized'  => 'en_proceso',
            default                                 => 'pendiente',
        };
    }

    public function getPublicKey(): string { return $this->publicKey; }
}
