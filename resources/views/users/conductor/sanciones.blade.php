@extends('layouts.conductor')
@section('title', 'Sanciones')

@section('content')

    {{-- Stats --}}
    <div class="stats-row" style="grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
        <div class="stat {{ $resumen['cantidad_pendiente'] > 0 ? 'red' : 'green' }}">
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-label">Pendientes de Flota</div>
            <div class="stat-val">{{ $resumen['cantidad_pendiente'] }}</div>
            <div class="stat-sub">S/ {{ number_format($resumen['total_pendiente'], 2) }}</div>
        </div>
        <div class="stat green">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-label">Pagado mes</div>
            <div class="stat-val">S/ {{ number_format($resumen['pagado_mes'], 0) }}</div>
        </div>
    </div>

    @if ($pendientes->count() > 0)
        <div class="alert warning" style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-triangle-exclamation"></i> La flota tiene <strong>{{ $pendientes->count() }}</strong> sanción(es) pendiente(s) por <strong>S/ {{ number_format($resumen['total_pendiente'], 2) }}</strong>
        </div>

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color:var(--orange); margin-right:5px;"></i> Sanciones de Flota</span>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach ($pendientes as $sancion)
                    <div class="sancion-row" style="flex-direction: column; align-items: stretch; gap: 12px; padding: 15px; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; gap: 12px; align-items: center; justify-content: space-between;">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div class="sancion-icon" style="flex-shrink: 0; color:var(--red); font-size:18px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                <div class="sancion-info" style="flex-grow: 1;">
                                    <div class="sancion-title" style="font-weight: 800; font-size: 15px; color: var(--text);">{{ $sancion->motivo }}</div>
                                    <div class="sancion-sub" style="font-size: 11.5px; color: var(--text3); margin-top: 2px;">
                                        <i class="fa-regular fa-calendar"></i> {{ $sancion->fecha->format('d/m/Y') }}
                                        @if ($sancion->vehiculo)
                                            <span style="margin: 0 5px;">•</span>
                                            <span style="font-weight: 700; color: var(--accent);">Padrón #{{ $sancion->vehiculo->numero_flota }}</span>
                                            · {{ $sancion->vehiculo->placa }}
                                        @endif
                                    </div>
                                    @if ($sancion->descripcion)
                                        <div class="sancion-sub" style="margin-top:5px; color: var(--text2); font-style: italic; font-size: 12px;">"{{ $sancion->descripcion }}"</div>
                                    @endif
                                </div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-weight:900; color:var(--red); font-size: 18px;">S/ {{ number_format($sancion->monto, 2) }}</div>
                                <span class="pill red" style="margin-top:4px;"><i class="fa-solid fa-clock"></i> Pendiente</span>
                            </div>
                        </div>

                        {{-- Botón de Pago MP --}}
                        <form action="{{ route('conductor.sanciones.pagar-mp', $sancion) }}" method="POST" style="margin-top: 5px;">
                            @csrf
                            <button type="submit" class="btn-mp" style="width: 100%; justify-content: center; padding: 12px; font-size: 13px; cursor: pointer; border: none; background: #009ee3; color: white; border-radius: 10px; display: flex; align-items: center; gap: 8px;">
                                <img src="https://http2.mlstatic.com/frontend-assets/billing/mpe-billing-v2/mercadopago/logo-mercadopago.svg" alt="Mercado Pago" style="height: 14px; filter: brightness(0) invert(1);">
                                <span>Pagar Online</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="alert success" style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-check"></i> No tienes sanciones pendientes.
        </div>
    @endif

    @if ($pagadas->count() > 0)
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-clipboard-list" style="color:var(--accent); margin-right:5px;"></i> Historial de Flota</span>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach ($pagadas as $sancion)
                    <div class="summary-row" style="padding:14px 16px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size:13px; font-weight:600; color: var(--text);">{{ $sancion->motivo }}</div>
                            <div style="font-size:11.5px; color:var(--text3); margin-top: 2px;">
                                {{ $sancion->fecha->format('d/m/Y') }}
                                @if ($sancion->vehiculo)
                                    · {{ $sancion->vehiculo->placa_form }}
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; color: var(--text);">S/ {{ number_format($sancion->monto, 2) }}</span>
                            <span class="pill green"><i class="fa-solid fa-check"></i> Pagado</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
