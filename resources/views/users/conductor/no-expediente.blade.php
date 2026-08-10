@extends('layouts.app')

@section('title', 'Cuenta no Vinculada — TransJunín')

@section('body_content')
    <div
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; text-align: center; background: var(--bg); padding: 24px;">

        <div
            style="background: var(--card); padding: 48px 32px; border-radius: 20px; box-shadow: var(--shadow-m); border: 1px solid var(--border); max-width: 440px; width: 100%; animation: fadeIn 0.4s ease-out;">

            {{-- Icono de expediente/usuario --}}
            <div style="font-size: 60px; margin-bottom: 24px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.08));">👤</div>

            <h1 style="font-size: 20px; font-weight: 800; color: var(--text); margin-bottom: 12px; letter-spacing: -0.02em;">
                Expediente no Vinculado
            </h1>

            <p style="color: var(--text2); margin-bottom: 32px; font-size: 14px; line-height: 1.6; text-align: left;">
                Tu cuenta de usuario está registrada como conductor, pero **no tiene un expediente de conducción asociado** en la empresa. <br><br>
                Por favor, comunícate con el administrador de la empresa para que registre tus datos de conductor (licencia y tarjeta de circulación) en el panel administrativo.
            </p>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                {{-- FORMULARIO DE LOGOUT --}}
                <form action="{{ route('logout') }}" method="POST" style="width: 100%; margin: 0;">
                    @csrf
                    <button type="submit" class="btn-primary"
                        style="width: 100%; justify-content: center; padding: 14px; font-size: 14px; font-weight: 800; border: none; cursor: pointer; font-family: inherit;">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
