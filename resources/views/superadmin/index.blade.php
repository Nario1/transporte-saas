@extends('layouts.admin')

@php
    $pageTitle = 'Dashboard Maestro';
    $pageSubtitle = 'Resumen global de la plataforma TransJunín';
@endphp

@section('content')
    {{-- Tarjetas de Estadísticas Rápidas --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">

        <div class="card"
            style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid var(--gold);">
            <div
                style="font-size: 35px; background: rgba(255, 215, 0, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 15px;">
                🏢</div>
            <div>
                <div
                    style="font-size: 13px; color: var(--text3); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    Empresas Totales</div>
                <div style="font-size: 28px; font-weight: 900; color: var(--text);">{{ \App\Models\Empresa::count() }}</div>
            </div>
        </div>

        <div class="card"
            style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #22c55e;">
            <div
                style="font-size: 35px; background: rgba(34, 197, 94, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 15px;">
                👥</div>
            <div>
                <div
                    style="font-size: 13px; color: var(--text3); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    Usuarios Registrados</div>
                <div style="font-size: 28px; font-weight: 900; color: var(--text);">{{ \App\Models\User::count() }}</div>
            </div>
        </div>

        <div class="card"
            style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid var(--accent);">
            <div
                style="font-size: 35px; background: rgba(29, 78, 216, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 15px;">
                📈</div>
            <div>
                <div
                    style="font-size: 13px; color: var(--text3); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    Empresas Activas</div>
                <div style="font-size: 28px; font-weight: 900; color: var(--text);">
                    {{ \App\Models\Empresa::where('activa', 1)->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Bienvenido, German Reyes</div>
        </div>
        <div class="card-body">
            <p style="color: var(--text2); line-height: 1.6;">
                Desde este panel maestro puedes monitorear todas las empresas de transporte en Huancayo que utilizan tu
                software.
                Recuerda que tienes el control total para habilitar o suspender servicios desde el módulo de <b>Empresas
                    Clientes</b>.
            </p>
        </div>
    </div>
@endsection
