@extends('layouts.admin')

@section('back_url', route('dashboard'))
@php
    $pageTitle = 'Reportes';
    $pageSubtitle = 'Análisis mensual acumulado de "' . (auth()->user()->empresa?->nombre ?? 'la empresa') . '"';
@endphp
@section('content')
    <div style="display: grid; gap: 25px;">
        <div class="card-header" style="padding:0;">
            <h2 style="margin:0;">Centro de Reportes y Estadísticas</h2>
            <p style="color: var(--text3);">Análisis mensual acumulado de la empresa</p>
        </div>

        {{-- Resumen Rápido Mensual --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <div class="card" style="padding: 20px; border-top: 4px solid #10b981;">
                <div style="font-size: 11px; color: var(--text3); text-transform: uppercase; font-weight: 700;">Recaudación
                    Mes</div>
                <div style="font-size: 22px; font-weight: 800; color: #10b981;">S/
                    {{ number_format($resumen['tributos_mes'], 2) }}</div>
            </div>
            <div class="card" style="padding: 20px; border-top: 4px solid var(--accent);">
                <div style="font-size: 11px; color: var(--text3); text-transform: uppercase; font-weight: 700;">Vueltas Mes
                </div>
                <div style="font-size: 22px; font-weight: 800;">{{ number_format($resumen['vueltas_mes']) }}</div>
            </div>
            <div class="card" style="padding: 20px; border-top: 4px solid #f59e0b;">
                <div style="font-size: 11px; color: var(--text3); text-transform: uppercase; font-weight: 700;">Sanciones
                    Mes</div>
                <div style="font-size: 22px; font-weight: 800; color: #f59e0b;">S/
                    {{ number_format($resumen['sanciones_mes'], 2) }}</div>
            </div>
            <div class="card" style="padding: 20px; border-top: 4px solid #ef4444;">
                <div style="font-size: 11px; color: var(--text3); text-transform: uppercase; font-weight: 700;">Deuda en
                    Calle</div>
                <div style="font-size: 22px; font-weight: 800; color: #ef4444;">S/
                    {{ number_format($resumen['deuda_total'], 2) }}</div>
            </div>
        </div>

        {{-- Menú de Reportes --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <a href="{{ route('reportes.tributos') }}" class="card report-link"
                style="text-decoration: none; transition: transform 0.2s;">
                <div class="card-body" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 40px; margin-bottom: 15px; color: #10b981;"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div style="font-weight: 800; font-size: 18px; color: var(--text1);">Reporte de Tributos</div>
                    <p style="font-size: 13px; color: var(--text3);">Ingresos, métodos de pago y comparativas por día.</p>
                </div>
            </a>

            <a href="{{ route('reportes.vueltas') }}" class="card report-link" style="text-decoration: none;">
                <div class="card-body" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 40px; margin-bottom: 15px; color: var(--accent);"><i class="fa-solid fa-arrows-rotate"></i></div>
                    <div style="font-weight: 800; font-size: 18px; color: var(--text1);">Productividad (Vueltas)</div>
                    <p style="font-size: 13px; color: var(--text3);">Vueltas por ruta, por vehículo y control de tiempos.
                    </p>
                </div>
            </a>

            <a href="{{ route('reportes.deudas') }}" class="card report-link" style="text-decoration: none;">
                <div class="card-body" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 40px; margin-bottom: 15px; color: #ef4444;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div style="font-weight: 800; font-size: 18px; color: var(--text1);">Ranking de Deudas</div>
                    <p style="font-size: 13px; color: var(--text3);">Listado de unidades morosas y montos acumulados.</p>
                </div>
            </a>

            <a href="{{ route('reportes.sanciones') }}" class="card report-link" style="text-decoration: none;">
                <div class="card-body" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 40px; margin-bottom: 15px; color: #f59e0b;"><i class="fa-solid fa-file-invoice"></i></div>
                    <div style="font-weight: 800; font-size: 18px; color: var(--text1);">Control de Sanciones</div>
                    <p style="font-size: 13px; color: var(--text3);">Infracciones más comunes y conductores amonestados.</p>
                </div>
            </a>

            <a href="{{ route('reportes.documentos') }}" class="card report-link" style="text-decoration: none;">
                <div class="card-body" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 40px; margin-bottom: 15px; color: var(--text3);"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div style="font-weight: 800; font-size: 18px; color: var(--text1);">Documentos y Vencimientos</div>
                    <p style="font-size: 13px; color: var(--text3);">Control de SOAT, Rev. Técnica y Licencias.</p>
                </div>
            </a>
        </div>
    </div>

    <style>
        .report-link:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }
    </style>
@endsection
