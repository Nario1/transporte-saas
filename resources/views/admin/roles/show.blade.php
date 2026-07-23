@extends('layouts.admin')

@php
    $pageTitle = 'Detalle de Roles';
    $pageSubtitle = 'Perfil de Acceso: ' . strtoupper($nombreVisible);
@endphp

@section('back_url', route('roles.index'))

@section('content')
    <div class="panel">

        {{-- 1. CABECERA DE PERFIL DE ROL --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="user-av"
                    style="width: 60px; height: 60px; font-size: 24px; border-radius: 16px; background: var(--sidebar); color: var(--text-inv);">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ strtoupper($nombreVisible) }}</h2>
                    <div class="flex-h" style="gap: 10px;">
                        @php
                            $esProtegido =
                                $nombreVisible === 'ADMIN' ||
                                in_array($role->name, ['SUPER_ADMIN', 'ADMIN', 'OPERADOR', 'conductor']);
                        @endphp
                        @if ($esProtegido)
                            <span class="pill gold" style="font-size: 11px;">SISTEMA</span>
                        @else
                            <span class="pill blue" style="font-size: 11px;">PERSONALIZADO</span>
                        @endif
                        <span style="font-size: 13px; color: var(--text3);">Cod: {{ $nombreVisible }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-h">
                @if (!$esProtegido || auth()->user()->hasRole('SUPER_ADMIN'))
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn-primary">
                        <i class="fa-solid fa-pen-to-square"></i> Modificar Facultades
                    </a>
                @endif
            </div>
        </div>

        <div class="g-2-1">
            <div class="flex-v" style="gap: 25px;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Matriz de Facultades Autorizadas</div>
                    </div>
                    <div class="card-body">
                        @if ($permissions->isEmpty())
                            <div style="text-align: center; padding: 40px; color: var(--text3);">
                                Este rol no tiene facultades asignadas.
                            </div>
                        @else
                            <div
                                style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:10px;">
                                @foreach ($permissions->sort() as $perm)
                                    <div class="flex-h"
                                        style="padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; gap: 8px;">
                                        <i class="fa-solid fa-check" style="color: var(--green); font-size: 11px;"></i>
                                        <span
                                            style="font-size: 11px; font-weight: 700; color: var(--text2);">{{ strtoupper(str_replace(['.', '_'], ' ', $perm)) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <aside class="flex-v" style="gap: 25px;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Usuarios Asignados</div>
                        <span class="pill" style="font-size: 10px;">{{ $role->users->count() }}</span>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div style="max-height: 400px; overflow-y: auto;">
                            @forelse($role->users as $u)
                                <div class="flex-h"
                                    style="padding: 12px 15px; border-bottom: 1px solid var(--border); gap: 10px;">
                                    <div class="user-av"
                                        style="width: 30px; height: 30px; font-size: 11px; background: var(--bg);">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-v">
                                        <div style="font-weight: 700; font-size: 13px;">{{ $u->name }}</div>
                                        <div style="font-size: 10px; color: var(--text3);">{{ $u->email }}</div>
                                    </div>
                                </div>
                            @empty
                                <div style="padding: 30px; text-align: center; color: var(--text3); font-size: 12px;">Sin
                                    usuarios.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if ($role->users->count() === 0 && (!$esProtegido || auth()->user()->hasRole('SUPER_ADMIN')))
                    <div class="card" style="border: 1px solid var(--red-l); background: var(--red-l);">
                        <div class="card-body flex-v" style="gap: 10px;">
                            <div style="font-weight: 900; color: var(--red); font-size: 10px;">ZONA DE PELIGRO</div>
                            <form method="POST" action="{{ route('roles.destroy', $role->id) }}"
                                onsubmit="return confirm('¿Eliminar rol?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm"
                                    style="width: 100%; border-radius: 8px; font-size: 11px;">
                                    BORRAR ROL
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
    </div>
@endsection
