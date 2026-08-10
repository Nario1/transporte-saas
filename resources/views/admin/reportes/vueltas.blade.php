@extends('layouts.admin')

@section('back_url', route('reportes.index'))

@php
    $pageTitle = 'Productividad de Vueltas';
    $pageSubtitle = 'Análisis de frecuencias y recorridos operativos';
@endphp

@section('content')
<div style="display: grid; gap: 24px;">

    {{-- 1. FILTROS --}}
    <div class="card no-print">
        <form action="{{ route('reportes.vueltas') }}" method="GET" class="card-body g-filters">
            <div class="field">
                <label>Desde:</label>
                <input type="date" name="desde" value="{{ $desde->toDateString() }}">
            </div>
            <div class="field">
                <label>Hasta:</label>
                <input type="date" name="hasta" value="{{ $hasta->toDateString() }}">
            </div>
            <div class="field">
                <label>N° Flota:</label>
                <input type="text" name="flota" value="{{ $flota }}" placeholder="Ej: 1" style="font-weight: 800; font-size: 15px;">
            </div>
            <div class="field" style="border-left: 1px solid var(--border); padding-left: 20px;">
                <label>Día Específico:</label>
                <input type="date" value="{{ $desde->toDateString() === $hasta->toDateString() ? $desde->toDateString() : '' }}" onchange="if(this.value){ document.getElementsByName('desde')[0].value=this.value; document.getElementsByName('hasta')[0].value=this.value; this.form.submit(); }">
            </div>
            <div class="flex-h" style="gap: 10px; margin-top: auto;">
                <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">📊 ANALIZAR</button>
                @if($flota !== '')
                    <a href="{{ route('reportes.vueltas', ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'flota' => '']) }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none;" title="Ver todas las flotas">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
                <button type="button" onclick="window.print()" class="btn-secondary" style="height: 48px; border-radius: 12px; width: 48px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- 2. HISTORIAL DETALLADO --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                Vueltas Realizadas por Vehículo
                <small style="color: var(--text3); font-weight: 400; font-size: 13px;">({{ $desde->format('d/m/Y') }} - {{ $hasta->format('d/m/Y') }})</small>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="tbl tbl-modern">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Vehículo</th>
                        <th>Flota</th>
                        <th style="text-align: center;">N° Vuelta</th>
                        <th>Hora Inicio</th>
                        <th>Hora Llegada</th>
                        <th>Ruta</th>
                        <th>Duración</th>
                        <th style="text-align: center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detalle as $reg)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">{{ $reg->fecha->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--accent);">{{ $reg->vehiculo?->placa ?? '---' }}</div>
                                <div style="font-size: 10px; color: var(--text3);">{{ $reg->conductor?->nombre_completo ?? '---' }}</div>
                            </td>
                            <td>
                                <span class="pill blue" style="font-weight: 800; font-size: 11px;">
                                    #{{ $reg->vehiculo?->numero_flota ?? '---' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="pill blue" style="font-weight: 800; font-size: 11px; background: var(--bg-hover); color: var(--text);">
                                    V{{ $reg->numero_vuelta }}
                                </span>
                            </td>
                            <td class="mono">
                                {{ $reg->hora_salida ? \Carbon\Carbon::parse($reg->hora_salida)->format('h:i A') : '--:--' }}
                            </td>
                            <td class="mono">
                                {{ $reg->hora_llegada ? \Carbon\Carbon::parse($reg->hora_llegada)->format('h:i A') : '--:--' }}
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 600;">{{ $reg->ruta?->nombre ?? 'Sin Ruta' }}</div>
                                <div style="font-size: 10px; color: var(--text3);">{{ $reg->ruta?->origen }} - {{ $reg->ruta?->destino }}</div>
                            </td>
                            <td>
                                @if($reg->hora_llegada)
                                    @php
                                        $sec = \Carbon\Carbon::parse($reg->hora_salida)->diffInSeconds(\Carbon\Carbon::parse($reg->hora_llegada));
                                        if ($sec < 60) $dur = "$sec segundos";
                                        elseif ($sec < 3600) $dur = floor($sec/60) . " minutos";
                                        else $dur = floor($sec/3600) . "h " . (floor($sec/60)%60) . "min";
                                    @endphp
                                    <span class="mono" style="font-weight: 700;">{{ $dur }}</span>
                                @else
                                    <span class="pill green" style="font-size: 10px;">EN RUTA</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($reg->estado === 'completada')
                                    <span class="pill blue" style="font-size: 10px; font-weight: 800;">COMPLETADA</span>
                                @elseif($reg->estado === 'activa')
                                    <span class="pill green" style="font-size: 10px; font-weight: 800;">EN RUTA</span>
                                @else
                                    <span class="pill red" style="font-size: 10px; font-weight: 800;">{{ strtoupper($reg->estado) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center; padding: 40px; color:var(--text3);">No hay vueltas registradas para esta unidad.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($detalle->hasPages())
            <div style="padding:20px; border-top:1px solid var(--border);" class="no-print">
                {{ $detalle->links('partials.pagination') }}
            </div>
        @endif
    </div>
</div>
@endsection
