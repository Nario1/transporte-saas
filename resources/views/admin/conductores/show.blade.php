@extends('layouts.admin')

@php
    $pageTitle = 'Expediente del Conductor';
    $pageSubtitle = "{$conductor->nombre} {$conductor->apellidos}";
@endphp

@section('back_url', route('conductores.index'))

@section('content')
    <div class="panel">
        
        {{-- 1. CABECERA DE PERFIL --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="user-av" style="width: 60px; height: 60px; font-size: 24px; border-radius: 16px;">
                    {{ strtoupper(substr($conductor->nombre, 0, 1) . substr($conductor->apellidos, 0, 1)) }}
                </div>
                <div>
                    <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ $conductor->nombre }} {{ $conductor->apellidos }}</h2>
                    <div class="flex-h" style="gap: 10px;">
                        <span class="pill {{ $conductor->estado === 'activo' ? 'green' : ($conductor->estado === 'suspendido' ? 'orange' : 'red') }}">
                            {{ strtoupper($conductor->estado) }}
                        </span>
                        <span style="font-size: 13px; color: var(--text3);">DNI: {{ $conductor->dni }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-h">
                <a href="{{ route('conductores.edit', $conductor->id) }}" class="btn-primary">
                    <i class="fa-solid fa-user-pen"></i> Editar Expediente
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
                        <div class="card-title">Información Personal y de Contacto</div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="tbl">
                            <tbody>
                                @if($conductor->dni)
                                    <tr>
                                        <td style="width: 220px; color: var(--text3); font-weight: 600;">Documento de Identidad</td>
                                        <td><span class="mono">{{ $conductor->dni }}</span></td>
                                    </tr>
                                @endif
                                @if($conductor->telefono)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Teléfono Móvil</td>
                                        <td>
                                            <a href="tel:{{ $conductor->telefono }}" style="text-decoration: none; color: var(--text); font-weight: 700;">
                                                <i class="fa-solid fa-phone" style="font-size: 12px; color: var(--green); margin-right: 5px;"></i>
                                                {{ $conductor->telefono }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                @if($conductor->email)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Correo Electrónico</td>
                                        <td>{{ $conductor->email }}</td>
                                    </tr>
                                @endif
                                @if($conductor->direccion)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Dirección de Domicilio</td>
                                        <td>{{ $conductor->direccion }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Documentación y Capacitaciones --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Licencia y Documentación</div>
                    </div>
                    <div class="card-body">
                        <div class="g-3">
                            @if($conductor->tipo_licencia)
                                <div class="field">
                                    <label>Categoría de Licencia</label>
                                    <div class="pill blue" style="justify-content: center; font-size: 14px;">
                                        Cat. {{ $conductor->tipo_licencia }}
                                    </div>
                                </div>
                            @endif
                            
                            @if($conductor->licencia_vence)
                                @php
                                    $licVence = \Carbon\Carbon::parse($conductor->licencia_vence);
                                    $licDiff = now()->diffInDays($licVence, false);
                                    $licState = $licVence->isPast() ? 'date-expired' : ($licDiff < 30 ? 'date-warning' : 'date-valid');
                                @endphp
                                
                                <div class="field">
                                    <label>Vencimiento Licencia</label>
                                    <div class="{{ $licState }}" style="font-size: 16px;">
                                        {{ $licVence->format('d/m/Y') }}
                                    </div>
                                    <div style="font-size: 11px; color: var(--text3); margin-top: 2px;">
                                        {{ $licDiff < 0 ? '⚠️ Vencida hace ' . abs($licDiff) . ' días' : 'Vence en ' . $licDiff . ' días' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Vinculaciones --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Vinculación con la Empresa</div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="tbl">
                            <tbody>
                                <tr>
                                    <td style="width: 220px; color: var(--text3); font-weight: 600;">Propietario Responsable</td>
                                    <td>
                                        @if ($conductor->propietario)
                                            <div class="flex-h" style="gap: 10px;">
                                                <a href="{{ route('propietarios.show', $conductor->propietario_id) }}" class="flex-h" style="text-decoration: none; color: var(--accent);">
                                                    <i class="fa-solid fa-user-tie"></i>
                                                    <span style="font-weight: 700;">{{ $conductor->propietario->nombre }} {{ $conductor->propietario->apellidos }}</span>
                                                </a>
                                                @if($conductor->dni === $conductor->propietario->dni)
                                                    <span class="pill gold" style="font-size: 10px;">PROPIETARIO</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="pill red">Sin Propietario Asignado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 600;">Vehículo de Operación</td>
                                    <td>
                                        @if($conductor->vehiculos->first())
                                            <a href="{{ route('vehiculos.show', $conductor->vehiculos->first()->id) }}" class="flex-h" style="text-decoration: none; color: var(--accent);">
                                                <i class="fa-solid fa-bus"></i>
                                                <span style="font-weight: 800; color: var(--accent);">#{{ $conductor->vehiculos->first()->numero_flota }}</span>
                                                <span class="mono" style="font-size: 12px; margin-left: 5px;">{{ $conductor->vehiculos->first()->placa }}</span>
                                            </a>
                                        @else
                                            <span class="pill orange">Sin Vehículo Asignado</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notas / Observaciones --}}
                @if($conductor->notas)
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Observaciones Adicionales</div>
                        </div>
                        <div class="card-body">
                            <div style="font-size: 13px; color: var(--text2); line-height: 1.6; background: var(--bg); padding: 15px; border-radius: 12px; border: 1px dashed var(--border);">
                                {{ $conductor->notas }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- COLUMNA LATERAL (DERECHA) --}}
            <aside class="flex-v" style="gap: 24px;">
                
                {{-- Gestión de Acceso App --}}
                <div class="card" style="border-top: 4px solid var(--accent);">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-mobile-screen-button"></i> Acceso App Conductor</div>
                    </div>
                    <div class="card-body">
                        @if ($conductor->user)
                            <div class="flex-v" style="gap: 15px;">
                                <div class="field">
                                    <label>Usuario / Placa</label>
                                    <div class="mono" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; background: var(--bg);">
                                        {{ $conductor->user->email }}
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <form method="POST" action="{{ route('conductores.acceso.toggle', $conductor->id) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary {{ $conductor->user->activo ? 'text-red' : 'text-green' }}" style="width: 100%; justify-content: center; font-size: 13px;">
                                            @if($conductor->user->activo)
                                                <i class="fa-solid fa-power-off"></i> Suspender
                                            @else
                                                <i class="fa-solid fa-circle-play"></i> Activar
                                            @endif
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('conductores.acceso.reset', $conductor->id) }}" onsubmit="return confirm('¿Sincronizar acceso y clave con la PLACA actual del vehículo?')">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="width: 100%; justify-content: center; font-size: 13px;">
                                            <i class="fa-solid fa-rotate"></i> Reset / Sinc.
                                        </button>
                                    </form>
                                </div>

                                <div style="margin-top: 10px; padding-top: 15px; border-top: 1px dashed var(--border);">
                                    <form method="POST" action="{{ route('conductores.acceso.destroy', $conductor->id) }}" onsubmit="return confirm('¿ELIMINAR CREDENCIALES? Esta acción no se puede deshacer.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm" style="width: 100%; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fa-solid fa-user-slash"></i> Eliminar Usuario de App
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex-v" style="gap: 15px;">
                                <div style="font-size: 13px; color: var(--text3); text-align: center;">
                                    Este conductor aún no tiene acceso a la aplicación móvil.
                                </div>
                                <div style="background: var(--bg); padding: 15px; border-radius: 12px; border: 1px dashed var(--border); margin-bottom: 15px;">
                                    <div style="font-size: 12px; font-weight: 700; color: var(--text); margin-bottom: 5px;">Configuración Automática</div>
                                    <div style="font-size: 11px; color: var(--text3); line-height: 1.4;">
                                        Se utilizará la <strong>PLACA</strong> del vehículo asignado como nombre de usuario y contraseña inicial.
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('conductores.acceso.store', $conductor->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                                        <i class="fa-solid fa-plus-circle"></i> Habilitar Acceso con Placa
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Seguridad Biométrica --}}
                <div class="card" style="border-top: 4px solid var(--green);">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-face-smile"></i> Seguridad Biométrica</div>
                    </div>
                    <div class="card-body">
                        <div class="flex-v" style="gap: 15px;">
                            <div class="flex-between">
                                <span style="font-size: 13px; font-weight: 700; color: var(--text2);">Estado de Registro</span>
                                @if($conductor->rostro)
                                    <span class="pill green" style="font-size: 11px;">REGISTRADO</span>
                                @else
                                    <span class="pill red" style="font-size: 11px;">SIN REGISTRO</span>
                                @endif
                            </div>

                            <div style="padding-top: 15px; border-top: 1px dashed var(--border);">
                                <div class="flex-between" style="align-items: center;">
                                    <div>
                                        <div style="font-size: 13px; font-weight: 800; color: var(--text);">Requerir Facial</div>
                                        <div style="font-size: 11px; color: var(--text3);">Exigir rostro al iniciar vuelta</div>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="toggle-facial" {{ $conductor->requiere_facial ? 'checked' : '' }} onchange="toggleFacial({{ $conductor->id }})">
                                        <label for="toggle-facial"></label>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('conductores.rostro.show', $conductor->id) }}" class="btn-secondary" style="width: 100%; justify-content: center; font-size: 13px;">
                                <i class="fa-solid fa-camera"></i> {{ $conductor->rostro ? 'Actualizar Rostro' : 'Registrar Rostro Ahora' }}
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script>
        function toggleFacial(conductorId) {
            const checkbox = document.getElementById('toggle-facial');
            const status = checkbox.checked;

            fetch(`{{ url('admin/conductores') }}/${conductorId}/toggle-facial`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ requiere_facial: status })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.ok) {
                    alert('Error al actualizar: ' + data.error);
                    checkbox.checked = !status;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
                checkbox.checked = !status;
            });
        }
    </script>

    <style>
        .toggle-switch { position: relative; width: 44px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-switch label {
            position: absolute; cursor: pointer; inset: 0;
            background-color: #cbd5e1; border-radius: 34px; transition: .3s;
        }
        .toggle-switch label:before {
            position: absolute; content: ""; height: 18px; width: 18px;
            left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: .3s;
        }
        .toggle-switch input:checked + label { background-color: var(--accent); }
        .toggle-switch input:checked + label:before { transform: translateX(20px); }
    </style>
@endsection
