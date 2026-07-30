{{-- resources/views/users/conductor/dashboard.blade.php --}}

@extends('layouts.conductor')

@section('title', 'Estado de Flota')

@push('styles')
<style>
    /* ── STATS GRID 2x2 responsivo ── */
    .dash-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 14px;
    }

    /* stat-val fluido: no desborda en pantallas pequeñas */
    .dash-stats-grid .stat-val {
        font-size: clamp(13px, 4vw, 22px);
        word-break: break-all;
        line-height: 1.1;
    }
    .dash-stats-grid .stat-label {
        font-size: clamp(9px, 2.2vw, 10px);
    }
    .dash-stats-grid .stat {
        padding: 14px 12px;
    }

    /* ── TRIBUTO: MONTO ROW ── */
    .dash-monto-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }
    .dash-monto-col {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .dash-monto-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text2);
    }
    /* Monto fluido — nunca desborda en pantallas pequeñas */
    .dash-monto-val {
        font-size: clamp(17px, 5vw, 24px);
        font-weight: 800;
        line-height: 1;
        color: var(--text);
        word-break: break-word;
    }

    /* ── DEUDAS ACUMULADAS ── */
    .dash-deudas {
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 12px;
        margin-top: 14px;
    }
    .dash-deudas-title {
        font-weight: 800;
        color: #c53030;
        font-size: 11px;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .dash-deuda-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid #fecaca;
    }
    .dash-deuda-row:last-child { border-bottom: none; }
    .dash-deuda-info { flex: 1; min-width: 0; }
    .dash-deuda-fecha { font-size: 12px; font-weight: 700; color: #9b2c2c; }
    .dash-deuda-sub   { font-size: 10px; color: #c53030; opacity: .8; }
    .dash-deuda-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .dash-deuda-monto {
        font-weight: 800;
        color: #c53030;
        font-size: 13px;
        white-space: nowrap;
    }

    /* ── PAGADO INFO ── */
    .dash-paid-info {
        background: #f0fff4;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12px;
        color: #276749;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        line-height: 1.5;
    }

    /* ── SANCION ROW RESPONSIVA ── */
    .dash-sancion-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
    }
    .dash-sancion-row:last-child { border-bottom: none; }
    .dash-sancion-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }
    .dash-sancion-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .dash-sancion-monto {
        font-weight: 800;
        color: var(--red);
        font-size: clamp(12px, 3.5vw, 15px);
        white-space: nowrap;
    }

    /* ── PAYMENT BOX ── */
    .payment-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f0f7ff;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #bae6fd;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }
    .payment-label {
        font-size: 13px;
        color: #0369a1;
        font-weight: 600;
    }

    /* ── BREAKPOINT muy pequeño (< 360px) ── */
    @media (max-width: 360px) {
        .dash-stats-grid { gap: 7px; }
        .dash-stats-grid .stat { padding: 11px 9px; }
        .dash-monto-val { font-size: 16px; }
        .payment-box { flex-direction: column; align-items: stretch; }
        .payment-box .btn-mp { width: 100%; justify-content: center; }
        .dash-sancion-right { width: 100%; justify-content: space-between; }
    }

    /* ── BREAKPOINT grande (> 520px) ── */
    @media (min-width: 520px) {
        .dash-stats-grid { grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .dash-monto-val { font-size: 24px; }
    }
</style>
@endpush

@section('content')

    {{-- Alertas de documentos --}}
    @if (count($alertas) > 0)
        @foreach ($alertas as $alerta)
            <div class="alert warning mb16">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $alerta }}
            </div>
        @endforeach
    @endif

    {{-- Hero del vehículo --}}
    <div class="conductor-hero" style="background:linear-gradient(135deg,var(--gold) 0%,#92400e 100%); margin-bottom:14px;">
        <div class="conductor-av">
            <i class="fa-solid fa-car"></i>
        </div>
        <div class="conductor-hero-info" style="min-width:0;">
            <div class="conductor-hero-name">
                Flota {{ $conductor->vehiculos->first()?->numero_flota ?? 'S/N' }}
            </div>
            <div class="conductor-hero-sub">
                @if($conductor->vehiculos->first())
                    <span style="color:#fff; font-weight:800; font-size:clamp(13px,4vw,16px);">
                        {{ $conductor->vehiculos->first()->placa_form }}
                    </span>
                    <div style="opacity:.8; font-size:11px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $conductor->vehiculos->first()->marca }} {{ $conductor->vehiculos->first()->modelo }}
                    </div>
                @else
                    Sin vehículo asignado
                @endif
            </div>
        </div>
    </div>

    {{-- Grid de 4 stats (2 columnas en móvil, 4 en tablet) --}}
    <div class="dash-stats-grid">
        <div class="stat {{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}">
            <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="stat-label">Tributo Hoy</div>
            <div class="stat-val">{{ $tributoHoy ? 'S/ '.number_format($tributoHoy->monto,2) : 'S/ 0' }}</div>
            <div class="stat-sub">
                @if ($tributoHoy?->estado === 'pagado') <i class="fa-solid fa-circle-check"></i> Pagado
                @elseif($tributoHoy?->estado === 'exonerado') <i class="fa-solid fa-shield-halved"></i> Exonerado
                @elseif($tributoHoy) <i class="fa-solid fa-hourglass-half"></i> Pendiente
                @else Sin registro @endif
            </div>
        </div>

        <div class="stat blue">
            <div class="stat-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="stat-label">Vueltas</div>
            <div class="stat-val">{{ $vueltasHoy->count() }}</div>
            <div class="stat-sub">registradas hoy</div>
        </div>

        <div class="stat {{ $sancionesPendientes->count() > 0 ? 'orange' : 'green' }}">
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-label">Sanciones</div>
            <div class="stat-val">{{ $sancionesPendientes->count() }}</div>
            <div class="stat-sub">pendientes</div>
        </div>

        <div class="stat {{ $deudaTributos > 0 ? 'red' : 'green' }}">
            <div class="stat-icon"><i class="fa-solid fa-clipboard-list"></i></div>
            <div class="stat-label">Deuda</div>
            <div class="stat-val">S/ {{ number_format($deudaTributos, 2) }}</div>
            <div class="stat-sub">tributos</div>
        </div>
    </div>

    {{-- Card Tributo del día --}}
    @php
        $borderColor = $tributoHoy?->estado === 'pagado'
            ? 'var(--green)'
            : ($tributoHoy?->estado === 'exonerado' ? 'var(--accent)' : 'var(--red)');
    @endphp
    <div class="card mb16" style="border-left:4px solid {{ $borderColor }};">
        <div class="card-header">
            <span class="card-title">
                <i class="fa-solid fa-sack-dollar" style="color:var(--accent); margin-right:5px;"></i>
                Tributo de la Flota
            </span>
            <span style="font-size:11px; color:var(--text3); font-weight:600; white-space:nowrap;">
                {{ now()->locale('es')->isoFormat('ddd D MMM') }}
            </span>
        </div>
        <div class="card-body" style="padding:16px;">
            @if ($tributoHoy)
                {{-- Monto + Estado --}}
                <div class="dash-monto-row">
                    <div class="dash-monto-col">
                        <span class="dash-monto-label">Monto del Día</span>
                        <span class="dash-monto-val">S/ {{ number_format($tributoHoy->monto, 2) }}</span>
                    </div>
                    <div class="dash-monto-col" style="align-items:flex-end;">
                        <span class="dash-monto-label">Estado</span>
                        @if($tributoHoy->estado === 'pagado')
                            <span class="pill green"><i class="fa-solid fa-circle-check"></i> Pagado</span>
                        @elseif($tributoHoy->estado === 'exonerado')
                            <span class="pill blue"><i class="fa-solid fa-shield"></i> Exonerado</span>
                        @else
                            <span class="pill red"><i class="fa-solid fa-clock"></i> Pendiente</span>
                        @endif
                    </div>
                </div>

                {{-- Deudas acumuladas anteriores --}}
                @if ($tributosPendientes->count() > 0)
                    <div class="dash-deudas">
                        <div class="dash-deudas-title">
                            <i class="fa-solid fa-triangle-exclamation"></i> Deudas Acumuladas
                        </div>
                        @foreach($tributosPendientes as $deuda)
                            <div class="dash-deuda-row">
                                <div class="dash-deuda-info">
                                    <div class="dash-deuda-fecha">{{ $deuda->fecha->locale('es')->isoFormat('ddd D MMM') }}</div>
                                    <div class="dash-deuda-sub">Tributo Pendiente</div>
                                </div>
                                <div class="dash-deuda-right">
                                    <div class="dash-deuda-monto">S/ {{ number_format($deuda->monto, 2) }}</div>
                                    <form action="{{ route('conductor.tributos.pagar-mp', $deuda) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-mp btn-mp-sm btn-mp-danger">
                                            <i class="fa-solid fa-mobile-screen-button"></i>
                                            <span>PAGAR</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Acción de pago --}}
                @if ($tributoHoy->estado === 'pagado')
                    <div class="dash-paid-info" style="margin-top:14px;">
                        <i class="fa-solid fa-circle-check"></i>
                        Pago registrado: {{ $tributoHoy->cobrado_at?->format('d/m/Y h:i A') }}
                        vía {{ ucfirst($tributoHoy->metodo_pago) }}
                    </div>
                @else
                    @if($conductor->vehiculos->count() > 0)
                        <div class="payment-box">
                            <div class="payment-label">Pagar tributo de hoy:</div>
                            <form action="{{ route('conductor.tributos.pagar-mp', $tributoHoy) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mp">
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                    <span>PAGAR CON YAPE</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert warning" style="margin-top:14px;">
                            No tienes un vehículo asignado para realizar el pago.
                        </div>
                    @endif
                @endif
            @else
                <div class="empty-state">
                    <div style="font-size:32px; margin-bottom:8px; color:var(--text3);">
                        <i class="fa-regular fa-clipboard"></i>
                    </div>
                    <div>Sin tributo registrado para hoy</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Vueltas del día --}}
    <div class="card mb16">
        <div class="card-header">
            <span class="card-title">
                <i class="fa-solid fa-arrows-rotate" style="color:var(--accent); margin-right:5px;"></i>
                Mis Vueltas de Hoy
            </span>
            <a href="{{ route('conductor.vueltas') }}" class="btn btn-secondary btn-sm"
               style="text-decoration:none; flex-shrink:0;">Ver todas</a>
        </div>
        <div class="card-body" style="padding:12px;">
            @forelse($vueltasHoy as $vuelta)
                <div class="vuelta-card">
                    <div class="vuelta-num">{{ $vuelta->numero_vuelta }}</div>
                    <div class="vuelta-info">
                        <div class="vuelta-name">{{ $vuelta->ruta?->nombre_completo ?? 'Sin ruta' }}</div>
                        <div class="vuelta-sub">{{ $vuelta->vehiculo?->placa_form ?? '-' }}</div>
                    </div>
                    <div class="vuelta-time">
                        @if ($vuelta->hora_salida)
                            {{ \Carbon\Carbon::parse($vuelta->hora_salida)->format('h:i A') }}
                        @else
                            --:--
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div style="font-size:32px; margin-bottom:8px; color:var(--text3);">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>Sin vueltas registradas hoy</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Sanciones pendientes --}}
    @if ($sancionesPendientes->count() > 0)
        <div class="card mb16">
            <div class="card-header">
                <span class="card-title">
                    <i class="fa-solid fa-triangle-exclamation" style="color:var(--orange); margin-right:5px;"></i>
                    Sanciones Pendientes
                </span>
                <a href="{{ route('conductor.sanciones') }}" class="btn btn-secondary btn-sm"
                   style="text-decoration:none; flex-shrink:0;">Ver todas</a>
            </div>
            <div class="card-body" style="padding:12px;">
                @foreach ($sancionesPendientes as $sancion)
                    <div class="dash-sancion-row">
                        <div class="dash-sancion-left">
                            <div class="sancion-icon">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div class="sancion-info">
                                <div class="sancion-title">{{ $sancion->motivo }}</div>
                                <div class="sancion-sub">
                                    {{ $sancion->fecha->format('d/m/Y') }} · {{ $sancion->vehiculo?->placa_form }}
                                </div>
                            </div>
                        </div>
                        <div class="dash-sancion-right">
                            <div class="dash-sancion-monto">S/ {{ number_format($sancion->monto, 2) }}</div>
                            <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mp btn-mp-sm">
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                    <span>PAGAR</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
