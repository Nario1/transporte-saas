@extends('layouts.conductor')

@section('title', 'Registro Facial')

@section('content')
<div class="conductor-hero" style="margin-bottom:18px">
    <div class="conductor-av">👤</div>
    <div>
        <div class="conductor-hero-name">Registro de Rostro</div>
        <div class="conductor-hero-sub">Seguridad Biométrica TransJunín</div>
    </div>
</div>

<div style="padding: 0 16px;">
    {{-- Mensaje Informativo --}}
    @if (!$rostro)
        <div class="alert warning" style="background: var(--red-l); color: var(--red); border: 1px solid rgba(220,38,38,0.2); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-weight: 600; font-size: 13px;">
            ⚠️ Tu cuenta requiere un registro facial para poder iniciar vueltas. Por favor, captura tu rostro ahora.
        </div>
    @else
         <div class="card" style="margin-bottom: 20px; border-top: 4px solid var(--green);">
            <div class="card-body flex-h" style="gap: 15px; align-items: center;">
                <img src="{{ $rostro->foto_url }}" style="width: 60px; height: 60px; border-radius: 12px; object-fit: cover; border: 2px solid var(--green);">
                <div>
                    <div style="font-weight: 800; color: var(--green); font-size: 14px;">✓ Rostro Registrado</div>
                    <div style="font-size: 11px; color: var(--text3);">Sincronizado correctamente. Puedes actualizarlo si lo deseas.</div>
                </div>
            </div>
         </div>
    @endif

    {{-- Área de captura --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-camera"></i> Cámara de Registro</span>
        </div>
        <div class="card-body" style="padding: 16px;">
            
            {{-- Video --}}
            <div style="position: relative; border-radius: 14px; overflow: hidden; background: #fff; aspect-ratio: 4/3; margin-bottom: 15px; border: 8px solid #fff; box-shadow: 0 0 20px rgba(255, 255, 255, 0.8);">
                <video id="video" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                <canvas id="overlay" style="position: absolute; inset: 0; width: 100%; height: 100%;"></canvas>
                
                <div id="detection-status" style="position: absolute; bottom: 10px; left: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; font-size: 11px; font-weight: 700; padding: 8px; border-radius: 8px; text-align: center;">
                    Iniciando cámara...
                </div>
            </div>

            <canvas id="capture-canvas" style="display:none"></canvas>

            <div class="flex-v" style="gap: 10px;">
                <button id="btn-capturar" disabled onclick="capturarFoto()" class="btn btn-primary btn-block" style="padding: 14px; font-size: 15px;">
                    <i class="fa-solid fa-camera-retro"></i> Capturar y Guardar
                </button>
                
                @if($rostro)
                    <a href="{{ route('conductor.dashboard') }}" class="btn btn-secondary btn-block" style="padding: 12px; font-size: 13px; justify-content: center;">
                        Regresar al Dashboard
                    </a>
                @endif
            </div>

            <div id="procesando" class="hidden" style="text-align: center; margin-top: 15px; color: var(--accent); font-weight: 700; font-size: 13px;">
                <i class="fa-solid fa-circle-notch fa-spin"></i> Procesando biometría...
            </div>

            <div id="resultado" class="hidden" style="margin-top: 15px; padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 600; text-align: center;"></div>
        </div>
    </div>
</div>

<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const MODELS_URL = '/models-v2/';
    const STORE_URL  = '{{ route("conductor.rostro.store") }}';
    const CSRF       = '{{ csrf_token() }}';

    let detectionInterval = null;
    let rostroDetectado   = false;
    let watchdogTimer     = null; // Seguridad: fuerza captura si se atasca

    async function iniciarCamara() {
        setStatus('Cargando modelos...');
        try {
            const originalFetch = window.fetch;
            window.fetch = function(url, init) {
                if (typeof url === 'string' && url.includes('models-v2')) {
                    const sep = url.includes('?') ? '&' : '?';
                    return originalFetch(`${url}${sep}v=1.0.7`, init);
                }
                return originalFetch(url, init);
            };

            // En iOS forzar CPU para evitar pérdida de contexto WebGL
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (isIOS) {
                await faceapi.tf.setBackend('cpu');
                await faceapi.tf.ready();
            }

            // Carga en paralelo: los 3 modelos necesarios
            setStatus('Cargando modelos (1/3)...');
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_URL);
            setStatus('Cargando modelos (2/3)...');
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_URL);
            setStatus('Cargando modelos (3/3)...');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL);

            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user', 
                    width:  { ideal: 320, max: 640 },
                    height: { ideal: 240, max: 480 }
                } 
            });
            const video = document.getElementById('video');
            video.srcObject = stream;
            await video.play();

            setStatus('Posiciona tu rostro frente a la cámara');
            iniciarDeteccion();

            // WATCHDOG: Si en 25 segundos no hubo captura, habilitar botón manual
            watchdogTimer = setTimeout(() => {
                if (!rostroDetectado) {
                    setStatus('Rostro no detectado — pulsa el botón para capturar', 'error');
                    const btn = document.getElementById('btn-capturar');
                    if (btn) { btn.disabled = false; btn.style.display = ''; }
                }
            }, 25000);

        } catch (err) {
            setStatus('Error: ' + err.message, 'error');
        }
    }

    function detenerCamara() {
        const video = document.getElementById('video');
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(t => t.stop());
            video.srcObject = null;
        }
        if (detectionInterval) clearTimeout(detectionInterval);
    }

    let detectadoFrames = 0;
    const FRAMES_PARA_CAPTURA = 2; // 2 frames con rostro = captura automática

    function iniciarDeteccion() {
        const video  = document.getElementById('video');
        const canvas = document.getElementById('overlay');
        const ctx    = canvas.getContext('2d');
        // minConfidence más bajo = detecta más fácil en gama baja
        const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.28 });

        // Canvas diminuto 160x120 = 16x menos píxeles = 16x más rápido en CPU
        const aux    = document.createElement('canvas');
        aux.width    = 160;
        aux.height   = 120;
        const auxCtx = aux.getContext('2d');

        async function loopDeteccion() {
            if (rostroDetectado) {
                if (detectionInterval) clearTimeout(detectionInterval);
                return;
            }

            // Esperar a que el video tenga dimensiones reales
            if (!video.videoWidth || !video.videoHeight || video.readyState < 2) {
                detectionInterval = setTimeout(loopDeteccion, 150);
                return;
            }

            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            try {
                // Paso 1: detección simple ultra-rápida (sin landmarks)
                auxCtx.drawImage(video, 0, 0, 160, 120);
                const simpleDetection = await faceapi.detectSingleFace(aux, options);

                if (!simpleDetection) {
                    detectadoFrames = 0;
                    setStatus('Posiciona tu rostro frente a la cámara', 'info');
                    detectionInterval = setTimeout(loopDeteccion, 80);
                    return;
                }

                // Rostro detectado - dibujar indicador azul mientras procesa
                const scaledBox = faceapi.resizeResults(simpleDetection, { width: canvas.width, height: canvas.height }).box;
                ctx.strokeStyle = '#3b82f6';
                ctx.lineWidth   = 4;
                ctx.strokeRect(scaledBox.x, scaledBox.y, scaledBox.width, scaledBox.height);
                setStatus('Rostro detectado... procesando...', 'info');

                detectadoFrames++;

                // Paso 2: solo cuando hay suficientes frames = extracción de descriptor
                if (detectadoFrames >= FRAMES_PARA_CAPTURA) {
                    const detFull = await faceapi.detectSingleFace(aux, options)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detFull) {
                        rostroDetectado = true;
                        if (watchdogTimer) clearTimeout(watchdogTimer);
                        setStatus('✓ Rostro capturado. Guardando...', 'success');
                        // Dibujar caja verde
                        const box = faceapi.resizeResults(detFull, { width: canvas.width, height: canvas.height }).detection.box;
                        ctx.strokeStyle = '#22c55e';
                        ctx.lineWidth   = 4;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);
                        capturarFoto();
                        return; // Detener loop
                    } else {
                        // No logró sacar descriptor - reset y reintentar
                        detectadoFrames = 0;
                    }
                }
            } catch (err) {
                console.error('Error detección:', err);
                detectadoFrames = 0;
            }

            detectionInterval = setTimeout(loopDeteccion, 80);
        }

        loopDeteccion();
    }

    // Ocultar botón ya que es automático
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btn-capturar');
        if (btn) btn.style.display = 'none';
    });

    async function capturarFoto() {
        if (!rostroDetectado) return;

        if (detectionInterval) clearTimeout(detectionInterval);
        if (watchdogTimer)     clearTimeout(watchdogTimer);
        document.getElementById('procesando').classList.remove('hidden');

        const video  = document.getElementById('video');
        const canvas = document.getElementById('capture-canvas');
        canvas.width  = video.videoWidth  || 320;
        canvas.height = video.videoHeight || 240;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const fotoBase64 = canvas.toDataURL('image/jpeg', 0.85);

        // Canvas pequeño para descriptor rápido
        const sm = document.createElement('canvas');
        sm.width  = 160;
        sm.height = 120;
        sm.getContext('2d').drawImage(video, 0, 0, 160, 120);

        const det = await faceapi
            .detectSingleFace(sm, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.25 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!det) {
            // En gama baja puede fallar el descriptor - enviar con embedding vacío
            // y solo guardar la foto
            setStatus('Reintentando captura...', 'info');
            rostroDetectado = false;
            document.getElementById('procesando').classList.add('hidden');
            iniciarDeteccion();
            return;
        }

        const embedding = Array.from(det.descriptor);

        try {
            const resp = await fetch(STORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ embedding: JSON.stringify(embedding), foto_b64: fotoBase64 })
            });
            const data = await resp.json();

            if (data.ok) {
                detenerCamara();
                document.getElementById('video').parentElement.style.display = 'none';
                mostrarResultado(true, '✓ Registro exitoso. Redirigiendo...');
                setTimeout(() => window.location.href = data.redirect, 1800);
            } else {
                mostrarResultado(false, 'Error: ' + (data.error || 'Error al guardar'));
                rostroDetectado = false;
                iniciarDeteccion();
            }
        } catch (e) {
            mostrarResultado(false, 'Error de conexión');
            rostroDetectado = false;
        } finally {
            document.getElementById('procesando').classList.add('hidden');
        }
    }

    function setStatus(msg, tipo = 'info') {
        const el = document.getElementById('detection-status');
        el.textContent = msg;
        el.style.background = tipo === 'success' ? 'rgba(22,163,74,0.8)' : (tipo === 'error' ? 'rgba(220,38,38,0.8)' : 'rgba(0,0,0,0.6)');
    }

    function mostrarResultado(ok, msg) {
        const el = document.getElementById('resultado');
        el.classList.remove('hidden');
        el.style.background = ok ? 'var(--green-l)' : 'var(--red-l)';
        el.style.color = ok ? 'var(--green)' : 'var(--red)';
        el.textContent = msg;
    }

    document.addEventListener('DOMContentLoaded', iniciarCamara);
</script>
@endsection
