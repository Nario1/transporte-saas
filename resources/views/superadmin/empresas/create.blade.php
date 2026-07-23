@extends('layouts.admin')

@php
    $pageTitle = 'Registrar Nueva Empresa';
    $pageSubtitle = 'Dar de alta una empresa de transporte en el sistema global';
@endphp

@section('back_url', route('superadmin.empresas.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-city"></i> Datos Maestros de la Empresa</div>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.empresas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-section">
                        <div class="g-2">
                            <div class="field">
                                <label for="nombre">Nombre Comercial *</label>
                                <input type="text" id="nombre" name="nombre" placeholder="Ej: Transportes Junín" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="ruc">RUC *</label>
                                <input type="text" id="ruc" name="ruc" placeholder="11 dígitos" value="{{ old('ruc') }}" maxlength="11" required>
                                @error('ruc')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="field" style="margin-top: 20px;">
                            <label for="razon_social">Razón Social</label>
                            <input type="text" id="razon_social" name="razon_social" placeholder="Razón Social Legal" value="{{ old('razon_social') }}">
                            @error('razon_social')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" placeholder="Central telefónica" value="{{ old('telefono') }}">
                                @error('telefono')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" placeholder="Sede principal" value="{{ old('direccion') }}">
                                @error('direccion')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="plan">Plan SaaS *</label>
                                <select name="plan" id="plan" required>
                                    <option value="basico">Plan Básico</option>
                                    <option value="pro">Plan Pro</option>
                                    <option value="enterprise" selected>Plan Enterprise</option>
                                </select>
                                @error('plan')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="tributo_diario">Tributo Diario Base (S/) *</label>
                                <input type="number" step="0.01" id="tributo_diario" name="tributo_diario" value="24.00" required>
                                @error('tributo_diario')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="field" style="margin-top: 20px;">
                            <label for="logo">Logo de Empresa</label>
                            <input type="file" id="logo" name="logo" accept="image/*">
                            <small style="color: var(--text3); display: block; margin-top: 5px;">Sube el logo que se mostrará en el sidebar de este cliente.</small>
                            @error('logo')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- SECCIÓN 2: Usuario Administrador --}}
                    <div class="form-section" style="margin-top: 30px;">
                        <div class="form-section-title" style="font-weight: 800; font-size: 15px; margin-bottom: 15px; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                            <i class="fa-solid fa-user-shield"></i> Datos del Administrador de la Empresa
                        </div>
                        
                        <div class="g-2">
                            <div class="field">
                                <label for="admin_name">Nombre del Administrador *</label>
                                <input type="text" id="admin_name" name="admin_name" placeholder="Ej: Juan Pérez" value="{{ old('admin_name') }}" required>
                                @error('admin_name')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="admin_email">Correo Electrónico *</label>
                                <input type="email" id="admin_email" name="admin_email" placeholder="admin@empresa.com" value="{{ old('admin_email') }}" required>
                                @error('admin_email')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="password">Contraseña *</label>
                                <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                                @error('password')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="password_confirmation">Confirmar Contraseña *</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite la contraseña" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px;">
                        <a href="{{ route('superadmin.empresas.index') }}" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary" style="flex: 2;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Registrar Empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
