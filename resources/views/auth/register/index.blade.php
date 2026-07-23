@extends('layouts.app') {{-- Usando tu layout principal o auth --}}

@section('title', 'Registrar Empresa — TransJunín')

@section('body_content')
    <div
        style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg); padding: 20px;">

        <div
            style="background: var(--card); padding: 40px; border-radius: 20px; box-shadow: var(--shadow-m); border: 1px solid var(--border); max-width: 450px; width: 100%;">

            <div style="text-align: center; margin-bottom: 30px;">
                <div
                    style="background: var(--accent); color: white; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-weight: 800; font-size: 20px;">
                    TJ</div>
                <h1 style="font-size: 22px; font-weight: 800; color: var(--text);">Crea tu cuenta</h1>
                <p style="color: var(--text3); font-size: 13px;">Registra tu empresa de transporte hoy mismo</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div style="margin-bottom: 16px;">
                    <label
                        style="display:block; margin-bottom:6px; font-weight:700; font-size:12px; color: var(--text2);">NOMBRE
                        DE LA EMPRESA</label>
                    <input type="text" name="empresa_nombre" value="{{ old('empresa_nombre') }}" required
                        placeholder="Ej. TransJunín S.A."
                        style="width:100%; padding:11px; border:1px solid var(--border2); border-radius:10px; outline: none; font-family: inherit;">
                    @error('empresa_nombre')
                        <span style="color:var(--red); font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom: 16px;">
                    <label
                        style="display:block; margin-bottom:6px; font-weight:700; font-size:12px; color: var(--text2);">TU
                        NOMBRE COMPLETO</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Admin del sistema"
                        style="width:100%; padding:11px; border:1px solid var(--border2); border-radius:10px; outline: none; font-family: inherit;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label
                        style="display:block; margin-bottom:6px; font-weight:700; font-size:12px; color: var(--text2);">CORREO
                        ELECTRÓNICO</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="ejemplo@correo.com"
                        style="width:100%; padding:11px; border:1px solid var(--border2); border-radius:10px; outline: none; font-family: inherit;">
                    @error('email')
                        <span style="color:var(--red); font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:24px;">
                    <div>
                        <label
                            style="display:block; margin-bottom:6px; font-weight:700; font-size:12px; color: var(--text2);">CONTRASEÑA</label>
                        <input type="password" name="password" required
                            style="width:100%; padding:11px; border:1px solid var(--border2); border-radius:10px; outline: none;">
                    </div>
                    <div>
                        <label
                            style="display:block; margin-bottom:6px; font-weight:700; font-size:12px; color: var(--text2);">CONFIRMAR</label>
                        <input type="password" name="password_confirmation" required
                            style="width:100%; padding:11px; border:1px solid var(--border2); border-radius:10px; outline: none;">
                    </div>
                    @error('password')
                        <div style="grid-column: 1/-1; color:var(--red); font-size:11px;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary"
                    style="width:100%; padding:14px; font-weight:800; border-radius:12px; justify-content: center; font-size: 14px; box-shadow: var(--shadow-m);">
                    🚀 Registrar mi Empresa
                </button>

                <div style="text-align:center; margin-top:24px; font-size:13px; color: var(--text2);">
                    ¿Ya tienes una cuenta? <a href="{{ route('login') }}"
                        style="color:var(--accent); font-weight:700; text-decoration:none;">Inicia sesión</a>
                </div>
            </form>
        </div>
    </div>
@endsection
