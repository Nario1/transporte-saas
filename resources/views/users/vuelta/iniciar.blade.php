@extends('layouts.conductor')

@section('title', 'Iniciar Vuelta')

@section('extra_css')
<style>
    .verificacion-paso {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px 16px;
        margin-bottom: 14px;
    }
    .paso-titulo {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .paso-sub {
        font-size: 12.5px;
        color: var(--text2);
        margin-bottom: 14px;
    }
    .cam-wrap {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        background: #111;
        aspect-ratio: 4/3;
        margin-bottom: 12px;
    }
    #video-vuelta {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #overlay-vuelta {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .cam-status {
        position: absolute;
        bottom: 8px;
        left: 8px;
        right: 8px;
        background: rgba(0,0,0,0.65);
        color: #fff;
        font-size: 11.5px;
        font-weight: 600;
        padding: 6px 10px;
        border-radius: 8px;
        text-align: center;
    }
    .check-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
    }
    .check-badge.ok    { background: var(--green-l); color: var(--green); }
    .check-badge.fail  { background: var(--red-l);   color: var(--red); }
    .check-badge.wait  { background: var(--accent-l); color: var(--accent); }
    .badge-step {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .no-rostro-alert {
        background: var(--orange-l);
        color: var(--orange);
        border: 1px solid rgba(234,88,12,.2);
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 14px;
    }
</style>
@endsection

@section('content')

{{-- Sin rostro registrado --}}
@if(!$tieneRostro)
    <div class="no-rostro-alert">
        <i class="fa-solid fa-triangle-exclamation"></i> ALERTA: No tienes rostro registrado. Contacta a tu administrador para habilitarte.
    </div>
@endif

{{-- Cabecera --}}
<div class="conductor-hero" style="margin-bottom:18px">
    <div class="conductor-av"><i class="fa-solid fa-car"></i></div>
    <div>
        <div class="conductor-hero-name">Iniciar Vuelta #{{ $proximaVuelta }}</div>
        <div class="conductor-hero-sub">{{ today()->locale('es')->isoFormat('dddd D [de] MMM') }}</div>
    </div>
</div>

{{-- PASO 1: Verificación Facial --}}
<div class="verificacion-paso">
    <div class="paso-titulo">
        <span class="badge-step">1</span>
        Verificación Facial
    </div>
    <div class="paso-sub">Tu cámara verificará tu identidad antes de iniciar.</div>

    @if($requiereFacial && $tieneRostro)
        <div class="cam-wrap">
            <video id="video-vuelta" autoplay muted playsinline></video>
            <canvas id="overlay-vuelta"></canvas>
            <div class="cam-status" id="cam-status-txt">Iniciando cámara...</div>
        </div>
        <canvas id="cap-canvas" style="display:none"></canvas>

        <div id="verificacion-resultado" style="margin-bottom:12px"></div>
    @elseif(!$requiereFacial)
        <div class="alert success" style="background: var(--green-l); color: var(--green); border: 1px solid rgba(34,197,94,0.2); border-radius: 12px; padding: 14px 16px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-check"></i> Autenticación facial no requerida para tu cuenta.
        </div>
    @else
        <div class="alert warning" style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-xmark"></i> AVISO: Sin rostro registrado y autenticación requerida. Contacta a soporte.
        </div>
    @endif
</div>

{{-- PASO 2: Datos de la Vuelta --}}
<div class="verificacion-paso">
    <div class="paso-titulo">
        <span class="badge-step">2</span>
        Datos de la Vuelta
    </div>

    <div class="field mb14">
        <label>Ruta Asignada</label>
        <select id="ruta-select" class="form-control" style="padding:10px;" required>
            <option value="">-- Seleccionar Ruta --</option>
            @foreach($rutas as $r)
                <option value="{{ $r->id }}">{{ $r->nombreCompleto }}</option>
            @endforeach
        </select>
    </div>

    {{-- Sin campos de lat/lng en el DOM para evitar alteraciones --}}
    <div class="field mb14">
        <label>Ubicación GPS (Automática)</label>
        <div id="gps-display-text" style="font-size:14px; font-weight:700; color:var(--accent); background:var(--border); padding:10px; border-radius:10px;">
            Obteniendo ubicación...
        </div>
    </div>
</div>

{{-- Botón iniciar --}}
<button id="btn-iniciar-vuelta"
        onclick="iniciarVuelta()"
        class="btn btn-primary btn-block"
        {{ !$tieneRostro ? '' : 'disabled' }}
        style="font-size:15px;padding:14px">
    Iniciar Vuelta #{{ $proximaVuelta }}
</button>

<div id="iniciando-msg" class="hidden" style="text-align:center;margin-top:12px;color:var(--accent);font-weight:600;font-size:13px">
    Registrando vuelta...
</div>

@php
    $embeddingJson = $rostro ? json_encode($rostro->embedding) : 'null';
@endphp

<script src="{{ asset('js/face-api.min.js') }}"></script>

<script>
const MODELS_URL      = '/models-v2/';
const STORED_EMBED    = @json($rostro?->embedding);
const TIENE_ROSTRO    = {{ $tieneRostro ? 'true' : 'false' }};
const REQUIERE_FACIAL = {{ $requiereFacial ? 'true' : 'false' }};
const CSRF            = '{{ csrf_token() }}';
const INICIAR_URL     = '{{ route("conductor.vuelta.iniciar.post", [], false) }}';
let rostroVerificado  = !REQUIERE_FACIAL; 
if (!TIENE_ROSTRO && REQUIERE_FACIAL) rostroVerificado = false; 
let embeddingCapturado = null;
let detTimeoutId      = null;

let gpsActual = { lat: null, lng: null };

// Función interna para capturar GPS limpio
function capturarGPSInterno() {
    const display = document.getElementById('gps-display-text');
    if (display) display.textContent = 'Buscando satelites...';

    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            if (display) display.textContent = 'GPS no soportado en este navegador';
            resolve(null);
            return;
        }

        const options = { 
            enableHighAccuracy: true, 
            timeout: 15000, // Aumentar a 15 segundos
            maximumAge: 0 
        };

        navigator.geolocation.getCurrentPosition(
            pos => {
                gpsActual.lat = pos.coords.latitude;
                gpsActual.lng = pos.coords.longitude;
                if (display) {
                    display.innerHTML = `<span style="color:var(--green)">${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}</span>`;
                }
                resolve(gpsActual);
            },
            err => {
                console.error("GPS Error:", err);
                let msg = 'Error de ubicacion';
                if (err.code === 1) msg = 'Permiso de GPS denegado';
                else if (err.code === 3) msg = 'Tiempo agotado (reintente)';
                
                if (display) {
                    display.innerHTML = `<span style="color:var(--red)">${msg}</span> <a href="#" onclick="capturarGPSInterno(); return false;" style="margin-left:10px; text-decoration:underline;">Reintentar</a>`;
                }
                resolve(null);
            },
            options
        );
    });
}
capturarGPSInterno(); // Inicio

