@extends('layouts.admin')

@php
    $pageTitle = 'Ficha del Propietario';
    $pageSubtitle = "{$propietario->nombre} {$propietario->apellidos}";
@endphp

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel">
        
        {{-- 1. CABECERA CON ACCIONES --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="user-av" style="width: 60px; height: 60px; font-size: 24px; border-radius: 16px; background: var(--sidebar); color: var(--text-inv);">
                    {{ strtoupper(substr($propietario->nombre, 0, 1) . substr($propietario->apellidos, 0, 1)) }}
                </div>
                <div>
                    <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ $propietario->nombre }} {{ $propietario->apellidos }}</h2>
                    <div class="flex-h" style="gap: 10px;">
                        <span class="pill {{ $propietario->activo ? 'green' : 'red' }}">
                            {{ $propietario->activo ? 'VIGENTE' : 'INACTIVO' }}
                        </span>
                        @if($propietario->conductor)
                            <span class="pill gold" style="font-size: 11px; font-weight: 800;">
                                <i class="fa-solid fa-id-card"></i> SOCIO-CONDUCTOR
                            </span>
                        @endif
                        <span style="font-size: 13px; color: var(--text3);">Socio ID: #{{ $propietario->id }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-h">
                <a href="{{ route('propietarios.edit', $propietario->id) }}" class="btn-primary">
                    <i class="fa-solid fa-user-pen"></i> Editar Perfil
                </a>
            </div>
        </div>

        {{-- 2. CUERPO EN DOS COLUMNAS --}}
        <div class="g-2-1">
            
            {{-- COLUMNA PRINCIPAL (IZQUIERDA) --}}
            <div class="flex-v" style="gap: 24px;">
                
                {{-- Información Personal --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Datos Personales y Legales</div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="tbl">
                            <tbody>
                                @if($propietario->dni)
                                    <tr>
                                        <td style="width: 220px; color: var(--text3); font-weight: 600;">DNI / RUC</td>
                                        <td><span class="mono">{{ $propietario->dni }}</span></td>
                                    </tr>
                                @endif
                                @if($propietario->telefono)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Teléfono de Contacto</td>
                                        <td>
                                            <a href="tel:{{ $propietario->telefono }}" style="text-decoration: none; color: var(--text); font-weight: 700;">
                                                <i class="fa-solid fa-phone" style="font-size: 12px; color: var(--green); margin-right: 5px;"></i>
                                                {{ $propietario->telefono }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                @if($propietario->direccion)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Domicilio Fiscal</td>
                                        <td>{{ $propietario->direccion }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tabla de Vehículos Asociados --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Unidades de Transporte Asociadas</div>
                        <span class="pill blue" style="font-size: 11px;">{{ $propietario->vehiculos->count() }} Vehículos</span>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Unidad</th>
                                    <th>Marca / Modelo</th>
                                    <th>Año</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($propietario->vehiculos as $v)
                                    <tr>
                                        <td>
                                            <div class="flex-v" style="gap:2px;">
                                                <div style="font-weight: 800; color: var(--accent);">#{{ $v->numero_flota }}</div>
                                                <div class="mono" style="font-size: 11px;">{{ $v->placa }}</div>
                                            </div>
                                        </td>
                                        <td><div style="font-size: 13px; font-weight: 600;">{{ $v->marca }} {{ $v->modelo }}</div></td>
                                        <td><div class="mono" style="font-size: 12px;">{{ $v->anio }}</div></td>
                                        <td>
                                            <span class="pill {{ $v->estado === 'activo' ? 'green' : 'orange' }}" style="font-size: 10px;">
                                                {{ strtoupper($v->estado) }}
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('vehiculos.show', $v->id) }}" class="action-icon show-icon"><i class="fa-solid fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text3);">
                                            <i class="fa-solid fa-bus" style="font-size: 32px; opacity: 0.1; display: block; margin-bottom: 10px;"></i>
                                            Este socio no tiene vehículos vinculados actualmente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- COLUMNA LATERAL (DERECHA) --}}
            <aside class="flex-v" style="gap: 24px;">
                
                {{-- Resumen Operativo --}}
                <div class="stat blue" style="padding: 24px;">
                    <div class="stat-label">Capacidad de Flota</div>
                    <div class="stat-val" style="font-size: 32px; margin-top: 10px;">{{ $propietario->vehiculos->count() }}</div>
                    <div class="stat-sub">Vehículos en operación</div>
                    <div class="stat-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
                </div>

                {{-- Acceso a Perfil de Conductor o Habilitación --}}
                @if($propietario->conductor)
                    <div class="card" style="border-left: 4px solid var(--gold);">
                        <div class="card-body flex-v" style="gap: 12px;">
                            <div style="font-weight: 800; font-size: 13px;">Perfil de Conducción Activo</div>
                            <div style="font-size: 11px; color: var(--text3);">Este socio opera unidades en el sistema.</div>
                            <a href="{{ route('conductores.show', $propietario->conductor->id) }}" class="btn-primary btn-sm" style="justify-content: center; background: var(--gold); border: none;">
                                <i class="fa-solid fa-address-card"></i> Ver Historial de Conductor
                            </a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
