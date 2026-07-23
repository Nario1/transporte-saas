@extends('layouts.conductor')
@section('title', 'Mi Unidad')

@section('content')

    {{-- Hero - Centrado en el Vehículo --}}
    @php $vehiculo = $conductor->vehiculos->first(); @endphp
    <div class="conductor-hero"
        style="flex-direction:column; text-align:center; padding:28px 20px; background:linear-gradient(135deg, var(--gold) 0%, #92400e 100%); color:white; border-bottom:none;">
        <div class="conductor-av"
            style="width:72px; height:72px; font-size:28px; margin:0 auto 12px; background:rgba(255,255,255,0.1); border:2px solid rgba(255,255,255,0.2); box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <i class="fa-solid fa-bus"></i>
        </div>
        <div class="conductor-hero-name" style="font-size:20px; font-weight:800; letter-spacing:-0.02em;">
            {{ $vehiculo?->placa_form ?? 'Sin Placa' }}</div>
        <div class="conductor-hero-sub" style="opacity:0.8; font-size:13px; margin-top:4px;">
            Unidad {{ $vehiculo?->numero_flota ?? 'S/N' }} · {{ $conductor->empresa?->nombre ?? 'Transporte SaaS' }}</div>
    </div>

    <div style="margin-top:-20px; padding:0 16px;">

        {{-- 1. Datos de la Unidad --}}
        @if ($vehiculo)
            <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9;">
                    <span class="card-title" style="font-size:14px; color:#64748b;">🚌 Especificaciones de la Unidad</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Padrón / Nro. Flota</span>
                        <span style="font-weight:800; color:#2563eb;">#{{ $vehiculo->numero_flota ?? '???' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Marca / Modelo</span>
                        <span style="font-weight:600; color:#1e293b;">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Color</span>
                        <span style="font-weight:600;">{{ $vehiculo->color ?? '—' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Número de Motor</span>
                        <span class="mono" style="font-size: 11px;">{{ $vehiculo->numero_motor ?? '—' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Número de Chasis</span>
                        <span class="mono" style="font-size: 11px;">{{ $vehiculo->numero_chasis ?? '—' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px;">
                        <span class="summary-label" style="font-weight:500;">Ruta Asignada</span>
                        <span style="font-weight:800; color: #16a34a;">
                            {{ $vehiculo->rutas->where('pivot.activo', true)->first()?->nombre ?? 'Sin ruta' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. Documentos del Vehículo (Vencimientos) --}}
            <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9;">
                    <span class="card-title" style="font-size:14px; color:#64748b;">📄 Documentación</span>
                </div>
                <div class="card-body" style="padding:0;">
                    @php $hoy = now(); @endphp

                    {{-- SOAT --}}
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">SOAT</span>
                        @if ($vehiculo->soat_vence)
                            @php
                                $colorSoat = $vehiculo->soat_vence->isPast() ? 'var(--red)' : ($vehiculo->soat_vence->diffInDays($hoy) <= 15 ? 'var(--orange)' : 'var(--green)');
                            @endphp
                            <span style="font-weight:700; color:{{ $colorSoat }};">
                                {{ $vehiculo->soat_vence->format('d/m/Y') }}
                                @if($vehiculo->soat_vence->isPast()) (Vencido) @endif
                            </span>
                        @else
                            <span style="color:var(--text3);">—</span>
                        @endif
                    </div>

                    {{-- Rev. Técnica --}}
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Revisión Técnica</span>
                        @if ($vehiculo->rev_tecnica_vence)
                            @php
                                $colorRev = $vehiculo->rev_tecnica_vence->isPast() ? 'var(--red)' : ($vehiculo->rev_tecnica_vence->diffInDays($hoy) <= 15 ? 'var(--orange)' : 'var(--green)');
                            @endphp
                            <span style="font-weight:700; color:{{ $colorRev }};">
                                {{ $vehiculo->rev_tecnica_vence->format('d/m/Y') }}
                                @if($vehiculo->rev_tecnica_vence->isPast()) (Vencida) @endif
                            </span>
                        @else
                            <span style="color:var(--text3);">—</span>
                        @endif
                    </div>

                    {{-- Tarjeta Propiedad --}}
                    <div class="summary-row" style="padding:14px 16px;">
                        <span class="summary-label" style="font-weight:500;">Tarjeta Propiedad</span>
                        @if ($vehiculo->tarjeta_prop_vence)
                            <span style="font-weight:600;">{{ $vehiculo->tarjeta_prop_vence->format('d/m/Y') }}</span>
                        @else
                            <span style="color:var(--text3);">—</span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="card" style="border:2px dashed #e2e8f0; background:transparent; text-align:center; padding:30px 20px;">
                <div style="font-size:32px; margin-bottom:10px; filter:grayscale(1);">🚗</div>
                <div style="font-size:14px; color:#64748b; font-weight:600;">Sin vehículo asignado</div>
                <div style="font-size:12px; color:#94a3b8; margin-top:4px;">No hay una unidad vinculada a esta cuenta.</div>
            </div>
        @endif

        {{-- 3. Personal Asignado (El Conductor de esta cuenta) --}}
        <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9;">
                <span class="card-title" style="font-size:14px; color:#64748b;">👤 Personal de Conducción</span>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                    <span class="summary-label" style="font-weight:500;">Nombre</span>
                    <span style="font-weight:600; color:#1e293b;">{{ $conductor->nombre_completo }}</span>
                </div>
                <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                    <span class="summary-label" style="font-weight:500;">DNI</span>
                    <span class="mono" style="font-weight:600;">{{ $conductor->dni ?? '—' }}</span>
                </div>
                <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                    <span class="summary-label" style="font-weight:500;">Licencia</span>
                    <span class="pill blue" style="font-weight:700;">{{ $conductor->tipo_licencia }}</span>
                </div>
                <div class="summary-row" style="padding:14px 16px;">
                    <span class="summary-label" style="font-weight:500;">Teléfono</span>
                    <span style="font-weight:600;">{{ $conductor->telefono ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- 4. Propietario / Socio --}}
        @if ($vehiculo && $vehiculo->propietario)
            <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9;">
                    <span class="card-title" style="font-size:14px; color:#64748b;">🤝 Propietario Responsable</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc;">
                        <span class="summary-label" style="font-weight:500;">Socio</span>
                        <span style="font-weight:700; color:#1e293b;">{{ $vehiculo->propietario->nombre_completo }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px;">
                        <span class="summary-label" style="font-weight:500;">Contacto</span>
                        <span style="font-weight:600; color:#2563eb;">{{ $vehiculo->propietario->telefono ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Acciones --}}
        <div style="display:flex; flex-direction:column; gap:12px; margin-top:20px; margin-bottom:30px;">
            <a href="{{ route('conductor.cambiar-password') }}" class="btn btn-secondary btn-block"
                style="justify-content:center; padding:14px; font-weight:600; border-radius:12px;">
                🔑 Gestionar Clave
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-block"
                    style="justify-content:center; padding:14px; font-weight:600; border-radius:12px; background:#ef4444;">
                    🚪 Cerrar Sesión de Unidad
                </button>
            </form>
        </div>

    </div>

@endsection
