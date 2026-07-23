@extends('layouts.admin')

@section('back_url', route('rutas.index'))

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
                                            <div style="font-size: 10px; color: var(--text3); text-transform: uppercase;">{{ $p->tipo }}</div>
                                        </div>
                                        <div class="flex-h" style="gap: 8px;">
                                            <a href="{{ route('rutas.kiosco', [$ruta->id, $p->id]) }}" class="action-icon show-icon" target="_blank" title="Abrir Kiosco">
                                                <i class="fa-solid fa-camera"></i>
                                            </a>
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
                                <option value="origen">Origen</option>
                                <option value="destino">Destino</option>
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
@endsection
