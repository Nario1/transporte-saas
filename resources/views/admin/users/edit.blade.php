@extends('layouts.admin')

@php
    $pageTitle = 'Editar Perfil';
    $pageSubtitle = "Modificación de credenciales: {$user->name}";
@endphp

@section('back_url', route('users.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 650px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Gestión de Cuenta de Usuario</div>
            </div>
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-address-card"></i> Información Básica
                        </div>
                        
                        <div class="field">
                            <label for="name">Nombre Completo</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="email">Dirección de Correo</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="role">Nivel de Acceso (Rol)</label>
                            <select name="role" id="role" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ strtoupper($role->nombre_limpio) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-section" style="background: var(--bg); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
                        <div class="form-section-title" style="margin-bottom: 10px;">
                            <i class="fa-solid fa-key"></i> Seguridad del Acceso
                        </div>
                        <p style="font-size: 11px; color: var(--text3); margin-bottom: 20px;">
                            <i class="fa-solid fa-info-circle"></i> Deje en blanco los campos de contraseña si no desea cambiar la clave actual.
                        </p>
                        
                        <div class="g-2">
                            <div class="field">
                                <label for="password">Nueva Contraseña</label>
                                <input type="password" id="password" name="password" placeholder="••••••••">
                                @error('password')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Confirmar Clave</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px;">
                        <a href="{{ route('users.index') }}" class="btn-secondary" style="flex: 1; text-align: center;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary" style="flex: 2;">
                            <i class="fa-solid fa-save"></i> Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
