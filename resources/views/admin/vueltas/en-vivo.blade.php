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
        @php
            $vueltasPorRuta = $vueltasActivas->groupBy(function($v) {
                return $v->ruta?->nombre ?? 'Sin Ruta';
            });
        @endphp
        @forelse($vueltasPorRuta as $nombreRuta => $grupo)
            <div class="stat-mini-card">
                <div class="stat-mini-icon" style="background: var(--green-l); color: var(--green);">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div>
                    <div style="font-size: 18px; font-weight: 800;">{{ $grupo->count() }}</div>
                    <div style="font-size: 11px; color: var(--text3); font-weight: 600; text-transform: uppercase;">{{ $nombreRuta }}</div>
                </div>
            </div>
        @empty
            <div class="stat-mini-card">
                <div class="stat-mini-icon" style="background: var(--gray-l); color: var(--text3);">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div>
                    <div style="font-size: 18px; font-weight: 800;">0</div>
                    <div style="font-size: 11px; color: var(--text3); font-weight: 600;">SIN UNIDADES EN RUTA</div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- MAPA INTERACTIVO --}}
    <div id="mapa-live" class="no-print"></div>

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

                            <td class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">
                                {{ $v->hora_salida }}
                            </td>

                            <td class="mono" style="color:var(--text3);">
                                —
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
                                <span class="pill green" style="font-size: 12px; font-weight: 800; padding: 6px 12px;">ACTIVA</span>
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
        const rutas = Object.keys(conteoPorRuta);
        
        if (rutas.length === 0) {
            htmlStats = `
                <div class="stat-mini-card">
                    <div class="stat-mini-icon" style="background: var(--gray-l); color: var(--text3);">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                    <div>
                        <div style="font-size: 18px; font-weight: 800;">0</div>
                        <div style="font-size: 11px; color: var(--text3); font-weight: 600;">SIN UNIDADES EN RUTA</div>
                    </div>
                </div>
            `;
        } else {
            rutas.forEach(nombreRuta => {
                htmlStats += `
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon" style="background: var(--green-l); color: var(--green);">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div>
                            <div style="font-size: 18px; font-weight: 800;">${conteoPorRuta[nombreRuta]}</div>
                            <div style="font-size: 11px; color: var(--text3); font-weight: 600; text-transform: uppercase;">${nombreRuta}</div>
                        </div>
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
                    <td class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">${v.hora_salida}</td>
                    <td class="mono" style="font-weight: 800; font-size: 15px; color: #0f172a;">${v.hora_llegada || '—'}</td>
                    <td class="mono">
                        ${htmlDuracion}
                    </td>
                    <td>
                        <span class="pill ${isActive ? 'green' : 'gray'}" style="font-size: 12px; font-weight: 800; padding: 6px 12px; display: inline-block;">
                            ${v.estado.toUpperCase()}
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

    // Detener auto-ajuste de cámara si el usuario mueve el mapa
    map.on('movestart', () => map._manualMove = true);
    setTimeout(() => map._manualMove = false, 30000); // Reactivar cada 30s

    // --- INICIO ---
    actualizarDatos();
    setInterval(actualizarDatos, 30000); // Polling de seguridad cada 30s
});
</script>

@endsection
