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
    .en-ruta-icon { font-size: 40px; margin-bottom: 6px; }
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
    <div class="en-ruta-icon">🚌</div>
    <div class="en-ruta-titulo">¡En Ruta!</div>
    <div class="en-ruta-sub">Vuelta #{{ $vuelta->numero_vuelta }} — {{ $vuelta->hora_salida }}</div>
</div>

{{-- Cronómetro --}}
<div class="card">
    <div class="card-header">
        <span class="card-title"><span class="pulse-dot"></span>Tiempo en ruta</span>
    </div>
    <div class="card-body" style="padding: 0 16px;">
        <div class="cronometro" id="cronometro">00:00:00</div>
    </div>
</div>

{{-- Info de vuelta --}}
<div class="card">
    <div class="card-body">
        <div class="summary-row">
            <span class="summary-label">Ruta</span>
            <span class="summary-val">{{ $vuelta->ruta?->nombre ?? 'Sin ruta asignada' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Vehículo</span>
            <span class="summary-val">{{ $vuelta->vehiculo?->placa ?? '—' }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Salida</span>
            <span class="summary-val">{{ $vuelta->hora_salida }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Fecha</span>
            <span class="summary-val">{{ $vuelta->fecha->format('d/m/Y') }}</span>
        </div>
    </div>
</div>

{{-- Botón terminar --}}
<button class="btn-terminar" id="btn-terminar" onclick="confirmarTerminar()">
    🏁 Terminar Vuelta
</button>

<div id="terminando-msg" class="hidden"
     style="text-align:center;margin-top:12px;color:var(--red);font-weight:600;font-size:13px">
    ⏳ Registrando llegada...
</div>

<script>
const TERMINAR_URL = '{{ route("conductor.vuelta.terminar", [], false) }}';
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

let terminando = false;

function confirmarTerminar() {
    const tiempoActual = document.getElementById('cronometro').textContent;
    terminando = true; // Detener polling temporalmente durante la confirmación
    
    Swal.fire({
        title: '¿Finalizar Vuelta?',
        html: `El tiempo transcurrido es <b style="font-family:monospace; font-size:1.2em;">${tiempoActual}</b>.<br><br>¿Estás seguro que deseas terminar la vuelta ahora?`,
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
            terminarVuelta();
        } else {
            terminando = false; // Reanudar polling si cancela
        }
    });
}

async function terminarVuelta() {
    document.getElementById('btn-terminar').disabled = true;
    document.getElementById('terminando-msg').classList.remove('hidden');

    // Captura de GPS rápida (máximo 3 segundos) para no colgar el proceso
    let lat = null, lng = null;
    try {
        const pos = await new Promise((resolve) => {
            const timeout = setTimeout(() => resolve(null), 3000);
            navigator.geolocation.getCurrentPosition(
                p => { clearTimeout(timeout); resolve(p); },
                e => { clearTimeout(timeout); resolve(null); },
                { enableHighAccuracy: true, timeout: 2500 }
            );
        });
        if (pos) {
            lat = pos.coords.latitude;
            lng = pos.coords.longitude;
        }
    } catch (_) {}

    try {
        const resp = await fetch(TERMINAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ latitud: lat, longitud: lng })
        });
        const data = await resp.json();
        if (data.ok) {
            window.location.href = data.redirect;
        } else {
            alert('❌ ' + (data.error || 'Error al terminar vuelta'));
            document.getElementById('btn-terminar').disabled = false;
            document.getElementById('terminando-msg').classList.add('hidden');
            terminando = false; // Reanudar polling
        }
    } catch (e) {
        alert('Error de conexion');
        document.getElementById('btn-terminar').disabled = false;
        document.getElementById('terminando-msg').classList.add('hidden');
        terminando = false; // Reanudar polling
    }
}

// Polling de estado de la vuelta (en vivo)
const ESTADO_URL = '{{ route("conductor.vuelta.estado", [], false) }}';
async function verificarEstadoVuelta() {
    if (terminando) return;
    try {
        const resp = await fetch(ESTADO_URL);
        const data = await resp.json();
        if (!data.activa) {
            terminando = true; // Prevenir múltiples alertas
            if (cronometroIntervalId) clearInterval(cronometroIntervalId);
            
            Swal.fire({
                title: '¡Vuelta Completada!',
                text: 'La vuelta ha sido marcada como completada por el administrador.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false,
                confirmButtonColor: 'var(--accent)',
                backdrop: `rgba(22, 163, 74, 0.1)`
            }).then(() => {
                window.location.href = '{{ route("conductor.vueltas") }}';
            });
        }
    } catch (e) {
        console.warn("Falla chequeo de estado de vuelta");
    }
}
setInterval(verificarEstadoVuelta, 5000);

// REPORTERO GPS EN TIEMPO REAL
const UBICACION_URL = '{{ route("conductor.vuelta.ubicacion", [], false) }}';
function reportarUbicacion() {
    if (!navigator.geolocation) return;
    
    navigator.geolocation.getCurrentPosition(async (pos) => {
        try {
            await fetch(UBICACION_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ latitud: pos.coords.latitude, longitud: pos.coords.longitude })
            });
            console.log("GPS reportado");
        } catch (e) {
            console.warn("Falla reporte GPS");
        }
    }, null, { enableHighAccuracy: true });
}

// Reportar cada 20 segundos
setInterval(reportarUbicacion, 20000);
reportarUbicacion(); // Primer reporte inmediato
</script>
@endsection
