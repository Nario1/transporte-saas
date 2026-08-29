@extends('layouts.conductor')
@section('title', 'Vueltas')

@section('content')

    {{-- Stats mes --}}
    <div class="stats-row">
        <div class="stat blue">
            <div class="stat-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="stat-label">Vueltas de Flota (Mes)</div>
            <div class="stat-val">{{ $resumenMes['total_vueltas'] }}</div>
        </div>
        <div class="stat green">
            <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            <div class="stat-label">Días trabajados (Flota)</div>
            <div class="stat-val">{{ $resumenMes['dias_trabajados'] }}</div>
        </div>
    </div>

    {{-- Acciones principales --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('conductor.vuelta.iniciar') }}" class="btn btn-primary btn-block" style="font-size: 16px; padding: 14px;">
            Iniciar Nueva Vuelta
        </a>
    </div>

    {{-- Mini gráfico --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Historial de Flota (7 dias)</span>
        </div>
        <div class="card-body">
            @php $maxV = collect($ultimos7)->max('vueltas') ?: 1; @endphp
            <div class="chart-bars">
                @foreach ($ultimos7 as $dia)
                    <div class="cb-wrap">
                        <div class="cb" style="height:60px;">
                            <div class="cb-fill" style="height:{{ ($dia['vueltas'] / $maxV) * 100 }}%;"></div>
                        </div>
                        <div class="cb-label">{{ $dia['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Filtro fecha --}}
    <div class="card">
        <div class="card-body" style="padding:12px 14px;">
            <form method="GET" action="{{ route('conductor.vueltas') }}"
                style="display:flex; gap:10px; align-items:flex-end;">
                <div class="field" style="flex:1; margin:0;">
                    <label>Fecha</label>
                    <input type="date" name="fecha" value="{{ $fecha }}" class="form-control"
                        style="padding:8px 12px;">
                </div>
                <button type="submit" class="btn btn-primary" style="height:40px; padding:0 16px;">Ver</button>
            </form>
        </div>
    </div>

    {{-- Vueltas del día --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Vueltas de la Flota</span>
            <span class="badge">{{ count($vueltas) }}</span>
        </div>
        <div class="card-body" style="padding:12px 14px;">
            @forelse($vueltas as $vuelta)
                <div class="vuelta-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; gap: 12px; align-items: stretch;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="vuelta-num" style="width: 28px; height: 28px; background: var(--accent); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: #fff; flex-shrink: 0;">
                                {{ $vuelta->numero_vuelta }}
                            </div>
                            <div style="font-weight: 700; font-size: 15px; color: #1e293b;">
                                {{ $vuelta->ruta?->nombre ?? 'Sin ruta' }}
                            </div>
                        </div>
                        <div style="font-size: 12px; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 4px 8px; border-radius: 6px;">
                            {{ $vuelta->vehiculo?->placa_form ?? '—' }}
                        </div>
                    </div>
                    
                    @if ($vuelta->ruta)
                        <div style="font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-route" style="color: #94a3b8;"></i>
                            <span>{{ $vuelta->ruta->origen }} → {{ $vuelta->ruta->destino }}</span>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 10px 12px; border-radius: 8px; border: 1px solid #f1f5f9;">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Salida</span>
                            <span style="font-size: 13px; font-weight: 700; color: #334155; font-family: monospace;">
                                {{ $vuelta->hora_salida ? \Carbon\Carbon::parse($vuelta->hora_salida)->format('h:i A') : '--:--' }}
                            </span>
                        </div>
                        <div style="height: 20px; width: 1px; background: #e2e8f0;"></div>
                        <div style="display: flex; flex-direction: column; gap: 2px; text-align: right;">
                            <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Llegada</span>
                            <span style="font-size: 13px; font-weight: 700; color: #334155; font-family: monospace;">
                                {{ $vuelta->hora_llegada ? \Carbon\Carbon::parse($vuelta->hora_llegada)->format('h:i A') : '--:--' }}
                            </span>
                        </div>
                    </div>

                    @if($vuelta->hora_llegada)
                        @php
                            $sec = \Carbon\Carbon::parse($vuelta->hora_salida)->diffInSeconds(\Carbon\Carbon::parse($vuelta->hora_llegada));
                            $hh = floor($sec / 3600);
                            $mm = floor(($sec % 3600) / 60);
                            $ss = $sec % 60;
                            $dur = ($hh > 0 ? "{$hh}h " : "") . "{$mm}m {$ss}s";
                        @endphp
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                            <span style="color: #64748b; font-weight: 500;">Duración de viaje:</span>
                            <span class="pill gray" style="font-family: monospace; font-weight: 800; font-size: 13px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-regular fa-clock" style="color: var(--accent);"></i> {{ $dur }}
                            </span>
                        </div>
                    @else
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                            <span style="color: #64748b; font-weight: 500;">Duración de viaje:</span>
                            <span class="pill green" style="font-family: monospace; font-weight: 800; font-size: 13px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-spinner fa-spin"></i> En curso...
                            </span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">Sin vueltas para esta fecha</div>
            @endforelse
        </div>
    </div>

@endsection
