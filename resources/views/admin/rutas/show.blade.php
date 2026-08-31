@extends('layouts.admin')

@section('back_url', route('rutas.index'))

@section('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endsection

@section('content')
    <div class="panel">
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="brand-icon" style="width: 50px; height: 50px; font-size: 24px;">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div>
                    <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ $ruta->nombre }}</h2>
                    <div class="flex-h" style="gap: 10px;">
                        <span class="pill {{ $ruta->estado === 'activa' ? 'green' : 'red' }}">
                            {{ strtoupper($ruta->estado) }}
                        </span>
                        <span style="font-size: 13px; color: var(--text3);">Cod: {{ $ruta->codigo }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-h">
                <button onclick="document.getElementById('modalParadero').classList.add('open')" class="btn-primary">
                    <i class="fa-solid fa-location-dot"></i> Añadir Punto de Control
                </button>
            </div>
        </div>

        <div class="g-2-1">
            {{-- COLUMNA IZQUIERDA: Itinerario y Unidades --}}
            <div class="flex-v" style="gap: 25px;">
                
                {{-- Métrica Rápida --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="stat blue" style="padding: 20px;">
                        <div class="stat-label">UNIDADES EN RUTA</div>
                        <div class="stat-val" style="font-size: 24px;">{{ $ruta->vehiculos->count() }}</div>
                        <div class="stat-icon"><i class="fa-solid fa-bus"></i></div>
                    </div>
                    <div class="stat green" style="padding: 20px;">
                        <div class="stat-label">VUELTAS HOY</div>
                        <div class="stat-val" style="font-size: 24px;">{{ $ruta->vueltas->where('fecha', today())->count() }}</div>
                        <div class="stat-icon"><i class="fa-solid fa-rotate"></i></div>
                    </div>
                </div>

                {{-- Itinerario --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Secuencia de Itinerario</div>
                    </div>
                    <div class="card-body">
                        @php $paraderos = $ruta->paraderos->sortBy('orden'); @endphp
                        <div class="flex-v" style="gap: 0; position: relative; padding-left: 20px;">
                            <div style="position: absolute; left: 26px; top: 10px; bottom: 10px; width: 2px; background: var(--border2); z-index: 1;"></div>
                            
                            @foreach ($paraderos as $p)
                                <div class="flex-h" style="padding: 15px 0; gap: 20px; position: relative; z-index: 2;">
                                    <div style="width: 14px; height: 14px; border-radius: 50%; background: {{ $p->tipo === 'origen' ? 'var(--green)' : ($p->tipo === 'destino' ? 'var(--red)' : 'var(--accent)') }}; border: 3px solid var(--card); box-shadow: 0 0 0 1px var(--border);"></div>
                                    <div class="flex-h" style="flex: 1; justify-content: space-between;">
                                        <div>
                                            <div style="font-size: 14px; font-weight: 700;">{{ $p->nombre }}</div>
                                            <div style="font-size: 10px; color: var(--text3); text-transform: uppercase;">{{ $p->tipo === 'origen' ? 'Origen / Destino' : ($p->tipo === 'destino' ? 'Destino / Origen' : 'Intermedio') }}</div>
                                        </div>
                                        <div class="flex-h" style="gap: 8px;">
                                            <button type="button" class="btn-icon-submit" onclick="abrirModalCoordenadas({{ $p->id }}, '{{ addslashes($p->nombre) }}', '{{ $p->latitud_a }}', '{{ $p->longitud_a }}', '{{ $p->latitud_b }}', '{{ $p->longitud_b }}', {{ $p->tolerancia ?? 30 }})" title="Definir Coordenadas Geográficas (Tramo A-B)">
                                                <i class="fa-solid fa-location-dot action-icon show-icon" style="color: {{ ($p->latitud_a && $p->longitud_a) ? 'var(--green)' : 'var(--accent)' }};"></i>
                                            </button>
                                            <form action="{{ route('rutas.paraderos.destroy', [$ruta->id, $p->id]) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-icon-submit" onclick="return confirm('¿Quitar paradero?')">
                                                    <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Unidades --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Unidades vinculadas</div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                            @forelse($ruta->vehiculos as $v)
                                <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 12px; padding: 12px;">
                                    <div class="flex-between" style="margin-bottom: 8px;">
                                        <span class="mono" style="font-weight: 800; color: var(--accent); font-size: 12px;">{{ $v->placa }}</span>
                                        <span style="font-size: 10px; font-weight: 700; color: var(--text3);">#{{ $v->numero_flota }}</span>
                                    </div>
                                    <div style="font-size: 11px; font-weight: 700; color: var(--text2);">{{ $v->conductor?->nombre ?? 'Sin Conductor' }}</div>
                                </div>
                            @empty
                                <div style="padding: 20px; text-align: center; color: var(--text3); font-size: 12px;">Sin vehículos asignados.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Resumen --}}
            <aside class="flex-v" style="gap: 25px;">
                <div class="card" style="border-top: 4px solid var(--accent);">
                    <div class="card-header">
                        <div class="card-title">Resumen de Itinerario</div>
                    </div>
                    <div class="card-body flex-v" style="gap: 15px;">
                        <div class="flex-between">
                            <span style="font-size: 12px; color: var(--text3);">Origen</span>
                            <span style="font-weight: 700; font-size: 13px;">{{ $ruta->origen }}</span>
                        </div>
                        <div class="flex-between">
                            <span style="font-size: 12px; color: var(--text3);">Destino</span>
                            <span style="font-weight: 700; font-size: 13px;">{{ $ruta->destino }}</span>
                        </div>
                        <div class="flex-between">
                            <span style="font-size: 12px; color: var(--text3);">Tiempo Estimado</span>
                            <span style="font-weight: 700; font-size: 13px; color: var(--accent);">{{ $ruta->duracion_min }} min</span>
                        </div>
                    </div>
                </div>

                {{-- Mapa de Ruta --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-map-location-dot" style="margin-right: 5px;"></i> Visualización en Mapa</div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div id="mapa-ruta" style="height: 250px; border-radius: 0 0 16px 16px; position: relative; z-index: 10;"></div>
                    </div>
                </div>
                
                <a href="{{ route('rutas.index') }}" class="btn-secondary" style="justify-content: center;">
                    <i class="fa-solid fa-list"></i> Lista de Rutas
                </a>
            </aside>
        </div>
    </div>

    {{-- MODAL PARADERO INTEGRADO --}}
    <div id="modalParadero" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="card-title">Añadir Punto de Control</div>
                <button onclick="document.getElementById('modalParadero').classList.remove('open')"
                    style="border:none; background:none; cursor:pointer; font-size: 18px;">&times;</button>
            </div>
            <form action="{{ route('rutas.paraderos.store', $ruta->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-grid" style="grid-template-columns: 1fr;">
                        <div class="field">
                            <label>Nombre del Paradero</label>
                            <input type="text" name="nombre" required placeholder="Ej. Parque Huamanmarca">
                        </div>
                        <div class="field">
                            <label>Tipo de Punto</label>
                            <select name="tipo" required>
                                <option value="intermedio">Intermedio</option>
                                <option value="origen">Origen / Destino</option>
                                <option value="destino">Destino / Origen</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Orden en la Secuencia</label>
                            <input type="number" name="orden" value="{{ $ruta->paraderos->count() + 1 }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="document.getElementById('modalParadero').classList.remove('open')"
                        class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Punto</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL COORDENADAS PARADERO --}}
    <div id="modalCoordenadas" class="modal-overlay">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <div class="card-title" id="modalCoordenadasTitulo">Definir Coordenadas del Paradero</div>
                <button type="button" onclick="document.getElementById('modalCoordenadas').classList.remove('open')"
                    style="border:none; background:none; cursor:pointer; font-size: 18px;">&times;</button>
            </div>
            <form id="formCoordenadas" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p style="font-size: 12px; color: var(--text3); margin-bottom: 15px;">
                        Define los límites inicial (A) y final (B) de la calle que corresponden a la zona válida para este paradero.
                    </p>
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 12px;">
                        
                        {{-- Punto Inicial A --}}
                        <div style="grid-column: span 2; font-weight: 700; font-size: 13px; color: var(--accent); margin-bottom: 4px;">
                            <i class="fa-solid fa-map-pin"></i> LÍMITE INICIAL (PUNTO A)
                        </div>
                        <div class="field">
                            <label>Latitud A</label>
                            <input type="number" step="any" name="latitud_a" id="input_latitud_a" placeholder="Ej: -12.0654">
                        </div>
                        <div class="field">
                            <label>Longitud A</label>
                            <input type="number" step="any" name="longitud_a" id="input_longitud_a" placeholder="Ej: -75.2048">
                        </div>

                        {{-- Punto Final B --}}
                        <div style="grid-column: span 2; font-weight: 700; font-size: 13px; color: var(--accent); margin-top: 10px; margin-bottom: 4px;">
                            <i class="fa-solid fa-flag"></i> LÍMITE FINAL (PUNTO B)
                        </div>
                        <div class="field">
                            <label>Latitud B</label>
                            <input type="number" step="any" name="latitud_b" id="input_latitud_b" placeholder="Ej: -12.0660">
                        </div>
                        <div class="field">
                            <label>Longitud B</label>
                            <input type="number" step="any" name="longitud_b" id="input_longitud_b" placeholder="Ej: -75.2055">
                        </div>

                        {{-- Tolerancia --}}
                        <div style="grid-column: span 2; margin-top: 10px;">
                            <div class="field">
                                <label>Tolerancia Lateral (metros)</label>
                                <input type="number" name="tolerancia" id="input_tolerancia" min="5" max="500" value="30" required>
                                <small style="display:block; font-size:10.5px; color:var(--text3); margin-top:4px;">
                                    Distancia máxima perpendicular (lado a lado de la pista) para validar GPS.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="document.getElementById('modalCoordenadas').classList.remove('open')"
                        class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Coordenadas</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    function abrirModalCoordenadas(id, nombre, latA, lngA, latB, lngB, tolerancia) {
        document.getElementById('modalCoordenadasTitulo').textContent = `Coordenadas: ${nombre}`;
        
        // Configurar la URL de acción del formulario
        const form = document.getElementById('formCoordenadas');
        form.action = `/admin/rutas/{{ $ruta->id }}/paraderos/${id}/coordenadas`;
        
        // Rellenar campos
        document.getElementById('input_latitud_a').value = latA || '';
        document.getElementById('input_longitud_a').value = lngA || '';
        document.getElementById('input_latitud_b').value = latB || '';
        document.getElementById('input_longitud_b').value = lngB || '';
        document.getElementById('input_tolerancia').value = tolerancia || 30;
        
        // Abrir modal
        document.getElementById('modalCoordenadas').classList.add('open');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const paraderos = @json($ruta->paraderos->sortBy('orden')->values());
        
        // Inicializar mapa
        const defaultCenter = [-12.067, -75.21]; // Huancayo por defecto
        const map = L.map('mapa-ruta').setView(defaultCenter, 14);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const latlngs = [];
        
        paraderos.forEach((p, idx) => {
            if (p.latitud_a && p.longitud_a) {
                const coords = [parseFloat(p.latitud_a), parseFloat(p.longitud_a)];
                latlngs.push(coords);
                
                let color = '#3b82f6';
                if (p.tipo === 'origen') color = '#22c55e';
                if (p.tipo === 'destino') color = '#ef4444';

                L.circleMarker(coords, {
                    radius: 6,
                    fillColor: color,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                })
                .addTo(map)
                .bindPopup(`<b>Paradero:</b> ${p.nombre}<br><b>Orden:</b> ${p.orden}<br><b>Tipo:</b> ${p.tipo.toUpperCase()}`);
            }
        });

        if (latlngs.length >= 2) {
            L.polyline(latlngs, {
                color: '#3b82f6',
                weight: 4,
                opacity: 0.8,
                dashArray: '5, 10'
            }).addTo(map);

            const bounds = L.latLngBounds(latlngs);
            map.fitBounds(bounds, { padding: [30, 30] });
        } else if (latlngs.length === 1) {
            map.setView(latlngs[0], 15);
        }
    });
</script>
@endpush
