@extends('layouts.admin')

@php
    $pageTitle = 'Editar Empresa (Master)';
    $pageSubtitle = 'Gestión Global de ' . $empresa->nombre;
@endphp

@section('back_url', route('superadmin.empresas.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-city"></i> Actualizar Información Master</div>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Sección: Datos de Empresa --}}
                    <div class="form-section">
                        <div class="g-2">
                            <div class="field">
                                <label for="nombre">Nombre Comercial *</label>
                                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $empresa->nombre) }}" required>
                                @error('nombre')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="ruc">RUC *</label>
                                <input type="text" id="ruc" name="ruc" value="{{ old('ruc', $empresa->ruc) }}" maxlength="11" required>
                                @error('ruc')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="field" style="margin-top: 20px;">
                            <label for="razon_social">Razón Social</label>
                            <input type="text" id="razon_social" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}">
                            @error('razon_social')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $empresa->telefono) }}">
                                @error('telefono')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $empresa->direccion) }}">
                                @error('direccion')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="plan">Plan SaaS *</label>
                                <select name="plan" id="plan" required>
                                    <option value="basico" {{ old('plan', $empresa->plan) == 'basico' ? 'selected' : '' }}>Plan Básico</option>
                                    <option value="pro" {{ old('plan', $empresa->plan) == 'pro' ? 'selected' : '' }}>Plan Pro</option>
                                    <option value="enterprise" {{ old('plan', $empresa->plan) == 'enterprise' ? 'selected' : '' }}>Plan Enterprise</option>
                                </select>
                                @error('plan')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="activa">Estado de Suscripción *</label>
                                <select name="activa" id="activa" required>
                                    <option value="1" {{ old('activa', $empresa->activa) ? 'selected' : '' }}>Activa / Operativa</option>
                                    <option value="0" {{ !old('activa', $empresa->activa) ? 'selected' : '' }}>Suspendida / Inactiva</option>
                                </select>
                                @error('activa')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="tributo_diario">Tributo Diario Base (S/) *</label>
                                <input type="number" step="0.01" id="tributo_diario" name="tributo_diario" value="{{ old('tributo_diario', $empresa->tributo_diario) }}" required>
                                @error('tributo_diario')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="logo">Logo de Empresa</label>
                                <div style="display: flex; gap: 15px; align-items: center;">
                                    @if($empresa->logo_path)
                                        <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo" style="width: 40px; height: 40px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border); background: var(--bg); padding: 4px; flex-shrink: 0;">
                                    @endif
                                    <input type="file" id="logo" name="logo" accept="image/*" style="flex: 1; font-size: 12px;">
                                </div>
                                @error('logo')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Administrador de Empresa --}}
                    <div class="form-section" style="margin-top: 30px; padding-top: 20px; border-top: 2px dashed var(--border);">
                        <h3 style="font-size: 14px; font-weight: 800; color: var(--accent); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-user-gear"></i> Credenciales del Administrador Principal
                        </h3>

                        <div class="g-2">
                            <div class="field">
                                <label for="admin_name">Nombre del Administrador *</label>
                                <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name', $admin?->name) }}" required>
                                @error('admin_name')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="admin_email">Correo del Administrador *</label>
                                <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email', $admin?->email) }}" required>
                                @error('admin_email')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="admin_password">Nueva Contraseña (Opcional)</label>
                                <input type="password" id="admin_password" name="admin_password" placeholder="Mínimo 6 caracteres">
                                <small style="color: var(--text3); font-size: 11px; display: block; margin-top: 2px;">Dejar en blanco para conservar la contraseña actual.</small>
                                @error('admin_password')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="admin_password_confirmation">Confirmar Nueva Contraseña</label>
                                <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" placeholder="Repite la contraseña">
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="form-actions" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="font-size: 11px; color: var(--text3);">
                            Última actualización: {{ $empresa->updated_at->format('d/m/Y H:i') }}
                        </div>
                        <div style="display: flex; gap: 10px; width: 60%; justify-content: flex-end; min-width: 250px;">
                            <a href="{{ route('superadmin.empresas.index') }}" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary" style="flex: 2; font-size: 13px; font-weight: 700;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
