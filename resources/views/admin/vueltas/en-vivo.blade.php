@extends('layouts.admin')

@section('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #mapa-live {
        height: 400px;
        width: 100%;
        border-radius: 18px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-m);
        z-index: 1;
    }
    .vuelta-row { transition: all 0.3s ease; cursor: pointer; }
    .vuelta-row:hover { background: #f1f5f9 !important; }
    .vuelta-row.completada { background: #f8fafc; color: #94a3b8; }
    .marker-active { filter: hue-rotate(90deg); } /* Green-ish */
    .marker-finished { filter: grayscale(1); opacity: 0.7; }

    .live-stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-mini-card {
        background: var(--card);
        padding: 15px;
        border-radius: 14px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .stat-mini-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .route-toggle-card {
        border: 2px solid transparent;
        opacity: 0.55;
    }
    .route-toggle-card.selected {
        background: var(--card);
        opacity: 1;
        box-shadow: var(--shadow-s);
        border-color: var(--accent);
    }
    .vertex-tooltip {
        background: var(--card) !important;
        border: 1px solid var(--border) !important;
        color: var(--text) !important;
        font-weight: 800 !important;
        font-size: 9px !important;
        padding: 1px 4px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
    }
    #mapa-live-container.fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
        background: var(--bg);
    }
    #mapa-live-container.fullscreen #mapa-live {
        height: 100% !important;
        width: 100% !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
</style>
@endsection

@section('content')

<div class="panel">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
        <div>
            <h2 style="font-size:20px;font-weight:800;">Panel de Control en Tiempo Real</h2>
            <div style="font-size:12px;color:var(--text3);">
                <i class="fa-solid fa-bolt" style="color:var(--accent);"></i> Modo Monitorización Activo
            </div>
        </div>
        
        <form method="GET" action="{{ route('vueltas.en-vivo') }}" style="display: flex; gap: 8px; align-items: center;" class="no-print">
            <div class="field" style="margin: 0; width: 200px;">
                <input type="text" id="filtro-flota" name="flota" value="{{ request('flota') }}" placeholder="🔍 Filtrar por N° Flota..." style="font-weight: 800; font-size: 14px; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>
            <button type="submit" class="btn-primary" style="height: 40px; padding: 0 16px; font-size: 12px; font-weight: 700; border-radius: 10px;">Filtrar</button>
            @if(request()->filled('flota'))
                <a href="{{ route('vueltas.en-vivo') }}" class="btn-secondary" style="height: 40px; display: flex; align-items: center; justify-content: center; padding: 0 14px; text-decoration: none; border-radius: 10px;" title="Limpiar filtro">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>

        <div style="text-align: right;">
            <span id="ultima-actualizacion" style="font-size:12px;color:var(--text3); display: block;">
                Actualizado: Ahora
            </span>
            <div class="flex-h" style="gap:5px; margin-top:4px; justify-content: flex-end;">
                <div class="pulse-dot"></div>
                <span style="font-size: 10px; font-weight: 800; color: var(--green);">LIVE</span>
            </div>
        </div>
    </div>

    <div class="live-stats-bar no-print" id="stats-por-ruta">
        @forelse($rutasTrazados as $idx => $ruta)
            @php
                $cantActivas = $vueltasActivas->where('ruta_id', $ruta['id'])->count();
                $coloresPaleta = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'];
                $color = $ruta['color'] ?? $coloresPaleta[$idx % count($coloresPaleta)];
            @endphp
            <div class="stat-mini-card route-toggle-card selected" 
                 style="cursor: pointer; border-left: 5px solid {{ $color }}; transition: all 0.2s; position: relative; padding-right: 45px; display: flex; align-items: center; justify-content: space-between; min-width: 170px;">
                <div onclick="toggleRutaPath({{ $ruta['id'] }})" style="flex: 1; display: flex; align-items: center; gap: 10px;">
                    <div class="stat-mini-icon" style="background: {{ $color }}20; color: {{ $color }}; width: 32px; height: 32px; font-size: 12px; min-width: 32px; border-radius: 8px;">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div>
                        <div style="font-size: 15px; font-weight: 800; line-height: 1.2;">{{ $cantActivas }} <span style="font-size: 9px; color: var(--text3); font-weight: 500;">activas</span></div>
                        <div style="font-size: 10px; color: var(--text2); font-weight: 700; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px;" title="{{ $ruta['nombre'] }}">{{ $ruta['nombre'] }}</div>
                    </div>
                </div>
                <button type="button" onclick="event.stopPropagation(); activarEditorTrazado({{ $ruta['id'] }});" title="Editar trazado de la ruta" 
                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--bg2); color: var(--text3); border: 1px solid var(--border); cursor: pointer;">
                    <i class="fa-solid fa-pencil" style="font-size: 10px;"></i>
                </button>
            </div>
        @empty
            <div class="stat-mini-card">
                <div class="stat-mini-icon" style="background: var(--gray-l); color: var(--text3);">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div>
                    <div style="font-size: 18px; font-weight: 800;">0</div>
                    <div style="font-size: 11px; color: var(--text3); font-weight: 600;">SIN RUTAS CONFIGURADAS</div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- MAPA INTERACTIVO --}}
    <div style="position: relative;" class="no-print" id="mapa-live-container">
        <div id="mapa-live" style="margin-bottom: 20px;"></div>
        
        {{-- BOTON FULLSCREEN --}}
        <button type="button" onclick="toggleFullscreenMap()" id="btn-fullscreen-map" title="Pantalla Completa" 
                style="position: absolute; top: 10px; left: 50px; z-index: 1000; width: 34px; height: 34px; border-radius: 4px; background: white; border: 2px solid rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; color: #333; box-shadow: none;">
            <i class="fa-solid fa-expand" style="font-size: 14px;"></i>
        </button>
        
        {{-- PANEL DE CONTROL DE EDITOR --}}
        <div id="editor-control-panel" style="display: none; position: absolute; top: 20px; right: 20px; z-index: 1000; background: var(--card); padding: 18px; border-radius: 16px; box-shadow: var(--shadow-l); width: 290px; border: 1px solid var(--border);">
            <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; color: var(--text);">
                <i class="fa-solid fa-pencil" style="color: var(--accent); margin-right: 5px;"></i> Editando Ruta:<br>
                <span id="editor-ruta-nombre" style="color: var(--accent); font-size: 15px; font-weight: 900; display: block; margin-top: 2px;">—</span>
            </h4>
            
            <div style="margin-bottom: 14px;">
                <label style="font-size: 11px; font-weight: 700; color: var(--text3); display: block; margin-bottom: 5px; text-transform: uppercase;">Color de la Ruta:</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" id="editor-color-picker" style="border: 0; width: 45px; height: 32px; padding: 0; cursor: pointer; border-radius: 6px; background: none;">
                    <span style="font-size: 12px; font-weight: 700; font-family: monospace; color: var(--text2);" id="editor-color-hex">#3b82f6</span>
                </div>
            </div>

            <div style="font-size: 11px; color: var(--text3); margin-bottom: 15px; padding: 10px; background: var(--bg); border-radius: 8px; line-height: 1.5; border: 1px dashed var(--border);">
                <i class="fa-solid fa-info-circle" style="color: var(--accent); margin-right: 3px;"></i> <b>Instrucciones:</b><br>
                • Haz <b>clic</b> en el mapa para agregar puntos.<br>
                • <b>Arrastra</b> los puntos para reacomodarlos.<br>
                • Haz <b>doble clic</b> en un punto para eliminarlo.
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="btn-secondary" onclick="limpiarTrazadoEditor()" style="flex: 1; padding: 10px; font-size: 11px; font-weight: 700; border-radius: 8px; cursor: pointer;">
                        <i class="fa-solid fa-trash-can"></i> Limpiar
                    </button>
                    <button type="button" class="btn-secondary" onclick="autocompletarTrazadoEditor()" style="flex: 1; padding: 10px; font-size: 11px; font-weight: 700; border-radius: 8px; cursor: pointer;" title="Unir paraderos con líneas rectas">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-unir
                    </button>
                </div>
                <button type="button" class="btn-primary" onclick="guardarTrazadoEditor()" style="width: 100%; padding: 12px; font-size: 12px; font-weight: 800; border-radius: 8px; cursor: pointer;">
                    <i class="fa-solid fa-save"></i> Guardar Trazado
                </button>
                <button type="button" class="btn-secondary" onclick="salirEditor()" style="width: 100%; padding: 12px; font-size: 12px; font-weight: 700; border-radius: 8px; background: var(--red-l); color: var(--red); border-color: transparent; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <div class="card" style="border-radius: 18px; overflow: hidden; border: none; box-shadow: var(--shadow-l);">
        <div class="card-header" style="background: var(--bg2); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title"><i class="fa-solid fa-list-ul"></i> Detalle de Actividad</div>
            <div style="font-size: 11px; font-weight: 700; color: var(--text3);">AUTOREFRESH CADA 15S / REVERB PUSH</div>
        </div>

        <div class="card-body" style="padding:0;">

            <table class="tbl">

                <thead>
                    <tr>
                        <th style="padding-left: 24px;">Conductor</th>
                        <th>Flota</th>
                        <th>Ruta</th>
                        <th>Salida</th>
                        <th>Llegada</th>
                        <th>Tiempo en Ruta</th>
                        <th>Estado</th>
                        <th>Vuelta</th>
                        <th>G Salida</th>
                        <th style="text-align: right; padding-right: 24px;">G Llegada</th>
                    </tr>
                </thead>

                <tbody id="tbody-vueltas">

                    @forelse($vueltasActivas as $v)

                        @php
                        $segundos = \Carbon\Carbon::parse($v->fecha->format('Y-m-d').' '.$v->hora_salida)->diffInSeconds(now());
                        @endphp

                        <tr id="vuelta-{{ $v->id }}" data-segundos="{{ $segundos }}" class="vuelta-row">

                            <td style="padding-left: 24px;">
                                <div style="font-weight: 700;">{{ $v->conductor?->nombre_completo ?? '—' }}</div>
                                <div style="font-size:12px;color:var(--text3); font-family: monospace;">
                                    {{ $v->conductor?->dni }}
                                </div>
                            </td>

                            <td>
                                <div style="font-weight: 800; font-size: 16px; color: #0f172a;">#{{ $v->vehiculo?->numero_flota ?? '?' }}</div>
                                <div style="font-size: 12px; color: var(--text3); font-family: monospace;">{{ $v->vehiculo?->placa ?? '—' }}</div>
                            </td>

                            <td>
                                <div style="font-weight: 600; font-size: 14px;">{{ $v->ruta?->nombre ?? 'Sin ruta' }}</div>
                            </td>

                            <td>
                                <div class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">
                                    {{ $v->hora_salida }}
                                </div>
                                <div class="text-sub" style="font-size:11px; margin-top:2px;">
                                    <i class="fa-solid fa-map-pin" style="color:var(--text3); font-size:9px;"></i> {{ $v->paraderoSalida?->nombre ?? '—' }}
                                </div>
                            </td>

                            <td>
                                <div class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">
                                    {{ $v->hora_llegada ?? '—' }}
                                </div>
                                <div class="text-sub" style="font-size:11px; margin-top:2px;">
                                    <i class="fa-solid fa-flag" style="color:var(--text3); font-size:9px;"></i> {{ $v->paraderoLlegada?->nombre ?? '—' }}
                                </div>
                            </td>

                            <td>
                                @php
                                    $secArr = \Carbon\Carbon::parse($v->fecha->format('Y-m-d').' '.$v->hora_salida)->diffInSeconds(now());
                                    $minutosTrans = floor($secArr / 60);
                                    $estimado = $v->ruta?->duracion_min ?? 0;
                                    $excede = $estimado > 0 && $minutosTrans > $estimado;

                                    $hh = floor($secArr / 3600);
                                    $mm = floor(($secArr % 3600) / 60);
                                    $ss = $secArr % 60;
                                    $durArr = ($hh > 0 ? "{$hh}h " : "0h ") . "{$mm}m {$ss}s";
                                @endphp
                                <span class="pill {{ $excede ? 'red' : 'green' }} tiempo-cronometro" 
                                      data-inicio="{{ $v->fecha->format('Y-m-d').' '.$v->hora_salida }}" 
                                      data-estimado-minutos="{{ $estimado }}"
                                      style="font-weight: 800; font-family: monospace; font-size: 14px; padding: 8px 14px;">
                                    @if ($excede)
                                        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 5px;"></i> {{ $durArr }} (Excedido)
                                    @else
                                        <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> {{ $durArr }}
                                    @endif
                                </span>
                            </td>

                            <td>
                                <span style="font-size: 13.5px; font-weight: 800; padding: 8px 14px; border-radius: 99px; background: var(--green-l); color: var(--green); display: inline-block; text-align: center;">
                                    ACTIVA
                                </span>
                            </td>

                            <td>
                                <span class="pill blue" style="font-weight: 800; padding: 6px 12px; font-size: 12px;">
                                    V{{ $v->numero_vuelta }}
                                </span>
                            </td>

                            <td>
                                @if($v->latitud && $v->longitud)
                                    <a href="https://maps.google.com/?q={{ $v->latitud }},{{ $v->longitud }}"
                                       target="_blank"
                                       class="btn-secondary"
                                       style="font-size:11px; padding: 6px 12px; border-radius: 8px; text-decoration: none;">
                                        🛫 Salida
                                    </a>
                                @else
                                    <span style="font-size:14px;color:var(--text3);">—</span>
                                @endif
                            </td>

                            <td style="text-align: right; padding-right: 24px;">
                                @if($v->estado === 'activa')
                                    @if($v->lat_actual && $v->lng_actual)
                                        <a href="https://maps.google.com/?q={{ $v->lat_actual }},{{ $v->lng_actual }}"
                                           target="_blank"
                                           class="btn-secondary"
                                           style="font-size:11px; padding: 6px 12px; border-radius: 8px; text-decoration: none; background: var(--green); color: white;">
                                            📍 En vivo
                                        </a>
                                    @else
                                        <span style="color:var(--green); font-size:11px; font-weight:800;"><i class="fa-solid fa-spinner fa-spin" style="margin-right: 5px;"></i> En ruta</span>
                                    @endif
                                @elseif($v->latitud_fin && $v->longitud_fin)
                                    <a href="https://maps.google.com/?q={{ $v->latitud_fin }},{{ $v->longitud_fin }}"
                                       target="_blank"
                                       class="btn-secondary"
                                       style="font-size:11px; padding: 6px 12px; border-radius: 8px; text-decoration: none; background: var(--accent); color: white;">
                                        🏁 Llegada
                                    </a>
                                @else
                                    <span style="font-size:14px;color:var(--text3);">—</span>
                                @endif
                            </td>

                        </tr>

                    @empty

                    <tr id="empty-row">
                        <td colspan="10" style="text-align:center;padding:80px;">
                            <div style="font-weight:800; color:var(--text); font-size:18px;">
                                No hay conductores en ruta ahora
                            </div>
                            <div style="font-size:14px;color:var(--text3); margin-top: 5px;">
                                Las nuevas vueltas aparecerán aquí automáticamente.
                            </div>
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
        @if($vueltasActivas->hasPages())
            <div style="padding:20px; border-top:1px solid var(--border);">
                {{ $vueltasActivas->links('partials.pagination') }}
            </div>
        @endif
    </div>

