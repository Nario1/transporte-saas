@extends('layouts.admin')

@php
    $pageTitle = 'Permisos Globales';
    $pageSubtitle = 'Gestión y control de acceso para roles del sistema';
@endphp

@section('content')
    <div style="display: grid; gap: 20px;">
        
        {{-- Alertas de Éxito/Error --}}
        @if(session('success'))
            <div style="background: #e6fcf5; border-left: 5px solid #099268; color: #099268; padding: 15px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Filtros y Barra de Búsqueda --}}
        <div class="card no-print">
            <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 20px;">
                <form action="{{ route('superadmin.permisos.index') }}" method="GET" style="display: flex; gap: 10px; flex: 1; max-width: 450px;">
                    <div style="position: relative; flex: 1;">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar permisos..." 
                               style="padding-left: 40px; height: 45px; border-radius: 10px; border: 1.5px solid var(--border); width: 100%; font-size: 14px;">
                        <i class="fa-solid fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text3);"></i>
                    </div>
                    <button type="submit" class="btn-primary" style="height: 45px; padding: 0 20px; font-weight: 700; border-radius: 10px;">
                        🔍 Buscar
                    </button>
                    @if($search)
                        <a href="{{ route('superadmin.permisos.index') }}" class="btn-secondary" style="height: 45px; display: flex; align-items: center; justify-content: center; padding: 0 15px; border-radius: 10px; text-decoration: none;" title="Limpiar búsqueda">
                            Limpiar
                        </a>
                    @endif
                </form>

                <div>
                    <a href="{{ route('superadmin.permisos.create') }}" class="btn-primary" style="height: 45px; display: inline-flex; align-items: center; gap: 8px; padding: 0 20px; text-decoration: none; border-radius: 10px; font-weight: 700;">
                        <i class="fa-solid fa-plus"></i> NUEVO PERMISO
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabla de Permisos --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Listado de Permisos Registrados</div>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Nombre del Permiso</th>
                            <th>Guard Name</th>
                            <th style="text-align: center;">Roles Asignados</th>
                            <th style="text-align: right;" class="no-print">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permisos as $p)
                            <tr>
                                <td>
                                    <div style="font-weight: 800; font-size: 14.5px; color: var(--text);">{{ $p->name }}</div>
                                </td>
                                <td>
                                    <code style="background: var(--border); padding: 3px 8px; border-radius: 5px; font-size: 12px; color: var(--text2);">{{ $p->guard_name }}</code>
                                </td>
                                <td style="text-align: center;">
                                    @php
                                        $rolesCount = $p->roles->count();
                                    @endphp
                                    @if($rolesCount > 0)
                                        <span class="pill blue" style="font-size: 10.5px; font-weight: 800;">
                                            {{ $rolesCount }} {{ $rolesCount == 1 ? 'Rol' : 'Roles' }}
                                        </span>
                                    @else
                                        <span class="pill gray" style="font-size: 10.5px; font-weight: 800;">Sin roles</span>
                                    @endif
                                </td>
                                <td style="text-align: right;" class="no-print">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 8px;">
                                        <a href="{{ route('superadmin.permisos.show', $p->id) }}" class="btn-secondary" style="height: 35px; width: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none;" title="Ver detalle">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('superadmin.permisos.edit', $p->id) }}" class="btn-secondary" style="height: 35px; width: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; color: var(--gold);" title="Editar permiso">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('superadmin.permisos.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este permiso global? Esta acción podría afectar el acceso de los usuarios.');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary" style="height: 35px; width: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; color: var(--red); border: 1px solid rgba(220,53,69,0.1);" title="Eliminar permiso">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 40px; color: var(--text3);">
                                    No se encontraron permisos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($permisos->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);" class="no-print">
                    {{ $permisos->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
