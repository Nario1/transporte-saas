@extends('layouts.admin')

@section('back_url', route('reportes.index'))

@php
    $pageTitle = 'Reporte de Sanciones';
    $pageSubtitle = 'Control de infracciones y cumplimiento normativo';
@endphp

@section('content')
<div style="display: grid; gap: 20px;">

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
                <input type="text" name="flota" value="{{ $flota }}" placeholder="Ej: 1" style="font-weight: 800; font-size: 15px;">
            </div>
            <div class="field" style="border-left: 1px solid var(--border); padding-left: 20px;">
                <label>Día Específico:</label>
                <input type="date" value="{{ $desde->toDateString() === $hasta->toDateString() ? $desde->toDateString() : '' }}" onchange="if(this.value){ document.getElementsByName('desde')[0].value=this.value; document.getElementsByName('hasta')[0].value=this.value; this.form.submit(); }">
            </div>
            <div class="flex-h" style="gap: 10px; margin-top: auto;">
                <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">📊 FILTRAR</button>
                @if($flota !== '')
                    <a href="{{ route('reportes.sanciones', ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'flota' => '']) }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none;" title="Ver todas las deudas">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
                <button type="button" onclick="window.print()" class="btn-secondary" style="height: 48px; border-radius: 12px; width: 48px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
            <div style="width: 100%; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 15px;">
                <div style="background: #e0f2fe; color: #0369a1; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 14px;">
                    Total Recaudado (en rango): S/ {{ number_format($porEstado['pagado'], 2) }}
                </div>
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
                        <th>F. Emisión</th>
                        <th>F. Pago</th>
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
                                <div style="font-size: 10px; color: var(--text3); font-family: monospace; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $s->created_at->format('h:i A') }}
                                </div>
                                <div style="font-size: 10px; color: var(--text3); margin-top: 2px;">Reg: {{ explode(' ', $s->registrador?->name ?? 'Sistema')[0] }}</div>
                            </td>
                            <td>
                                @if($s->estado === 'pagado' && $s->cobrado_at)
                                    <div style="font-weight: 700; color: var(--green);">{{ $s->cobrado_at->format('d/m/Y') }}</div>
                                    <div style="font-size: 10px; color: var(--text3);">{{ $s->cobrado_at->format('h:i A') }}</div>
                                @elseif($s->estado === 'exonerado' && $s->exonerado_at)
                                    <div style="font-weight: 700; color: var(--text2);">{{ $s->exonerado_at->format('d/m/Y') }}</div>
                                    <div style="font-size: 10px; color: var(--text3);">Exonerado</div>
                                @else
                                    <span style="color: var(--text3); font-style: italic;">Pendiente</span>
                                @endif
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
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span class="pill {{ $s->estado === 'pagado' ? 'green' : ($s->estado === 'exonerado' ? 'blue' : 'red') }}" style="font-size:9px;">
                                        {{ strtoupper($s->estado) }}
                                    </span>
                                    @if ($s->estado === 'pagado')
                                         @php
                                             $isDigital = in_array(strtolower($s->metodo_pago), ['yape', 'plin', 'mercadopago']);
                                             $pillStyle = $isDigital ? 'background-color: #7c3aed; color: #fff;' : '';
                                         @endphp
                                         <span class="pill blue" style="font-size: 8px; padding: 2px 4px; font-weight: 800; {{ $pillStyle }}">
                                             EFECTIVO
                                         </span>
                                     @endif
                                </div>
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
                {{ $sanciones->links('partials.pagination') }}
            </div>
        @endif
    </div>
</div>
@endsection
