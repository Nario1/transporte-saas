@extends('layouts.conductor')

@section('title', 'Vuelta en Curso')

@section('extra_css')
<style>
    .en-ruta-hero {
        background: linear-gradient(135deg, #16a34a 0%, #14532d 100%);
        border-radius: 14px;
        padding: 22px 18px;
        color: #fff;
        margin-bottom: 16px;
        text-align: center;
    }
    .en-ruta-titulo { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
    .en-ruta-sub { font-size: 13px; opacity: .8; }

    .cronometro {
        font-family: 'JetBrains Mono', monospace;
        font-size: 48px;
        font-weight: 800;
        color: var(--accent);
        text-align: center;
        letter-spacing: .05em;
        padding: 20px 0;
    }
    .pulse-dot {
        display: inline-block;
        width: 10px; height: 10px;
        background: var(--green);
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse 1.2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%,100% { transform: scale(1); opacity: 1; }
        50%      { transform: scale(1.4); opacity: .6; }
    }
    .btn-terminar {
        background: var(--red);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 16px;
        font-weight: 700;
        width: 100%;
        cursor: pointer;
        font-family: inherit;
        transition: opacity .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-terminar:hover { opacity: .88; }
    .btn-terminar:disabled { opacity: .5; cursor: not-allowed; }
</style>
@endsection

@section('content')

<div class="en-ruta-hero">
    <div style="font-size: 36px; margin-bottom: 12px; color: white;"><i class="fa-solid fa-car"></i></div>
    <div class="en-ruta-titulo">¡En Ruta!</div>
    <div class="en-ruta-sub">Vuelta #{{ $vuelta->numero_vuelta }} — {{ $vuelta->hora_salida }}</div>
</div>

{{-- Cronómetro --}}
<div class="card">
    <div class="card-header" style="padding: 14px 16px; display: flex; align-items: center;">
        <span class="card-title" style="display: flex; align-items: center;"><span class="pulse-dot"></span>Tiempo en ruta</span>
    </div>
    <div class="card-body" style="padding: 0 16px;">
        <div class="cronometro" id="cronometro">00:00:00</div>
    </div>
</div>

{{-- Info de vuelta --}}
<div class="card">
    <div class="card-body" style="padding: 16px;">
        <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
            <span class="summary-label" style="font-weight:500; color: var(--text2);">Ruta</span>
            <span class="summary-val" style="font-weight: 600; color: var(--text);">{{ $vuelta->ruta?->nombre ?? 'Sin ruta asignada' }}</span>
        </div>
        <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
            <span class="summary-label" style="font-weight:500; color: var(--text2);">Vehículo</span>
            <span class="summary-val" style="font-weight: 600; color: var(--text);">{{ $vuelta->vehiculo?->placa ?? '—' }}</span>
        </div>
        <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
            <span class="summary-label" style="font-weight:500; color: var(--text2);">Salida</span>
            <span class="summary-val" style="font-weight: 600; color: var(--text);">{{ $vuelta->hora_salida }}</span>
        </div>
        <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0;">
            <span class="summary-label" style="font-weight:500; color: var(--text2);">Fecha</span>
            <span class="summary-val" style="font-weight: 600; color: var(--text);">{{ $vuelta->fecha->format('d/m/Y') }}</span>
        </div>
    </div>
</div>

{{-- Selector de Paradero de Llegada --}}
<div class="card" style="margin-bottom: 16px;">
    <div class="card-header" style="padding: 14px 16px; border-bottom: 1px solid #f8fafc;">
        <span class="card-title" style="font-size:14px; color:#64748b; font-weight: 700;"><i class="fa-solid fa-location-dot" style="color: var(--red); margin-right: 5px;"></i> ¿Dónde terminarás la vuelta?</span>
    </div>
    <div class="card-body" style="padding: 16px;">
        <div class="field" style="margin: 0;">
            <select id="paradero_llegada_id" name="paradero_llegada_id" onchange="verificarGPSParaderoSeleccionado()" style="width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--border); padding: 0 12px; font-weight: 700; font-size: 14px; color: var(--text); background: white;">
                <option value="" disabled selected>-- Selecciona el paradero de llegada --</option>
                @foreach($paraderosLlegada as $p)
                    <option value="{{ $p->id }}" 
                            data-lat-a="{{ $p->latitud_a }}" 
                            data-lng-a="{{ $p->longitud_a }}" 
                            data-lat-b="{{ $p->latitud_b }}" 
                            data-lng-b="{{ $p->longitud_b }}" 
                            data-tolerancia="{{ $p->tolerancia ?? 30 }}">
                        {{ $p->nombre }} ({{ strtoupper($p->tipo) }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Panel Informativo de Coordenadas y Distancia --}}
        <div id="paradero-coords-info" style="margin-top: 12px; display: none; padding: 12px; border-radius: 8px; background: var(--bg); border: 1px solid var(--border); font-size: 13px;">
            <div style="font-weight: 700; color: var(--text2); margin-bottom: 6px;"><i class="fa-solid fa-circle-info" style="color: var(--accent);"></i> Estado del Paradero</div>
            <div class="flex-v" style="gap: 4px; color: var(--text3);">
                <div><b>Punto A:</b> <span id="info-pto-a">—</span></div>
                <div><b>Punto B:</b> <span id="info-pto-b">—</span></div>
                <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span id="info-badge" class="pill" style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 99px; color: white;">—</span>
                    <span id="info-dist-text" style="font-weight: 700; color: var(--text);">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Botón terminar --}}
<button class="btn-terminar" id="btn-terminar" onclick="confirmarTerminar()">
    <i class="fa-solid fa-flag-checkered"></i> Terminar Vuelta
</button>

<div id="terminando-msg" class="hidden"
     style="text-align:center;margin-top:12px;color:var(--red);font-weight:600;font-size:13px">
    <i class="fa-solid fa-spinner fa-spin"></i> Registrando llegada...
</div>

<script>
const TERMINAR_URL = '{{ route("conductor.vuelta.terminar", [], false) }}';
const UBICACION_URL = '{{ route("conductor.vuelta.ubicacion", [], false) }}';
const CSRF         = '{{ csrf_token() }}';
const INICIO_MS    = {{ \Carbon\Carbon::parse($vuelta->fecha->format("Y-m-d") . ' ' . $vuelta->hora_salida)->timestamp * 1000 }};
const SERVER_AHORA = {{ now()->timestamp * 1000 }};

// Cronómetro
const inicio       = new Date(INICIO_MS);
const clockOffset  = SERVER_AHORA - Date.now(); // Desfase entre server y cliente

function actualizarCronometro() {
    const ahoraAjustado = Date.now() + clockOffset;
    let diff = Math.max(0, Math.floor((ahoraAjustado - INICIO_MS) / 1000));
    
    // Si sigue en 0 pero sabemos que ya inició, forzar a 1s para feedback visual
    if (diff === 0 && (ahoraAjustado > INICIO_MS)) diff = 1;

    const hh = String(Math.floor(diff / 3600)).padStart(2, '0');
    let residuo = diff % 3600;
    const mm = String(Math.floor(residuo / 60)).padStart(2, '0');
    const ss = String(residuo % 60).padStart(2, '0');
    
    document.getElementById('cronometro').textContent = `${hh}:${mm}:${ss}`;
}
let cronometroIntervalId = setInterval(actualizarCronometro, 1000);
actualizarCronometro();

// --- GEOLOCALIZACIÓN DEL PARADERO EN TIEMPO REAL ---
let currentLat = null;
let currentLng = null;

window.verificarGPSParaderoSeleccionado = function() {
    const selectEl = document.getElementById('paradero_llegada_id');
    const infoPanel = document.getElementById('paradero-coords-info');
    
    if (!selectEl.value) {
        infoPanel.style.display = 'none';
        return;
    }

    const opt = selectEl.options[selectEl.selectedIndex];
    const latA = parseFloat(opt.getAttribute('data-lat-a'));
    const lngA = parseFloat(opt.getAttribute('data-lng-a'));
    const latB = parseFloat(opt.getAttribute('data-lat-b'));
    const lngB = parseFloat(opt.getAttribute('data-lng-b'));
    const tolerance = parseInt(opt.getAttribute('data-tolerancia')) || 30;

    infoPanel.style.display = 'block';

    if (isNaN(latA) || isNaN(lngA)) {
        document.getElementById('info-pto-a').textContent = 'No configurado';
        document.getElementById('info-pto-b').textContent = 'No configurado';
        
        const badge = document.getElementById('info-badge');
        badge.textContent = 'PERMITIDO';
        badge.style.background = 'var(--green)';
        document.getElementById('info-dist-text').textContent = 'Este paradero no exige validación de GPS.';
        document.getElementById('info-dist-text').style.color = 'var(--text)';
        return;
    }

    document.getElementById('info-pto-a').textContent = `${latA.toFixed(6)}, ${lngA.toFixed(6)}`;
    document.getElementById('info-pto-b').textContent = (isNaN(latB) || isNaN(lngB)) ? 'Igual a Punto A' : `${latB.toFixed(6)}, ${lngB.toFixed(6)}`;

    if (currentLat === null || currentLng === null) {
        const badge = document.getElementById('info-badge');
        badge.textContent = 'ESPERANDO GPS';
        badge.style.background = 'var(--orange)';
        document.getElementById('info-dist-text').textContent = 'Obteniendo señal de GPS de tu celular...';
        document.getElementById('info-dist-text').style.color = 'var(--text)';
        return;
    }

    const check = isPointWithinSegmentJS(currentLat, currentLng, latA, lngA, isNaN(latB) ? latA : latB, isNaN(lngB) ? lngA : lngB, tolerance);
    
    const badge = document.getElementById('info-badge');
    const distText = document.getElementById('info-dist-text');

    if (check.within) {
        badge.textContent = 'DENTRO DE RANGO';
        badge.style.background = '#22c55e';
        distText.textContent = `Distancia: ${check.distance.toFixed(1)} metros (Límite: ${check.tolerance}m). ¡Puedes terminar!`;
        distText.style.color = '#22c55e';
    } else {
        badge.textContent = 'FUERA DE RANGO';
        badge.style.background = '#ef4444';
        distText.textContent = `Distancia: ${check.distance.toFixed(1)} metros (Límite: ${check.tolerance}m). Acércate más.`;
        distText.style.color = '#ef4444';
    }
};

function isPointWithinSegmentJS(latP, lngP, latA, lngA, latB, lngB, toleranceMeters) {
    const latRef = (latA + latB) / 2;
    const degToRad = Math.PI / 180;
    
    const scaleX = Math.cos(latRef * degToRad);
    
    const dy = latB - latA;
    const dx = (lngB - lngA) * scaleX;
    
    const dyp = latP - latA;
    const dxp = (lngP - lngA) * scaleX;
    
    const ab2 = (dx * dx) + (dy * dy);
    if (ab2 === 0) {
        const dist = calcularDistanciaMetros(latP, lngP, latA, lngA);
        return { within: dist <= toleranceMeters, distance: dist, tolerance: toleranceMeters };
    }
    
    const ap_ab = (dxp * dx) + (dyp * dy);
    let t = ap_ab / ab2;
    t = Math.max(0, Math.min(1, t));
    
    const latProj = latA + t * dy;
    const lngProj = lngA + t * (lngB - lngA);
    
    const distance = calcularDistanciaMetros(latP, lngP, latProj, lngProj);
    return { within: distance <= toleranceMeters, distance: distance, tolerance: toleranceMeters };
}

let terminando = false;

function confirmarTerminar() {
    const selectEl = document.getElementById('paradero_llegada_id');
    const paraderoLlegadaId = selectEl.value;
    if (!paraderoLlegadaId) {
        Swal.fire({
            title: 'Paradero Requerido',
            text: 'Debes seleccionar el paradero en el que vas a terminar tu vuelta.',
            icon: 'warning',
            confirmButtonColor: 'var(--accent)',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const tiempoActual = document.getElementById('cronometro').textContent;
    const paraderoNombre = selectEl.options[selectEl.selectedIndex].text;
    
    terminando = true; // Detener polling temporalmente durante la confirmación
    
    Swal.fire({
        title: '¿Finalizar Vuelta?',
        html: `El tiempo transcurrido es <b style="font-family:monospace; font-size:1.2em;">${tiempoActual}</b>.<br>Paradero de destino: <b>${paraderoNombre}</b>.<br><br>¿Estás seguro que deseas terminar la vuelta ahora?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--red)',
        cancelButtonColor: 'var(--text3)',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        backdrop: `rgba(220, 38, 38, 0.1)`
    }).then((result) => {
        if (result.isConfirmed) {
            // Detener cronómetro visualmente de inmediato
            if (cronometroIntervalId) clearInterval(cronometroIntervalId);
            terminarVuelta(paraderoLlegadaId);
        } else {
            terminando = false; // Reanudar polling si cancela
        }
    });
}

async function terminarVuelta(paraderoLlegadaId) {
    document.getElementById('btn-terminar').disabled = true;
    document.getElementById('terminando-msg').classList.remove('hidden');

    // Detener el rastreo activo para que no interfiera
    if (watchId) navigator.geolocation.clearWatch(watchId);

    // Capturar GPS con un margen de tiempo suficiente (hasta 15 segundos) para asegurar precisión
    let lat = null, lng = null;
    try {
        const pos = await new Promise((resolve) => {
            const timeout = setTimeout(() => resolve(null), 15000);
            navigator.geolocation.getCurrentPosition(
                p => { clearTimeout(timeout); resolve(p); },
                e => { clearTimeout(timeout); resolve(null); },
                { enableHighAccuracy: true, timeout: 14000, maximumAge: 0 }
            );
        });
        if (pos) {
            lat = pos.coords.latitude;
            lng = pos.coords.longitude;
        }
    } catch (_) {}

    // Evitar finalizar la vuelta si no se pudo obtener el GPS final
    if (lat === null || lng === null) {
        alert('No se pudo verificar tu ubicación de llegada. Asegúrate de tener activado el GPS de tu celular y vuelve a intentarlo.');
        document.getElementById('btn-terminar').disabled = false;
        document.getElementById('terminando-msg').classList.add('hidden');
        terminando = false;
        return;
    }

    try {
        const resp = await fetch(TERMINAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ 
                latitud: lat, 
                longitud: lng, 
                paradero_llegada_id: paraderoLlegadaId 
            })
        });
        const data = await resp.json();
        if (data.ok) {
            Swal.fire({
                title: '¡Vuelta Finalizada!',
                text: data.paradero ? `Has terminado la ruta en el paradero ${data.paradero}.` : 'Has terminado la vuelta correctamente.',
                icon: 'success',
                confirmButtonColor: 'var(--green)',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            alert('❌ ' + (data.error || 'Error al terminar vuelta'));
            document.getElementById('btn-terminar').disabled = false;
            document.getElementById('terminando-msg').classList.add('hidden');
            terminando = false;
        }
    } catch (e) {
        alert('❌ Error de conexión al servidor.');
        document.getElementById('btn-terminar').disabled = false;
        document.getElementById('terminando-msg').classList.add('hidden');
        terminando = false;
    }
}

// --- GPS BACKGROUND WATCHING OPTIMIZADO ---
let lastLat = null;
let lastLng = null;
let lastSendTime = 0;
let watchId = null;

function calcularDistanciaMetros(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radio de la Tierra en metros
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function iniciarRastreoGPS() {
    if (terminando) return;

    if (!navigator.geolocation) {
        console.warn("Geolocalización no soportada");
        return;
    }

    watchId = navigator.geolocation.watchPosition(
        async (pos) => {
            if (terminando) {
                if (watchId) navigator.geolocation.clearWatch(watchId);
                return;
            }

            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const ahora = Date.now();

            // Guardar ubicación actual en tiempo real para el feedback del paradero
            currentLat = lat;
            currentLng = lng;
            if (typeof verificarGPSParaderoSeleccionado === 'function') {
                verificarGPSParaderoSeleccionado();
            }

            // Filtro inteligente para no saturar el servidor ni gastar batería:
            if (lastLat !== null && lastLng !== null) {
                const distancia = calcularDistanciaMetros(lastLat, lastLng, lat, lng);
                const tiempoTranscurrido = (ahora - lastSendTime) / 1000;

                // Solo enviar si se ha movido más de 10 metros, O si han pasado al menos 20 segundos
                if (distancia < 10 && tiempoTranscurrido < 20) {
                    return;
                }
            }

            lastLat = lat;
            lastLng = lng;
            lastSendTime = ahora;

            try {
                const resp = await fetch(UBICACION_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ latitud: lat, longitud: lng })
                });
                const data = await resp.json();
                if (data.ok) {
                    console.log("GPS en ruta enviado:", lat, lng);
                }
            } catch (err) {
                console.error("Error enviando ubicación en segundo plano:", err);
            }
        },
        (err) => {
            console.error("Error capturando GPS en segundo plano:", err);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Iniciar rastreo dinámico al cargar la página
iniciarRastreoGPS();
</script>
@endsection
