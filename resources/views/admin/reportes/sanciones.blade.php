@extends('layouts.admin')

@section('back_url', route('reportes.index'))

@php
    $pageTitle = 'Reporte de Sanciones';
    $pageSubtitle = 'Control de infracciones y cumplimiento normativo';
@endphp

@section('content')
<div style="display: grid; gap: 24px;">

    {{-- 1. FILTROS --}}
    <div class="card no-print">
        <form action="{{ route('reportes.sanciones') }}" method="GET" class="card-body g-filters">
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
                <input type="text" name="flota" value="{{ request('flota', '1') }}" placeholder="Ej: 1" style="font-weight: 800; font-size: 15px;">
            </div>
            <div class="field" style="border-left: 1px solid var(--border); padding-left: 20px;">
                <label>Día Específico:</label>
                <input type="date" onchange="if(this.value){ document.getElementsByName('desde')[0].value=this.value; document.getElementsByName('hasta')[0].value=this.value; this.form.submit(); }">
            </div>
            <div class="flex-h" style="gap: 10px; margin-top: auto;">
                <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">📊 FILTRAR</button>
                @if(request()->has('flota'))
                    <a href="{{ route('reportes.sanciones', ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'flota' => '']) }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none;" title="Ver todas las deudas">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
                <button type="button" onclick="window.print()" class="btn-secondary" style="height: 48px; border-radius: 12px; width: 48px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- 2. TABLA DE DETALLE --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                Historial Detallado de Infracciones
                <small style="color: var(--text3); font-weight: 400; font-size: 13px;">({{ $desde->format('d/m/Y') }} - {{ $hasta->format('d/m/Y') }})</small>
            </div>
        </div>
        <div class="tbl-wrap">
            <table class="tbl tbl-modern">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Unidad</th>
                        <th>Conductor</th>
                        <th>Motivo / Infracción</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanciones as $s)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">{{ $s->fecha->format('d/m/Y') }}</div>
                                <div style="font-size: 10px; color: var(--text3);">Reg: {{ $s->registrador?->name ?? 'Sistema' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--accent);">#{{ $s->vehiculo?->numero_flota }}</div>
                                <div class="mono" style="font-size: 11px;">{{ $s->vehiculo?->placa }}</div>
                            </td>
                            <td><div style="font-weight: 600; font-size: 13px;">{{ $s->conductor?->nombre_completo ?? '---' }}</div></td>
                            <td style="max-width: 250px;">
                                <div style="font-size: 13px; font-weight: 500;">{{ $s->motivo }}</div>
                                @if($s->observaciones)
                                    <div style="font-size: 10px; color: var(--text3);">Obs: {{ \Str::limit($s->observaciones, 40) }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="pill {{ $s->estado === 'pagado' ? 'green' : ($s->estado === 'exonerado' ? 'blue' : 'red') }}">
                                    {{ strtoupper($s->estado) }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 900; color: {{ $s->estado === 'pagado' ? 'var(--green)' : 'var(--red)' }};">
                                S/ {{ number_format($s->monto, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding: 40px; color:var(--text3);">No hay sanciones en este rango.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sanciones->hasPages())
            <div style="padding:20px; border-top:1px solid var(--border);" class="no-print">
                {{ $sanciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
