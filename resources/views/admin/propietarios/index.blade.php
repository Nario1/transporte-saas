@extends('layouts.admin')

@php
    $pageTitle = 'Propietarios';
    $pageSubtitle = 'Gestión estratégica de socios y dueños de unidades';
@endphp

@section('content')
    <div class="panel">
        
        {{-- 1. INDICADORES --}}
        <div class="stats-row g-3">
            <div class="stat blue">
                <div class="stat-label">Socio Propietarios</div>
                <div class="stat-val">{{ $resumen['total'] }}</div>
                <div class="stat-sub">{{ $resumen['activos'] }} socios vigentes en flota</div>
                <span class="stat-icon"><i class="fa-solid fa-user-tie"></i></span>
            </div>
            
            <div class="stat green">
                <div class="stat-label">Indice de Participación</div>
                <div class="stat-val">
                    {{ number_format($resumen['total'] > 0 ? ($resumen['activos'] / $resumen['total']) * 100 : 0, 0) }}%
                </div>
                <div class="stat-sub">Actividad del padrón de socios</div>
                <span class="stat-icon"><i class="fa-solid fa-check-double"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES Y FILTROS --}}
        <div class="flex-between gap-24">
            <div class="card" style="flex: 1;">
                <form method="GET" action="{{ route('propietarios.index') }}" class="card-body g-filters">
                    <div class="field" style="flex: 2;">
                        <label>Buscar Propietario</label>
                        <input type="text" name="q" placeholder="Nombre o DNI..." value="{{ request('q') }}">
                    </div>
                    <div class="field" style="flex: 1;">
                        <label>Estado</label>
                        <select name="activo">
                            <option value="">TODOS</option>
                            <option value="activo" {{ request('activo') === 'activo' ? 'selected' : '' }}>ACTIVO</option>
                            <option value="inactivo" {{ request('activo') === 'inactivo' ? 'selected' : '' }}>INACTIVO</option>
                        </select>
                    </div>
                    <div class="flex-h">
                        <button type="submit" class="btn-primary" style="height: 48px; width: 48px; justify-content: center; padding: 0;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        @if (request()->hasAny(['q', 'activo']))
                            <a href="{{ route('propietarios.index') }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <a href="{{ route('propietarios.create') }}" class="btn-primary" style="padding: 0 32px; height: 70px; border-radius: 20px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-plus-circle"></i> NUEVO PROPIETARIO
            </a>
        </div>

        {{-- 3. TABLA DE PROPIETARIOS --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Padrón de Socios Propietarios</div>
                    <div style="font-size:12px; color:var(--text3); margin-top: 2px;">
                        {{ $propietarios->total() }} propietario(s) en total
                    </div>
                </div>
            </div>
            <div class="tbl-wrap">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Socio Propietario</th>
                            <th>DNI / RUC</th>
                            <th>Contacto / Dirección</th>
                            <th style="text-align: center;">Unidades</th>
                            <th style="text-align: center;">Conductor?</th>
                            <th class="col-status">Estado</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($propietarios as $p)
                            <tr>
                                <td>
                                    <span class="text-main">{{ $p->nombre_completo }}</span>
                                </td>
                                <td>
                                    <span class="text-main">{{ $p->dni ?? '---' }}</span>
                                </td>
                                <td>
                                    <span class="text-main">
                                        {{ $p->telefono ?? 'S/Telf' }}
                                    </span>
                                    <span class="text-sub">{{ $p->direccion ?? 'Sin dirección' }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="pill blue" style="font-size: 11px; font-weight: 800;">
                                        {{ $p->vehiculos_count ?? '0' }} UND
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    @if($p->conductor)
                                        <div class="flex-v" style="gap:4px; align-items:center;">
                                            <span class="pill gold" style="font-size: 10px; font-weight: 800;">SÍ</span>
                                            @if($p->conductor->tiene_acceso)
                                                <i class="fa-solid fa-mobile-screen-button" style="font-size: 11px; color: var(--green);" title="Tiene acceso a la App"></i>
                                            @endif
                                        </div>
                                    @else
                                        <span class="pill gray" style="font-size: 10px; opacity: 0.5;">NO</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="pill {{ $p->activo ? 'green' : 'red' }}" style="font-size: 11px;">
                                        {{ $p->activo ? 'ACTIVO' : 'INACTIVO' }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                        <a href="{{ route('propietarios.show', $p->id) }}" class="action-icon show-icon" title="Ver Detalle">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('propietarios.edit', $p->id) }}" class="action-icon edit-icon" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('propietarios.destroy', $p->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-icon-submit" onclick="return confirm('¿Eliminar registro de {{ $p->nombre_completo }}?')" title="Eliminar">
                                                <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:60px; color:var(--text3);">
                                    <i class="fa-solid fa-user-tie" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                    @if (request()->hasAny(['q', 'activo']))
                                        No se encontraron propietarios con esos filtros.
                                        <a href="{{ route('propietarios.index') }}" style="color:var(--accent); text-decoration:none; font-weight:700;">Limpiar búsqueda</a>
                                    @else
                                        No hay socios propietarios registrados.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($propietarios->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);">
                    {{ $propietarios->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
