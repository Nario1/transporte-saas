{{-- resources/views/users/conductor/dashboard.blade.php --}}

@extends('layouts.conductor')

@section('title', 'Estado de Flota')

@section('content')

    {{-- 1. Alertas de documentos --}}
    @if (count($alertas) > 0)
        @foreach ($alertas as $alerta)
            <div class="alert warning mb16">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $alerta }}
            </div>
        @endforeach
    @endif

    {{-- 2. Hero del conductor - Centrado en el Vehículo --}}
    <div class="conductor-hero mb16" style="background: linear-gradient(135deg, var(--gold) 0%, #92400e 100%); margin-bottom: 16px;">
        <div class="conductor-av">
            <i class="fa-solid fa-car"></i>
        </div>
        <div class="conductor-hero-info">
            <div class="conductor-hero-name">Flota {{ $conductor->vehiculos->first()?->numero_flota ?? 'S/N' }}</div>
            <div class="conductor-hero-sub">
                @if($conductor->vehiculos->first())
                    <span style="color: #fff; font-weight: 800; font-size: 16px;">{{ $conductor->vehiculos->first()->placa_form }}</span>
                    <div style="opacity: 0.8; font-size: 11px; margin-top: 2px;">{{ $conductor->vehiculos->first()->marca }} {{ $conductor->vehiculos->first()->modelo }}</div>
                @else
                    Sin vehículo asignado
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Stats del día (Grid de 2 columnas) --}}
    <div class="stats-row mb16" style="grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
        <div class="stat {{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}">
            <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="stat-label">Tributo Hoy</div>
            <div class="stat-val">{{ $tributoHoy ? 'S/ ' . number_format($tributoHoy->monto, 2) : 'S/ 0' }}</div>
            <div class="stat-sub">
                @if ($tributoHoy?->estado === 'pagado')
                    <i class="fa-solid fa-circle-check"></i> Pagado
                @elseif($tributoHoy?->estado === 'exonerado')
                    <i class="fa-solid fa-shield-halved"></i> Exonerado
                @elseif($tributoHoy)
                    <i class="fa-solid fa-hourglass-half"></i> Pendiente
                @else
                    Sin registro
                @endif
            </div>
        </div>
        <div class="stat blue">
            <div class="stat-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="stat-label">Vueltas de Flota</div>
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
            <div class="stat-label">Deuda de Flota</div>
            <div class="stat-val">S/ {{ number_format($deudaTributos, 2) }}</div>
            <div class="stat-sub">tributos pendientes</div>
        </div>
    </div>

    {{-- 4. Tributo del día --}}
    <div class="card mb16 border-{{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}" style="border-left: 5px solid; margin-bottom: 16px;">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-sack-dollar" style="color: var(--accent); margin-right: 5px;"></i> Tributo de la Flota</span>
            <span class="tb-date">{{ now()->locale('es')->isoFormat('dddd D MMM') }}</span>
        </div>
        <div class="card-body" style="padding: 16px;">
            @if ($tributoHoy)
                <div class="dashboard-tributo-summary">
                    <div class="summary-main" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div class="summary-col">
                            <span class="summary-label" style="font-size: 11px; color: var(--text3); text-transform: uppercase;">Monto del Día</span>
                            <span class="summary-val" style="font-size: 24px; font-weight: 800; color: var(--text); display: block; margin-top: 4px;">S/ {{ number_format($tributoHoy->monto, 2) }}</span>
                        </div>
                        <div class="summary-col" style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                            <span class="summary-label" style="font-size: 11px; color: var(--text3); text-transform: uppercase; margin-bottom: 4px;">Estado</span>
                            @if($tributoHoy->estado === 'pagado')
                                <span class="pill green"><i class="fa-solid fa-circle-check"></i> Pagado</span>
                            @elseif($tributoHoy->estado === 'exonerado')
                                <span class="pill blue"><i class="fa-solid fa-shield"></i> Exonerado</span>
                            @else
                                <span class="pill red"><i class="fa-solid fa-clock"></i> Pendiente</span>
                            @endif
                        </div>
                    </div>

                    @if ($tributosPendientes->count() > 0)
                        <div class="debt-warning" style="margin-top: 16px;">
                            <div style="font-weight: 800; color: var(--red); font-size: 11px; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Deudas Acumuladas
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($tributosPendientes as $deuda)
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; background: var(--red-l); padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(220, 38, 38, 0.2);">
                                    <div style="font-size: 12px; color: var(--red);">
                                        <div style="font-weight: 700;">{{ $deuda->fecha->locale('es')->isoFormat('ddd D MMM') }}</div>
                                        <div style="font-size: 10px; opacity: 0.8;">Tributo Pendiente</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="font-weight: 800; color: var(--red); font-size: 14px;">S/ {{ number_format($deuda->monto, 2) }}</div>
                                        <form action="{{ route('conductor.tributos.pagar-mp', $deuda) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn-mp btn-mp-sm btn-mp-danger">
                                                <i class="fa-solid fa-mobile-screen-button"></i>
                                                <span>PAGAR CON YAPE</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div style="margin-top: 16px;">
                        @if ($tributoHoy->estado === 'pagado')
                            <div class="payment-info" style="background: var(--green-l); border-radius: 10px; padding: 12px; font-size: 13px; color: var(--green); border: 1px solid rgba(22, 163, 74, 0.15);">
                                <strong>Pago registrado:</strong> {{ $tributoHoy->cobrado_at?->format('d/m/Y h:i A') }} vía {{ ucfirst($tributoHoy->metodo_pago) }}
                            </div>
                        @else
                            @if($conductor->vehiculos->count() > 0)
                                <div class="payment-box" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 12px 14px; border-radius: 12px; margin-top: 0;">
                                    <div class="payment-label" style="font-size: 13px; font-weight: 600;">Pagar tributo de hoy:</div>
                                    <form action="{{ route('conductor.tributos.pagar-mp', $tributoHoy) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn-mp">
                                            <i class="fa-solid fa-mobile-screen-button"></i>
                                            <span>PAGAR CON YAPE</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="alert warning" style="margin-bottom: 0;">
                                    No tienes un vehículo asignado para realizar el pago.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div style="font-size: 32px; margin-bottom: 8px; color: var(--text3);"><i class="fa-regular fa-clipboard"></i></div>
                    <div>Sin tributo registrado para hoy</div>
                </div>
            @endif
        </div>
    </div>

    {{-- 5. Vueltas del día --}}
    <div class="card mb16" style="margin-bottom: 16px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="card-title"><i class="fa-solid fa-arrows-rotate" style="color: var(--accent); margin-right: 5px;"></i> Mis Vueltas de Hoy</span>
            <a href="{{ route('conductor.vueltas') }}" class="btn btn-secondary btn-sm" style="text-decoration: none;">Ver todas</a>
        </div>
        <div class="card-body" style="padding: 16px;">
            @forelse($vueltasHoy as $vuelta)
                <div class="vuelta-card" style="margin-bottom: 8px;">
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
                <div class="empty-state" style="padding: 16px 0;">
                    <div style="font-size: 32px; margin-bottom: 8px; color: var(--text3);"><i class="fa-solid fa-arrows-rotate"></i></div>
                    <div>Sin vueltas registradas hoy</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 6. Sanciones pendientes --}}
    @if ($sancionesPendientes->count() > 0)
        <div class="card mb16" style="margin-bottom: 16px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color: var(--orange); margin-right: 5px;"></i> Sanciones Pendientes</span>
                <a href="{{ route('conductor.sanciones') }}" class="btn btn-secondary btn-sm" style="text-decoration: none;">Ver todas</a>
            </div>
            <div class="card-body" style="padding: 16px;">
                @foreach ($sancionesPendientes as $sancion)
                    <div class="sancion-row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 12px 14px; background: var(--card); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 8px; box-shadow: var(--shadow);">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 140px;">
                            <div class="sancion-icon" style="font-size: 16px; color: var(--red); background: var(--red-l); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div class="sancion-info" style="min-width: 0;">
                                <div class="sancion-title" style="font-weight: 700; color: var(--text); font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $sancion->motivo }}</div>
                                <div class="sancion-sub" style="font-size: 11px; color: var(--text3); margin-top: 2px;">{{ $sancion->fecha->format('d/m/Y') }} · {{ $sancion->vehiculo?->placa_form }}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-shrink: 0; min-width: 150px; justify-content: flex-end;">
                            <div style="font-weight: 800; color: var(--red); font-size: 14px;">
                                S/ {{ number_format($sancion->monto, 2) }}
                            </div>
                            <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-mp btn-mp-sm">
                                    <i class="fa-solid fa-mobile-screen-button"></i> <span>PAGAR CON YAPE</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection

@push('styles')
<style>
    .dashboard-tributo-summary .summary-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dashboard-tributo-summary .summary-col {
        display: flex;
        flex-direction: column;
    }
    .border-green { border-color: var(--green) !important; }
    .border-red { border-color: var(--red) !important; }
    .border-blue { border-color: var(--accent) !important; }
</style>
@endpush
