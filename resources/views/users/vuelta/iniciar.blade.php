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
    .facial-overlay {
        position: fixed;
        inset: 0;
        background: #ffffff;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .facial-container {
        position: relative;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        overflow: hidden;
        border: 8px solid #3b82f6;
        box-shadow: 0 0 30px rgba(59, 130, 246, 0.4);
        background: #fff;
    }
    .facial-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .facial-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .facial-instructions {
        margin-top: 24px;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        text-align: center;
        padding: 0 20px;
    }
    .facial-btn-cancel {
        margin-top: 30px;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
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
        <div class="facial-overlay" id="facial-overlay-wrap" style="display:none">
            <div style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 24px; text-align: center;">
                Verificación de Identidad
            </div>
            <div class="facial-container">
                <video id="video-vuelta" class="facial-video" autoplay muted playsinline></video>
                <canvas id="overlay-vuelta" class="facial-canvas"></canvas>
            </div>
            <div class="facial-instructions" id="cam-status-txt">Cargando cámara...</div>
            <button type="button" class="facial-btn-cancel" onclick="cancelarVerificacion()">Cerrar / Cancelar</button>
        </div>
        <canvas id="cap-canvas" style="display:none"></canvas>
        
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div id="verificacion-resultado" style="margin: 0;">
                <span class="check-badge wait"><i class="fa-solid fa-hourglass-half"></i> Esperando verificación...</span>
            </div>
            <button type="button" id="btn-abrir-camara" class="btn btn-secondary btn-sm" onclick="abrirCamaraVerificacion()" style="font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 8px;">
                <i class="fa-solid fa-camera"></i> Iniciar Cámara
            </button>
        </div>
    @elseif(!$requiereFacial)
        <div class="alert success" style="background: var(--green-l); color: var(--green); border: 1px solid rgba(34,197,94,0.2); border-radius: 12px; padding: 14px 16px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; margin-bottom: 0;">
            <i class="fa-solid fa-circle-check"></i> Autenticación facial no requerida para tu cuenta.
        </div>
    @else
        <div class="alert warning" style="display: flex; align-items: center; gap: 8px; margin-bottom: 0;">
            <i class="fa-solid fa-circle-xmark"></i> AVISO: Sin rostro registrado y autenticación requerida. Contacta a soporte.
        </div>
    @endif
</div>
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

    <div class="field mb14" id="paradero-field" style="display:none;">
        <label>Punto / Paradero de Inicio</label>
        <select id="paradero-select" class="form-control" style="padding:10px;" required>
            <option value="">-- Seleccionar Paradero --</option>
        </select>
    </div>

    {{-- Sin campos de lat/lng en el DOM para evitar alteraciones --}}
    <div class="field mb14">
        <label>Ubicación GPS (Automática)</label>
        <div id="gps-display-text" style="font-size:14px; font-weight:700; color:var(--accent); background:var(--border); padding:10px; border-radius:10px;">
            Obteniendo ubicación...
        </div>
        <div id="geo-validation-msg" style="margin-top:10px; font-size:12.5px; font-weight:800; display:none; padding:10px; border-radius:8px;"></div>
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
const routesData      = @json($rutas);

let rostroVerificado  = !REQUIERE_FACIAL; 
let detTimeoutId      = null;
let gpsActual = { lat: null, lng: null };

function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // metros
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function isPointWithinSegment(latP, lngP, latA, lngA, latB, lngB, toleranceMeters = 30) {
    if (latA === null || lngA === null || latB === null || lngB === null) {
        return true; // Si no hay coordenadas, es válido por defecto
    }
    
    const latRef = (latA + latB) / 2;
    const degToRad = Math.PI / 180;
    const scaleX = Math.cos(latRef * degToRad);
    
    const dy = latB - latA;
    const dx = (lngB - lngA) * scaleX;
    
    const dyp = latP - latA;
    const dxp = (lngP - lngA) * scaleX;
    
    const ab2 = (dx * dx) + (dy * dy);
    if (ab2 === 0) {
        return haversineDistance(latP, lngP, latA, lngA) <= toleranceMeters;
    }
    
    const ap_ab = (dxp * dx) + (dyp * dy);
    const t = Math.max(0, Math.min(1, ap_ab / ab2));
    
    const latProj = latA + t * dy;
    const lngProj = lngA + t * (lngB - lngA);
    
    const distance = haversineDistance(latP, lngP, latProj, lngProj);
    return distance <= toleranceMeters;
}

function capturarGPSInterno() {
    const display = document.getElementById('gps-display-text');
    if (display) display.textContent = 'Buscando satélites...';

    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            if (display) display.textContent = 'GPS no soportado en este navegador';
            resolve(null);
            return;
        }

        const options = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };

        navigator.geolocation.getCurrentPosition(
            pos => {
                gpsActual.lat = pos.coords.latitude;
                gpsActual.lng = pos.coords.longitude;
                if (display) display.innerHTML = `<span style="color:var(--green)">${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}</span>`;
                actualizarEstadoBotonIniciar();
                resolve(gpsActual);
            },
            err => {
                console.error("GPS Error:", err);
                let msg = 'Error de ubicación';
                if (err.code === 1) msg = 'Permiso de GPS denegado';
                else if (err.code === 3) msg = 'Tiempo agotado (reintente)';
                if (display) display.innerHTML = `<span style="color:var(--red)">${msg}</span> <a href="#" onclick="capturarGPSInterno(); return false;" style="margin-left:10px; text-decoration:underline;">Reintentar</a>`;
                actualizarEstadoBotonIniciar();
                resolve(null);
            },
            options
        );
    });
}

