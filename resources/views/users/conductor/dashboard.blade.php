{{-- resources/views/users/conductor/dashboard.blade.php --}}

@extends('layouts.conductor')

@section('title', 'Estado de Unidad')

@section('content')

    {{-- Alertas de documentos --}}
    @if (count($alertas) > 0)
        @foreach ($alertas as $alerta)
            <div class="alert warning mb16">
                ⚠️ {{ $alerta }}
            </div>
        @endforeach
    @endif

    {{-- Bienvenida - Centrada en el Vehículo --}}
    <div class="conductor-hero" style="background: linear-gradient(135deg, var(--gold) 0%, #92400e 100%);">
        <div class="conductor-av">
            <i class="fa-solid fa-bus"></i>
        </div>
        <div class="conductor-hero-info">
            <div class="conductor-hero-name">Unidad {{ $conductor->vehiculos->first()?->numero_flota ?? 'S/N' }}</div>
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

    {{-- Stats del día --}}
    <div class="stats-row" style="grid-template-columns: repeat(2, 1fr);">
        <div class="stat {{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Tributo Hoy</div>
            <div class="stat-val">{{ $tributoHoy ? 'S/ ' . number_format($tributoHoy->monto, 2) : 'S/ 0' }}</div>
            <div class="stat-sub">
                @if ($tributoHoy?->estado === 'pagado')
                    ✅ Pagado
                @elseif($tributoHoy?->estado === 'exonerado')
                    🛡️ Exonerado
                @elseif($tributoHoy)
                    ⏳ Pendiente
                @else
                    Sin registro
                @endif
            </div>
        </div>
        <div class="stat blue">
            <div class="stat-icon">🔄</div>
            <div class="stat-label">Vueltas de Unidad</div>
            <div class="stat-val">{{ $vueltasHoy->count() }}</div>
            <div class="stat-sub">registradas hoy</div>
        </div>
        <div class="stat {{ $sancionesPendientes->count() > 0 ? 'orange' : 'green' }}">
            <div class="stat-icon">⚠️</div>
            <div class="stat-label">Sanciones</div>
            <div class="stat-val">{{ $sancionesPendientes->count() }}</div>
            <div class="stat-sub">pendientes</div>
        </div>
        <div class="stat {{ $deudaTributos > 0 ? 'red' : 'green' }}">
            <div class="stat-icon">📋</div>
            <div class="stat-label">Deuda de Unidad</div>
            <div class="stat-val">S/ {{ number_format($deudaTributos, 2) }}</div>
            <div class="stat-sub">tributos pendientes</div>
        </div>
    </div>

    {{-- Tributo del día --}}
    <div class="card mb16 border-{{ $tributoHoy?->estado === 'pagado' ? 'green' : ($tributoHoy?->estado === 'exonerado' ? 'blue' : 'red') }}" style="border-left: 5px solid;">
        <div class="card-header">
            <span class="card-title">💰 Tributo de la Unidad</span>
            <span class="tb-date">{{ now()->locale('es')->isoFormat('dddd D MMM') }}</span>
        </div>
        <div class="card-body" style="padding: 20px;">
            @if ($tributoHoy)
                <div class="dashboard-tributo-summary">
                    <div class="summary-main">
                        <div class="summary-col">
                            <span class="summary-label">Monto del Día</span>
                            <span class="summary-val" style="font-size: 24px; font-weight: 800;">S/ {{ number_format($tributoHoy->monto, 2) }}</span>
                        </div>
                        <div class="summary-col" style="text-align: right;">
                            <span class="summary-label">Estado</span>
                            @if($tributoHoy->estado === 'pagado')
                                <span class="pill green">✅ Pagado</span>
                            @elseif($tributoHoy->estado === 'exonerado')
                                <span class="pill blue">🛡️ Exonerado</span>
                            @else
                                <span class="pill red">⏳ Pendiente</span>
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
                                            <button type="submit" class="btn-mp" style="background: #ef4444; color: #fff; border: none; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.15);">
                                                <img src="https://http2.mlstatic.com/frontend-assets/billing/mpe-billing-v2/mercadopago/logo-mercadopago.svg" alt="MP" style="height: 10px; filter: brightness(0) invert(1);">
                                                <span>PAGAR</span>
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
                                <strong>Pago registrado:</strong> {{ $tributoHoy->cobrado_at?->format('d/m/Y h:i A') }} vía {{ ucfirst($tributoHoy->metodo_pago) }}
                            </div>
                        @else
                            @if($conductor->vehiculos->count() > 0)
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f0f7ff; padding: 12px 16px; border-radius: 14px; border: 1px solid #bae6fd;">
                                    <div style="font-size: 13px; color: #0369a1; font-weight: 600;">Pagar tributo de hoy:</div>
                                    <form action="{{ route('conductor.tributos.pagar-mp', $tributoHoy) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-mp" style="background: #009ee3; color: #fff; border: none; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,158,227,0.15);">
                                            <img src="https://http2.mlstatic.com/frontend-assets/billing/mpe-billing-v2/mercadopago/logo-mercadopago.svg" alt="Mercado Pago" style="height: 12px; filter: brightness(0) invert(1);">
                                            <span>PAGAR ONLINE</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="alert warning">
                                    No tienes un vehículo asignado para realizar el pago.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                    <div>Sin tributo registrado para hoy</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Vueltas del día --}}
    <div class="card mb16">
        <div class="card-header">
            <span class="card-title">🔄 Mis Vueltas de Hoy</span>
            <a href="{{ route('conductor.vueltas') }}" class="btn btn-secondary btn-sm">Ver todas</a>
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
                    <div style="font-size: 32px; margin-bottom: 8px;">🔄</div>
                    <div>Sin vueltas registradas hoy</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Sanciones pendientes --}}
    @if ($sancionesPendientes->count() > 0)
        <div class="card mb16">
            <div class="card-header">
                <span class="card-title">⚠️ Sanciones Pendientes</span>
                <a href="{{ route('conductor.sanciones') }}" class="btn btn-secondary btn-sm">Ver todas</a>
            </div>
            <div class="card-body">
                @foreach ($sancionesPendientes as $sancion)
                    <div class="sancion-row" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #fff; border: 1px solid #fee2e2; border-radius: 12px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="sancion-icon" style="font-size: 20px;">⚠️</div>
                            <div class="sancion-info">
                                <div class="sancion-title" style="font-weight: 700; color: #0f172a; font-size: 14px;">{{ $sancion->motivo }}</div>
                                <div class="sancion-sub" style="font-size: 11px; color: #64748b;">{{ $sancion->fecha->format('d/m/Y') }} · {{ $sancion->vehiculo?->placa_form }}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="font-weight: 800; color: #ef4444; font-size: 16px;">
                                S/ {{ number_format($sancion->monto, 2) }}
                            </div>
                            <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mp" style="background: #009ee3; color: #fff; border: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-credit-card"></i> PAGAR
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
