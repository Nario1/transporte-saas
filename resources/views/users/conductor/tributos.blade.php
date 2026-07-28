@extends('layouts.conductor')
@section('title', 'Tributos')

@section('content')

    {{-- Resumen mes --}}
    <div class="stats-row" style="grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
        <div class="stat green">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-label">Pagado mes</div>
            <div class="stat-val">S/ {{ number_format($resumenMes['pagado'], 0) }}</div>
        </div>
        <div class="stat {{ $deudaTotal > 0 ? 'red' : 'green' }}">
            <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            <div class="stat-label">Deuda total</div>
            <div class="stat-val">S/ {{ number_format($deudaTotal, 0) }}</div>
            <div class="stat-sub">{{ $diasDeuda }} día(s)</div>
        </div>
    </div>

    {{-- Alerta deuda --}}
    @if ($deudaTotal > 0)
        <div class="alert warning" style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-triangle-exclamation"></i> Tienes <strong>{{ $diasDeuda }} día(s)</strong> de deuda por <strong>S/ {{ number_format($deudaTotal, 2) }}</strong>
        </div>
    @endif

    {{-- Tributo hoy --}}
    <div class="card" style="margin-bottom: 16px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="card-title"><i class="fa-solid fa-sack-dollar" style="color:var(--accent); margin-right:5px;"></i> Tributo del Día</span>
            <span style="font-size:12px; color:var(--text3);">{{ now()->locale('es')->isoFormat('D MMM YYYY') }}</span>
        </div>
        <div class="card-body" style="padding: 20px;">
            @if ($tributoHoy)
                <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
                    <span class="summary-label" style="font-weight:500; color:var(--text2);">Padrón / Placa</span>
                    <span class="summary-val" style="font-weight:700; color:#2563eb;">#{{ $tributoHoy->vehiculo?->numero_flota ?? '???' }} — {{ $tributoHoy->vehiculo?->placa ?? '—' }}</span>
                </div>
                <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
                    <span class="summary-label" style="font-weight:500; color:var(--text2);">Empresa</span>
                    <span class="summary-val" style="font-weight:600; color:var(--text);">{{ $tributoHoy->vehiculo?->empresa?->nombre ?? ($tributoHoy->empresa?->nombre ?? '—') }}</span>
                </div>
                <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
                    <span class="summary-label" style="font-weight:500; color:var(--text2);">Monto a Pagar</span>
                    <span class="summary-val" style="font-size:1.1rem; font-weight:800;">S/ {{ number_format($tributoHoy->monto, 2) }}</span>
                </div>
                <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc; align-items: center;">
                    <span class="summary-label" style="font-weight:500; color:var(--text2);">Estado</span>
                    @if($tributoHoy->estado === 'pagado')
                        <span class="pill green"><i class="fa-solid fa-circle-check"></i> Pagado</span>
                    @elseif($tributoHoy->estado === 'exonerado')
                        <span class="pill blue"><i class="fa-solid fa-shield"></i> Exonerado</span>
                    @else
                        <span class="pill red"><i class="fa-solid fa-clock"></i> Pendiente</span>
                    @endif
                </div>
                
                @if ($tributoHoy->estado === 'pagado')
                    <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500; color:var(--text2);">Método de Pago</span>
                        <span class="summary-val" style="font-weight:600;">{{ ucfirst($tributoHoy->metodo_pago ?? '—') }}</span>
                    </div>
                    <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span class="summary-label" style="font-weight:500; color:var(--text2);">Fecha y Hora</span>
                        <span class="summary-val" style="font-weight:600;">{{ $tributoHoy->cobrado_at?->format('d/m/Y h:i A') ?? '—' }}</span>
                    </div>
                @elseif($tributoHoy->estado === 'exonerado')
                    <div class="summary-row" style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span class="summary-label" style="font-weight:500; color:var(--text2);">Motivo Exoneración</span>
                        <span class="summary-val" style="font-weight:600;">{{ $tributoHoy->observaciones }}</span>
                    </div>
                @else
                    <div class="payment-box" style="margin-top: 18px;">
                        <div class="payment-label">Pagar tributo del día:</div>
                        <form action="{{ route('conductor.tributos.pagar-mp', $tributoHoy) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-mp">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                <span>PAGAR CON YAPE</span>
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <div class="empty-state" style="text-align: center; padding: 30px;">
                    <div style="font-size:40px; margin-bottom:10px; color:var(--text3);"><i class="fa-regular fa-clipboard"></i></div>
                    Sin tributo registrado para hoy.<br>
                    <span style="font-size:12px; color:#999;">Si crees que es un error, contacta con tu empresa.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Historial --}}
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-clipboard-list" style="color:var(--accent); margin-right:5px;"></i> Historial de Pagos</span>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($historial as $tributeInHistory)
                <div class="summary-row" style="padding: 12px 16px; border-bottom: 1px solid #f8f9fa; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size:13px; font-weight:600;">
                            {{ $tributeInHistory->fecha->locale('es')->isoFormat('ddd D MMM') }}
                        </div>
                        <div style="font-size:11.5px; color:#6b7280; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                            <i class="fa-solid fa-car" style="font-size: 10px;"></i> {{ $tributeInHistory->vehiculo?->placa ?? '—' }}
                            @if ($tributeInHistory->metodo_pago)
                                · {{ ucfirst($tributeInHistory->metodo_pago) }}
                            @endif
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; text-align:right;">
                        <div style="margin-right: 4px;">
                            <span style="font-weight:700; display:block; font-size:14px;">S/ {{ number_format($tributeInHistory->monto, 2) }}</span>
                            <div style="margin-top: 2px;">
                                @if($tributeInHistory->estado === 'pagado')
                                    <span class="pill green" style="font-size:9px; padding:2px 6px;"><i class="fa-solid fa-check"></i> Pagado</span>
                                @elseif($tributeInHistory->estado === 'exonerado')
                                    <span class="pill blue" style="font-size:9px; padding:2px 6px;"><i class="fa-solid fa-shield"></i> Exonerado</span>
                                @else
                                    <span class="pill red" style="font-size:9px; padding:2px 6px;"><i class="fa-solid fa-clock"></i> Pendiente</span>
                                @endif
                            </div>
                        </div>
                        @if($tributeInHistory->estado === 'pendiente')
                            <form action="{{ route('conductor.tributos.pagar-mp', $tributeInHistory) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mp btn-mp-sm">
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                    <span>PAGAR CON YAPE</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state" style="text-align: center; padding: 20px; color: var(--text3);">Sin historial</div>
            @endforelse
        </div>
    </div>

@endsection
