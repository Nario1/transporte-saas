@extends('layouts.app')

@section('title', 'Acceso Restringido — TransJunín')

@section('body_content')
    <div
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; text-align: center; background: var(--bg); padding: 24px;">

        <div
            style="background: var(--card); padding: 48px 32px; border-radius: 20px; box-shadow: var(--shadow-m); border: 1px solid var(--border); max-width: 440px; width: 100%; animation: fadeIn 0.4s ease-out;">

            {{-- Icono de seguridad --}}
            <div style="font-size: 60px; margin-bottom: 24px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">🛡️</div>

            <h1 style="font-size: 22px; font-weight: 800; color: var(--text); margin-bottom: 12px; letter-spacing: -0.02em;">
                Acceso Restringido
            </h1>

            <p style="color: var(--text2); margin-bottom: 32px; font-size: 14px; line-height: 1.6;">
                Se ha intentado acceder a una zona no autorizada. <br>
                <span style="color: var(--red); font-weight: 600;">La sesión ha sido cerrada por seguridad.</span>
            </p>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                {{-- BOTÓN HACIA EL LOGIN --}}
                <a href="{{ route('login') }}" class="btn-primary"
                    style="justify-content: center; padding: 14px; text-decoration: none; font-size: 14px;">
                    Volver al Inicio de Sesión
                </a>

                {{-- INFORMACIÓN ADICIONAL --}}
                <p
                    style="color: var(--text3); font-size: 11px; margin-top: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                    TransJunín SaaS — Control de Seguridad
                </p>
            </div>
        </div>
    </div>
@endsection
