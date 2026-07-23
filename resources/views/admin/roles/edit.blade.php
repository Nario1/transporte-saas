{{-- ══════════════════════════════════════════════════════════
     resources/views/admin/roles/edit.blade.php
══════════════════════════════════════════════════════════ --}}
@extends('layouts.admin')

@section('back_url', route('roles.index'))

@php
    $pageTitle = 'Editar Rol';
    $pageSubtitle = "Modificar permisos de: {$nombreVisible}";

    $groups = [
        'Personal' => ['user', 'conductor'],
        'Flota' => ['vehiculo', 'propietario'],
        'Operaciones' => ['ruta', 'vuelta', 'paradero'],
        'Finanzas' => ['tributo', 'sancion'],
        'Sistema' => ['rol', 'empresa', 'ajuste']
    ];
    $shownPermissions = [];
@endphp

@section('content')

    <div style="max-width:900px; margin:0 auto;">

        @if ($errors->any())
            <div class="alert danger" style="margin-bottom:16px;">
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <span class="card-title">Editando nivel: <span class="badge blue">{{ $nombreVisible }}</span></span>
            </div>
            <div class="card-body">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf @method('PUT')

                    {{-- Nombre --}}
                    <div class="form-section">
                        <div class="form-section-title" style="font-size: 13px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px; font-weight: 800;">
                            <i class="fa-solid fa-tag"></i> Identidad del Nivel
                        </div>
                        <div class="field">
                            <label style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--text2); font-size: 13px;">Etiqueta del Rol</label>
                            <input type="text" name="name" value="{{ old('name', $nombreVisible) }}"
                                style="background:var(--bg); border:1px solid var(--border2); border-radius:12px; padding:12px 16px; font-size:14px; width:100%; color: var(--text); outline: none; font-weight: 700;">
                        </div>
                    </div>

                    {{-- Permisos --}}
                    <div class="form-section" style="margin-top:32px;">
                        <div class="form-section-title" style="font-size: 13px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 20px; font-weight: 800;">
                            <i class="fa-solid fa-key"></i> Matriz de Facultades
                            <span style="float:right; font-size:11px; font-weight:400; text-transform: none; letter-spacing: 0;">
                                <a href="#" onclick="toggleTodos(true); return false;"
                                    style="color:var(--accent); text-decoration: none; font-weight: 700;">Marcar todos</a>
                                ·
                                <a href="#" onclick="toggleTodos(false); return false;"
                                    style="color:var(--text3); text-decoration: none;">Desmarcar</a>
                            </span>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:10px;">
                            @foreach ($allPermissions as $permiso)
                                <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; cursor:pointer; background: var(--bg); border: 1px solid var(--border); border-radius:10px; transition: border .2s;"
                                    onmouseover="this.style.borderColor='var(--accent)'" 
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="permissions[]" value="{{ $permiso }}"
                                        class="perm-check" style="width:16px; height:16px; accent-color:var(--accent);"
                                        {{ in_array($permiso, $permisosActivos) ? 'checked' : '' }}>
                                    <span style="font-size:12px; font-weight:700; color:var(--text2);">
                                        {{ str_replace(['.', '_'], ' ', strtoupper($permiso)) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 40px; display: flex; gap: 16px;">
                        <a href="{{ route('roles.index') }}" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; padding: 14px; border-radius: 12px; font-weight: 700;">CANCELAR</a>
                        <button type="submit" class="btn-primary" style="flex: 2; border: none; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 15px;">
                            <i class="fa-solid fa-floppy-disk"></i> ACTUALIZAR FACULTADES
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function toggleTodos(marcar) {
            document.querySelectorAll('.perm-check').forEach(c => c.checked = marcar);
        }
    </script>
@endpush
