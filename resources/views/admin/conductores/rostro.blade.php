@extends('layouts.admin')

@section('back_url', route('conductores.show', $conductor))

@section('title', 'Rostro Facial — ' . $conductor->nombre_completo)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registro Facial</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $conductor->nombre_completo }}</p>
        </div>
        <a href="{{ route('conductores.show', $conductor) }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
            ← Volver
        </a>
    </div>

    {{-- Estado actual del rostro --}}
    @if ($rostro)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6 flex items-center gap-6">
            <img src="{{ $rostro->foto_url }}" alt="Foto facial"
                 class="w-24 h-24 rounded-xl object-cover border-2 border-green-300">
            <div>
                <div class="text-green-600 font-bold text-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Rostro registrado
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Registrado el {{ $rostro->created_at->format('d/m/Y \a \l\a\s H:i') }}
                </p>
                <div class="flex gap-3 mt-3">
                    <button onclick="iniciarCaptura()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-camera"></i> Recapturar
                    </button>
                    <form action="{{ route('conductores.rostro.destroy', $conductor) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar registro facial de este conductor?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-100 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-200 transition">
                            <i class="fa-solid fa-trash-can"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 text-yellow-700 text-sm font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i> Este conductor no tiene rostro registrado. Capture uno para habilitar la verificación facial.
        </div>
    @endif

    {{-- Área de captura --}}
    <div id="captura-area" class="{{ $rostro ? 'hidden' : '' }}">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4"><i class="fa-solid fa-camera"></i> Captura de Rostro</h2>

            {{-- Video webcam --}}
            <div class="relative rounded-xl overflow-hidden bg-gray-900 mb-4" style="aspect-ratio: 4/3;">
                <video id="video" autoplay muted playsinline
                       class="w-full h-full object-cover"></video>
                <canvas id="overlay" class="absolute inset-0 w-full h-full"></canvas>

                {{-- Estado de detección --}}
                <div id="detection-status"
                     class="absolute bottom-3 left-3 right-3 bg-black/60 text-white text-xs font-semibold px-3 py-2 rounded-lg text-center">
                    Iniciando camara...
                </div>
            </div>

            {{-- Canvas oculto para captura --}}
            <canvas id="capture-canvas" class="hidden"></canvas>

            {{-- Botones --}}
            <div class="flex gap-3">
                <button id="btn-capturar" disabled onclick="capturarFoto()"
                        class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-camera-retro"></i> Capturar y Registrar
                </button>
                @if($rostro)
                    <button onclick="cancelarCaptura()"
                            class="px-5 py-3 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition">
                        Cancelar
                    </button>
                @endif
            </div>

            {{-- Progreso --}}
            <div id="procesando" class="hidden mt-4 text-center text-blue-600 font-semibold text-sm">
                <i class="fa-solid fa-circle-notch fa-spin"></i> Procesando embedding facial...
            </div>
            <div id="resultado" class="hidden mt-4"></div>
        </div>
    </div>

    {{-- Botón para iniciar captura si ya tiene rostro --}}
    @if($rostro)
        <button id="btn-iniciar-captura" onclick="iniciarCaptura()"
                class="hidden">Recapturar</button>
    @endif
</div>

{{-- Scripts face-api.js --}}
<script src="{{ asset('js/face-api.min.js') }}"></script>

<script>
const MODELS_URL  = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js/weights/';
const CSRF_TOKEN  = '{{ csrf_token() }}';
const STORE_URL   = '{{ route("conductores.rostro.store", $conductor) }}';

let detectionInterval = null;
let rostroDetectado   = false;

// Iniciar cámara y modelos face-api
async function iniciarCaptura() {
    document.getElementById('captura-area').classList.remove('hidden');
    if (document.getElementById('btn-iniciar-captura')) {
        document.getElementById('btn-iniciar-captura').classList.add('hidden');
    }
    await iniciarCamara();
}

function cancelarCaptura() {
    document.getElementById('captura-area').classList.add('hidden');
    detenerCamara();
}

async function iniciarCamara() {
    setStatus('Cargando modelos de reconocimiento facial...');
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL),
        ]);
        setStatus('Modelos cargados. Iniciando cámara...');

        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: 640, height: 480 }
        });

        const video = document.getElementById('video');
        video.srcObject = stream;
        await video.play();

        setStatus('Posicione el rostro en el centro del cuadro...');
        iniciarDeteccion();
    } catch (err) {
        setStatus('Error: ' + err.message, 'error');
        console.error(err);
    }
}

