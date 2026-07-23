@extends('layouts.admin')

@section('back_url', route('vehiculos.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 1000px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Editar Unidad: {{ $vehiculo->placa }}</div>
            </div>
            <div class="card-body">

                {{-- BLOQUE DE ERRORES --}}
                @if ($errors->any())
                    <div class="alert warning">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('vehiculos.update', $vehiculo->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-grid" style="grid-template-columns: repeat(3, 1fr);">

                        {{-- SECCIÓN: IDENTIFICACIÓN --}}
                        <div class="field">
                            <label for="placa">Placa</label>
                            <input type="text" id="placa" name="placa" value="{{ old('placa', $vehiculo->placa) }}"
                                required maxlength="8"
                                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '')"
                                placeholder="ABC-123">
                        </div>

                        <div class="field">
                            <label for="numero_flota">Número de Padrón</label>
                            <input type="number" id="numero_flota" name="numero_flota"
                                value="{{ old('numero_flota', $vehiculo->numero_flota) }}" placeholder="Ej: 105">
                        </div>

                        <div class="field">
                            <label for="estado">Estado Operativo</label>
                            <select name="estado" id="estado" required>
                                <option value="activo" {{ old('estado', $vehiculo->estado) == 'activo' ? 'selected' : '' }}>
                                    Activo</option>
                                <option value="inactivo"
                                    {{ old('estado', $vehiculo->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                <option value="mantenimiento"
                                    {{ old('estado', $vehiculo->estado) == 'mantenimiento' ? 'selected' : '' }}>En
                                    Mantenimiento</option>
                                <option value="sin_salir"
                                    {{ old('estado', $vehiculo->estado) == 'sin_salir' ? 'selected' : '' }}>Sin Salir
                                </option>
                            </select>
                        </div>

                        {{-- SECCIÓN: DETALLES TÉCNICOS --}}
                        <div class="field">
                            <label for="marca">Marca</label>
                            <input type="text" id="marca" name="marca"
                                value="{{ old('marca', $vehiculo->marca) }}">
                        </div>

                        <div class="field">
                            <label for="modelo">Modelo</label>
                            <input type="text" id="modelo" name="modelo"
                                value="{{ old('modelo', $vehiculo->modelo) }}">
                        </div>

                        <div class="field">
                            <label for="anio">Año de Fabricación</label>
                            <select name="anio" id="anio" required>
                                @php
                                    $anioActual = date('Y');
                                    $anioSeleccionado = old('anio', $vehiculo->anio);
                                @endphp
                                @for ($i = $anioActual + 1; $i >= 1990; $i--)
                                    <option value="{{ $i }}" {{ $anioSeleccionado == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- SECCIÓN: DOCUMENTACIÓN (CORREGIDA CON FORMATO YYYY-MM-DD) --}}
                        <div class="field">
                            <label for="soat_vence">Vencimiento SOAT</label>
                            <input type="date" id="soat_vence" name="soat_vence"
                                value="{{ old('soat_vence', $vehiculo->soat_vence ? \Carbon\Carbon::parse($vehiculo->soat_vence)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="field">
                            <label for="rev_tecnica_vence">Revisión Técnica Vence</label>
                            <input type="date" id="rev_tecnica_vence" name="rev_tecnica_vence"
                                value="{{ old('rev_tecnica_vence', $vehiculo->rev_tecnica_vence ? \Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="field">
                            <label for="tarjeta_prop_vence">Tarjeta Propiedad Vence</label>
                            <input type="date" id="tarjeta_prop_vence" name="tarjeta_prop_vence"
                                value="{{ old('tarjeta_prop_vence', $vehiculo->tarjeta_prop_vence ? \Carbon\Carbon::parse($vehiculo->tarjeta_prop_vence)->format('Y-m-d') : '') }}">
                        </div>

                        {{-- SECCIÓN: ASIGNACIONES --}}
                        <div class="field">
                            <label for="propietario_id">Propietario</label>
                            <select name="propietario_id" id="propietario_id">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($propietarios as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('propietario_id', $vehiculo->propietario_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre }} {{ $p->apellidos }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="conductor_id">Conductor Habitual</label>
                            <select name="conductor_id" id="conductor_id">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($conductores as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('conductor_id', $vehiculo->conductor_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre }} {{ $c->apellidos }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color"
                                value="{{ old('color', $vehiculo->color) }}">
                        </div>

                        {{-- SECCIÓN: RUTAS (FULL WIDTH) --}}
                        <div class="field field-full">
                            <label style="margin-bottom: 10px; display: block;">Rutas Autorizadas</label>
                            <div
                                style="display: flex; flex-wrap: wrap; gap: 12px; background: var(--bg); padding: 15px; border-radius: 10px; border: 1px solid var(--border2);">
                                @php
                                    $rutasActuales = old('rutas', $vehiculo->rutas->pluck('id')->toArray());
                                @endphp
                                @foreach ($rutas as $ruta)
                                    <label
                                        style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; background: var(--card); padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border);">
                                        <input type="checkbox" name="rutas[]" value="{{ $ruta->id }}"
                                            {{ in_array($ruta->id, $rutasActuales) ? 'checked' : '' }}>
                                        {{ $ruta->nombre }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- NOTAS --}}
                        <div class="field field-full">
                            <label for="notas">Notas / Observaciones</label>
                            <textarea id="notas" name="notas" rows="2" style="resize: none;"
                                placeholder="Detalles adicionales de la unidad...">{{ old('notas', $vehiculo->notas) }}</textarea>
                        </div>
                    </div>

                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('vehiculos.index') }}" class="btn-secondary"
                            style="text-decoration: none;">Cancelar</a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-rotate"></i> Actualizar Unidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
