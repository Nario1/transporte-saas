@extends('layouts.conductor')
@section('title', 'Mi Flota')

@section('content')

    {{-- Hero - Centrado en el Vehículo --}}
    @php $vehiculo = $conductor->vehiculos->first(); @endphp
    <div class="conductor-hero"
        style="flex-direction:column; text-align:center; padding:28px 20px; background:linear-gradient(135deg, var(--gold) 0%, #92400e 100%); color:white; border-bottom:none;">
        <div class="conductor-av"
            style="width:72px; height:72px; font-size:28px; margin:0 auto 12px; background:rgba(255,255,255,0.1); border:2px solid rgba(255,255,255,0.2); box-shadow:0 4px 12px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-car"></i>
        </div>
        <div class="conductor-hero-name" style="font-size:20px; font-weight:800; letter-spacing:-0.02em;">
            {{ $vehiculo?->placa_form ?? 'Sin Placa' }}</div>
        <div class="conductor-hero-sub" style="opacity:0.8; font-size:13px; margin-top:4px;">
            Flota {{ $vehiculo?->numero_flota ?? 'S/N' }} · {{ $conductor->empresa?->nombre ?? 'Transporte SaaS' }}</div>
    </div>

    <div style="margin-top:-20px; padding:0 16px;">
        <form action="{{ route('conductor.perfil.update') }}" method="POST">
            @csrf
            @method('PUT')

        {{-- 1. Datos de la Flota --}}
        @if ($vehiculo)
            <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9; padding: 14px 16px; display: flex; align-items: center;">
                    <span class="card-title" style="font-size:14px; color:#64748b; font-weight: 700;"><i class="fa-solid fa-car" style="margin-right: 5px;"></i> Especificaciones de la Flota</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Padrón / Nro. Flota</span>
                        <span style="font-weight:800; color:#2563eb;">#{{ $vehiculo->numero_flota ?? '???' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Marca / Modelo</span>
                        <span style="font-weight:600; color:#1e293b;">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Color</span>
                        <span style="font-weight:600; color:#1e293b;">{{ $vehiculo->color ?? '—' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Número de Motor</span>
                        <span class="mono" style="font-size: 11px; font-weight:600; color:#1e293b;">{{ $vehiculo->numero_motor ?? '—' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Número de Chasis</span>
                        <span class="mono" style="font-size: 11px; font-weight:600; color:#1e293b;">{{ $vehiculo->numero_chasis ?? '—' }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Ruta Asignada</span>
                        <span style="font-weight:800; color: #16a34a;">
                            {{ $vehiculo->rutas->where('pivot.activo', true)->first()?->nombre ?? 'Sin ruta' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. Documentos del Vehículo (Vencimientos) --}}
            <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9; padding: 14px 16px; display: flex; align-items: center;">
                    <span class="card-title" style="font-size:14px; color:#64748b; font-weight: 700;"><i class="fa-solid fa-file-invoice" style="margin-right: 5px;"></i> Documentación</span>
                </div>
                <div class="card-body" style="padding:0;">
                    @php $hoy = now(); @endphp

                    {{-- SOAT --}}
                    <div class="summary-row" style="padding:10px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">SOAT</span>
                        <input type="date" name="soat_vence" value="{{ old('soat_vence', $vehiculo->soat_vence ? $vehiculo->soat_vence->toDateString() : '') }}" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                    </div>

                    {{-- Rev. Técnica --}}
                    <div class="summary-row" style="padding:10px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Revisión Técnica</span>
                        <input type="date" name="rev_tecnica_vence" value="{{ old('rev_tecnica_vence', $vehiculo->rev_tecnica_vence ? $vehiculo->rev_tecnica_vence->toDateString() : '') }}" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                    </div>

                    {{-- Tarjeta Propiedad --}}
                    <div class="summary-row" style="padding:14px 16px; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Tarjeta Propiedad</span>
                        @if ($vehiculo->tarjeta_prop_vence)
                            <span style="font-weight:600; color:#1e293b;">{{ $vehiculo->tarjeta_prop_vence->format('d/m/Y') }}</span>
                        @else
                            <span style="color:var(--text3);">—</span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="card" style="border:2px dashed #e2e8f0; background:transparent; text-align:center; padding:30px 20px;">
                <div style="font-size:32px; margin-bottom:10px; color: var(--text3);"><i class="fa-solid fa-car"></i></div>
                <div style="font-size:14px; color:#64748b; font-weight:600;">Sin flota asignada</div>
                <div style="font-size:12px; color:#94a3b8; margin-top:4px;">No hay una flota vinculada a esta cuenta.</div>
            </div>
        @endif

        {{-- 3. Personal Asignado (El Conductor de esta cuenta) --}}
        <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9; padding: 14px 16px; display: flex; align-items: center;">
                <span class="card-title" style="font-size:14px; color:#64748b; font-weight: 700;"><i class="fa-solid fa-user" style="margin-right: 5px;"></i> Personal de Conducción</span>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">Nombre</span>
                    <span style="font-weight:600; color:#1e293b;">{{ $conductor->nombre_completo }}</span>
                </div>
                <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">DNI</span>
                    <span class="mono" style="font-weight:600; color:#1e293b;">{{ $conductor->dni ?? '—' }}</span>
                </div>
                <div class="summary-row" style="padding:10px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">Licencia</span>
                    <input type="text" name="tipo_licencia" value="{{ old('tipo_licencia', $conductor->tipo_licencia) }}" placeholder="Ej: A1" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white; text-align: right; width: 150px;">
                </div>
                <div class="summary-row" style="padding:10px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">Vence Licencia</span>
                    <input type="date" name="licencia_vence" value="{{ old('licencia_vence', $conductor->licencia_vence ? $conductor->licencia_vence->toDateString() : '') }}" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                </div>
                <div class="summary-row" style="padding:10px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">Carnet Habilitación</span>
                    <input type="text" name="carnet_habilitacion_tipo" value="{{ old('carnet_habilitacion_tipo', $conductor->carnet_habilitacion_tipo) }}" placeholder="Ej: A" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white; text-align: right; width: 150px;">
                </div>
                <div class="summary-row" style="padding:10px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">Vence Carnet Hab.</span>
                    <input type="date" name="carnet_habilitacion_vence" value="{{ old('carnet_habilitacion_vence', $conductor->carnet_habilitacion_vence ? \Carbon\Carbon::parse($conductor->carnet_habilitacion_vence)->toDateString() : '') }}" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                </div>
                <div class="summary-row" style="padding:10px 16px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <span class="summary-label" style="font-weight:500; color: var(--text2);">Teléfono</span>
                    <input type="text" name="telefono" value="{{ old('telefono', $conductor->telefono) }}" placeholder="Ej: 987654321" style="border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white; text-align: right; width: 150px;">
                </div>
            </div>
        </div>

        {{-- 4. Propietario / Socio --}}
        @if ($vehiculo && $vehiculo->propietario)
            <div class="card" style="box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid #f1f5f9; padding: 14px 16px; display: flex; align-items: center;">
                    <span class="card-title" style="font-size:14px; color:#64748b; font-weight: 700;"><i class="fa-solid fa-handshake" style="margin-right: 5px;"></i> Propietario Responsable</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="summary-row" style="padding:14px 16px; border-bottom:1px solid #f8fafc; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Socio</span>
                        <span style="font-weight:700; color:#1e293b;">{{ $vehiculo->propietario->nombre_completo }}</span>
                    </div>
                    <div class="summary-row" style="padding:14px 16px; display: flex; justify-content: space-between;">
                        <span class="summary-label" style="font-weight:500; color: var(--text2);">Contacto</span>
                        <span style="font-weight:600; color:#2563eb;">{{ $vehiculo->propietario->telefono ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Acciones --}}
        <div style="display:flex; flex-direction:column; gap:12px; margin-top:20px; margin-bottom:30px;">
            <button type="submit" class="btn btn-primary btn-block"
                style="justify-content:center; padding:14px; font-weight:700; border-radius:12px; display: flex; align-items: center; background: #2563eb; color: white; border: none; cursor: pointer;">
                <i class="fa-solid fa-save" style="margin-right: 5px;"></i> Guardar Cambios de Perfil
            </button>
            <a href="{{ route('conductor.cambiar-password') }}" class="btn btn-secondary btn-block"
                style="justify-content:center; padding:14px; font-weight:600; border-radius:12px; display: flex; align-items: center; text-decoration: none;">
                <i class="fa-solid fa-key" style="margin-right: 5px;"></i> Gestionar Clave
            </a>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-block"
                    style="justify-content:center; padding:14px; font-weight:600; border-radius:12px; background:#ef4444; color: white; border: none; width: 100%; display: flex; align-items: center; cursor: pointer;">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="margin-right: 5px;"></i> Cerrar Sesión de Flota
                </button>
            </form>
        </div>

    </div>

@endsection