function detenerCamara() {
    const video = document.getElementById('video');
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(t => t.stop());
        video.srcObject = null;
    }
    clearInterval(detectionInterval);
}

function iniciarDeteccion() {
    const video   = document.getElementById('video');
    const canvas  = document.getElementById('overlay');
    const ctx     = canvas.getContext('2d');
    const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });

    let detectadoSegundos = 0;
    const intervalTime = 400; // ms

    detectionInterval = setInterval(async () => {
        if (video.paused || video.ended) return;

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const detection = await faceapi
            .detectSingleFace(video, options)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (detection) {
            // Dibujar caja del rostro
            const box = detection.detection.box;
            ctx.strokeStyle = '#22c55e';
            ctx.lineWidth   = 3;
            ctx.beginPath();
            ctx.rect(box.x, box.y, box.width, box.height);
            ctx.stroke();

            rostroDetectado = true;
            detectadoSegundos += intervalTime;

            if (detectadoSegundos >= 1500) {
                clearInterval(detectionInterval);
                setStatus('Capturando rostro...', 'success');
                capturarFoto();
            } else {
                setStatus('Rostro detectado... mantente quieto', 'success');
            }
        } else {
            rostroDetectado = false;
            detectadoSegundos = 0;
            setStatus('Acerque su rostro a la camara...', 'warn');
        }
    }, intervalTime);
}

// Ocultar botón ya que ahora es automático
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-capturar');
    if (btn) btn.style.display = 'none';
});

async function capturarFoto() {
    if (!rostroDetectado) {
        alert('Por favor posicione el rostro en la cámara primero.');
        return;
    }

    clearInterval(detectionInterval);
    document.getElementById('btn-capturar').disabled = true;
    document.getElementById('procesando').classList.remove('hidden');

    const video   = document.getElementById('video');
    const canvas  = document.getElementById('capture-canvas');
    const ctx     = canvas.getContext('2d');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    const fotoBase64 = canvas.toDataURL('image/jpeg', 0.92);

    try {
        const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
        const detection = await faceapi
            .detectSingleFace(canvas, options)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            mostrarResultado(false, 'No se detectó rostro en la imagen capturada. Intente nuevamente.');
            document.getElementById('procesando').classList.add('hidden');
            iniciarDeteccion();
            document.getElementById('btn-capturar').disabled = false;
            return;
        }

        const embedding = Array.from(detection.descriptor); // 128 floats

        // Enviar al servidor
        const resp = await fetch(STORE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                embedding: JSON.stringify(embedding),
                foto_b64:  fotoBase64,
            })
        });

        const data = await resp.json();
        document.getElementById('procesando').classList.add('hidden');

        if (data.ok) {
            detenerCamara();
            document.getElementById('captura-area').classList.add('hidden');
            mostrarResultado(true, data.mensaje);
            setTimeout(() => location.reload(), 2000);
        } else {
            mostrarResultado(false, (data.error || 'Error al guardar'));
            iniciarDeteccion();
            document.getElementById('btn-capturar').disabled = false;
        }
    } catch (err) {
        console.error(err);
        document.getElementById('procesando').classList.add('hidden');
        mostrarResultado(false, 'Error tecnico: ' + err.message);
    }
}

function setStatus(msg, tipo = 'info') {
    const el = document.getElementById('detection-status');
    el.innerHTML = msg;
    el.style.background = tipo === 'success' ? 'rgba(22,163,74,0.8)'
                        : tipo === 'error'   ? 'rgba(220,38,38,0.8)'
                        : tipo === 'warn'    ? 'rgba(234,88,12,0.8)'
                        :                      'rgba(0,0,0,0.6)';
}

function mostrarResultado(ok, msg) {
    const el = document.getElementById('resultado');
    el.classList.remove('hidden');
    el.className = 'mt-4 p-4 rounded-xl text-sm font-semibold ' +
        (ok ? 'bg-green-50 text-green-700 border border-green-200'
            : 'bg-red-50 text-red-700 border border-red-200');
    el.innerHTML = msg;
}

// Auto-iniciar si no hay rostro
@if(!$rostro)
document.addEventListener('DOMContentLoaded', () => iniciarCamara());
@endif
</script>
@endsection
