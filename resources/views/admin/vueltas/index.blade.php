@extends('layouts.admin')

@php
    $pageTitle = 'Control de Vueltas';
    $pageSubtitle = 'Registro y monitoreo de recorridos operativos';
@endphp

@section('content')
    <div class="panel">
        
        {{-- 1. INDICADORES --}}
        <div class="stats-row g-3">
            <div class="stat blue">
                <div class="stat-label">Vueltas Totales</div>
                <div class="stat-val">{{ $resumen['total'] }}</div>
                <div class="stat-sub">
                    @if($fecha)
                        Servicios registrados para el {{ \Carbon\Carbon::parse($fecha)->format('d/m') }}
                    @else
                        Servicios registrados en total
                    @endif
                </div>
                <span class="stat-icon"><i class="fa-solid fa-arrows-rotate"></i></span>
            </div>
            
            <div class="stat green">
                <div class="stat-label">Unidades en Ruta</div>
                <div class="stat-val">{{ $resumen['vehiculos'] }}</div>
                <div class="stat-sub">
                    @if($fecha)
                        Vehículos con actividad hoy
                    @else
                        Vehículos con actividad histórica
                    @endif
                </div>
                <span class="stat-icon"><i class="fa-solid fa-bus"></i></span>
            </div>

            <div class="stat gold">
                <div class="stat-label">Fuerza Laboral</div>
                <div class="stat-val">{{ $resumen['conductores'] }}</div>
                <div class="stat-sub">
                    @if($fecha)
                        Conductores en operación
                    @else
                        Conductores registrados en vueltas
                    @endif
                </div>
                <span class="stat-icon"><i class="fa-solid fa-user-tie"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES Y FILTROS --}}
        <div class="flex-between gap-24">
            <div class="card" style="flex: 1;">
                <form method="GET" action="{{ route('vueltas.index') }}" class="card-body g-filters" style="display: flex; gap: 15px; align-items: flex-end;">
                    <div class="field" style="flex: 1; margin: 0;">
                        <label>Fecha de Operación</label>
                        <input type="date" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()" style="font-weight: 800; font-size: 15px;">
                    </div>
                    <div class="field" style="flex: 1; margin: 0;">
                        <label>N° de Flota (Padrón)</label>
                        <input type="text" name="flota" value="{{ request('flota') }}" placeholder="Ej: 105" style="font-weight: 800; font-size: 15px;">
                    </div>
                    <div class="flex-h" style="gap: 5px;">
                        <button type="submit" class="btn-primary" style="height: 48px; width: 48px; justify-content: center; padding: 0; display: flex; align-items: center;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        @if(request()->filled('flota') || request()->filled('fecha'))
                            <a href="{{ route('vueltas.index') }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none;" title="Limpiar todos los filtros">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                        <a href="{{ route('vueltas.en-vivo') }}" class="btn-primary" style="height: 48px; background: var(--green); white-space: nowrap; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                            <i class="fa-solid fa-satellite-dish"></i> PANEL EN VIVO
                        </a>
                    </div>
                </form>
            </div>
            
            <a href="{{ route('vueltas.create') }}" class="btn-primary" style="padding: 0 32px; height: 80px; border-radius: 20px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-plus-circle"></i> REGISTRAR VUELTA
            </a>
        </div>

        {{-- 3. TABLA DE VUELTAS --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Historial de Despachos del Día</div>
            </div>
            <div class="tbl-wrap">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Unidad / Conductor</th>
                            <th>Ruta Operativa</th>
                            <th>Salida</th>
                            <th>Llegada</th>
                            <th>Duracion</th>
                            <th class="col-status" style="width: 150px;">Estado</th>
                            <th>G Salida</th>
                            <th>G Llegada</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vueltas as $vuelta)
                            <tr>
                                <td>
                                    <span class="text-main" style="font-size: 16px; font-weight: 800; color: #0f172a;">#{{ $vuelta->vehiculo?->numero_flota }}</span>
                                    <span class="text-sub">{{ $vuelta->conductor?->nombre_completo }} • {{ $vuelta->vehiculo?->placa }}</span>
                                </td>
                                <td>
                                    <span class="text-main">{{ $vuelta->ruta?->nombre ?? 'Sin Ruta' }}</span>
                                    <span class="text-sub">{{ $vuelta->ruta?->origen }} » {{ $vuelta->ruta?->destino }}</span>
                                </td>
                                <td>
                                    <div class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">
                                        {{ $vuelta->hora_salida ? \Carbon\Carbon::parse($vuelta->hora_salida)->format('h:i A') : '--:--' }}
                                    </div>
                                    <div class="text-sub" style="font-size:11px; margin-top:2px;">
                                        <i class="fa-solid fa-map-pin" style="color:var(--text3); font-size:9px;"></i> {{ $vuelta->paraderoSalida?->nombre ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">
                                        {{ $vuelta->hora_llegada ? \Carbon\Carbon::parse($vuelta->hora_llegada)->format('h:i A') : '--:--' }}
                                    </div>
                                    <div class="text-sub" style="font-size:11px; margin-top:2px;">
                                        <i class="fa-solid fa-flag" style="color:var(--text3); font-size:9px;"></i> {{ $vuelta->paraderoLlegada?->nombre ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if($vuelta->hora_llegada)
                                        @php
                                            $sec = \Carbon\Carbon::parse($vuelta->hora_salida)->diffInSeconds(\Carbon\Carbon::parse($vuelta->hora_llegada));
                                            $minutosTotal = floor($sec / 60);
                                            $estimado = $vuelta->ruta?->duracion_min ?? 0;
                                            $excede = $estimado > 0 && $minutosTotal > $estimado;

                                            $hh = floor($sec / 3600);
                                            $mm = floor(($sec % 3600) / 60);
                                            $ss = $sec % 60;
                                            $dur = ($hh > 0 ? "{$hh}h " : "0h ") . "{$mm}m {$ss}s";
                                        @endphp
                                        @if($excede)
                                            <span class="pill red" style="font-weight: 800; font-family: monospace; font-size: 14px; padding: 8px 14px;" title="Estimado de Ruta: {{ $estimado }} min">
                                                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 5px;"></i> {{ $dur }} (Excedido)
                                            </span>
                                        @else
                                            <span class="pill gray" style="font-weight: 800; font-family: monospace; font-size: 14px; padding: 8px 14px;">
                                                <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> {{ $dur }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="duracion-vivo pill green" 
                                              data-salida-timestamp="{{ \Carbon\Carbon::parse($vuelta->fecha->format('Y-m-d') . ' ' . $vuelta->hora_salida)->timestamp * 1000 }}"
                                              data-estimado-minutos="{{ $vuelta->ruta?->duracion_min ?? 0 }}"
                                              style="font-family: monospace; font-weight: 800; font-size: 14px; padding: 8px 14px;">
                                            <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> En Ruta...
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $bgColor = 'var(--accent-l)';
                                        $textColor = 'var(--accent)';
                                        $label = 'COMPLETADA';

                                        if ($vuelta->estado === 'activa') {
                                            $bgColor = 'var(--green-l)';
                                            $textColor = 'var(--green)';
                                            $label = 'ACTIVA';
                                        } elseif ($vuelta->estado === 'completada') {
                                            $salidaTipo = $vuelta->paraderoSalida?->tipo;
                                            $llegadaTipo = $vuelta->paraderoLlegada?->tipo;

                                            if ($salidaTipo && $llegadaTipo) {
                                                if ($salidaTipo === 'intermedio' || $llegadaTipo === 'intermedio') {
                                                    $bgColor = 'var(--red-l)';
                                                    $textColor = 'var(--red)';
                                                    $label = 'CORTADA';
                                                } else {
                                                    $bgColor = 'var(--accent-l)';
                                                    $textColor = 'var(--accent)';
                                                    $label = 'COMPLETADA';
                                                }
                                            } else {
                                                $bgColor = 'var(--accent-l)';
                                                $textColor = 'var(--accent)';
                                                $label = 'COMPLETADA';
                                            }
                                        }
                                    @endphp
                                    <span style="font-size: 13.5px; font-weight: 800; padding: 8px 14px; border-radius: 99px; background: {{ $bgColor }}; color: {{ $textColor }}; display: inline-block; text-align: center;">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td>
                                    @if($vuelta->latitud && $vuelta->longitud)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $vuelta->latitud }},{{ $vuelta->longitud }}" target="_blank" class="pill gray" style="text-decoration:none; display:inline-block; font-size:10px; font-weight:800;">
                                            🛫 Salida
                                        </a>
                                    @else
                                        <span style="color:var(--text3); font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($vuelta->estado === 'activa')
                                        @if($vuelta->lat_actual && $vuelta->lng_actual)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $vuelta->lat_actual }},{{ $vuelta->lng_actual }}" target="_blank" class="pill green" style="text-decoration:none; display:inline-block; font-size:10px; font-weight:800;">
                                                📍 En vivo
                                            </a>
                                        @else
                                            <span style="color:var(--green); font-size:11px; font-weight:800;">⏳ En ruta</span>
                                        @endif
                                    @elseif($vuelta->latitud_fin && $vuelta->longitud_fin)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $vuelta->latitud_fin }},{{ $vuelta->longitud_fin }}" target="_blank" class="pill blue" style="text-decoration:none; display:inline-block; font-size:10px; font-weight:800;">
                                            🏁 Llegada
                                        </a>
                                    @else
                                        <span style="color:var(--text3); font-size:12px;">—</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                        @if ($vuelta->estado === 'activa')
                                            <form action="{{ route('vueltas.completar', $vuelta) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="action-icon show-icon" style="background: var(--green-l); color: var(--green); border: none;" title="Terminar">
                                                    <i class="fa-solid fa-check-double"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('vueltas.destroy', $vuelta) }}" method="POST" onsubmit="return confirm('¿Eliminar registro?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-icon-submit" title="Eliminar">
                                                <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 60px; color: var(--text3);">
                                    <i class="fa-solid fa-arrows-rotate" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                    No hay vueltas registradas para esta fecha.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($vueltas->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);">
                    {{ $vueltas->links('partials.pagination') }}
                </div>
            @endif
        </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const clockOffset = {{ now()->timestamp * 1000 }} - Date.now();

            function actualizarDuracionesEnVivo() {
                const ahora = Date.now() + clockOffset;
                document.querySelectorAll('.duracion-vivo').forEach(el => {
                    const salidaMs = parseInt(el.getAttribute('data-salida-timestamp'));
                    let diff = Math.max(0, Math.floor((ahora - salidaMs) / 1000));
                    
                    const hh = Math.floor(diff / 3600);
                    let residuo = diff % 3600;
                    const mm = Math.floor(residuo / 60);
                    const ss = residuo % 60;
                    
                    let durStr = `${hh}h ${mm}m ${ss}s`;

                    const diffMin = Math.floor(diff / 60);
                    const estimado = parseInt(el.getAttribute('data-estimado-minutos')) || 0;
                    const excede = estimado > 0 && diffMin > estimado;

                    if (excede) {
                        el.className = "duracion-vivo pill red";
                        el.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="margin-right: 5px;"></i> ${durStr} (Excedido)`;
                    } else {
                        el.className = "duracion-vivo pill green";
                        el.innerHTML = `<i class="fa-regular fa-clock" style="margin-right: 5px;"></i> ${durStr}`;
                    }
                });
            }

            setInterval(actualizarDuracionesEnVivo, 1000);
            actualizarDuracionesEnVivo();
        });
    </script>
@endpush
