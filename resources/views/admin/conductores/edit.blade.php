@extends('layouts.admin')

@section('back_url', route('conductores.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 900px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Editar Conductor: {{ $conductor->nombre }}</div>
            </div>

            <div class="card-body">
                {{-- BLOQUE PARA ERRORES DE VALIDACIÓN --}}
                @if ($errors->any())
                    <div class="alert warning">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('conductores.update', $conductor->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        {{-- NOMBRE --}}
                        <div class="field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre"
                                value="{{ old('nombre', $conductor->nombre) }}" required pattern="[A-Za-zÀ-ÿ\s]{2,60}"
                                placeholder="Ej. Carlos Alberto">
                            @error('nombre')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- APELLIDOS --}}
                        <div class="field">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos"
                                value="{{ old('apellidos', $conductor->apellidos) }}" pattern="[A-Za-zÀ-ÿ\s]{2,60}"
                                placeholder="Ej. Ramos Castillo">
                            @error('apellidos')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- DNI CON BLOQUEO --}}
                        <div class="field">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" value="{{ old('dni', $conductor->dni) }}"
                                maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                placeholder="8 dígitos">
                            @error('dni')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- TELÉFONO CON BLOQUEO --}}
                        <div class="field">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono"
                                value="{{ old('telefono', $conductor->telefono) }}" maxlength="9"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                                placeholder="9 dígitos">
                            @error('telefono')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- EMAIL --}}
                        <div class="field">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $conductor->email) }}"
                                placeholder="ejemplo@correo.com">
                            @error('email')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- PROPIETARIO ASIGNADO --}}
                        <div class="field">
                            <label for="propietario_id">Propietario Asignado</label>
                            <select name="propietario_id" id="propietario_id">
                                <option value="">-- Seleccionar Propietario --</option>
                                @foreach ($propietarios as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('propietario_id', $conductor->propietario_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre }} {{ $p->apellidos }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TIPO DE LICENCIA --}}
                        <div class="field">
                            <label for="tipo_licencia">Tipo de Licencia</label>
                            <input type="text" id="tipo_licencia" name="tipo_licencia"
                                value="{{ old('tipo_licencia', $conductor->tipo_licencia) }}" placeholder="Ej: AIIB, AIIIC"
                                required>
                        </div>

                        {{-- VENCIMIENTO LICENCIA (CORREGIDO CON FORMATO YYYY-MM-DD) --}}
                        <div class="field">
                            <label for="licencia_vence">Vencimiento de Licencia</label>
                            <input type="date" id="licencia_vence" name="licencia_vence"
                                value="{{ old('licencia_vence', $conductor->licencia_vence ? \Carbon\Carbon::parse($conductor->licencia_vence)->format('Y-m-d') : '') }}">
                            @error('licencia_vence')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTADO --}}
                        <div class="field">
                            <label for="estado">Estado Actual</label>
                            <select name="estado" id="estado" required>
                                <option value="activo"
                                    {{ old('estado', $conductor->estado) == 'activo' ? 'selected' : '' }}>🟢 Activo
                                </option>
                                <option value="suspendido"
                                    {{ old('estado', $conductor->estado) == 'suspendido' ? 'selected' : '' }}>🟡 Suspendido
                                </option>
                                <option value="inactivo"
                                    {{ old('estado', $conductor->estado) == 'inactivo' ? 'selected' : '' }}>🔴 Inactivo
                                </option>
                            </select>
                        </div>

                        {{-- DIRECCIÓN --}}
                        <div class="field field-full">
                            <label for="direccion">Dirección</label>
                            <input type="text" id="direccion" name="direccion"
                                value="{{ old('direccion', $conductor->direccion) }}"
                                placeholder="Jr. Libertad 456, Huancayo">
                        </div>

                        {{-- VEHÍCULO ASIGNADO (Solo lectura) --}}
                        <div class="field">
                            <label>Unidad Asignada Atual</label>
                            @php $v = $conductor->vehiculos->first(); @endphp
                            <div style="padding: 9px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 700; color: var(--accent);">
                                @if($v)
                                    <i class="fa-solid fa-bus"></i> #{{ $v->numero_flota }} — {{ $v->placa }}
                                @else
                                    <span style="color: var(--text3); font-weight: 400;">Ninguna unidad asignada</span>
                                @endif
                            </div>
                            <small style="font-size: 10px; color: var(--text3);">Para cambiarla, diríjase a la sección de Vehículos.</small>
                        </div>

                        {{-- NOTAS --}}
                        <div class="field field-full">
                            <label for="notas">Notas / Observaciones</label>
                            <textarea id="notas" name="notas" rows="3" style="resize: none;"
                                placeholder="Información adicional relevante...">{{ old('notas', $conductor->notas) }}</textarea>
                        </div>
                    </div>

                    {{-- ACCIONES --}}
                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('conductores.index') }}" class="btn-secondary" style="text-decoration: none;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-rotate"></i> Actualizar Conductor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
