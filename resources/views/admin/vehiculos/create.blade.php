@extends('layouts.admin')

@php
    $pageTitle = 'Nueva Unidad';
    $pageSubtitle = 'Añadir vehículo a la flota';
@endphp

@section('back_url', route('vehiculos.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 1000px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Registrar Nueva Unidad de Transporte</div>
            </div>
            <div class="card-body">

                <form action="{{ route('vehiculos.store') }}" method="POST">
                    @csrf

                    {{-- SECCIÓN 1: Identificación y Estado --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-id-card"></i> Identificación del Vehículo
                        </div>
                        <div class="g-3">
                            <div class="field">
                                <label for="placa">Placa</label>
                                <input type="text" id="placa" name="placa" value="{{ old('placa') }}" required
                                    maxlength="8" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '')"
                                    placeholder="ABC-123">
                                @error('placa')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="numero_flota">Número de Padrón</label>
                                <input type="number" id="numero_flota" name="numero_flota" value="{{ old('numero_flota') }}"
                                    placeholder="Ej: 105">
                            </div>

                            <div class="field">
                                <label for="estado">Estado Operativo</label>
                                <select name="estado" id="estado" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: Detalles Técnicos --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-gears"></i> Especificaciones Técnicas
                        </div>
                        <div class="g-3">
                            <div class="field">
                                <label for="marca">Marca</label>
                                <input type="text" id="marca" name="marca" value="{{ old('marca') }}" placeholder="Ej: Mercedes-Benz">
                            </div>

                            <div class="field">
                                <label for="modelo">Modelo</label>
                                <input type="text" id="modelo" name="modelo" value="{{ old('modelo') }}" placeholder="Ej: Atego 1725">
                            </div>

                            <div class="field">
                                <label for="color">Color</label>
                                <input type="text" id="color" name="color" value="{{ old('color') }}" placeholder="Ej: Blanco/Rojo">
                            </div>

                            <div class="field">
                                <label for="anio">Año de Fabricación</label>
                                <select name="anio" id="anio" required>
                                    <option value="">-- Seleccionar --</option>
                                    @php
                                        $anioActual = date('Y');
                                        $anioInicio = 1990;
                                    @endphp
                                    @for ($i = $anioActual; $i >= $anioInicio; $i--)
                                        <option value="{{ $i }}" {{ old('anio') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('anio')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="field">
                                <label for="combustible">Combustible</label>
                                <select name="combustible" id="combustible">
                                    <option value="Diesel" {{ old('combustible') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                    <option value="Gasolina" {{ old('combustible') == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                                    <option value="GNV" {{ old('combustible') == 'GNV' ? 'selected' : '' }}>GNV</option>
                                    <option value="GLP" {{ old('combustible') == 'GLP' ? 'selected' : '' }}>GLP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: Documentación Legal --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-file-shield"></i> Vencimientos de Documentos
                        </div>
                        <div class="g-3">
                            <div class="field">
                                <label for="soat_vence">Vencimiento SOAT</label>
                                <input type="date" id="soat_vence" name="soat_vence" value="{{ old('soat_vence') }}">
                            </div>

                            <div class="field">
                                <label for="rev_tecnica_vence">Revisión Técnica</label>
                                <input type="date" id="rev_tecnica_vence" name="rev_tecnica_vence" value="{{ old('rev_tecnica_vence') }}">
                            </div>

                            <div class="field">
                                <label for="tarjeta_prop_vence">Tarjeta Propiedad</label>
                                <input type="date" id="tarjeta_prop_vence" name="tarjeta_prop_vence" value="{{ old('tarjeta_prop_vence') }}">
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: Personal y Rutas --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-link"></i> Asignaciones Operativas
                        </div>
                        <div class="g-3">
                            <div class="field">
                                <label for="propietario_id">Propietario</label>
                                <select name="propietario_id" id="propietario_id">
                                    <option value="">-- Sin Asignar --</option>
                                    @foreach ($propietarios as $p)
                                        <option value="{{ $p->id }}" {{ old('propietario_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nombre }} {{ $p->apellidos }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <label for="conductor_id">Conductor Habitual</label>
                                <select name="conductor_id" id="conductor_id">
                                    <option value="">-- Sin Asignar --</option>
                                    @foreach ($conductores as $c)
                                        <option value="{{ $c->id }}" {{ old('conductor_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->nombre }} {{ $c->apellidos }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="field field-full" style="margin-top: 20px;">
                            <label style="margin-bottom: 12px; display: block; font-weight: 700;">Rutas Autorizadas</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px; background: var(--bg); padding: 20px; border-radius: 12px; border: 1px solid var(--border2);">
                                @foreach ($rutas as $ruta)
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; background: var(--card); padding: 8px 16px; border-radius: 99px; border: 1px solid var(--border); transition: all 0.2s;">
                                        <input type="checkbox" name="rutas[]" value="{{ $ruta->id }}"
                                            {{ is_array(old('rutas')) && in_array($ruta->id, old('rutas')) ? 'checked' : '' }}>
                                        {{ $ruta->nombre }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- BOTONES DE ACCIÓN --}}
                    <div class="form-actions">
                        <a href="{{ route('vehiculos.index') }}" class="btn-secondary">
                            Cancelar Operación
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-bus"></i> Registrar Vehículo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
