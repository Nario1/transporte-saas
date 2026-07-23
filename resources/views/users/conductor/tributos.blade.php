@extends('layouts.conductor')
@section('title', 'Tributos')

@section('content')

    {{-- Resumen mes --}}
    <div class="stats-row">
        <div class="stat green">
            <div class="stat-icon">✅</div>
            <div class="stat-label">Pagado mes</div>
            <div class="stat-val">S/ {{ number_format($resumenMes['pagado'], 0) }}</div>
        </div>
        <div class="stat {{ $deudaTotal > 0 ? 'red' : 'green' }}">
            <div class="stat-icon">⏳</div>
            <div class="stat-label">Deuda total</div>
            <div class="stat-val">S/ {{ number_format($deudaTotal, 0) }}</div>
            <div class="stat-sub">{{ $diasDeuda }} día(s)</div>
        </div>
    </div>

    {{-- Alerta deuda --}}
    @if ($deudaTotal > 0)
        <div class="alert warning">
            ⚠️ Tienes <strong>{{ $diasDeuda }} día(s)</strong> de deuda por <strong>S/
                {{ number_format($deudaTotal, 2) }}</strong>
        </div>
    @endif

    {{-- Tributo hoy --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">💰 Tributo del Día</span>
            <span style="font-size:12px; color:var(--text3);">{{ now()->locale('es')->isoFormat('D MMM YYYY') }}</span>
        </div>
        <div class="card-body">
            @if ($tributoHoy)
                <div class="summary-row">
                    <span class="summary-label">Padrón / Placa</span>
                    <span class="summary-val" style="font-weight:700; color:#2563eb;">#{{ $tributoHoy->vehiculo?->numero_flota ?? '???' }} — {{ $tributoHoy->vehiculo?->placa ?? '—' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Empresa</span>
                    <span class="summary-val">{{ $tributoHoy->vehiculo?->empresa?->nombre ?? ($tributoHoy->empresa?->nombre ?? '—') }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Monto a Pagar</span>
                    <span class="summary-val" style="font-size:1.1rem; font-weight:800;">S/ {{ number_format($tributoHoy->monto, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Estado</span>
                    @if($tributoHoy->estado === 'pagado')
                        <span class="pill green">Pagado</span>
                    @elseif($tributoHoy->estado === 'exonerado')
                        <span class="pill blue">Exonerado</span>
                    @else
                        <span class="pill red">Pendiente</span>
                    @endif
                </div>
                
                @if ($tributoHoy->estado === 'pagado')
                    <div class="summary-row">
                        <span class="summary-label">Método de Pago</span>
                        <span class="summary-val">{{ ucfirst($tributoHoy->metodo_pago ?? '—') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Fecha y Hora</span>
                        <span class="summary-val">{{ $tributoHoy->cobrado_at?->format('d/m/Y h:i A') ?? '—' }}</span>
                    </div>
                @elseif($tributoHoy->estado === 'exonerado')
                    <div class="summary-row">
                        <span class="summary-label">Motivo Exoneración</span>
                        <span class="summary-val">{{ $tributoHoy->observaciones }}</span>
                    </div>
                @else
                    <div style="margin-top: 18px; border-top: 1px dashed #eee; padding-top: 18px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; color: #666; font-weight: 600;">Pagar tributo del día:</span>
                        <form action="{{ route('conductor.tributos.pagar-mp', $tributoHoy) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-mp" style="background:#009ee3; color:white; border:none; font-size:13px; padding:10px 20px; font-weight:bold; border-radius:12px; display:flex; align-items:center; justify-content:center; gap:10px; box-shadow: 0 4px 12px rgba(0,158,227,0.2);">
                                <img src="https://http2.mlstatic.com/frontend-assets/billing/mpe-billing-v2/mercadopago/logo-mercadopago.svg" alt="Mercado Pago" style="height: 14px; filter: brightness(0) invert(1);">
                                <span>PAGAR TRIBUTO ONLINE</span>
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <div style="font-size:40px; margin-bottom:10px;">📒</div>
                    Sin tributo registrado para hoy.<br>
                    <span style="font-size:12px; color:#999;">Si crees que es un error, contacta con tu empresa.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Historial --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Historial de Pagos</span>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($historial as $tributeInHistory)
                <div class="summary-row" style="padding: 12px 16px; border-bottom: 1px solid #f8f9fa;">
                    <div>
                        <div style="font-size:13px; font-weight:600;">
                            {{ $tributeInHistory->fecha->locale('es')->isoFormat('ddd D MMM') }}
                        </div>
                        <div style="font-size:11.5px; color:#6b7280;">
                            🚗 {{ $tributeInHistory->vehiculo?->placa ?? '—' }}
                            @if ($tributeInHistory->metodo_pago)
                                · {{ ucfirst($tributeInHistory->metodo_pago) }}
                            @endif
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; text-align:right;">
                        <div style="margin-right: 4px;">
                            <span style="font-weight:700; display:block; font-size:14px;">S/ {{ number_format($tributeInHistory->monto, 2) }}</span>
                            @if($tributeInHistory->estado === 'pagado')
                                <span class="pill green" style="font-size:9px; padding:2px 6px;">Pagado</span>
                            @elseif($tributeInHistory->estado === 'exonerado')
                                <span class="pill blue" style="font-size:9px; padding:2px 6px;">Exonerado</span>
                            @else
                                <span class="pill red" style="font-size:9px; padding:2px 6px;">Pendiente</span>
                            @endif
                        </div>
                        @if($tributeInHistory->estado === 'pendiente')
                            <form action="{{ route('conductor.tributos.pagar-mp', $tributeInHistory) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mp" style="padding: 6px 12px; font-size: 11px; height: 32px; min-width: 100px; justify-content: center; gap: 6px;">
                                    <img src="https://http2.mlstatic.com/frontend-assets/billing/mpe-billing-v2/mercadopago/logo-mercadopago.svg" alt="MP" style="height: 10px; filter: brightness(0) invert(1);">
                                    <span>PAGAR</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">Sin historial</div>
            @endforelse
        </div>
    </div>

@endsection
