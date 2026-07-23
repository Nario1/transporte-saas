@extends('layouts.conductor')
@section('title', 'Vueltas')

@section('content')

    {{-- Stats mes --}}
    <div class="stats-row">
        <div class="stat blue">
            <div class="stat-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div class="stat-label">Vueltas de Unidad (Mes)</div>
            <div class="stat-val">{{ $resumenMes['total_vueltas'] }}</div>
        </div>
        <div class="stat green">
            <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
            <div class="stat-label">Días trabajados (Unidad)</div>
            <div class="stat-val">{{ $resumenMes['dias_trabajados'] }}</div>
        </div>
    </div>

    {{-- Acciones principales --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('conductor.vuelta.iniciar') }}" class="btn btn-primary btn-block" style="font-size: 16px; padding: 14px;">
            Iniciar Nueva Vuelta (Autenticacion Facial)
        </a>
    </div>

    {{-- Mini gráfico --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Historial de Unidad (7 dias)</span>
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
            <span class="card-title">Vueltas de la Unidad</span>
            <span class="badge">{{ count($vueltas) }}</span>
        </div>
        <div class="card-body" style="padding:12px 14px;">
            @forelse($vueltas as $vuelta)
                <div class="vuelta-card">
                    <div class="vuelta-num">{{ $vuelta->numero_vuelta }}</div>
                    <div class="vuelta-info">
                        <div class="vuelta-name">{{ $vuelta->ruta?->nombre ?? 'Sin ruta' }}</div>
                        <div class="vuelta-sub">
                            {{ $vuelta->vehiculo?->placa_form ?? '—' }}
                            @if ($vuelta->ruta)
                                · {{ $vuelta->ruta->origen }} → {{ $vuelta->ruta->destino }}
                            @endif
                        </div>
                        @if($vuelta->hora_llegada)
                            @php
                                $sec = \Carbon\Carbon::parse($vuelta->hora_salida)->diffInSeconds(\Carbon\Carbon::parse($vuelta->hora_llegada));
                                if ($sec < 60) $dur = "$sec segundos";
                                elseif ($sec < 3600) $dur = floor($sec/60) . " minutos";
                                else $dur = floor($sec/3600) . "h " . (floor($sec/60)%60) . "min";
                            @endphp
                            <div style="font-size: 11.5px; color: var(--accent); font-weight: 700; margin-top: 4px;">
                                <i class="fa-solid fa-clock"></i> Duración: {{ $dur }}
                            </div>
                        @else
                            <div style="font-size: 11.5px; color: var(--green); font-weight: 700; margin-top: 4px;">
                                <i class="fa-solid fa-circle-play"></i> En curso...
                            </div>
                        @endif
                    </div>
                    <div class="vuelta-time">
                        <div style="font-weight: 700;">
                            {{ $vuelta->hora_salida ? \Carbon\Carbon::parse($vuelta->hora_salida)->format('h:i A') : '--:--' }}
                            @if($vuelta->hora_llegada)
                                - {{ \Carbon\Carbon::parse($vuelta->hora_llegada)->format('h:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">Sin vueltas para esta fecha</div>
            @endforelse
        </div>
    </div>

@endsection
