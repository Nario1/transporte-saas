@extends('layouts.admin')

@php
    $pageTitle = 'Personal del Sistema';
    $pageSubtitle = 'Panel de administración de accesos y perfiles';
@endphp

@section('content')
    <div class="panel">
        
        {{-- 1. CABECERA DE ACCIÓN --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="user-av" style="background: var(--sidebar); color: var(--text-inv);">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--text);">Usuarios Registrados</h2>
                    <div style="font-size: 11px; color: var(--text3); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                        {{ $users->count() }} CUENTAS ACTIVAS
                    </div>
                </div>
            </div>
            
            <a href="{{ route('users.create') }}" class="btn-primary" style="padding: 0 25px; height: 50px; border-radius: 12px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-user-plus"></i> NUEVO USUARIO
            </a>
        </div>

        {{-- 2. TABLA DE USUARIOS --}}
        <div class="card">
            <div class="tbl-wrap">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Usuario / Identificación</th>
                            <th>Acceso (Rol)</th>
                            <th class="col-status">Estado</th>
                            <th class="col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="flex-h" style="gap: 12px;">
                                        <div class="user-av" style="width: 36px; height: 36px; font-size: 14px; background: var(--bg); color: var(--accent); border: 1px solid var(--border);">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="text-main">{{ $user->name }}</span>
                                            <span class="text-sub">{{ $user->email }} • UID: #{{ $user->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="pill blue" style="padding: 4px 12px; font-weight: 800; font-size: 11px; border: 1px solid var(--accent-t);">
                                        {{ strtoupper($user->roles_limpios ?: 'SIN ROL') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="pill {{ $user->activo ? 'green' : 'red' }}" style="font-size: 11px;">
                                        {{ $user->activo ? 'ACTIVO' : 'SUSPENDIDO' }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                        <a href="{{ route('users.edit', $user->id) }}" class="action-icon edit-icon" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @if (!$user->is_admin_protected)
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar definitivamente a este usuario?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-icon-submit" title="Eliminar">
                                                    <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="action-icon" style="opacity: 0.2; cursor: not-allowed;" title="Protegido">
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Alerta informativa --}}
        <div style="margin-top: 25px; padding: 15px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; display: flex; align-items: center; gap: 15px;">
            <i class="fa-solid fa-circle-info" style="color: var(--accent); font-size: 20px;"></i>
            <div style="font-size: 12px; color: var(--text3); font-weight: 600;">
                Los usuarios con el rol <span style="color: var(--text2);">ADMIN</span> son vitales para la sincronización con la flota y no pueden ser eliminados para mantener la integridad de la empresa.
            </div>
        </div>
    </div>
@endsection
