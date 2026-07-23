@extends('layouts.admin')

@php
    $pageTitle = 'Nuevo Registro';
    $pageSubtitle = 'Creación de credenciales de acceso';
@endphp

@section('back_url', route('users.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-user-plus"></i> Formulario de Alta de Usuario</div>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-section">
                        {{-- Identidad --}}
                        <div class="field">
                            <label for="name">Nombre Completo del Colaborador</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Ej: Juan Pérez">
                            @error('name')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="email">Correo Electrónico Laboral</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="usuario@empresa.com">
                            @error('email')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Password Grid --}}
                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field" style="margin: 0;">
                                <label for="password">Contraseña segura</label>
                                <input type="password" id="password" name="password" required>
                                @error('password')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="field" style="margin: 0;">
                                <label for="password_confirmation">Confirmar clave</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        {{-- Roles --}}
                        <div class="field" style="margin-top: 20px;">
                            <label for="role">Asignar Perfil de Acceso (Rol)</label>
                            <select name="role" id="role" required>
                                <option value="" disabled selected>-- Selecciona un Nivel --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ strtoupper($role->nombre_limpio) }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px;">
                        <a href="{{ route('users.index') }}" class="btn-secondary" style="flex: 1; text-align: center;">
                            Descartar
                        </a>
                        <button type="submit" class="btn-primary" style="flex: 2;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Registrar Colaborador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
