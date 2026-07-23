@extends('layouts.admin')

@section('back_url', route('dashboard'))
@php
    $pageTitle = 'Ajustes';
    $pageSubtitle = 'Configuración de "' . (auth()->user()->empresa?->nombre ?? 'la empresa') . '"';
@endphp
@section('content')
    <div style="display: grid; gap: 25px;">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title"><i class="fa-solid fa-sliders"></i> Ajustes de Empresa</div>
                <a href="{{ route('ajustes.edit') }}" class="btn-primary"
                    style="padding: 10px 15px; text-decoration: none; font-size: 13px;">
                    <i class="fa-solid fa-pen-to-square"></i> Editar Ajustes
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div
                        style="background: #e6ffed; border: 1px solid #10b981; color: #10b981; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 30px; align-items: start;">
                    <div style="text-align: center;">
                        <div
                            style="width: 150px; height: 150px; border-radius: 10px; background: #f1f5f9; margin: 0 auto; display:flex; align-items:center; justify-content:center; overflow:hidden; border: 2px dashed #cbd5e1;">
                            @if ($empresa->logo_path)
                                <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo Empresa"
                                    style="width: 100%; height: 100%; object-fit: contain;">
                            @else
                                <span style="color: #94a3b8; font-size: 12px;">Sin Logo</span>
                            @endif
                        </div>
                        <h4 style="margin-top: 15px; color: var(--text1);">{{ $empresa->nombre }}</h4>
                        <span class="pill"
                            style="background: {{ $empresa->activa ? '#dcfce7' : '#fee2e2' }}; color: {{ $empresa->activa ? '#166534' : '#991b1b' }}">
                            {{ $empresa->activa ? 'ACTIVA' : 'INACTIVA' }}
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="font-size: 11px; color: var(--text3); text-transform: uppercase;">Razón
                                Social</label>
                            <div style="font-size: 15px; font-weight: 600;">
                                {{ $empresa->razon_social ?? 'No especificado' }}</div>
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--text3); text-transform: uppercase;">RUC</label>
                            <div style="font-size: 15px; font-weight: 600;">{{ $empresa->ruc }}</div>
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--text3); text-transform: uppercase;">Teléfono</label>
                            <div style="font-size: 15px; font-weight: 600;">{{ $empresa->telefono ?? 'No especificado' }}
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--text3); text-transform: uppercase;">Plan
                                Actual</label>
                            <div
                                style="font-size: 15px; font-weight: 600; text-transform: capitalize; color: var(--accent);">
                                {{ $empresa->plan }}</div>
                        </div>
                        <div style="grid-column: span 2;">
                            <label
                                style="font-size: 11px; color: var(--text3); text-transform: uppercase;">Dirección</label>
                            <div style="font-size: 15px; font-weight: 600;">{{ $empresa->direccion ?? 'No especificada' }}
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--text3); text-transform: uppercase;">Tributo Diario
                                Base</label>
                            <div style="font-size: 15px; font-weight: 800; color: #10b981;">S/
                                {{ number_format($empresa->tributo_diario, 2) }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
