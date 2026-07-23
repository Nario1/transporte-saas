@extends('layouts.admin')

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Editar Propietario: {{ $propietario->nombre }}</div>
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

                <form action="{{ route('propietarios.update', $propietario->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- VITAL para la actualización --}}

                    <div class="form-grid">
                        {{-- NOMBRE --}}
                        <div class="field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre"
                                value="{{ old('nombre', $propietario->nombre) }}" required pattern="[A-Za-zÀ-ÿ\s]{2,60}"
                                placeholder="Ej. Juan Manuel">
                            @error('nombre')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- APELLIDOS --}}
                        <div class="field">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos"
                                value="{{ old('apellidos', $propietario->apellidos) }}" required
                                pattern="[A-Za-zÀ-ÿ\s]{2,60}" placeholder="Ej. Perez Garcia">
                            @error('apellidos')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- DNI CON BLOQUEO DE CARACTERES --}}
                        <div class="field">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" value="{{ old('dni', $propietario->dni) }}"
                                maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                placeholder="8 dígitos">
                            @error('dni')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- TELÉFONO CON BLOQUEO DE CARACTERES --}}
                        <div class="field">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono"
                                value="{{ old('telefono', $propietario->telefono) }}" maxlength="9"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                                placeholder="9 dígitos">
                            @error('telefono')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTADO --}}
                        <div class="field">
                            <label for="activo">Estado del Registro</label>
                            <select name="activo" id="activo">
                                <option value="1" {{ old('activo', $propietario->activo) == 1 ? 'selected' : '' }}>
                                    🟢 Activo
                                </option>
                                <option value="0" {{ old('activo', $propietario->activo) == 0 ? 'selected' : '' }}>
                                    🔴 Inactivo
                                </option>
                            </select>
                            @error('activo')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CORREO (OPCIONAL) --}}
                        <div class="field">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $propietario->email) }}" placeholder="ejemplo@correo.com">
                        </div>

                        {{-- DIRECCIÓN (FULL WIDTH) --}}
                        <div class="field field-full">
                            <label for="direccion">Dirección Residencial</label>
                            <input type="text" id="direccion" name="direccion"
                                value="{{ old('direccion', $propietario->direccion) }}"
                                placeholder="Av. Principal 123, Huancayo">
                            @error('direccion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- NOTAS (FULL WIDTH) --}}
                        <div class="field field-full">
                            <label for="notas">Notas / Observaciones</label>
                            <textarea id="notas" name="notas" rows="3" style="resize: none;"
                                placeholder="Información adicional relevante...">{{ old('notas', $propietario->notas) }}</textarea>
                        </div>
                    </div>

                    {{-- BOTONES DE ACCIÓN --}}
                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('propietarios.index') }}" class="btn-secondary"
                            style="text-decoration: none; display: flex; align-items: center;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <span class="ni"></span> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
