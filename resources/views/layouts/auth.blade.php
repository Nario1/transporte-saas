@extends('layouts.app')

@section('extra_css')
    <style>
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            padding: 32px;
            box-shadow: var(--shadow-m);
        }

        .auth-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        /* Estilo rápido para las alertas de login */
        .auth-alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
@endsection

@section('body_content')
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="brand-icon" style="width:48px; height:48px; font-size:20px;">TJ</div>
            </div>

            <div style="text-align:center; margin-bottom:24px;">
                <h1 style="font-size:20px; font-weight:800;">@yield('auth_title')</h1>
                <p style="color:var(--text3); font-size:13px; margin-top:4px;">TransJunín Plataforma SaaS</p>
            </div>

            {{-- ALERTAS DE SESIÓN --}}
            @if (session('success'))
                <div class="auth-alert"
                    style="background: var(--green-l); color: var(--green); border: 1px solid rgba(22, 163, 74, 0.2);">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="auth-alert"
                    style="background: var(--red-l); color: var(--red); border: 1px solid rgba(220, 38, 38, 0.2);">
                    🔒 {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </div>
    </div>
@endsection
