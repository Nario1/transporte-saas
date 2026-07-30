{{-- resources/views/users/conductor/dashboard.blade.php --}}

@extends('layouts.conductor')

@section('title', 'Estado de Flota')

@section('content')

    {{-- Alertas de documentos --}}
    @if (count($alertas) > 0)
        @foreach ($alertas as $alerta)
            <div class="alert warning mb16">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ $alerta }}
            </div>
        @endforeach
    @endif

    {{-- Bienvenida - Centrada en el Veh├¡culo --}}
    <div class="conductor-hero" style="background: linear-gradient(135deg, var(--gold) 0%, #92400e 100%);">
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
                    Sin veh├¡culo asignado
                @endif
            </div>
        </div>
    </div>

    {{-- Stats del d├¡a --}}
    <div class="stats-row" style="grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
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

    {{-- Tributo del d├¡a --}}
    <div class="card mb16 border-{{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}" style="border-left: 5px solid;">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-sack-dollar" style="color: var(--accent); margin-right: 5px;"></i> Tributo de la Flota</span>
            <span class="tb-date">{{ now()->locale('es')->isoFormat('dddd D MMM') }}</span>
        </div>
        <div class="card-body" style="padding: 20px;">
            @if ($tributoHoy)
                <div class="dashboard-tributo-summary">
                    <div class="summary-main">
                        <div class="summary-col">
                            <span class="summary-label">Monto del Día</span>
                            <span class="summary-val" style="font-size: 24px; font-weight: 680;">S/ {{ number_format($tributoHoy->monto, 2) }}</span>
                        </div>
                        <div class="summary-col" style="text-align: right;">
                            <span class="summary-label">Estado</span>
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
                        <div class="debt-warning" style="margin-top: 20px;">
                            <div style="font-weight: 800; color: #c53030; font-size: 12px; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Deudas Acumuladas
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($tributosPendientes as $deuda)
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #fff5f5; padding: 8px 12px; border-radius: 10px; border: 1px solid #feb2b2;">
                                    <div style="font-size: 12px; color: #9b2c2c;">
                                        <div style="font-weight: 700;">{{ $deuda->fecha->locale('es')->isoFormat('ddd D MMM') }}</div>
                                        <div style="font-size: 10px; opacity: 0.8;">Tributo Pendiente</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="font-weight: 800; color: #c53030; font-size: 14px;">S/ {{ number_format($deuda->monto, 2) }}</div>
                                        <form action="{{ route('conductor.tributos.pagar-mp', $deuda) }}" method="POST">
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

                    <div style="margin-top: 20px;">
                        @if ($tributoHoy->estado === 'pagado')
                            <div class="payment-info" style="background: #f0fff4; border-radius: 12px; padding: 12px; font-size: 13px; color: #276749;">
                                <strong>Pago registrado:</strong> {{ $tributoHoy->cobrado_at?->format('d/m/Y h:i A') }} v├¡a {{ ucfirst($tributoHoy->metodo_pago) }}
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
                                <div class="alert warning">
                                    No tienes un veh├¡culo asignado para realizar el pago.
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

    {{-- Vueltas del d├¡a --}}
    <div class="card mb16">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="card-title"><i class="fa-solid fa-arrows-rotate" style="color: var(--accent); margin-right: 5px;"></i> Mis Vueltas de Hoy</span>
            <a href="{{ route('conductor.vueltas') }}" class="btn btn-secondary btn-sm" style="text-decoration: none;">Ver todas</a>
        </div>
        <div class="card-body" style="padding: 16px;">
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
                    <div style="font-size: 32px; margin-bottom: 8px; color: var(--text3);"><i class="fa-solid fa-arrows-rotate"></i></div>
                    <div>Sin vueltas registradas hoy</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Sanciones pendientes --}}
    @if ($sancionesPendientes->count() > 0)
        <div class="card mb16">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color: var(--orange); margin-right: 5px;"></i> Sanciones Pendientes</span>
                <a href="{{ route('conductor.sanciones') }}" class="btn btn-secondary btn-sm" style="text-decoration: none;">Ver todas</a>
            </div>
            <div class="card-body" style="padding: 16px;">
                @foreach ($sancionesPendientes as $sancion)
                    <div class="sancion-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #fff; border: 1px solid #fee2e2; border-radius: 12px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="sancion-icon" style="font-size: 18px; color: var(--red);"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div class="sancion-info">
                                <div class="sancion-title" style="font-weight: 700; color: #0f172a; font-size: 14px;">{{ $sancion->motivo }}</div>
                                <div class="sancion-sub" style="font-size: 11px; color: #64748b;">{{ $sancion->fecha->format('d/m/Y') }} ┬À {{ $sancion->vehiculo?->placa_form }}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="font-weight: 800; color: #ef4444; font-size: 16px;">
                                S/ {{ number_format($sancion->monto, 2) }}
                            </div>
                            <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mp" style="background: #009ee3; color: #fff; border: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-mobile-screen-button"></i> PAGAR CON YAPE
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
    .border-green { border-color: #48bb78 !important; }
    .border-red { border-color: #f56565 !important; }
    .border-blue { border-color: #4299e1 !important; }
</style>
@endpush