</div>

<style>
    .vuelta-row {
        transition: background 0.5s ease, opacity 0.5s ease, transform 0.5s ease;
    }
    .vuelta-row.new-row {
        background: #f0fdf4;
        animation: highlightRow 2s forwards;
    }
    .vuelta-row.fade-out {
        opacity: 0;
        transform: translateX(20px);
    }
    @keyframes highlightRow {
        0% { background: #f0fdf4; }
        100% { background: transparent; }
    }
</style>

@vite(['resources/js/app.js'])

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- CONFIGURACIÓN ---
    const empresaId = {{ auth()->user()->empresa_id }};
    const flotaParam = '{{ request("flota") }}';
    const API_URL   = '{{ route("vueltas.api.activas") }}' + (flotaParam ? '?flota=' + encodeURIComponent(flotaParam) : '');
    const CSRF      = '{{ csrf_token() }}';
    
    // --- ELEMENTOS UI ---
    const tbody = document.getElementById('tbody-vueltas');
    const ultimaActEl = document.getElementById('ultima-actualizacion');

    // --- MAPA ---
    const map = L.map('mapa-live').setView([-12.067, -75.21], 14); // Huancayo
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const rutasTrazados = @json($rutasTrazados);
    const visibleRoutes = {};
    const rutaPolylines = {};
    const rutaStops = [];

    function renderRutasTrazados() {
        Object.keys(rutaPolylines).forEach(rId => {
            if (map.hasLayer(rutaPolylines[rId])) map.removeLayer(rutaPolylines[rId]);
        });
        rutaStops.forEach(m => {
            if (map.hasLayer(m)) map.removeLayer(m);
        });
        rutaStops.length = 0;

        rutasTrazados.forEach((ruta, idx) => {
            if (visibleRoutes[ruta.id] === false) {
                return;
            }

            let latlngs = [];
            if (ruta.trazado && ruta.trazado.length > 0) {
                latlngs = ruta.trazado.map(coord => [parseFloat(coord[0]), parseFloat(coord[1])]);
            } else {
                ruta.paraderos.forEach(paradero => {
                    if (paradero.latitud_a && paradero.longitud_a) {
                        latlngs.push([parseFloat(paradero.latitud_a), parseFloat(paradero.longitud_a)]);
                    }
                });
            }

            if (latlngs.length >= 2) {
                const coloresPaleta = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'];
                const color = ruta.color || coloresPaleta[idx % coloresPaleta.length];

                const polyline = L.polyline(latlngs, {
                    color: color,
                    weight: 4,
                    opacity: 0.75,
                    dashArray: '4, 8'
                }).addTo(map);

                polyline.bindTooltip(`Ruta: <b>${ruta.nombre}</b> (${ruta.origen} - ${ruta.destino})`, { sticky: true });
                rutaPolylines[ruta.id] = polyline;

                ruta.paraderos.forEach(paradero => {
                    if (paradero.latitud_a && paradero.longitud_a) {
                        const marker = L.circleMarker([paradero.latitud_a, paradero.longitud_a], {
                            radius: 5,
                            fillColor: color,
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        })
                        .addTo(map)
                        .bindPopup(`<b>Paradero:</b> ${paradero.nombre}<br><b>Orden:</b> ${paradero.orden} (${paradero.tipo})`);
                        
                        rutaStops.push(marker);
                    }
                });
            }
        });
    }

    // Dibujar inicialmente
    renderRutasTrazados();

    window.toggleRutaPath = function(rutaId) {
        if (editorMode) return;
        const visibleIds = Object.keys(visibleRoutes).filter(id => visibleRoutes[id] !== false);

        if (visibleIds.length === 1 && visibleIds[0] == rutaId) {
            rutasTrazados.forEach(r => visibleRoutes[r.id] = true);
        } else {
            rutasTrazados.forEach(r => {
                visibleRoutes[r.id] = (r.id === rutaId);
            });
        }

        renderRutasTrazados();
        recalcularYRenderizarStatsPorRuta(todasLasVueltas);
    }

    // --- VARIABLES DEL EDITOR ---
    let editorMode = false;
    let editorRutaId = null;
    let editorCoordinates = [];
    let editorMarkers = [];
    let editorPolyline = null;
    let editorColor = '#3b82f6';

    window.activarEditorTrazado = function(rutaId) {
        const ruta = rutasTrazados.find(r => r.id === rutaId);
        if (!ruta) return;

        editorMode = true;
        editorRutaId = rutaId;
        editorColor = ruta.color || '#3b82f6';
        
        if (ruta.trazado && ruta.trazado.length > 0) {
            editorCoordinates = ruta.trazado.map(coord => [parseFloat(coord[0]), parseFloat(coord[1])]);
        } else {
            editorCoordinates = [];
            ruta.paraderos.forEach(p => {
                if (p.latitud_a && p.longitud_a) {
                    editorCoordinates.push([parseFloat(p.latitud_a), parseFloat(p.longitud_a)]);
                }
            });
        }

        // Limpiar capas del mapa temporales
        Object.keys(rutaPolylines).forEach(rId => {
            if (map.hasLayer(rutaPolylines[rId])) map.removeLayer(rutaPolylines[rId]);
        });
        rutaStops.forEach(m => {
            if (map.hasLayer(m)) map.removeLayer(m);
        });

        if (editorPolyline) map.removeLayer(editorPolyline);
        editorPolyline = L.polyline(editorCoordinates, {
            color: editorColor,
            weight: 8, // Grosor mayor en modo edición para facilitar clics
            opacity: 0.9,
            interactive: true
        }).addTo(map);

        // Click sobre la línea para insertar un vértice
        editorPolyline.on('click', function(e) {
            if (!editorMode) return;
            L.DomEvent.stopPropagation(e);

            const clickLatLng = e.latlng;
            const segmentIdx = getClosestSegmentIndex(clickLatLng, editorCoordinates);
            
            if (segmentIdx !== -1) {
                const newPoint = [clickLatLng.lat, clickLatLng.lng];
                editorCoordinates.splice(segmentIdx + 1, 0, newPoint);
                
                editorPolyline.setLatLngs(editorCoordinates);
                actualizarVerticeMarkers();
            }
        });

        actualizarVerticeMarkers();

        document.getElementById('editor-ruta-nombre').textContent = ruta.nombre;
        document.getElementById('editor-color-picker').value = editorColor;
        document.getElementById('editor-color-hex').textContent = editorColor;
        document.getElementById('editor-control-panel').style.display = 'block';

        const colorPicker = document.getElementById('editor-color-picker');
        colorPicker.oninput = function(e) {
            editorColor = e.target.value;
            document.getElementById('editor-color-hex').textContent = editorColor;
            editorPolyline.setStyle({ color: editorColor });
            editorMarkers.forEach(m => {
                const el = m.getElement();
                if (el) {
                    const circle = el.querySelector('div');
                    if (circle) circle.style.background = editorColor;
                }
            });
        };

        if (editorCoordinates.length >= 2) {
            map.fitBounds(editorPolyline.getBounds(), { padding: [40, 40] });
        }

        map.on('click', onMapClickForEditor);
    }

    function onMapClickForEditor(e) {
        if (!editorMode) return;
        const coords = [e.latlng.lat, e.latlng.lng];
        editorCoordinates.push(coords);
        
        editorPolyline.setLatLngs(editorCoordinates);
        actualizarVerticeMarkers();
    }

    // --- AYUDANTES GEOMÉTRICOS PARA SEGMENTOS (ESTILO VECTORIAL) ---
    function getClosestSegmentIndex(clickLatLng, points) {
        const lat = clickLatLng.lat;
        const lng = clickLatLng.lng;
        let minDistance = Infinity;
        let closestIndex = -1;

        for (let i = 0; i < points.length - 1; i++) {
            const p1 = points[i];
            const p2 = points[i + 1];

            const dist = distToSegment([lat, lng], p1, p2);
            if (dist < minDistance) {
                minDistance = dist;
                closestIndex = i;
            }
        }

        return closestIndex;
    }

    function distToSegment(p, v, w) {
        const l2 = dist2(v, w);
        if (l2 === 0) return dist2(p, v);
        let t = ((p[0] - v[0]) * (w[0] - v[0]) + (p[1] - v[1]) * (w[1] - v[1])) / l2;
        t = Math.max(0, Math.min(1, t));
        return Math.sqrt(dist2(p, [v[0] + t * (w[0] - v[0]), v[1] + t * (w[1] - v[1])]));
    }

    function dist2(v, w) {
        return Math.pow(v[0] - w[0], 2) + Math.pow(v[1] - w[1], 2);
    }

    function actualizarVerticeMarkers() {
        editorMarkers.forEach(m => map.removeLayer(m));
        editorMarkers = [];

        editorCoordinates.forEach((coords, idx) => {
            const marker = L.marker(coords, {
                draggable: true,
                icon: L.divIcon({
                    html: `<div style="background:${editorColor}; width:12px; height:12px; border-radius:50%; border:2px solid white; box-shadow:0 0 4px rgba(0,0,0,0.5);"></div>`,
                    className: 'vertex-icon',
                    iconSize: [12, 12],
                    iconAnchor: [6, 6]
                })
            }).addTo(map);

            marker.bindTooltip((idx + 1).toString(), { permanent: true, direction: 'top', className: 'vertex-tooltip' });

            marker.on('drag', function(e) {
                editorCoordinates[idx] = [e.target.getLatLng().lat, e.target.getLatLng().lng];
                editorPolyline.setLatLngs(editorCoordinates);
            });

            marker.on('dragend', function() {
                actualizarVerticeMarkers();
            });

            marker.on('dblclick', function() {
                editorCoordinates.splice(idx, 1);
                editorPolyline.setLatLngs(editorCoordinates);
                actualizarVerticeMarkers();
            });

            editorMarkers.push(marker);
        });
    }

    window.limpiarTrazadoEditor = function() {
        if (!editorMode) return;
        editorCoordinates = [];
        editorPolyline.setLatLngs([]);
        editorMarkers.forEach(m => map.removeLayer(m));
        editorMarkers = [];
    };

    window.autocompletarTrazadoEditor = function() {
        if (!editorMode) return;
        const ruta = rutasTrazados.find(r => r.id === editorRutaId);
        if (!ruta) return;

        editorCoordinates = [];
        ruta.paraderos.forEach(p => {
            if (p.latitud_a && p.longitud_a) {
                editorCoordinates.push([parseFloat(p.latitud_a), parseFloat(p.longitud_a)]);
            }
        });
        editorPolyline.setLatLngs(editorCoordinates);
        actualizarVerticeMarkers();
        
        if (editorCoordinates.length >= 2) {
            map.fitBounds(editorPolyline.getBounds(), { padding: [40, 40] });
        }
    };

    window.guardarTrazadoEditor = async function() {
        if (!editorMode) return;

        const ruta = rutasTrazados.find(r => r.id === editorRutaId);
        if (!ruta) return;

        const saveUrl = `/admin/rutas/${editorRutaId}/trazado`;

        try {
            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    trazado: editorCoordinates,
                    color: editorColor
                })
            });

            const resData = await response.json();
            if (resData.success) {
                ruta.trazado = [...editorCoordinates];
                ruta.color = editorColor;

                alert('Trazado guardado correctamente.');
                salirEditor();
            } else {
                alert('Error al guardar el trazado: ' + (resData.error || 'Intente nuevamente.'));
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al guardar el trazado.');
        }
    };

    window.salirEditor = function() {
        editorMode = false;
        editorRutaId = null;
        editorCoordinates = [];
        editorMarkers.forEach(m => map.removeLayer(m));
        editorMarkers = [];
        
        if (editorPolyline) {
            map.removeLayer(editorPolyline);
            editorPolyline = null;
        }

        map.off('click', onMapClickForEditor);

        document.getElementById('editor-control-panel').style.display = 'none';

        renderRutasTrazados();
    };

    let markers = {};

    function getIcon(estado, flota) {
        const color = estado === 'activa' ? '#22c55e' : '#64748b';
        return L.divIcon({
            html: `<div style="background:${color}; width:24px; height:24px; border-radius:50%; border:2px solid white; box-shadow:0 0 5px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; color:white; font-size:10px; font-weight:900;">${flota}</div>`,
            className: 'custom-div-icon',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    }

    let todasLasVueltas = [];

    // --- LÓGICA DE DATOS ---
    async function actualizarDatos() {
        try {
            const resp = await fetch(API_URL);
            const data = await resp.json();
            
            todasLasVueltas = data.vueltas;
            
            // Recalcular estadísticas por ruta
            recalcularYRenderizarStatsPorRuta(todasLasVueltas);
            
            aplicarFiltroYRenderizar();
            
            ultimaActEl.textContent = 'Actualizado: ' + new Date().toLocaleTimeString();
        } catch (e) {
            console.error("Error polling data:", e);
        }
    }

    function recalcularYRenderizarStatsPorRuta(vueltas) {
        const statsContainer = document.getElementById('stats-por-ruta');
        if (!statsContainer) return;
        
        const conteoPorRuta = {};
        
        vueltas.forEach(v => {
            if (v.estado === 'activa') {
                const rutaNombre = v.ruta || 'Sin Ruta';
                conteoPorRuta[rutaNombre] = (conteoPorRuta[rutaNombre] || 0) + 1;
            }
        });
        
        let htmlStats = '';
        
        if (rutasTrazados.length === 0) {
            htmlStats = `
                <div class="stat-mini-card">
                    <div class="stat-mini-icon" style="background: var(--gray-l); color: var(--text3);">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                    <div>
                        <div style="font-size: 18px; font-weight: 800;">0</div>
                        <div style="font-size: 11px; color: var(--text3); font-weight: 600;">SIN RUTAS CONFIGURADAS</div>
                    </div>
                </div>
            `;
        } else {
            rutasTrazados.forEach((ruta, idx) => {
                const cantActivas = conteoPorRuta[ruta.nombre] || 0;
                const coloresPaleta = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'];
                const color = ruta.color || coloresPaleta[idx % coloresPaleta.length];
                
                const isVisible = visibleRoutes[ruta.id] !== false;
                
                htmlStats += `
                    <div class="stat-mini-card route-toggle-card ${isVisible ? 'selected' : ''}" 
                         style="cursor: pointer; border-left: 5px solid ${color}; transition: all 0.2s; position: relative; padding-right: 45px; display: flex; align-items: center; justify-content: space-between; min-width: 170px;">
                        <div onclick="toggleRutaPath(${ruta.id})" style="flex: 1; display: flex; align-items: center; gap: 10px;">
                            <div class="stat-mini-icon" style="background: ${color}20; color: ${color}; width: 32px; height: 32px; font-size: 12px; min-width: 32px; border-radius: 8px;">
                                <i class="fa-solid fa-route"></i>
                            </div>
                            <div>
                                <div style="font-size: 15px; font-weight: 800; line-height: 1.2;">${cantActivas} <span style="font-size: 9px; color: var(--text3); font-weight: 500;">activas</span></div>
                                <div style="font-size: 10px; color: var(--text2); font-weight: 700; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px;" title="${ruta.nombre}">${ruta.nombre}</div>
                            </div>
                        </div>
                        <button type="button" onclick="event.stopPropagation(); activarEditorTrazado(${ruta.id});" title="Editar trazado de la ruta" 
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--bg2); color: var(--text3); border: 1px solid var(--border); cursor: pointer;">
                            <i class="fa-solid fa-pencil" style="font-size: 10px;"></i>
                        </button>
                    </div>
                `;
            });
        }
        
        statsContainer.innerHTML = htmlStats;
    }

    function aplicarFiltroYRenderizar() {
        const filtroVal = document.getElementById('filtro-flota').value.trim().toLowerCase();
        
        let vueltasFiltradas = todasLasVueltas;
        if (filtroVal !== '') {
            vueltasFiltradas = todasLasVueltas.filter(v => {
                const flotaStr = v.flota ? v.flota.toString().toLowerCase() : '';
                const placaStr = v.vehiculo ? v.vehiculo.toString().toLowerCase() : '';
                const conductorStr = v.conductor ? v.conductor.toString().toLowerCase() : '';
                return flotaStr.includes(filtroVal) || placaStr.includes(filtroVal) || conductorStr.includes(filtroVal);
            });
        }
        
        renderTablaVueltas(vueltasFiltradas);
        renderMapaVueltas(vueltasFiltradas);
    }

    // Escuchar el input del filtro
    document.getElementById('filtro-flota').addEventListener('input', aplicarFiltroYRenderizar);

    function renderTablaVueltas(vueltas) {
        if (vueltas.length === 0) {
            tbody.innerHTML = `
                <tr id="empty-row">
                    <td colspan="10" style="text-align:center;padding:80px;">
                        <div style="font-size:40px; margin-bottom: 15px;">🏁</div>
                        <div style="font-weight:800; color:var(--text); font-size:18px;">No hay actividad coincidente ahora</div>
                        <div style="font-size:14px;color:var(--text3); margin-top: 5px;">Revisa tu filtro o espera a que inicien nuevas vueltas.</div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        let countRecientes = 0;

        vueltas.forEach(v => {
            if(v.estado === 'completada') countRecientes++;
            
            const isActive = v.estado === 'activa';
            const estimado = parseInt(v.estimado_min) || 0;
            const minutosTotal = parseInt(v.minutos_total) || 0;
            const excedeCompletada = !isActive && estimado > 0 && minutosTotal > estimado;
            
            let htmlDuracion = '';
            if (isActive) {
                htmlDuracion = `
                    <span class="pill green tiempo-cronometro" data-inicio-ts="${v.inicio_ts}" data-estimado-minutos="${estimado}" style="font-weight: 800; font-family: monospace; font-size: 14px; padding: 8px 14px;">
                        <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> 0s
                    </span>
                `;
            } else if (excedeCompletada) {
                htmlDuracion = `
                    <span class="pill red" style="font-weight: 800; font-family: monospace; font-size: 14px; padding: 8px 14px;" title="Estimado de Ruta: ${estimado} min">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 5px;"></i> ${v.tiempo_total_msg} (Excedido)
                    </span>
                `;
            } else {
                htmlDuracion = `
                    <span class="pill gray" style="font-weight: 800; font-family: monospace; font-size: 14px; padding: 8px 14px;">
                        <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> ${v.tiempo_total_msg || '—'}
                    </span>
                `;
            }

            let badgeBg = 'var(--accent-l)';
            let badgeColor = 'var(--accent)';
            let badgeLabel = 'COMPLETADA';

            if (isActive) {
                badgeBg = 'var(--green-l)';
                badgeColor = 'var(--green)';
                badgeLabel = 'ACTIVA';
            } else {
                if (v.paradero_salida_tipo && v.paradero_llegada_tipo) {
                    if (v.paradero_salida_tipo === 'intermedio' || v.paradero_llegada_tipo === 'intermedio') {
                        badgeBg = 'var(--red-l)';
                        badgeColor = 'var(--red)';
                        badgeLabel = 'CORTADA';
                    }
                }
            }

            html += `
                <tr id="vuelta-${v.id}" class="vuelta-row ${v.estado}">
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 700;">${v.conductor}</div>
                        <div style="font-size:12px; color:var(--text3); font-family: monospace;">${v.estado.toUpperCase()}</div>
                    </td>
                    <td>
                        <div style="font-weight: 800; font-size: 16px; color: #0f172a;">#${v.flota}</div>
                        <div style="font-size: 12px; color: var(--text3); font-family: monospace;">${v.vehiculo}</div>
                    </td>
                    <td><div style="font-weight: 600; font-size: 14px;">${v.ruta}</div></td>
                    <td>
                        <div class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">${v.hora_salida}</div>
                        <div class="text-sub" style="font-size:11px; margin-top:2px;">
                            <i class="fa-solid fa-map-pin" style="color:var(--text3); font-size:9px;"></i> ${v.paradero_salida || '—'}
                        </div>
                    </td>
                    <td>
                        <div class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">${v.hora_llegada || '—'}</div>
                        <div class="text-sub" style="font-size:11px; margin-top:2px;">
                            <i class="fa-solid fa-flag" style="color:var(--text3); font-size:9px;"></i> ${v.paradero_llegada || '—'}
                        </div>
                    </td>
                    <td class="mono">
                        ${htmlDuracion}
                    </td>
                    <td>
                        <span style="font-size: 13.5px; font-weight: 800; padding: 8px 14px; border-radius: 99px; background: ${badgeBg}; color: ${badgeColor}; display: inline-block; text-align: center;">
                            ${badgeLabel}
                        </span>
                    </td>
                    <td><span class="pill blue" style="font-size: 12px; font-weight: 800; padding: 6px 12px;">V${v.numero_vuelta}</span></td>
                    <td>
                        ${(v.lat_salida && v.lng_salida) ? `
                            <a href="https://maps.google.com/?q=${v.lat_salida},${v.lng_salida}" target="_blank" class="btn-secondary" style="font-size:10px; padding: 5px 10px; text-decoration: none;">🛫 Salida</a>
                        ` : '—'}
                    </td>
                    <td style="text-align: right; padding-right: 24px;">
                        ${isActive ? (
                            (v.lat_actual && v.lng_actual) ? `
                                <a href="https://maps.google.com/?q=${v.lat_actual},${v.lng_actual}" target="_blank" class="btn-secondary" style="font-size:10px; padding: 5px 10px; text-decoration: none; background: var(--green); color: white;">📍 En vivo</a>
                            ` : `<span style="color:var(--green); font-size:11px; font-weight:800;"><i class="fa-solid fa-spinner fa-spin" style="margin-right: 5px;"></i> En ruta</span>`
                        ) : (
                            (v.latitud_fin && v.longitud_fin) ? `
                                <a href="https://maps.google.com/?q=${v.latitud_fin},${v.longitud_fin}" target="_blank" class="btn-secondary" style="font-size:10px; padding: 5px 10px; text-decoration: none; background: var(--accent); color: white;">🏁 Llegada</a>
                            ` : '—'
                        )}
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }

    function renderMapaVueltas(vueltas) {
        // Limpiar markers antiguos que no están en la lista
        const idsNuevos = vueltas.map(v => v.id);
        Object.keys(markers).forEach(id => {
            if (!idsNuevos.includes(parseInt(id))) {
                map.removeLayer(markers[id]);
                delete markers[id];
            }
        });

        const bounds = [];

        vueltas.forEach(v => {
            const isActive = v.estado === 'activa';
            if (!isActive) return; // SOLO MOSTRAR ACTIVOS EN EL MAPA

            const lat = v.latitud;
            const lng = v.longitud;

            if (lat && lng) {
                if (markers[v.id]) {
                    markers[v.id].setLatLng([lat, lng]);
                    markers[v.id].setIcon(getIcon(v.estado, v.flota));
                } else {
                    markers[v.id] = L.marker([lat, lng], { icon: getIcon(v.estado, v.flota) })
                        .addTo(map)
                        .bindPopup(`<b>Unidad #${v.flota}</b><br>${v.conductor}<br>EN RUTA`);
                }
                bounds.push([lat, lng]);
            }
        });

        if (bounds.length > 0 && !map._manualMove) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        }
    }

    // --- CRONOMETRO EN VIVO ---
    function formatTimeSpanish(sec) {
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        return `${h}h ${m}m ${s}s`;
    }

    function actualizarCronometros() {
        const ahora = Date.now();
        document.querySelectorAll('.tiempo-cronometro').forEach(el => {
            let inicioTs = el.dataset.inicioTs;
            
            // Si viene del renderizado estático de Blade
            if (!inicioTs && el.dataset.inicio) {
                inicioTs = new Date(el.dataset.inicio).getTime();
                el.dataset.inicioTs = inicioTs;
            }

            if (inicioTs) {
                const diffSec = Math.max(0, Math.floor((ahora - parseInt(inicioTs)) / 1000));
                const diffMin = Math.floor(diffSec / 60);
                const estimado = parseInt(el.dataset.estimadoMinutos) || 0;
                const excede = estimado > 0 && diffMin > estimado;
                
                const timeStr = formatTimeSpanish(diffSec);
                
                if (excede) {
                    el.className = "pill red tiempo-cronometro";
                    el.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="margin-right: 5px;"></i> ${timeStr} (Excedido)`;
                } else {
                    el.className = "pill green tiempo-cronometro";
                    el.innerHTML = `<i class="fa-regular fa-clock" style="margin-right: 5px;"></i> ${timeStr}`;
                }
            }
        });
    }
    setInterval(actualizarCronometros, 1000);

    // --- EVENTOS REAL-TIME ---
    if (window.Echo) {
        window.Echo.private(`empresa.${empresaId}.vueltas`)
            .listen('.vuelta.iniciada', () => {
                console.log("Push Reverb: Vuelta Iniciada");
                actualizarDatos();
            })
            .listen('.vuelta.terminada', () => {
                console.log("Push Reverb: Vuelta Terminada");
                actualizarDatos();
            })
            .listen('.vuelta.ubicacion_actualizada', (e) => {
                console.log("Push Reverb: Ubicación Actualizada", e);
                const marker = markers[e.vuelta_id];
                if (marker) {
                    marker.setLatLng([e.latitud, e.longitud]);
                    
                    // Actualizar caché de coordenadas para que no se pierdan al filtrar
                    const v = todasLasVueltas.find(item => item.id === e.vuelta_id);
                    if (v) {
                        v.lat_actual = e.latitud;
                        v.lng_actual = e.longitud;
                        v.latitud = e.latitud;
                        v.longitud = e.longitud;
                    }
                } else {
                    actualizarDatos();
                }
            });
    }

    // Centrar mapa al hacer click en la fila de un conductor
    tbody.addEventListener('click', (e) => {
        const tr = e.target.closest('tr.vuelta-row');
        if (!tr) return;
        
        // Evitar que interfiera si el click es en enlaces o botones
        if (e.target.closest('a') || e.target.closest('button')) return;
        
        const idParts = tr.id.split('-');
        const vueltaId = parseInt(idParts[1]);
        
        if (markers[vueltaId]) {
            const marker = markers[vueltaId];
            map.setView(marker.getLatLng(), 16);
            marker.openPopup();
            
            // Marcar mapa como movido manualmente para que fitBounds no lo resetee de inmediato
            map._manualMove = true;
            
            // Efecto flash visual temporal
            const originalBg = tr.style.background;
            tr.style.background = '#dbeafe';
            setTimeout(() => {
                tr.style.background = originalBg;
            }, 800);
        }
    });

    // Detener auto-ajuste de cámara si el usuario mueve el mapa
    map.on('movestart', () => map._manualMove = true);
    setTimeout(() => map._manualMove = false, 30000); // Reactivar cada 30s

    window.toggleFullscreenMap = function() {
        const container = document.getElementById('mapa-live-container');
        const btn = document.getElementById('btn-fullscreen-map');
        const icon = btn.querySelector('i');

        container.classList.toggle('fullscreen');

        if (container.classList.contains('fullscreen')) {
            icon.className = 'fa-solid fa-compress';
            btn.title = 'Salir de Pantalla Completa';
        } else {
            icon.className = 'fa-solid fa-expand';
            btn.title = 'Pantalla Completa';
        }

        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    };

    // --- INICIO ---
    actualizarDatos();
    setInterval(actualizarDatos, 30000); // Polling de seguridad cada 30s
});
</script>

@endsection
