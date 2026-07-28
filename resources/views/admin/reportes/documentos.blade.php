@extends('layouts.admin')

@section('back_url', route('reportes.index'))

@section('content')
    <div style="display: grid; gap: 20px;">
        {{-- Resumen de Alertas --}}
        <div class="flex-h no-print" style="gap: 20px; margin-bottom: 5px;">
            <div class="card flex-1" style="border-left: 5px solid var(--red); padding: 15px;">
                <div style="font-size: 11px; font-weight: 800; color: var(--text3); text-transform: uppercase;">Críticos (7d)</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--red);">{{ $resumen['criticos'] }}</div>
            </div>
            <div class="card flex-1" style="border-left: 5px solid var(--blue); padding: 15px;">
                <div style="font-size: 11px; font-weight: 800; color: var(--text3); text-transform: uppercase;">Este Mes</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--blue);">{{ $resumen['mes_actual'] }}</div>
            </div>
            <div class="card flex-1" style="border-left: 5px solid #111; padding: 15px;">
                <div style="font-size: 11px; font-weight: 800; color: var(--text3); text-transform: uppercase;">Ya Vencidos</div>
                <div style="font-size: 24px; font-weight: 800; color: #111;">{{ $resumen['vencidos'] }}</div>
            </div>
        </div>

        {{-- Filtros (Solo Flota) --}}
        <div class="card no-print">
            <form action="{{ route('reportes.documentos') }}" method="GET" class="card-body g-filters">
                <div class="field" style="max-width: 300px;">
                    <label>N° Flota:</label>
                    <input type="text" name="flota" value="{{ request()->has('flota') ? request('flota') : '1' }}" placeholder="Ej: 1" style="font-weight: 800; font-size: 15px;">
                </div>
                <div class="flex-h" style="gap: 10px; margin-top: auto;">
                    <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">📊 FILTRAR</button>
                    @if(request('flota') !== '')
                        <a href="{{ route('reportes.documentos', ['flota' => '']) }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none;" title="Ver todas las flotas">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                    <button type="button" onclick="window.print()" class="btn-secondary" style="height: 48px; border-radius: 12px; width: 48px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- TABLA UNIFICADA DE DOCUMENTOS --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    @if($flota) 📑 Documentos y Vencimientos de la Unidad #{{ $flota }}
                    @else 📑 Todos los Documentos y Vencimientos
                    @endif
                </div>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Placa / Conductor</th>
                            <th>Documento</th>
                            <th style="text-align: center;">Fecha Vencimiento</th>
                            <th style="text-align: right;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedAlertas as $a)
                            @php
                                $dias = (int) $hoy->diffInDays($a->fecha, false);
                                $colorClass = $dias < 0 ? 'red' : ($dias <= 15 ? 'orange' : 'gold');
                                $estadoTxt = $dias < 0 ? 'VENCIDO' : ($dias == 0 ? 'VENCE HOY' : "VENCE EN " . abs($dias) . " DÍAS");
                            @endphp
                            <tr style="{{ $dias < 0 ? 'background: #fff5f5;' : '' }}">
                                <td>
                                    <div style="font-weight: 800; color: var(--accent); font-size: 15px;">{{ $a->placa }}</div>
                                    <div style="font-size: 11px; color: var(--text3);">Conductor: {{ $a->conductor }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px;">
                                        @if($a->documento == 'SOAT') <i class="fa-solid fa-shield-heart" style="color:var(--green);"></i> 
                                        @elseif($a->documento == 'Revisión Técnica') <i class="fa-solid fa-wrench" style="color:var(--orange);"></i>
                                        @else <i class="fa-solid fa-id-card" style="color:var(--accent);"></i> @endif
                                        {{ $a->documento }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="font-weight: 700; font-size: 15px;">{{ $a->fecha->format('d/m/Y') }}</div>
                                    <div style="font-size: 10px; color: var(--text3);">{{ $a->fecha->diffForHumans() }}</div>
                                </td>
                                <td style="text-align: right;">
                                    <span class="pill {{ $colorClass }}" style="font-size: 10px; font-weight: 800;">
                                        {{ $estadoTxt }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 60px; color: var(--text3);">
                                    <i class="fa-solid fa-file-circle-check" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                    No se encontraron documentos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($paginatedAlertas->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);" class="no-print">
                    {{ $paginatedAlertas->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
