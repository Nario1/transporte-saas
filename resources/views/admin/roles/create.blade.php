{{-- ══════════════════════════════════════════════════════════
     resources/views/admin/roles/create.blade.php
══════════════════════════════════════════════════════════ --}}
@extends('layouts.admin')

@section('back_url', route('roles.index'))

@php
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

        {{-- Alertas de validación --}}

        @if ($errors->any())
            <div class="alert danger" style="margin-bottom:16px;">
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <span class="card-title">Formulario de registro de Nivel</span>
            </div>
            <div class="card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    {{-- Nombre del rol --}}
                    <div class="form-section" style="margin-bottom: 32px;">
                        <div class="form-section-title" style="font-size: 13px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px; font-weight: 800;">
                            <i class="fa-solid fa-tag"></i> Identidad del Nivel
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                            <div class="field">
                                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--text2); font-size: 13px;">Seleccionar tipo base</label>
                                <select id="role_selector" onchange="checkRole(this)"
                                    style="background:var(--bg); border:1px solid var(--border2); border-radius:12px; padding:12px 16px; font-size:14px; width:100%; color: var(--text); outline: none; font-weight: 700;">
                                    <option value="" disabled selected>Seleccione un tipo...</option>
                                    <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                                    <option value="OPERADOR">OPERADOR</option>
                                    <option value="SUPERVISOR">SUPERVISOR</option>
                                    <option value="custom">— Escribir nombre personalizado —</option>
                                </select>
                            </div>
                            <div id="custom_role_wrapper" class="field" style="display:none;">
                                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--text2); font-size: 13px;">Nombre personalizado</label>
                                <input type="text" id="custom_role_input" name="name"
                                    placeholder="Ej: GERENTE, DESPACHADOR..." value="{{ old('name') }}"
                                    style="background:var(--bg); border:1px solid var(--border2); border-radius:12px; padding:12px 16px; font-size:14px; width:100%; color: var(--text); outline: none; font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- Permisos --}}
                    <div class="form-section" style="margin-top:24px;">
                        <div class="form-section-title" style="font-size: 13px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px; font-weight: 800;">
                            <i class="fa-solid fa-key"></i> Matriz de Facultades
                        </div>
                        <p style="font-size:12px; color:var(--text3); margin-bottom:20px;">
                            Habilita las secciones a las que este nivel podrá acceder dentro del panel:
                        </p>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:10px;">
                            @foreach ($allPermissions as $permiso)
                                <label style="display:flex; align-items:center; gap:10px; padding:10px 12px; cursor:pointer; background: var(--bg); border: 1px solid var(--border); border-radius:10px; transition: border .2s;"
                                    onmouseover="this.style.borderColor='var(--accent)'" 
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="permissions[]" value="{{ $permiso }}"
                                        class="perm-check" style="width:16px; height:16px; accent-color:var(--accent);"
                                        {{ in_array($permiso, old('permissions', [])) ? 'checked' : '' }}>
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
                            <i class="fa-solid fa-floppy-disk"></i> GUARDAR ROL
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function checkRole(select) {
            const wrapper = document.getElementById('custom_role_wrapper');
            const input = document.getElementById('custom_role_input');

            if (select.value === 'custom') {
                wrapper.style.display = 'block';
                input.name = 'name';
                select.removeAttribute('name');
                input.focus();
            } else {
                wrapper.style.display = 'none';
                select.name = 'name';
                input.removeAttribute('name');
            }
        }
    </script>
@endpush