function actualizarEstadoBotonIniciar() {
    const rutaSelect = document.getElementById('ruta-select').value;
    const paraderoSelect = document.getElementById('paradero-select').value;
    const btn = document.getElementById('btn-iniciar-vuelta');
    const msgEl = document.getElementById('geo-validation-msg');
    if (!btn) return;
    
    const tieneRuta = !!rutaSelect;
    const tieneParadero = !!paraderoSelect;
    const tieneGps = gpsActual.lat !== null && gpsActual.lng !== null;
    const tieneFacial = rostroVerificado;
    
    let dentroDeRango = true;
    
    if (!tieneRuta) {
        msgEl.style.display = 'block';
        msgEl.style.background = '#eff6ff';
        msgEl.style.color = '#1e40af';
        msgEl.style.border = '1px solid #dbeafe';
        msgEl.innerHTML = `<i class="fa-solid fa-info-circle"></i> Selecciona la ruta`;
        dentroDeRango = false;
    } else if (!tieneParadero) {
        msgEl.style.display = 'block';
        msgEl.style.background = '#eff6ff';
        msgEl.style.color = '#1e40af';
        msgEl.style.border = '1px solid #dbeafe';
        msgEl.innerHTML = `<i class="fa-solid fa-info-circle"></i> Selecciona un paradero`;
        dentroDeRango = false;
    } else if (!tieneGps) {
        msgEl.style.display = 'block';
        msgEl.style.background = '#fffbeb';
        msgEl.style.color = '#92400e';
        msgEl.style.border = '1px solid #fef3c7';
        msgEl.innerHTML = `<i class="fa-solid fa-hourglass-half"></i> Obteniendo ubicación GPS...`;
        dentroDeRango = false;
    } else {
        const opt = document.getElementById('paradero-select').selectedOptions[0];
        const latAStr = opt.getAttribute('data-lat-a');
        
        if (latAStr) {
            const latA = parseFloat(latAStr);
            const lngA = parseFloat(opt.getAttribute('data-lng-a'));
            const latB = parseFloat(opt.getAttribute('data-lat-b'));
            const lngB = parseFloat(opt.getAttribute('data-lng-b'));
            const tolerancia = parseFloat(opt.getAttribute('data-tolerancia'));
            
            dentroDeRango = isPointWithinSegment(gpsActual.lat, gpsActual.lng, latA, lngA, latB, lngB, tolerancia);
            
            if (!dentroDeRango) {
                msgEl.style.display = 'block';
                msgEl.style.background = '#fef2f2';
                msgEl.style.color = '#991b1b';
                msgEl.style.border = '1px solid #fee2e2';
                msgEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> No estás dentro de las coordenadas.`;
            } else {
                msgEl.style.display = 'block';
                msgEl.style.background = '#f0fdf4';
                msgEl.style.color = '#166534';
                msgEl.style.border = '1px solid #dcfce7';
                msgEl.innerHTML = `<i class="fa-solid fa-circle-check"></i> Ubicación correcta: Estás dentro del tramo del paradero.`;
            }
        } else {
            msgEl.style.display = 'block';
            msgEl.style.background = '#f0fdf4';
            msgEl.style.color = '#166534';
            msgEl.style.border = '1px solid #dcfce7';
            msgEl.innerHTML = `<i class="fa-solid fa-circle-check"></i> Este paradero no requiere validación de coordenadas.`;
            dentroDeRango = true;
        }
    }
    
    btn.disabled = !(tieneRuta && tieneParadero && tieneGps && tieneFacial && dentroDeRango);
}

async function abrirCamaraVerificacion() {
    const overlayWrap = document.getElementById('facial-overlay-wrap');
    if (overlayWrap) overlayWrap.style.display = 'flex';
    setCamStatus('Cargando modelos...');
    try {
        const originalFetch = window.fetch;
        window.fetch = function(url, init) {
            if (typeof url === 'string' && url.includes('models-v2')) {
                const sep = url.includes('?') ? '&' : '?';
                return originalFetch(`${url}${sep}v=1.0.7`, init);
            }
            return originalFetch(url, init);
        };
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) { await faceapi.tf.setBackend('cpu'); await faceapi.tf.ready(); }

        setCamStatus('Detector (1/3)...');
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL);
        setCamStatus('Landmarks (2/3)...');
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL);
        setCamStatus('Reconocimiento (3/3)...');
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL);

        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 320, max: 640 }, height: { ideal: 240, max: 480 } }
        });
        const video = document.getElementById('video-vuelta');
        video.srcObject = stream;
        await video.play();
        setCamStatus('Posiciona tu rostro frente a la cámara...');
        iniciarDeteccion();

        if (detTimeoutId) clearTimeout(detTimeoutId);
        detTimeoutId = setTimeout(() => {
            if (!rostroVerificado) {
                detenerCamara();
                setCamStatus('Tiempo de espera agotado', 'warn');
                mostrarResultado(false, '<i class="fa-solid fa-circle-xmark"></i> Tiempo de espera agotado. Vuelve a intentarlo.');
                actualizarEstadoBotonIniciar();
            }
        }, 35000);
    } catch (e) {
        setCamStatus('Error de cámara: ' + e.message, 'error');
        mostrarResultado(false, '<i class="fa-solid fa-circle-xmark"></i> Error de cámara: ' + e.message);
        actualizarEstadoBotonIniciar();
    }
}

function detenerCamara() {
    const video = document.getElementById('video-vuelta');
    if (video && video.srcObject) { video.srcObject.getTracks().forEach(t => t.stop()); video.srcObject = null; }
    if (detTimeoutId) clearTimeout(detTimeoutId);
    const overlayWrap = document.getElementById('facial-overlay-wrap');
    if (overlayWrap) overlayWrap.style.display = 'none';
}

function cancelarVerificacion() {
    detenerCamara();
    setCamStatus('Verificación cancelada', 'warn');
    mostrarResultado(false, '<i class="fa-solid fa-circle-xmark"></i> Verificación facial cancelada.');
    rostroVerificado = false;
    actualizarEstadoBotonIniciar();
}

function iniciarDeteccion() {
    const video = document.getElementById('video-vuelta');
    const canvas = document.getElementById('overlay-vuelta');
    const ctx = canvas.getContext('2d');
    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.35 });
    const stored = new Float32Array(STORED_EMBED);
    const UMBRAL = 0.6;
    let frameCount = 0;

    async function loopDeteccion() {
        if (rostroVerificado) { detenerCamara(); return; }
        if (!video.videoWidth || !video.videoHeight || video.readyState < 2) {
            detTimeoutId = setTimeout(loopDeteccion, 100);
            return;
        }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        try {
            frameCount++;
            const det1 = await faceapi.detectSingleFace(video, options);
            if (!det1) {
                setCamStatus('Centra tu rostro en la cámara...', 'info');
                detTimeoutId = setTimeout(loopDeteccion, 100);
                return;
            }
            const box1 = det1.box;
            ctx.strokeStyle = '#3b82f6';
            ctx.lineWidth = 3;
            ctx.strokeRect(box1.x, box1.y, box1.width, box1.height);
            setCamStatus('Rostro detectado... analizando...', 'info');
            if (frameCount % 2 === 0) {
                const detFull = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true).withFaceDescriptor();
                if (detFull) {
                    const distancia = faceapi.euclideanDistance(detFull.descriptor, stored);
                    const box2 = detFull.detection.box;
                    ctx.strokeStyle = distancia < UMBRAL ? '#22c55e' : '#ef4444';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box2.x, box2.y, box2.width, box2.height);
                    if (distancia < UMBRAL) {
                        detenerCamara();
                        setCamStatus('✓ Identidad verificada', 'success');
                        rostroVerificado = true;
                        mostrarResultado(true, '<i class="fa-solid fa-circle-check"></i> Verificación exitosa.');
                        actualizarEstadoBotonIniciar();
                        const rutaSel = document.getElementById('ruta-select').value;
                        if (rutaSel) setTimeout(() => { iniciarVuelta(); }, 800);
                        return;
                    } else {
                        setCamStatus('Rostro no coincide. Intenta de nuevo...', 'error');
                    }
                }
            }
        } catch (err) { console.error('Error en detección facial:', err); }
        detTimeoutId = setTimeout(loopDeteccion, 60);
    }
    loopDeteccion();
}

function setCamStatus(msg, tipo = 'info') {
    const el = document.getElementById('cam-status-txt');
    if (!el) return;
    el.textContent = msg;
    el.style.background = tipo === 'success' ? 'rgba(22,163,74,0.8)' : tipo === 'error' ? 'rgba(220,38,38,0.8)' : tipo === 'warn' ? 'rgba(234,88,12,0.8)' : 'rgba(0,0,0,0.65)';
}

function mostrarResultado(ok, msg) {
    const el = document.getElementById('verificacion-resultado');
    if (!el) return;
    el.innerHTML = `<span class="check-badge ${ok ? 'ok' : 'fail'}">${msg}</span>`;
}

async function iniciarVuelta() {
    if (REQUIERE_FACIAL && !rostroVerificado) {
        alert('Espera a que se complete la verificación facial con éxito.');
        return;
    }
    const rutaSelect = document.getElementById('ruta-select').value;
    const paraderoSelect = document.getElementById('paradero-select').value;
    if (!rutaSelect) { alert('Debes seleccionar una ruta antes de iniciar la vuelta.'); return; }
    if (!paraderoSelect) { alert('Debes seleccionar el paradero de inicio antes de iniciar la vuelta.'); return; }
    
    document.getElementById('btn-iniciar-vuelta').disabled = true;
    document.getElementById('iniciando-msg').classList.remove('hidden');
    const posFinal = await capturarGPSInterno();
    if (!posFinal || posFinal.lat === null || posFinal.lng === null) {
        alert('No se pudo detectar tu ubicación. Asegúrate de tener activado el GPS (Ubicación) de tu celular y vuelve a intentarlo.');
        document.getElementById('btn-iniciar-vuelta').disabled = false;
        document.getElementById('iniciando-msg').classList.add('hidden');
        return;
    }
    const body = { 
        verificado_rostro: rostroVerificado, 
        ruta_id: rutaSelect, 
        ruta_paradero_id: paraderoSelect, 
        latitud: posFinal.lat, 
        longitud: posFinal.lng 
    };
    try {
        const resp = await fetch(INICIAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body)
        });
        const data = await resp.json();
        if (data.ok) { window.location.href = data.redirect; } 
        else { alert('Error: ' + (data.error || 'Error al iniciar vuelta')); document.getElementById('btn-iniciar-vuelta').disabled = false; document.getElementById('iniciando-msg').classList.add('hidden'); }
    } catch (e) {
        alert('Error de conexión: ' + e.message);
        document.getElementById('btn-iniciar-vuelta').disabled = false;
        document.getElementById('iniciando-msg').classList.add('hidden');
    }
}

// Lógica de llenado dinámico del paradero select
document.getElementById('ruta-select').addEventListener('change', function() {
    const rutaId = this.value;
    const paraderoField = document.getElementById('paradero-field');
    const paraderoSelect = document.getElementById('paradero-select');
    
    // Limpiar opciones previas
    paraderoSelect.innerHTML = '<option value="">-- Seleccionar Paradero --</option>';
    
    if (!rutaId) {
        paraderoField.style.display = 'none';
        actualizarEstadoBotonIniciar();
        return;
    }
    
    const rutaObj = routesData.find(r => r.id == rutaId);
    if (rutaObj && rutaObj.paraderos) {
        rutaObj.paraderos.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = `${p.nombre} (${p.tipo.toUpperCase()})`;
            
            // Guardar coordenadas y tolerancia como atributos de datos
            if (p.latitud_a !== null && p.longitud_a !== null && p.latitud_b !== null && p.longitud_b !== null) {
                opt.setAttribute('data-lat-a', p.latitud_a);
                opt.setAttribute('data-lng-a', p.longitud_a);
                opt.setAttribute('data-lat-b', p.latitud_b);
                opt.setAttribute('data-lng-b', p.longitud_b);
                opt.setAttribute('data-tolerancia', p.tolerancia || 30);
            }
            
            paraderoSelect.appendChild(opt);
        });
        paraderoField.style.display = 'block';
    } else {
        paraderoField.style.display = 'none';
    }
    
    actualizarEstadoBotonIniciar();
});

document.getElementById('paradero-select').addEventListener('change', actualizarEstadoBotonIniciar);

capturarGPSInterno();
if (TIENE_ROSTRO && STORED_EMBED && REQUIERE_FACIAL) { abrirCamaraVerificacion(); } else { actualizarEstadoBotonIniciar(); }
</script>
@endsection
