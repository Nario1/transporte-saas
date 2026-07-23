<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar {{ $tipo === 'sancion' ? 'Sanción' : 'Tributo' }} — TransJunín</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f0f2f7;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px 16px 48px;
        }

        .container {
            width: 100%;
            max-width: 480px;
        }

        .logo-bar {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            background: #1d4ed8;
            border-radius: 14px;
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .logo-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .logo-sub {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .card-head {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            padding: 20px 24px;
            color: #fff;
        }

        .card-head-label {
            font-size: 12px;
            opacity: .75;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .card-head-amount {
            font-size: 42px;
            font-weight: 800;
            margin: 4px 0;
        }

        .card-head-sub {
            font-size: 13px;
            opacity: .8;
        }

        .card-body {
            padding: 20px 24px;
        }

        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13.5px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
        }

        .info-val {
            font-weight: 600;
            color: #0f172a;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .pill.red {
            background: #fee2e2;
            color: #dc2626;
        }

        .pill.green {
            background: #dcfce7;
            color: #16a34a;
        }

        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 16px 0;
        }

        .secure-note {
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 16px;
        }

        .secure-note span {
            color: #16a34a;
            font-weight: 600;
        }

        .already-paid {
            background: #dcfce7;
            color: #16a34a;
            border-radius: 12px;
            padding: 18px 20px;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
        }

        .error-box {
            background: #fee2e2;
            color: #dc2626;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .loading-mp {
            text-align: center;
            padding: 32px 0;
            color: #64748b;
            font-size: 13px;
        }

        .spinner {
            width: 28px;
            height: 28px;
            border: 3px solid #e2e8f0;
            border-top-color: #1d4ed8;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* El Payment Brick puede necesitar más espacio vertical */
        #wallet_container {
            min-height: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo-bar">
            <div class="logo-icon">TJ</div>
            <div class="logo-name">TransJunín</div>
            <div class="logo-sub">Sistema de cobro {{ $tipo === 'sancion' ? 'de sanciones' : 'de tributos' }}</div>
        </div>

        @if ($tributo->estado === 'pagado')
            <div class="card">
                <div class="card-body">
                    <div class="already-paid">
                        ✅ {{ $tipo === 'sancion' ? 'Esta sanción' : 'Este tributo' }} ya fue pagado.<br>
                        <span style="font-size:13px;font-weight:400;margin-top:6px;display:block;">
                            Pagado el {{ $tributo->cobrado_at?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        @else
            {{-- Resumen del tributo --}}
            <div class="card">
                <div class="card-head"
                    style="background: {{ $tipo === 'sancion' ? 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)' : 'linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%)' }};">
                    <div class="card-head-label">Monto a pagar</div>
                    <div class="card-head-amount">S/ {{ number_format($tributo->monto, 2) }}</div>
                    <div class="card-head-sub">
                        {{ $tipo === 'sancion' ? 'Sanción: ' . $tributo->motivo : 'Tributo del ' . $tributo->fecha->format('d/m/Y') }}
                    </div>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Conductor</span>
                        <span class="info-val">{{ $tributo->conductor?->nombre_completo ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Empresa</span>
                        <span class="info-val">{{ $tributo->empresa?->nombre ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Padrón / Vehículo</span>
                        <div style="text-align: right;">
                            <div class="info-val">#{{ $tributo->vehiculo?->numero_flota }} —
                                {{ $tributo->vehiculo?->placa }}</div>
                            <div style="font-size: 11px; color: #64748b;">{{ $tributo->vehiculo?->marca }}
                                {{ $tributo->vehiculo?->modelo }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Estado</span>
                        <span class="pill red">● Pendiente</span>
                    </div>
                </div>
                <div style="padding: 0 24px 16px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">DISPONIBLE:</span>
                    <span
                        style="background: #73229b; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 800;">YAPE</span>
                    <span
                        style="background: #00bef0; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 800;">PLIN</span>
                    <span
                        style="background: #1d4ed8; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 800;">TARJETAS</span>
                </div>
            </div>

            {{-- Formulario de pago --}}
            <div class="card">
                <div class="card-body">
                    @if (isset($mpError))
                        <div class="error-box">⚠️ {{ $mpError }}</div>
                    @endif

                    @if ($preferenceId)
                        {{-- Wallet Brick: Específicamente para YAPE y Saldo Mercado Pago --}}
                        <div id="wallet_brick_container" style="margin-bottom: 24px;"></div>

                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
                            <div style="font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase;">O
                                pagar con tarjeta</div>
                            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
                        </div>

                        {{-- Payment Brick: Para tarjetas --}}
                        <div id="payment_brick_container"></div>

                        <div class="secure-note">
                            🔒 <span>Pago 100% seguro</span> procesado por Mercado Pago
                        </div>
                    @else
                        <div class="error-box">
                            ⚠️ No se pudo conectar con Mercado Pago.
                            <a href="{{ request()->url() }}" style="color:#dc2626;font-weight:700;">↺ Reintentar</a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if (isset($preferenceId) && $tributo->estado !== 'pagado')
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>
            const mp = new MercadoPago('{{ $publicKey }}', {
                locale: 'es-PE'
            });
            const bricks = mp.bricks();

            /**
             * Payment Brick con preferenceId.
             *
             * El preferenceId es NECESARIO para que el brick funcione en sandbox Perú.
             * La preferencia se crea siempre con back_urls apuntando a localhost:8000
             * y SIN notification_url (que causaba el 503 al ser usado por MP como redirect).
             *
             * Flujo:
             * 1. Brick muestra formulario de tarjeta
             * 2. Usuario paga → onSubmit dispara → llamamos /pago/procesar
             * 3. /pago/procesar llama MP API directamente → actualiza BD
             * 4. JS redirige a localhost:8000/pago/retorno
             * (si el brick hace su propio redirect via back_url → también va a localhost)
             */
            // 1. Wallet Brick (Para Yape / Saldo MP)
            bricks.create('wallet', 'wallet_brick_container', {
                initialization: {
                    preferenceId: '{{ $preferenceId }}',
                    redirectMode: 'modal'
                },
                callbacks: {
                    onReady: () => {
                        const el = document.getElementById('loading-mp');
                        if (el) el.style.display = 'none';
                    },
                    onError: (error) => console.error('Wallet Brick error:', error)
                }
            });

            // 2. Payment Brick (Para Tarjetas)
            bricks.create('payment', 'payment_brick_container', {
                initialization: {
                    amount: {{ (float) $tributo->monto }},
                    preferenceId: '{{ $preferenceId }}',
                    payer: {
                        email: 'test@test.com',
                    },
                },
                customization: {
                    paymentMethods: {
                        creditCard: 'all',
                        debitCard: 'all',
                        ticket: 'all',
                        bankTransfer: 'all',
                    },
                    // El brick de pago individual solo acepta 'individual' o 'association'
                    // pero prefiero omitirlo para dejar los defaults de Sandbox
                },
                callbacks: {
                    onReady: () => {
                        // Ya manejado por Wallet Brick
                    },
                    onSubmit: ({
                        selectedPaymentMethod,
                        formData
                    }) => {
                        return new Promise((resolve, reject) => {
                            fetch('{{ route('pago.procesar') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        formData,
                                        token_pago: '{{ $tributo->token_pago }}',
                                        tipo: '{{ $tipo }}'
                                    }),
                                })
                                .then(r => {
                                    if (!r.ok) throw new Error('HTTP ' + r.status);
                                    return r.json();
                                })
                                .then(data => {
                                    if (data.error) {
                                        reject(data.error);
                                        return;
                                    }
                                    const ok = ['approved', 'in_process', 'pending'];
                                    if (ok.includes(data.status)) {
                                        resolve();
                                        window.location.replace(data.redirect);
                                    } else {
                                        reject(data.message ||
                                            'Pago rechazado. Intenta con otra tarjeta.');
                                    }
                                })
                                .catch(err => {
                                    console.error('Error procesar pago:', err);
                                    reject('Error al conectar con el servidor. Inténtalo de nuevo.');
                                });
                        });
                    },
                    onError: (error) => {
                        console.error('Payment Brick error:', error);
                    },
                },
            });
            // 3. Polling de estado (para Yape y otros redireccionamientos)
            setInterval(() => {
                fetch('{{ route('pago.consultar') }}?token={{ $tributo->token_pago }}&tipo={{ $tipo }}')
                    .then(r => r.json())
                    .then(data => {
                        if (data.pagado) {
                            const container = document.querySelector('.card-body');
                            container.innerHTML = `
                            <div style="text-align:center; padding:20px;">
                                <div style="color: #16a34a; background: #dcfce7; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 40px; box-shadow: 0 4px 12px rgba(22,163,74,0.2);">✓</div>
                                <h2 style="font-size: 24px; font-weight: 800; color: #16a34a; margin-bottom: 8px;">¡Listo, tu pago ya se acreditó!</h2>
                                <p style="font-size: 14px; color: #64748b; margin-bottom: 24px;">El sistema ha procesado tu pago correctamente.</p>
                                <a href="{{ $tipo === 'sancion' ? route('conductor.sanciones') : route('conductor.tributos') }}" 
                                   style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: #1d4ed8; color: #fff; border: none; border-radius: 14px; padding: 16px 32px; font-size: 16px; font-weight: 800; text-decoration: none; width: 100%; transition: all 0.2s; box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);">
                                    <i class="fa-solid fa-arrow-left"></i> Volver a mis {{ $tipo === 'sancion' ? 'Sanciones' : 'Tributos' }}
                                </a>
                            </div>
                        `;
                        }
                    })
                    .catch(err => console.error('Polling error:', err));
            }, 4000);
        </script>
    @endif
</body>

</html>
