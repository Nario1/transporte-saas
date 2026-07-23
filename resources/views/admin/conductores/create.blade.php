@extends('layouts.admin')

@php
    $pageTitle = 'Nuevo Conductor';
    $pageSubtitle = 'Registro de personal operativo';
@endphp

@section('back_url', route('conductores.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 900px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Registrar Nuevo Personal de Conducción</div>
            </div>

            <div class="card-body">
                <form action="{{ route('conductores.store') }}" method="POST">
                    @csrf

                    {{-- SECCIÓN 1: Identidad y Contacto --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-id-card"></i> Datos de Identidad y Contacto
                        </div>
                        <div class="g-3">
                            <div class="field">
                                <label for="nombre">Nombre(s)</label>
                                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', request('nombre')) }}" required
                                    pattern="[A-Za-zÀ-ÿ\s]{2,60}" placeholder="Ej. Carlos Alberto">
                                @error('nombre')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', request('apellidos')) }}" required
                                    pattern="[A-Za-zÀ-ÿ\s]{2,60}" placeholder="Ej. Ramos Castillo">
                                @error('apellidos')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="dni">Número de DNI</label>
                                <input type="text" id="dni" name="dni" value="{{ old('dni', request('dni')) }}" maxlength="8"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                    placeholder="8 dígitos" required>
                                @error('dni')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="telefono">Teléfono / WhatsApp</label>
                                <input type="text" id="telefono" name="telefono" value="{{ old('telefono', request('telefono')) }}"
                                    maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                                    placeholder="9 dígitos">
                                @error('telefono')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field" style="grid-column: span 2;">
                                <label for="email">Correo Electrónico (Opcional)</label>
                                <input type="email" id="email" name="email" value="{{ old('email', request('email')) }}"
                                    placeholder="ejemplo@correo.com">
                                @error('email')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="field field-full" style="margin-top: 15px;">
                            <label for="direccion">Dirección de Domicilio</label>
                            <input type="text" id="direccion" name="direccion" value="{{ old('direccion', request('direccion')) }}"
                                placeholder="Ej: Jr. Libertad 456, Huancayo">
                            @error('direccion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- SECCIÓN 2: Documentación y Empresa --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-file-signature"></i> Licencia y Asignación
                        </div>
                        <div class="g-3">
                            <div class="field">
                                <label for="tipo_licencia">Categoría Licencia</label>
                                <input type="text" id="tipo_licencia" name="tipo_licencia"
                                    value="{{ old('tipo_licencia') }}" placeholder="Ej: AIIB, AIIIC" required>
                                @error('tipo_licencia')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="licencia_vence">Vencimiento Licencia</label>
                                <input type="date" id="licencia_vence" name="licencia_vence"
                                    value="{{ old('licencia_vence') }}" required>
                                @error('licencia_vence')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="estado">Estado Inicial</label>
                                <select name="estado" id="estado" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>ACTIVO</option>
                                    <option value="suspendido" {{ old('estado') == 'suspendido' ? 'selected' : '' }}>SUSPENDIDO</option>
                                    <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>INACTIVO</option>
                                </select>
                            </div>
                            
                            <div class="field" style="grid-column: span 3;">
                                <label for="propietario_id">Propietario Responsable</label>
                                <select name="propietario_id" id="propietario_id">
                                    <option value="">-- Sin Asignar / Independiente --</option>
                                    @foreach ($propietarios as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('propietario_id', request('propietario_id')) == $p->id ? 'selected' : '' }}>
                                            {{ $p->nombre }} {{ $p->apellidos }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: Notas --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-comment-dots"></i> Observaciones Adicionales
                        </div>
                        <div class="field field-full">
                            <textarea id="notas" name="notas" rows="3" style="resize: none;"
                                placeholder="Especifique cualquier detalle relevante sobre el personal...">{{ old('notas') }}</textarea>
                        </div>
                    </div>

                    {{-- ACCIONES FINALIZACIÓN --}}
                    <div class="form-actions">
                        <a href="{{ route('conductores.index') }}" class="btn-secondary">
                            Descartar
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-save"></i> Registrar Conductor en Sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
