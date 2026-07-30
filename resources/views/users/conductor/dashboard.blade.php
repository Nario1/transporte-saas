{{-- resources/views/users/conductor/dashboard.blade.php --}}
@extends('layouts.conductor')
@section('title', 'Inicio')

@push('styles')
<style>
/* ════════════════════════════════════════════════
   TOKENS DE ESPACIADO — un solo lugar para cambiar
   ════════════════════════════════════════════════ */
:root {
    --gap:    12px;   /* espacio entre bloques */
    --pad:    14px;   /* padding interno de cards */
    --radius: 14px;   /* radio de tarjetas */
}

/* ── HERO ─────────────────────────────────────── */
.d-hero {
    background: linear-gradient(135deg, var(--accent) 0%, #1e3a8a 100%);
    border-radius: var(--radius);
    padding: var(--pad) 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: var(--gap);
    color: #fff;
}
.d-hero-av {
    width: 44px; height: 44px;
    background: rgba(255,255,255,.18);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.d-hero-info { min-width: 0; flex: 1; }
.d-hero-name {
    font-size: clamp(13px, 3.8vw, 15px);
    font-weight: 700;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.d-hero-sub {
    font-size: 11px; opacity: .75; margin-top: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.d-hero-placa {
    font-size: clamp(18px, 5vw, 22px);
    font-weight: 800; letter-spacing: .02em;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── STATS 2×2 ────────────────────────────────── */
.d-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--gap);
    margin-bottom: var(--gap);
}
.d-stat {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 13px 12px 11px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow);
}
/* Barra de color arriba — idéntica en todos */
.d-stat::before {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    border-radius: var(--radius) var(--radius) 0 0;
}
.d-stat.green::before  { background: var(--green); }
.d-stat.red::before    { background: var(--red); }
.d-stat.blue::before   { background: var(--accent); }
.d-stat.orange::before { background: var(--orange); }

/* Icono decorativo — idéntico en todos */
.d-stat-icon {
    position: absolute; top: 11px; right: 11px;
    font-size: 17px; opacity: .15;
}
/* Texto — misma escala en todos */
.d-stat-lbl {
    font-size: 9.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    color: var(--text2); margin-bottom: 5px;
}
.d-stat-val {
    font-size: clamp(14px, 4.2vw, 20px);
    font-weight: 800; line-height: 1.05;
    word-break: break-all;
}
.d-stat.green  .d-stat-val { color: var(--green); }
.d-stat.red    .d-stat-val { color: var(--red); }
.d-stat.blue   .d-stat-val { color: var(--accent); }
.d-stat.orange .d-stat-val { color: var(--orange); }

.d-stat-sub {
    font-size: 10px; color: var(--text3);
    margin-top: 3px;
}

/* ── CARD BASE ────────────────────────────────── */
.d-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: var(--gap);
    overflow: hidden;
}
.d-card-head {
    padding: 12px var(--pad);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    justify-content: space-between; gap: 8px;
}
.d-card-title {
    font-size: 13px; font-weight: 700;
    display: flex; align-items: center; gap: 6px;
}
.d-card-body { padding: var(--pad); }
.d-card-body.p-sm { padding: 10px 12px; }

/* ── TRIBUTO: bloque de monto ─────────────────── */
.d-trib-top {
    display: flex; align-items: center;
    justify-content: space-between; gap: 10px;
    flex-wrap: wrap;
}
.d-trib-lbl {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text2); margin-bottom: 4px;
}
.d-trib-val {
    font-size: clamp(22px, 6vw, 30px);
    font-weight: 800; line-height: 1;
    color: var(--text);
}
/* separador */
.d-sep { height: 1px; background: var(--border); margin: 12px 0; }

