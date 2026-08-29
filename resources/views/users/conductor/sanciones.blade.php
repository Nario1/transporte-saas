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
            </div>            <div class="card-body" style="padding: 12px 14px;">
                @foreach ($pendientes as $sancion)
                    <div class="sancion-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; gap: 10px; align-items: stretch;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="sancion-icon" style="width: 28px; height: 28px; background: #fee2e2; color: #ef4444; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div style="font-weight: 800; font-size: 15px; color: #1e293b;">
                                    {{ $sancion->motivo }}
                                </div>
                            </div>
                            <span class="pill red" style="font-size: 11px; font-weight: 800; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Deuda
                            </span>
                        </div>

                        @if ($sancion->descripcion)
                            <div style="font-size: 12.5px; color: #64748b; font-style: italic; background: #f8fafc; padding: 8px 12px; border-radius: 6px; border-left: 3px solid #cbd5e1;">
                                "{{ $sancion->descripcion }}"
                            </div>
                        @endif

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Fecha</span>
                                <span style="font-weight: 700; color: #334155;">{{ $sancion->fecha->format('d/m/Y') }}</span>
                            </div>
                            @if ($sancion->vehiculo)
                                <div style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
                                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Unidad</span>
                                    <span style="font-weight: 700; color: var(--accent);">#{{ $sancion->vehiculo->numero_flota }} · {{ $sancion->vehiculo->placa }}</span>
                                </div>
                            @endif
                            <div style="display: flex; flex-direction: column; gap: 2px; text-align: right;">
                                <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Monto</span>
                                <span style="font-weight: 900; color: #ef4444; font-size: 15px;">S/ {{ number_format($sancion->monto, 2) }}</span>
                            </div>
                        </div>
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
            <div class="card-body" style="padding: 12px 14px;">
                @foreach ($pagadas as $sancion)
                    <div class="sancion-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 10px; align-items: stretch;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="sancion-icon" style="width: 28px; height: 28px; background: #dcfce7; color: #22c55e; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div style="font-weight: 700; font-size: 14.5px; color: #1e293b;">
                                    {{ $sancion->motivo }}
                                </div>
                            </div>
                            <span class="pill green" style="font-size: 11px; font-weight: 800; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-check"></i> Pagado
                            </span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Fecha</span>
                                <span style="font-weight: 700; color: #334155;">{{ $sancion->fecha->format('d/m/Y') }}</span>
                            </div>
                            @if ($sancion->vehiculo)
                                <div style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
                                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Unidad</span>
                                    <span style="font-weight: 700; color: #334155;">{{ $sancion->vehiculo->placa_form }}</span>
                                </div>
                            @endif
                            @if ($sancion->metodo_pago)
                                <div style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
                                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Método</span>
                                    @php
                                        $isDigital = in_array(strtolower($sancion->metodo_pago), ['yape', 'plin', 'mercadopago']);
                                        $efectivoColor = $isDigital ? '#7c3aed' : 'inherit';
                                        $efectivoWeight = $isDigital ? '700' : 'normal';
                                    @endphp
                                    <span style="color: {{ $efectivoColor }}; font-weight: {{ $efectivoWeight }}; font-size: 12.5px;">EFECTIVO</span>
                                </div>
                            @endif
                            <div style="display: flex; flex-direction: column; gap: 2px; text-align: right;">
                                <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Monto</span>
                                <span style="font-weight: 800; color: #1e293b; font-size: 14.5px;">S/ {{ number_format($sancion->monto, 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
