@extends('layouts.app')

@section('body_content')
    <aside class="sb sb-modern">
        <div class="sb-brand sb-brand-modern">
            <div class="brand-logo brand-logo-modern">
                <div style="display: flex; align-items: center; gap: 12px; overflow: hidden;">
                    @php
                        $logo =
                            Auth::check() && Auth::user()->empresa && Auth::user()->empresa->logo_path
                                ? asset('storage/' . Auth::user()->empresa->logo_path)
                                : null;
                    @endphp

                    @if ($logo)
                        <img src="{{ $logo }}" alt="Logo"
                            style="width: 36px; height: 36px; object-fit: contain; border-radius: 9px; flex-shrink: 0; background: white; padding: 2px;">
                    @else
                        <div
                            class="brand-icon brand-icon-modern {{ auth()->user()->hasRole('SUPER_ADMIN') ? 'brand-icon-sa' : 'brand-icon-tj' }}">
                            {{ auth()->user()->hasRole('SUPER_ADMIN') ? 'SA' : 'TJ' }}
                        </div>
                    @endif

                    <div style="overflow: hidden;">
                        <div class="brand-name brand-name-modern">
                            {{ auth()->user()->hasRole('SUPER_ADMIN') ? 'Panel Maestro' : Auth::user()->empresa->nombre ?? 'TransJunín' }}
                        </div>
                        <div class="brand-sub brand-sub-modern">
                            {{ auth()->user()->hasRole('SUPER_ADMIN') ? 'Administración Global' : 'Gestión de Flota' }}
                        </div>
                    </div>
                </div>

                @unless (auth()->user()->hasRole('SUPER_ADMIN'))
                    @can('gestionar ajustes de empresa')
                        <a href="{{ route('ajustes.index') }}" title="Ajustes de Empresa" class="logout-btn-modern">
                            <i class="fa-solid fa-gear" style="font-size: 15px; color: #8e8e93;"></i>
                        </a>
                    @endcan
                @endunless
            </div>
        </div>

        <nav class="sb-nav">

            {{-- 1. SECCIÓN PRINCIPAL --}}
            <div class="nav-group">

                @role('SUPER_ADMIN')
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="nav-item nav-item-modern {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <span class="ni"><i class="fa-solid fa-layer-group"></i></span> Dashboard Maestro
                    </a>
                @else
                    @can('ver dashboard')
                        <a href="{{ route('dashboard') }}"
                            class="nav-item nav-item-modern {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <span class="ni"><i class="fa-solid fa-gauge-high"></i></span> Dashboard
                        </a>
                    @endcan
                @endrole
            </div>

            {{-- 2. SECCIÓN PARA EL SUPER ADMIN --}}
            @role('SUPER_ADMIN')
                <div class="nav-group">
                    <a href="{{ route('superadmin.empresas.index') }}"
                        class="nav-item nav-item-modern {{ request()->routeIs('superadmin.empresas.*') ? 'active' : '' }}">
                        <span class="ni"><i class="fa-solid fa-city"></i></span> Empresas
                    </a>
                    <a href="{{ route('superadmin.backups.index') }}"
                        class="nav-item nav-item-modern {{ request()->routeIs('superadmin.backups.*') ? 'active' : '' }}">
                        <span class="ni"><i class="fa-solid fa-database"></i></span> Backup Global
                    </a>
                    <a href="{{ route('superadmin.auditoria.index') }}"
                        class="nav-item nav-item-modern {{ request()->routeIs('superadmin.auditoria.*') ? 'active' : '' }}">
                        <span class="ni"><i class="fa-solid fa-clock-rotate-left"></i></span> Auditoría Global
                    </a>
                </div>
            @endrole

            {{-- 3. SECCIÓN OPERATIVA (Solo para Empresas) --}}
            @unless (auth()->user()->hasRole('SUPER_ADMIN'))
                @if (auth()->user()->can('ver vehiculos') ||
                        auth()->user()->can('ver conductores') ||
                        auth()->user()->can('ver propietarios') ||
                        auth()->user()->can('ver rutas'))
                    <div class="nav-group">
                        @can('ver propietarios')
                            <a href="{{ route('propietarios.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('propietarios.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-user-tie"></i></span> Propietarios
                            </a>
                        @endcan
                        @can('ver conductores')
                            <a href="{{ route('conductores.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('conductores.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-id-card-clip"></i></span> Conductores
                            </a>
                        @endcan
                        @can('ver vehiculos')
                            <a href="{{ route('vehiculos.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-bus"></i></span> Vehículos
                            </a>
                        @endcan

                        @can('ver rutas')
                            <a href="{{ route('rutas.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('rutas.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-route"></i></span> Rutas
                            </a>
                        @endcan
                    </div>
                @endif

                {{-- GRUPO: OPERACIÓN --}}
                @if (auth()->user()->can('ver vueltas') || auth()->user()->can('ver tributos') || auth()->user()->can('ver sanciones'))
                    <div class="nav-group">
                        @can('ver vueltas')
                            <a href="{{ route('vueltas.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('vueltas.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-arrows-spin"></i></span> Control de Vueltas
                            </a>
                        @endcan
                        @can('ver tributos')
                            <a href="{{ route('tributos.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('tributos.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-sack-dollar"></i></span> Tributos
                            </a>
                        @endcan
                        @can('ver sanciones')
                            <a href="{{ route('sanciones.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('sanciones.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-triangle-exclamation"></i></span> Sanciones
                            </a>
                        @endcan
                    </div>
                @endif

                {{-- GRUPO: REPORTES --}}
                @can('ver reportes')
                    <div class="nav-group">
                        <a href="{{ route('reportes.index') }}"
                            class="nav-item nav-item-modern {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                            <span class="ni"><i class="fa-solid fa-chart-line"></i></span> Reportes Generales
                        </a>
                    </div>
                @endcan

                {{-- GRUPO: CONFIGURACIÓN --}}
                @if (auth()->user()->can('gestionar usuarios') ||
                        auth()->user()->can('gestionar roles') ||
                        auth()->user()->can('gestionar ajustes de empresa') ||
                        auth()->user()->can('gestionar backups'))
                    <div class="nav-group">
                        @can('gestionar usuarios')
                            <a href="{{ route('users.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-users-gear"></i></span> Mi Personal
                            </a>
                        @endcan
                        @can('gestionar roles')
                            <a href="{{ route('roles.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-key"></i></span> Roles / Permisos
                            </a>
                        @endcan
                        @can('gestionar ajustes de empresa')
                            <a href="{{ route('ajustes.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('ajustes.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-sliders"></i></span> Ajustes de Empresa
                            </a>
                        @endcan
                        @can('gestionar backups')
                            <a href="{{ route('backups.index') }}"
                                class="nav-item nav-item-modern {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                                <span class="ni"><i class="fa-solid fa-database"></i></span> Copia de Seguridad
                            </a>
                        @endcan
                    </div>
                @endif
            @endunless
        </nav>
    </aside>

    <div class="main main-modern">
        <header class="topbar topbar-modern">
            <div class="tb-left" style="display: flex; align-items: center; gap: 16px;">
                <div>
                    <div class="tb-title tb-title-modern" id="topTitle">
                        {{ $pageTitle ?? 'TransJunín' }}
                    </div>
                    <div class="tb-crumb tb-crumb-modern" id="topCrumb">
                        {{ $pageSubtitle ?? 'Huancayo, Perú' }}
                    </div>
                </div>
            </div>

            <div class="tb-right-section">
                <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar Tema">
                    <i class="fa-solid fa-moon"></i>
                    <i class="fa-solid fa-sun"></i>
                </button>

                <div class="tb-date tb-date-modern">
                    <i class="fa-regular fa-calendar-days"></i>
                    {{ ucfirst(\Carbon\Carbon::now()->locale('es')->translatedFormat('D, d M Y')) }}
                </div>

                <div class="tb-user-wrap">
                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                        <div class="tb-user-name" style="line-height: 1.2;">
                            {{ Auth::user()->name }}
                        </div>
                        <div
                            style="font-size: 10px; font-weight: 800; color: var(--accent); opacity: 0.8; margin-top: 2px; text-transform: uppercase;">
                            {{ Auth::user()->roles_limpios }}
                        </div>
                    </div>

                    <div class="tb-avatar-btn" title="Cerrar Sesión"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>

                @yield('topbar_right_content')
            </div>
        </header>

        <div class="content content-modern panel-anim">
            @if (
                !auth()->user()->hasRole('SUPER_ADMIN') &&
                    auth()->user()->can('gestionar ajustes de empresa') &&
                    (auth()->user()->empresa->tributo_diario ?? 0) <= 0)
                <div
                    style="background: #fff4f2; border: 1px solid #ffccc7; color: #ff4d4f; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
                    <div
                        style="width: 44px; height: 44px; background: #ff4d4f; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; flex-shrink: 0;">
                        1</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 15px;">Paso 1: Configura el Tributo Diario</div>
                        <div style="font-size: 13px; opacity: 0.8;">Para habilitar todos los módulos, primero debes definir
                            el monto base de recaudación diaria.</div>
                    </div>
                    <a href="{{ route('ajustes.index') }}" class="btn-primary"
                        style="background: #ff4d4f; padding: 8px 15px; font-size: 12px; white-space: nowrap;">CONFIGURAR
                        AHORA</a>
                </div>
            @endif

            @hasSection('back_url')
                <a href="@yield('back_url')" class="btn-back-content">
                    <i class="fa-solid fa-arrow-left"></i> volver
                </a>
            @endif

            @yield('content')
        </div>
    </div>

    {{-- ALERTAS CON SWEETALERT2 --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('config_tutorial'))
                Swal.fire({
                    title: '¡Paso Inicial Requerido! 🚀',
                    html: `<div style="text-align: left; font-size: 14px; line-height: 1.6; color: var(--text2);">
                        ¡Bienvenido a <b>TransJunín</b>! Antes de registrar vehículos o conductores, el sistema necesita una configuración básica:
                        <br><br>
                        <i class="fa-solid fa-check-circle" style="color: var(--green);"></i> <b>Monto del Tributo Diario:</b> Es el cargo que se generará automáticamente a cada unidad.
                        <br><br>
                        Sin este dato no es posible activar los demás módulos para garantizar la integridad de tu recaudación.
                    </div>`,
                    icon: 'info',
                    confirmButtonText: 'Entendido, vamos a configurar',
                    confirmButtonColor: 'var(--accent)',
                    allowOutsideClick: false,
                    borderRadius: '20px',
                    background: 'var(--card)',
                    color: 'var(--text)'
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validación Incorrecta',
                    html: `<ul style="text-align: left; font-size: 13px; color: var(--text2);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>`,
                    borderRadius: '16px',
                    background: 'var(--card)',
                    color: 'var(--text)',
                    confirmButtonColor: 'var(--accent)'
                });
            @endif

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: "{!! addslashes(session('success')) !!}",
                    timer: 3000,
                    showConfirmButton: false,
                    borderRadius: '16px',
                    background: 'var(--card)',
                    color: 'var(--text)'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{!! addslashes(session('error')) !!}",
                    borderRadius: '16px',
                    background: 'var(--card)',
                    color: 'var(--text)'
                });
            @endif
        });
    </script>
    <style>
        @media print {

            .sb,
            .topbar,
            .btn-back-content,
            .no-print,
            .theme-toggle,
            .logout-btn-modern {
                display: none !important;
            }

            .main {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .content {
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
@endsection
