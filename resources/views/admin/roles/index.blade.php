@extends('layouts.admin')

@php
    $pageTitle = 'Niveles de Acceso';
    $pageSubtitle = 'Configuración de roles y permisos sectorizados';
@endphp

@section('content')
    <div class="panel">
        
        {{-- 1. CABECERA Y BÚSQUEDA --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h" style="flex: 1; max-width: 500px;">
                <div style="position: relative; width: 100%;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text3); font-size: 14px;"></i>
                    <input type="text" id="roleSearch" placeholder="Buscar rol por nombre..." onkeyup="filtrarRoles(this.value)" 
                           style="width: 100%; padding: 14px 16px 14px 44px; background: var(--card); border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none; box-shadow: var(--shadow-s);">
                </div>
            </div>
            
            <a href="{{ route('roles.create') }}" class="btn-primary" style="padding: 0 25px; height: 50px; border-radius: 12px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-plus-circle"></i> NUEVO ROL
            </a>
        </div>

        {{-- 2. TABLA DE ROLES --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Configuración de Privilegios</div>
                <span class="pill" style="font-size: 11px;">{{ $roles->count() }} roles definidos</span>
            </div>
            <div class="tbl-wrap">
                <table class="tbl tbl-modern" id="tablaRoles">
                    <thead>
                        <tr>
                            <th>Rol / Nivel</th>
                            <th>Permisos Asignados</th>
                            <th style="text-align: center;">Usuarios</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            @php
                                $nombreLimpio = strtoupper($role->nombre_visible);
                                $esSistema = $nombreLimpio === 'ADMIN' || in_array($role->name, ['SUPER_ADMIN', 'ADMIN', 'OPERADOR', 'conductor']);
                            @endphp
                            <tr>
                                <td>
                                    <span class="text-main">{{ $nombreLimpio }}</span>
                                    <span class="text-sub">
                                        @if($esSistema) <i class="fa-solid fa-lock" style="font-size: 9px; opacity:0.5;"></i> SISTEMA @else PERSONALIZADO @endif
                                    </span>
                                </td>
                                <td>
                                    <div class="flex-h" style="flex-wrap: wrap; gap: 4px; max-width: 350px;">
                                        @forelse($role->permissions->take(4) as $permiso)
                                            <span class="pill" style="font-size: 8px; background: var(--bg); opacity:0.8;">
                                                {{ strtoupper(str_replace('.', ' ', $permiso->name)) }}
                                            </span>
                                        @empty
                                            <span class="text-sub">Sin permisos</span>
                                        @endforelse
                                        @if ($role->permissions->count() > 4)
                                            <span class="text-sub" style="font-weight: 800; color: var(--accent);">+{{ $role->permissions->count() - 4 }} más</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="text-main">{{ $role->users_count }}</span>
                                    <span class="text-sub">ASIGNADOS</span>
                                </td>
                                <td class="col-actions">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                        <a href="{{ route('roles.show', $role->id) }}" class="action-icon show-icon" title="Ver">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @if (!$esSistema || auth()->user()->hasRole('SUPER_ADMIN'))
                                            <a href="{{ route('roles.edit', $role->id) }}" class="action-icon edit-icon" title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            @if ($role->users_count === 0)
                                                <form method="POST" action="{{ route('roles.destroy', $role->id) }}" onsubmit="return confirm('¿Confirmas la eliminación del rol {{ $nombreLimpio }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-icon-submit" title="Eliminar Rol">
                                                        <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span style="font-size: 10px; font-weight: 800; color: var(--text3); background: var(--bg); padding: 4px 8px; border-radius: 6px;">
                                                <i class="fa-solid fa-lock"></i> LOCK
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 60px; color: var(--text3);">
                                    <i class="fa-solid fa-users-slash" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                    No se encontraron roles en esta sección.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function filtrarRoles(q) {
            const filas = document.querySelectorAll('#tablaRoles tbody tr');
            filas.forEach(f => {
                f.style.display = f.innerText.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
            });
        }
    </script>
@endpush
