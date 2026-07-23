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
            <div style="position: relative; border-radius: 14px; overflow: hidden; background: #111; aspect-ratio: 4/3; margin-bottom: 15px;">
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
    const MODELS_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js/weights/';
    const STORE_URL  = '{{ route("conductor.rostro.store") }}';
    const CSRF       = '{{ csrf_token() }}';

    let detectionInterval = null;
    let rostroDetectado   = false;

    async function iniciarCamara() {
        setStatus('Cargando modelos faciales...');
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL),
            ]);
            
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user', width: 640 } 
            });
            const video = document.getElementById('video');
            video.srcObject = stream;
            await video.play();
            
            setStatus('Posiciona tu rostro frente a la camara');
            iniciarDeteccion();
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
        if (detectionInterval) clearInterval(detectionInterval);
    }

    let detectadoSegundos = 0;
    const intervalTime = 500; // ms

    function iniciarDeteccion() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('overlay');
        const ctx = canvas.getContext('2d');
        const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });

        detectionInterval = setInterval(async () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const det = await faceapi.detectSingleFace(video, options)
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (det) {
                const box = det.detection.box;
                ctx.strokeStyle = '#22c55e';
                ctx.lineWidth = 3;
                ctx.strokeRect(box.x, box.y, box.width, box.height);

                rostroDetectado = true;
                detectadoSegundos += intervalTime;

                if (detectadoSegundos >= 1500) {
                    clearInterval(detectionInterval);
                    setStatus('Procesando registro...', 'success');
                    capturarFoto();
                } else {
                    setStatus('Rostro detectado... manten la posicion', 'success');
                }
            } else {
                rostroDetectado = false;
                detectadoSegundos = 0;
                setStatus('Buscando rostro...', 'info');
                document.getElementById('btn-capturar').disabled = true;
            }
        }, intervalTime);
    }

    // Ocultar botón ya que es automático
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btn-capturar');
        if (btn) btn.style.display = 'none';
    });

    async function capturarFoto() {
        if (!rostroDetectado) return;

        clearInterval(detectionInterval);
        document.getElementById('btn-capturar').disabled = true;
        document.getElementById('procesando').classList.remove('hidden');

        const video = document.getElementById('video');
        const canvas = document.getElementById('capture-canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const fotoBase64 = canvas.toDataURL('image/jpeg', 0.9);

        // Re-detectar en canvas para sacar el descriptor final
        const det = await faceapi.detectSingleFace(canvas, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!det) {
            alert('Error al procesar la imagen. Intentalo de nuevo.');
            location.reload();
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
                mostrarResultado(true, 'Registro exitoso. Redirigiendo...');
                setTimeout(() => window.location.href = data.redirect, 2000);
            } else {
                mostrarResultado(false, 'Error: ' + (data.error || 'Error al guardar'));
                document.getElementById('btn-capturar').disabled = false;
                iniciarDeteccion();
            }
        } catch (e) {
            mostrarResultado(false, 'Error de conexion');
            document.getElementById('btn-capturar').disabled = false;
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