/* ── DEUDAS ───────────────────────────────────── */
.d-deudas {
    background: var(--red-l);
    border: 1px solid #fecaca;
    border-radius: 10px; padding: 10px 12px;
    margin-top: 10px;
}
.d-deudas-head {
    font-size: 10.5px; font-weight: 800; color: var(--red);
    text-transform: uppercase; letter-spacing: .06em;
    display: flex; align-items: center; gap: 5px;
    margin-bottom: 8px;
}
.d-deuda-row {
    display: flex; align-items: center;
    justify-content: space-between; gap: 8px;
    padding: 7px 0;
    border-bottom: 1px solid #fecaca;
}
.d-deuda-row:last-child { border-bottom: none; padding-bottom: 0; }
.d-deuda-fecha { font-size: 12px; font-weight: 700; color: #9b2c2c; }
.d-deuda-tag   { font-size: 10px; color: var(--red); opacity: .8; }
.d-deuda-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.d-deuda-amt   { font-size: 13px; font-weight: 800; color: #c53030; white-space: nowrap; }

/* ── PAGO BOX ─────────────────────────────────── */
.d-pay-box {
    display: flex; align-items: center;
    justify-content: space-between;
    gap: 10px; flex-wrap: wrap;
    background: #f0f7ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 11px 13px;
    margin-top: 12px;
}
.d-pay-lbl { font-size: 12.5px; font-weight: 600; color: #0369a1; }
.d-paid-info {
    display: flex; align-items: center;
    gap: 6px; flex-wrap: wrap;
    background: var(--green-l);
    border-radius: 10px; padding: 10px 12px;
    font-size: 12px; color: #276749;
    margin-top: 12px; line-height: 1.5;
}

/* ── VUELTA ITEM ──────────────────────────────── */
.d-vuelta {
    display: flex; align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid var(--border);
}
.d-vuelta:last-child { border-bottom: none; }
.d-vuelta-num {
    width: 32px; height: 32px; flex-shrink: 0;
    background: var(--accent-l); color: var(--accent);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800;
}
.d-vuelta-info { flex: 1; min-width: 0; }
.d-vuelta-name {
    font-size: 13px; font-weight: 600;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.d-vuelta-sub { font-size: 11px; color: var(--text3); margin-top: 1px; }
.d-vuelta-hora {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11.5px; color: var(--text2);
    flex-shrink: 0; white-space: nowrap;
}

/* ── SANCION ITEM ─────────────────────────────── */
.d-sancion {
    display: flex; align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid var(--border);
}
.d-sancion:last-child { border-bottom: none; }
.d-sancion-icon {
    width: 32px; height: 32px; flex-shrink: 0;
    background: var(--orange-l); color: var(--orange);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
}
.d-sancion-info { flex: 1; min-width: 0; }
.d-sancion-name {
    font-size: 13px; font-weight: 600;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.d-sancion-sub { font-size: 11px; color: var(--text3); margin-top: 1px; }
.d-sancion-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.d-sancion-amt {
    font-size: clamp(12px, 3vw, 14px);
    font-weight: 800; color: var(--red); white-space: nowrap;
}

/* ── EMPTY ────────────────────────────────────── */
.d-empty {
    text-align: center; padding: 24px 12px;
    color: var(--text3); font-size: 12.5px;
}
.d-empty i { font-size: 28px; opacity: .2; display: block; margin-bottom: 8px; }

/* ── LINK SECUNDARIO ──────────────────────────── */
.d-link {
    font-size: 11.5px; font-weight: 700;
    color: var(--accent); text-decoration: none;
    white-space: nowrap; flex-shrink: 0;
}
.d-link:hover { opacity: .75; }

/* ── BREAKPOINTS ──────────────────────────────── */
@media (max-width: 359px) {
    .d-pay-box  { flex-direction: column; align-items: stretch; }
    .d-pay-box .btn-mp { width: 100%; justify-content: center; }
    .d-sancion-right { width: 100%; justify-content: space-between; }
    .d-stats { gap: 8px; }
    .d-stat  { padding: 11px 10px 9px; }
}
@media (min-width: 540px) {
    .d-trib-val { font-size: 28px; }
}
</style>
@endpush

@section('content')

{{-- ① ALERTAS DOCUMENTARIAS (si hay) ─────────── --}}
@foreach ($alertas as $alerta)
    <div class="alert warning" style="margin-bottom:var(--gap);">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ $alerta }}
    </div>
@endforeach

{{-- ② HERO: conductor + vehículo ─────────────── --}}
<div class="d-hero">
    <div class="d-hero-av"><i class="fa-solid fa-id-card-clip"></i></div>
    <div class="d-hero-info">
        <div class="d-hero-name">{{ $conductor->nombre }} {{ $conductor->apellidos }}</div>
        <div class="d-hero-sub">
            @if($conductor->vehiculos->first())
                {{ $conductor->vehiculos->first()->marca }}
                {{ $conductor->vehiculos->first()->modelo }}
                · {{ $conductor->vehiculos->first()->anio }}
            @else
                Sin vehículo asignado
            @endif
        </div>
    </div>
    @if($conductor->vehiculos->first())
        <div class="d-hero-placa">
            {{ $conductor->vehiculos->first()->placa_form }}
        </div>
    @endif
</div>

{{-- ③ 4 STATS — mismo peso visual, mismo tamaño ─ --}}
<div class="d-stats">
    {{-- Tributo --}}
    <div class="d-stat {{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}">
        <div class="d-stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
        <div class="d-stat-lbl">Tributo Hoy</div>
        <div class="d-stat-val">{{ $tributoHoy ? 'S/'.number_format($tributoHoy->monto,2) : 'S/0' }}</div>
        <div class="d-stat-sub">
            @if($tributoHoy?->estado === 'pagado') Pagado
            @elseif($tributoHoy?->estado === 'exonerado') Exonerado
            @elseif($tributoHoy) Pendiente
            @else Sin registro @endif
        </div>
    </div>

    {{-- Vueltas --}}
    <div class="d-stat blue">
        <div class="d-stat-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
        <div class="d-stat-lbl">Vueltas Hoy</div>
        <div class="d-stat-val">{{ $vueltasHoy->count() }}</div>
        <div class="d-stat-sub">registradas</div>
    </div>

    {{-- Sanciones --}}
    <div class="d-stat {{ $sancionesPendientes->count() > 0 ? 'orange' : 'green' }}">
        <div class="d-stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="d-stat-lbl">Sanciones</div>
        <div class="d-stat-val">{{ $sancionesPendientes->count() }}</div>
        <div class="d-stat-sub">pendientes</div>
    </div>

    {{-- Deuda --}}
    <div class="d-stat {{ $deudaTributos > 0 ? 'red' : 'green' }}">
        <div class="d-stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div class="d-stat-lbl">Deuda Total</div>
        <div class="d-stat-val">S/{{ number_format($deudaTributos,2) }}</div>
        <div class="d-stat-sub">tributos</div>
    </div>
</div>

{{-- ④ TRIBUTO DEL DÍA ─────────────────────────── --}}
@php
    $tribBorder = $tributoHoy?->estado === 'pagado'
        ? 'var(--green)'
        : ($tributoHoy?->estado === 'exonerado' ? 'var(--accent)' : 'var(--red)');
@endphp
<div class="d-card" style="border-left: 4px solid {{ $tribBorder }};">
    <div class="d-card-head">
        <div class="d-card-title">
            <i class="fa-solid fa-sack-dollar" style="color:var(--accent);"></i>
            Tributo del Día
        </div>
        <span style="font-size:11px; color:var(--text3); font-weight:600;">
            {{ now()->locale('es')->isoFormat('ddd D MMM') }}
        </span>
    </div>

    <div class="d-card-body">
        @if($tributoHoy)
            {{-- Monto principal + estado --}}
            <div class="d-trib-top">
                <div>
                    <div class="d-trib-lbl">Monto del Día</div>
                    <div class="d-trib-val">S/ {{ number_format($tributoHoy->monto, 2) }}</div>
                </div>
                <div>
                    @if($tributoHoy->estado === 'pagado')
                        <span class="pill green"><i class="fa-solid fa-circle-check"></i> Pagado</span>
                    @elseif($tributoHoy->estado === 'exonerado')
                        <span class="pill blue"><i class="fa-solid fa-shield"></i> Exonerado</span>
                    @else
                        <span class="pill red"><i class="fa-solid fa-clock"></i> Pendiente</span>
                    @endif
                </div>
            </div>

            {{-- Deudas anteriores --}}
            @if($tributosPendientes->count() > 0)
                <div class="d-deudas">
                    <div class="d-deudas-head">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Deudas Acumuladas ({{ $tributosPendientes->count() }})
                    </div>
                    @foreach($tributosPendientes as $deuda)
                        <div class="d-deuda-row">
                            <div>
                                <div class="d-deuda-fecha">
                                    {{ $deuda->fecha->locale('es')->isoFormat('ddd D MMM') }}
                                </div>
                                <div class="d-deuda-tag">Tributo pendiente</div>
                            </div>
                            <div class="d-deuda-right">
                                <div class="d-deuda-amt">S/ {{ number_format($deuda->monto, 2) }}</div>
                                <form action="{{ route('conductor.tributos.pagar-mp', $deuda) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-mp btn-mp-sm btn-mp-danger">
                                        <i class="fa-solid fa-mobile-screen-button"></i> PAGAR
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Acción principal de pago / confirmación --}}
            @if($tributoHoy->estado === 'pagado')
                <div class="d-paid-info">
                    <i class="fa-solid fa-circle-check"></i>
                    Pagado el {{ $tributoHoy->cobrado_at?->format('d/m/Y') }}
                    a las {{ $tributoHoy->cobrado_at?->format('h:i A') }}
                    vía {{ ucfirst($tributoHoy->metodo_pago) }}
                </div>
            @else
                @if($conductor->vehiculos->count() > 0)
                    <div class="d-pay-box">
                        <div class="d-pay-lbl">Pagar tributo de hoy:</div>
                        <form action="{{ route('conductor.tributos.pagar-mp', $tributoHoy) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-mp">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                <span>PAGAR CON YAPE</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert warning" style="margin-top:12px;">
                        No tienes vehículo asignado para realizar el pago.
                    </div>
                @endif
            @endif
        @else
            <div class="d-empty">
                <i class="fa-regular fa-clipboard"></i>
                Sin tributo registrado para hoy
            </div>
        @endif
    </div>
</div>

{{-- ⑤ VUELTAS DEL DÍA ──────────────────────────── --}}
<div class="d-card">
    <div class="d-card-head">
        <div class="d-card-title">
            <i class="fa-solid fa-arrows-rotate" style="color:var(--accent);"></i>
            Vueltas de Hoy
        </div>
        <a href="{{ route('conductor.vueltas') }}" class="d-link">Ver historial</a>
    </div>
    <div class="d-card-body p-sm">
        @forelse($vueltasHoy as $vuelta)
            <div class="d-vuelta">
                <div class="d-vuelta-num">{{ $vuelta->numero_vuelta }}</div>
                <div class="d-vuelta-info">
                    <div class="d-vuelta-name">{{ $vuelta->ruta?->nombre_completo ?? 'Sin ruta asignada' }}</div>
                    <div class="d-vuelta-sub">{{ $vuelta->vehiculo?->placa_form ?? '—' }}</div>
                </div>
                <div class="d-vuelta-hora">
                    {{ $vuelta->hora_salida ? \Carbon\Carbon::parse($vuelta->hora_salida)->format('h:i A') : '--:--' }}
                </div>
            </div>
        @empty
            <div class="d-empty">
                <i class="fa-solid fa-arrows-rotate"></i>
                Sin vueltas registradas hoy
            </div>
        @endforelse
    </div>
</div>

{{-- ⑥ SANCIONES PENDIENTES (solo si hay) ────────── --}}
@if($sancionesPendientes->count() > 0)
    <div class="d-card">
        <div class="d-card-head">
            <div class="d-card-title">
                <i class="fa-solid fa-triangle-exclamation" style="color:var(--orange);"></i>
                Sanciones Pendientes
            </div>
            <a href="{{ route('conductor.sanciones') }}" class="d-link">Ver todas</a>
        </div>
        <div class="d-card-body p-sm">
            @foreach($sancionesPendientes as $sancion)
                <div class="d-sancion">
                    <div class="d-sancion-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="d-sancion-info">
                        <div class="d-sancion-name">{{ $sancion->motivo }}</div>
                        <div class="d-sancion-sub">
                            {{ $sancion->fecha->format('d/m/Y') }}
                            · {{ $sancion->vehiculo?->placa_form }}
                        </div>
                    </div>
                    <div class="d-sancion-right">
                        <div class="d-sancion-amt">S/ {{ number_format($sancion->monto, 2) }}</div>
                        <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-mp btn-mp-sm">
                                <i class="fa-solid fa-mobile-screen-button"></i> PAGAR
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@endsection