// Iniciar reconocimiento facial
if (TIENE_ROSTRO && STORED_EMBED) {
    (async () => {
        setCamStatus('Cargando modelos...');
        try {
            const originalFetch = window.fetch;
            window.fetch = function(url, init) {
                if (typeof url === 'string' && url.includes('models-v2')) {
                    const separator = url.includes('?') ? '&' : '?';
                    return originalFetch(`${url}${separator}v=1.0.7`, init);
                }
                return originalFetch(url, init);
            };

            // Forzar backend CPU en iOS para evitar bugs de WebGL / perdida de contexto
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (isIOS) {
                await faceapi.tf.setBackend('cpu');
                await faceapi.tf.ready();
                console.log('Forced CPU backend on iOS');
            }

            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL),
            ]);
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });
            const video = document.getElementById('video-vuelta');
            video.srcObject = stream;
            await video.play();
            setCamStatus('Buscando rostro...');
            iniciarDeteccion();
        } catch (e) {
            setCamStatus('Error de camara: ' + e.message, 'error');
            // Si falla la cámara, permitir iniciar igual
            rostroVerificado = true;
            habilitarBoton();
        }
    })();
}

function detenerCamara() {
    const video = document.getElementById('video-vuelta');
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(t => t.stop());
        video.srcObject = null;
    }
    if (detTimeoutId) clearTimeout(detTimeoutId);
    
    const camWrap = document.querySelector('.cam-wrap');
    if (camWrap) camWrap.style.display = 'none';
}

