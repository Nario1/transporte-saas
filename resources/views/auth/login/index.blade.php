@extends('layouts.auth')

@section('auth_title', 'Iniciar Sesión')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Campo: Email / Placa --}}
        <div style="margin-bottom: 16px;">
            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px;">Usuario / Placa</label>
            <input type="text" name="email" value="{{ old('email') }}" required autofocus
                style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg);"
                placeholder="Ej. ABC-123">
            @error('email')
                <span
                    style="color:var(--danger); font-size:12px; font-weight:600; margin-top:4px; display:block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Campo: Password --}}
        <div style="margin-bottom: 16px;">
            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px;">Contraseña</label>
            <input type="password" name="password" required
                style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            @error('password')
                <span
                    style="color:var(--danger); font-size:12px; font-weight:600; margin-top:4px; display:block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Opciones adicionales --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer; color:var(--text2);">
                <input type="checkbox" name="remember"> Recordarme
            </label>
        </div>

        {{-- Botón de Acción --}}
        <button type="submit" class="btn-primary" style="width:100%; padding:14px; font-weight:800; border-radius:10px;">
            Entrar al Sistema
        </button>

        {{-- Link de Registro --}}
        <p style="text-align:center; margin-top:24px; font-size:13px; color:var(--text3);">
            ¿No tienes cuenta? <a href="{{ route('register') }}"
                style="color:var(--accent); font-weight:700; text-decoration:none;">Regístrate aquí</a>
        </p>
    </form>
@endsection
