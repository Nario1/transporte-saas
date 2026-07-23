@extends('layouts.admin')

@php
    $pageTitle = 'Conductores';
    $pageSubtitle = 'Gestión y control de personal de conducción';
@endphp

@section('content')
    <div class="panel">
        
        {{-- 1. INDICADORES --}}
        <div class="stats-row g-3">
            <div class="stat blue">
                <div class="stat-label">Conductor Registrado</div>
                <div class="stat-val">{{ $resumen['total'] }}</div>
                <div class="stat-sub">{{ $resumen['activos'] }} activos en el sistema</div>
                <span class="stat-icon"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="stat green">
                <div class="stat-label">Disponibilidad</div>
                <div class="stat-val">
                    {{ number_format($resumen['total'] > 0 ? ($resumen['activos'] / $resumen['total']) * 100 : 0, 0) }}%
                </div>
                <div class="stat-sub">Personal operativo</div>
                <span class="stat-icon"><i class="fa-solid fa-check-circle"></i></span>
            </div>
            <div class="stat red">
                <div class="stat-label">Alertas Licencia</div>
                <div class="stat-val">{{ $resumen['vencer_mes'] }}</div>
                <div class="stat-sub">Vencimientos próximos (30d)</div>
                <span class="stat-icon"><i class="fa-solid fa-id-card"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES Y FILTROS --}}
        <div class="flex-between gap-24">
            <div class="card" style="flex: 1;">
                <form method="GET" action="{{ route('conductores.index') }}" class="card-body g-filters">
                    <div class="field" style="flex: 2;">
                        <label>Buscar Conductor</label>
                        <input type="text" name="q" placeholder="Nombre, DNI o N° Flota..." value="{{ request('q') }}">
                    </div>
                    <div class="field" style="flex: 1;">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">TODOS</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>ACTIVO</option>
                            <option value="suspendido" {{ request('estado') === 'suspendido' ? 'selected' : '' }}>SUSPENDIDO</option>
                            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>INACTIVO</option>
                        </select>
                    </div>
                    <div class="flex-h">
                        <button type="submit" class="btn-primary" style="height: 48px; width: 48px; justify-content: center; padding: 0;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        @if (request()->hasAny(['q', 'estado']))
                            <a href="{{ route('conductores.index') }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <a href="{{ route('conductores.create') }}" class="btn-primary" style="padding: 0 32px; height: 70px; border-radius: 20px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-user-plus"></i> NUEVO CONDUCTOR
            </a>
        </div>

        {{-- 3. TABLA MODERNA --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Listado de Personal de Conducción</div>
                    <div style="font-size:12px; color:var(--text3); margin-top: 2px;">
                        {{ $conductores->total() }} conductor(es) encontrados
                    </div>
                </div>
            </div>
            <div class="tbl-wrap">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Conductor</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Licencia</th>
                            <th>Vehículo(s)</th>
                            <th>Ruta</th>
                            <th style="text-align: center;">Acceso App</th>
                            <th class="col-status">Estado</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conductores as $c)
                            @php 
                                $hoy = now();
                                $licVence = $c->licencia_vence ? \Carbon\Carbon::parse($c->licencia_vence) : null;
                                $licStatus = $licVence ? ($licVence->isPast() ? 'color:var(--red); font-weight:700;' : ($licVence->diffInDays($hoy) < 30 ? 'color:var(--orange); font-weight:700;' : '')) : '';
                                
                                $v = $c->vehiculos->first();
                                $ruta = $v ? $v->rutas->where('pivot.activo', true)->first() : null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="text-main">{{ $c->nombre_completo }}</span>
                                </td>
                                <td>
                                    <span class="text-main">{{ $c->dni ?? '---' }}</span>
                                </td>
                                <td>
                                    <span class="text-main">{{ $c->telefono ?? '---' }}</span>
                                </td>
                                <td>
                                    <span class="text-main">Cat. {{ $c->tipo_licencia }}</span>
                                    <span class="text-sub" style="{{ $licStatus }}">
                                        Vence: {{ $licVence ? $licVence->format('d/m/Y') : '---' }}
                                    </span>
                                </td>
                                <td>
                                    @if($v)
                                        <span class="text-main">#{{ $v->numero_flota }}</span>
                                        <span class="text-sub">{{ $v->placa }}</span>
                                    @else
                                        <span class="text-sub">Sin Unidad</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-main">{{ $ruta->nombre ?? '---' }}</span>
                                </td>
                                <td style="text-align: center;">
                                    @if($c->tiene_acceso)
                                        <span class="pill green" style="font-size: 10px; font-weight: 800;" title="Tiene usuario de App creado">
                                            <i class="fa-solid fa-mobile-screen-button"></i> SÍ
                                        </span>
                                    @else
                                        <span class="pill gray" style="font-size: 10px; opacity: 0.6;" title="Sin acceso a la aplicación">NO</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $pillColor = match($c->estado) {
                                            'activo' => 'green',
                                            'suspendido' => 'red',
                                            'inactivo' => 'red',
                                            default => 'gray'
                                        };
                                    @endphp
                                    <span class="pill {{ $pillColor }}" style="font-size: 11px;">
                                        {{ strtoupper($c->estado) }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                        <a href="{{ route('conductores.show', $c->id) }}" class="action-icon show-icon" title="Ver Detalle">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('conductores.edit', $c->id) }}" class="action-icon edit-icon" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('conductores.destroy', $c->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-icon-submit" onclick="return confirm('¿Eliminar conductor {{ $c->nombre }}?')" title="Eliminar">
                                                <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:60px; color:var(--text3);">
                                    <i class="fa-solid fa-id-badge" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                    @if (request()->hasAny(['q', 'estado']))
                                        No se encontraron conductores con esos filtros.
                                        <a href="{{ route('conductores.index') }}" style="color:var(--accent); text-decoration:none; font-weight:700;">Limpiar búsqueda</a>
                                    @else
                                        No hay conductores registrados.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($conductores->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);">
                    {{ $conductores->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