function iniciarDeteccion() {
    const video   = document.getElementById('video-vuelta');
    const canvas  = document.getElementById('overlay-vuelta');
    const ctx     = canvas.getContext('2d');
    const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.4 });
    const stored  = new Float32Array(STORED_EMBED);

    let intentos = 0;

    async function loopDeteccion() {
        if (rostroVerificado) { detenerCamara(); return; }

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        try {
            const det = await faceapi
                .detectSingleFace(video, options)
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!det) {
                setCamStatus('Acerque su rostro...', 'info');
            } else {
                intentos++;
                const distancia = faceapi.euclideanDistance(det.descriptor, stored);
                const UMBRAL    = 0.55; // Menos = más estricto

                // Dibujar caja
                const box = det.detection.box;
                ctx.strokeStyle = distancia < UMBRAL ? '#22c55e' : '#ef4444';
                ctx.lineWidth   = 3;
                ctx.beginPath();
                ctx.rect(box.x, box.y, box.width, box.height);
                ctx.stroke();

                if (distancia < UMBRAL) {
                    detenerCamara();
                    setCamStatus(`Identidad verificada (${(distancia*100).toFixed(0)}% coincidencia)`, 'success');
                    rostroVerificado = true;
                    habilitarBoton();
                    
                    // Verificar si hay ruta seleccionada para auto-iniciar
                    const rutaSel = document.getElementById('ruta-select').value;
                    if (rutaSel) {
                        mostrarResultado(true, 'Verificacion exitosa. Iniciando vuelta...');
                        setTimeout(() => {
                            iniciarVuelta();
                        }, 1000);
                    } else {
                        mostrarResultado(true, 'Identidad verificada. Selecciona una ruta para empezar.');
                    }
                    return; // Detener loop
                } else if (intentos >= 8) {
                    // Después de varios intentos fallidos, alerta pero permite iniciar
                    detenerCamara();
                    setCamStatus('No se pudo verificar', 'warn');
                    mostrarResultado(false, 'Verificacion fallida. Notifica a tu supervisor.');
                    rostroVerificado = true;
                    habilitarBoton();
                    return; // Detener loop
                } else {
                    setCamStatus(`Verificando... (intento ${intentos}/8)`, 'info');
                }
            }
        } catch (err) {
            console.error("Error en detección facial:", err);
        }

        // Programar la siguiente detección solo después de que la actual haya terminado
        detTimeoutId = setTimeout(loopDeteccion, 60);
    }

    loopDeteccion();
}

function habilitarBoton() {
    document.getElementById('btn-iniciar-vuelta').disabled = false;
}

function setCamStatus(msg, tipo = 'info') {
    const el = document.getElementById('cam-status-txt');
    if (!el) return;
    el.textContent = msg;
    el.style.background = tipo === 'success' ? 'rgba(22,163,74,0.8)'
                        : tipo === 'error'   ? 'rgba(220,38,38,0.8)'
                        : tipo === 'warn'    ? 'rgba(234,88,12,0.8)'
                        :                      'rgba(0,0,0,0.65)';
}

function mostrarResultado(ok, msg) {
    const el = document.getElementById('verificacion-resultado');
    el.innerHTML = `<span class="check-badge ${ok ? 'ok' : 'fail'}" style="emoji-free">${msg}</span>`;
}

async function iniciarVuelta() {
    if (!rostroVerificado) {
        alert('Espera a que se complete la verificación facial.');
        return;
    }

    const rutaSelect = document.getElementById('ruta-select').value;
    if (!rutaSelect) {
        alert('Debes seleccionar una ruta antes de iniciar la vuelta.');
        return;
    }

    document.getElementById('btn-iniciar-vuelta').disabled = true;
    document.getElementById('iniciando-msg').classList.remove('hidden');

    // Captura de GPS forzosa e inalterable justo antes de enviar
    const posFinal = await capturarGPSInterno();

    if (!posFinal || posFinal.lat === null || posFinal.lng === null) {
        alert('No se pudo detectar tu ubicación. Asegúrate de tener activado el GPS (Ubicación) de tu celular y vuelve a intentarlo.');
        document.getElementById('btn-iniciar-vuelta').disabled = false;
        document.getElementById('iniciando-msg').classList.add('hidden');
        return;
    }

    const body = {
        verificado_rostro: rostroVerificado,
        ruta_id:  rutaSelect,
        latitud:  posFinal.lat,
        longitud: posFinal.lng,
    };

    try {
        const resp = await fetch(INICIAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body)
        });
        const data = await resp.json();

        if (data.ok) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + (data.error || 'Error al iniciar vuelta'));
            document.getElementById('btn-iniciar-vuelta').disabled = false;
            document.getElementById('iniciando-msg').classList.add('hidden');
        }
    } catch (e) {
        alert('Error de conexión: ' + e.message);
        document.getElementById('btn-iniciar-vuelta').disabled = false;
        document.getElementById('iniciando-msg').classList.add('hidden');
    }
}
</script>
@endsection
