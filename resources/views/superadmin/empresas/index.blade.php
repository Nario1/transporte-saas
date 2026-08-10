@extends('layouts.admin')

@php
    $pageTitle = 'Empresas Clientes';
    $pageSubtitle = 'Gestión y control de acceso para transportistas registrados';
@endphp

@section('content')
    <div class="panel">
        {{-- CABECERA DE ACCIÓN --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="user-av" style="background: var(--sidebar); color: var(--text-inv);">
                    <i class="fa-solid fa-city"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--text);">Empresas Clientes</h2>
                    <div style="font-size: 11px; color: var(--text3); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                        {{ $empresas->count() }} Registradas en el Sistema
                    </div>
                </div>
            </div>
            
            <a href="{{ route('superadmin.empresas.create') }}" class="btn-primary" style="padding: 0 25px; height: 50px; border-radius: 12px; font-weight: 800; background: var(--sidebar); display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-plus-circle"></i> NUEVA EMPRESA
            </a>
        </div>

        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">Lista de Empresas Registradas</div>
                <div style="font-size: 12px; font-weight: 700; color: var(--text3);">Total: {{ $empresas->count() }}</div>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Empresa / Datos</th>
                            <th>Plan</th>
                            <th>Tributo Diario</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: var(--text); font-size: 14px;">{{ $empresa->nombre }}
                                    </div>
                                    <div style="font-size: 11px; color: var(--text3); display: flex; gap: 10px;">
                                        <span>🆔 RUC: {{ $empresa->ruc ?? 'No definido' }}</span>
                                        <span>📅 {{ $empresa->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="pill"
                                        style="background: var(--bg); color: var(--text2); border: 1px solid var(--border); font-size: 10px; font-weight: 800; padding: 4px 10px;">
                                        {{ strtoupper($empresa->plan) }}
                                    </span>
                                </td>
                                <td style="font-weight: 800; color: var(--accent);">
                                    S/ {{ number_format($empresa->tributo_diario, 2) }}
                                </td>
                                <td>
                                    @if ($empresa->activa)
                                        <span
                                            style="color: #16a34a; font-weight: 900; font-size: 11px; display: flex; align-items: center; gap: 6px;">
                                            <span
                                                style="width: 8px; height: 8px; background: #16a34a; border-radius: 50%;"></span>
                                            ACTIVA
                                        </span>
                                    @else
                                        <span
                                            style="color: #dc2626; font-weight: 900; font-size: 11px; display: flex; align-items: center; gap: 6px;">
                                            <span
                                                style="width: 8px; height: 8px; background: #dc2626; border-radius: 50%;"></span>
                                            SUSPENDIDA
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                        {{-- Botón de Suspensión/Activación --}}
                                        <form action="{{ route('superadmin.empresas.toggle', $empresa->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="{{ $empresa->activa ? 'btn-danger' : 'btn-primary' }}"
                                                style="padding: 8px 15px; font-size: 11px; font-weight: 800; border-radius: 10px; min-width: 100px; display: inline-flex; justify-content: center; align-items: center; gap: 4px;">
                                                @if($empresa->activa)
                                                    <i class="fa-solid fa-ban"></i> Suspender
                                                @else
                                                    <i class="fa-solid fa-circle-check"></i> Activar
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Botón Editar --}}
                                        <a href="{{ route('superadmin.empresas.edit', $empresa->id) }}" class="btn-secondary"
                                            style="padding: 8px 12px; border-radius: 10px; text-decoration: none; display: inline-flex; justify-content: center; align-items: center;">
                                            <i class="fa-solid fa-gear"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
