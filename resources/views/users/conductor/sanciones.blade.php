@extends('layouts.conductor')
@section('title', 'Sanciones')

@section('content')

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat {{ $resumen['cantidad_pendiente'] > 0 ? 'red' : 'green' }}">
            <div class="stat-icon">⚠️</div>
            <div class="stat-label">Pendientes de Unidad</div>
            <div class="stat-val">{{ $resumen['cantidad_pendiente'] }}</div>
            <div class="stat-sub">S/ {{ number_format($resumen['total_pendiente'], 2) }}</div>
        </div>
        <div class="stat green">
            <div class="stat-icon">✅</div>
            <div class="stat-label">Pagado mes</div>
            <div class="stat-val">S/ {{ number_format($resumen['pagado_mes'], 0) }}</div>
        </div>
    </div>

    @if ($pendientes->count() > 0)
        <div class="alert warning">
            ⚠️ La unidad tiene <strong>{{ $pendientes->count() }}</strong> sanción(es) pendiente(s)
            por <strong>S/ {{ number_format($resumen['total_pendiente'], 2) }}</strong>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">⚠️ Sanciones de Unidad</span>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach ($pendientes as $sancion)
                    <div class="sancion-row" style="flex-direction: column; align-items: stretch; gap: 12px; padding: 15px;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <div class="sancion-icon" style="flex-shrink: 0;">⚠️</div>
                            <div class="sancion-info" style="flex-grow: 1;">
                                <div class="sancion-title" style="font-weight: 800; font-size: 15px;">{{ $sancion->motivo }}</div>
                                <div class="sancion-sub">
                                    <i class="fa-regular fa-calendar"></i> {{ $sancion->fecha->format('d/m/Y') }}
                                    @if ($sancion->vehiculo)
                                        <span style="margin: 0 5px;">•</span>
                                        <span style="font-weight: 700; color: var(--accent);">Padrón #{{ $sancion->vehiculo->numero_flota }}</span>
                                        · {{ $sancion->vehiculo->placa }}
                                    @endif
                                </div>
                                @if ($sancion->descripcion)
                                    <div class="sancion-sub" style="margin-top:5px; color: var(--text2); font-style: italic;">"{{ $sancion->descripcion }}"</div>
                                @endif
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-weight:900; color:var(--red); font-size: 18px;">S/ {{ number_format($sancion->monto, 2) }}</div>
                                <span class="pill red" style="margin-top:4px;">Pendiente</span>
                            </div>
                        </div>

                        {{-- Botón de Pago MP --}}
                        <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-mp" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px;">
                                <img src="https://http2.mlstatic.com/frontend-assets/billing/mpe-billing-v2/mercadopago/logo-mercadopago.svg" alt="Mercado Pago" style="height: 14px;">
                                <span>Pagar Sanción Online</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="alert success">✅ No tienes sanciones pendientes.</div>
    @endif

    @if ($pagadas->count() > 0)
        <div class="card">
            <div class="card-header">
                <span class="card-title">✅ Historial de Unidad</span>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach ($pagadas as $sancion)
                    <div class="summary-row" style="padding:11px 16px;">
                        <div>
                            <div style="font-size:13px; font-weight:600;">{{ $sancion->motivo }}</div>
                            <div style="font-size:11.5px; color:var(--text3);">
                                {{ $sancion->fecha->format('d/m/Y') }}
                                @if ($sancion->vehiculo)
                                    · {{ $sancion->vehiculo->placa_form }}
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700;">S/ {{ number_format($sancion->monto, 2) }}</span>
                            <span class="pill green">Pagado</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
