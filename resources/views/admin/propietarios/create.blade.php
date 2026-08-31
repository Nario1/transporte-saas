@extends('layouts.admin')

@php
    $pageTitle = 'Nuevo Propietario';
    $pageSubtitle = 'Añadir socio al padrón de transportistas';
@endphp

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Registrar Nuevo Socio Propietario</div>
            </div>

            <div class="card-body">
                <form action="{{ route('propietarios.store') }}" method="POST">
                    @csrf

                    {{-- SECCIÓN 1: Identificación Legal --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-address-book"></i> Datos de Identificación
                        </div>
                        <div class="g-3" style="grid-template-columns: 1fr 1fr;">
                            <div class="field">
                                <label for="nombre">Nombre(s)</label>
                                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required
                                    pattern="[A-Za-zÀ-ÿ\s]{2,60}" placeholder="Ej. Juan Manuel">
                                @error('nombre')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required
                                    pattern="[A-Za-zÀ-ÿ\s]{2,60}" placeholder="Ej. Perez Garcia">
                                @error('apellidos')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="dni">DNI / RUC</label>
                                <input type="text" id="dni" name="dni" value="{{ old('dni') }}" maxlength="11"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    placeholder="8 o 11 dígitos" required>
                                @error('dni')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="telefono">Teléfono / Celular</label>
                                <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}"
                                    maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                                    placeholder="9 dígitos">
                                @error('telefono')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: Domicilio y Localización --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-map-location-dot"></i> Ubicación y Contacto
                        </div>
                        <div class="field field-full">
                            <label for="direccion">Dirección Fiscal / Residencial</label>
                            <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}"
                                placeholder="Ej: Av. Principal 123, Huancayo">
                            @error('direccion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- SECCIÓN 3: Configuración Especial (Socio-Conductor) --}}
                    <div class="form-section" style="background: var(--bg); border: 2px dashed var(--border); border-radius: 16px; padding: 25px;">
                        <label class="flex-h" style="cursor: pointer; gap: 12px; margin-bottom: 0;">
                            <input type="checkbox" name="es_conductor" id="es_conductor" value="1" 
                                {{ old('es_conductor') ? 'checked' : '' }} 
                                style="width: 20px; height: 20px; accent-color: var(--accent);"
                                onchange="document.getElementById('conductor_fields').style.display = this.checked ? 'grid' : 'none'">
                            <div>
                                <div style="font-weight: 800; font-size: 15px; color: var(--text);">¿Este socio también es conductor?</div>
                                <div style="font-size: 12px; color: var(--text3);">Si marcas esta opción, se creará automáticamente un perfil de conductor vinculado.</div>
                            </div>
                        </label>

                        <div id="conductor_fields" class="g-2" style="display: {{ old('es_conductor') ? 'grid' : 'none' }}; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                            <div class="field">
                                <label for="tipo_licencia">Categoría de Licencia</label>
                                <input type="text" id="tipo_licencia" name="tipo_licencia" value="{{ old('tipo_licencia') }}"
                                    placeholder="Ej: AIIB, AIIIC">
                                @error('tipo_licencia')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="licencia_vence">Vencimiento de Licencia</label>
                                <input type="date" id="licencia_vence" name="licencia_vence" value="{{ old('licencia_vence') }}">
                                @error('licencia_vence')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="carnet_habilitacion_tipo">Carnet de Habilitación (Tipo/Nro)</label>
                                <input type="text" id="carnet_habilitacion_tipo" name="carnet_habilitacion_tipo" value="{{ old('carnet_habilitacion_tipo') }}"
                                    placeholder="Ej: Municipal, Especial">
                                @error('carnet_habilitacion_tipo')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="carnet_habilitacion_vence">Vencimiento Carnet de Habilitación</label>
                                <input type="date" id="carnet_habilitacion_vence" name="carnet_habilitacion_vence" value="{{ old('carnet_habilitacion_vence') }}">
                                @error('carnet_habilitacion_vence')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="conductor_estado">Estado Inicial</label>
                                <select name="conductor_estado" id="conductor_estado">
                                    <option value="activo" {{ old('conductor_estado') == 'activo' ? 'selected' : '' }}>ACTIVO</option>
                                    <option value="suspendido" {{ old('conductor_estado') == 'suspendido' ? 'selected' : '' }}>SUSPENDIDO</option>
                                    <option value="inactivo" {{ old('conductor_estado') == 'inactivo' ? 'selected' : '' }}>INACTIVO</option>
                                </select>
                            </div>

                            <div class="field">
                                <label for="email">Correo Electrónico (Opcional)</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    placeholder="ejemplo@correo.com">
                                @error('email')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: Control de Monto de Ingreso --}}
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fa-solid fa-hand-holding-dollar"></i> Control de Monto de Ingreso (Total Obligado: S/. 600.00)
                        </div>
                        <div class="g-4" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; display: grid;">
                            <div class="field">
                                <label for="monto_inicial">Monto Inicial (S/.)</label>
                                <input type="number" id="monto_inicial" name="monto_inicial" step="0.01" min="0" max="600" value="{{ old('monto_inicial', '0.00') }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('monto_inicial')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="cuota_1">Cuota 1 (S/.)</label>
                                <input type="number" id="cuota_1" name="cuota_1" step="0.01" min="0" max="600" value="{{ old('cuota_1', '0.00') }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('cuota_1')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="cuota_2">Cuota 2 (S/.)</label>
                                <input type="number" id="cuota_2" name="cuota_2" step="0.01" min="0" max="600" value="{{ old('cuota_2', '0.00') }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('cuota_2')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="cuota_3">Cuota 3 (S/.)</label>
                                <input type="number" id="cuota_3" name="cuota_3" step="0.01" min="0" max="600" value="{{ old('cuota_3', '0.00') }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('cuota_3')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div style="margin-top: 15px; background: var(--bg); border: 1px solid var(--border); padding: 12px 18px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 800; font-size: 13px;">Total Recaudado: <span id="suma_total" style="color: var(--accent);">S/. 0.00</span> / S/. 600.00</span>
                            <span id="estado_badge" class="pill" style="font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 99px;">DEUDA</span>
                        </div>
                    </div>

                    @push('scripts')
                    <script>
                        function calcularTotalIngreso() {
                            const mi = parseFloat(document.getElementById('monto_inicial').value) || 0;
                            const c1 = parseFloat(document.getElementById('cuota_1').value) || 0;
                            const c2 = parseFloat(document.getElementById('cuota_2').value) || 0;
                            const c3 = parseFloat(document.getElementById('cuota_3').value) || 0;
                            
                            const total = mi + c1 + c2 + c3;
                            document.getElementById('suma_total').textContent = 'S/. ' + total.toFixed(2);
                            
                            const badge = document.getElementById('estado_badge');
                            if (total >= 600) {
                                badge.textContent = 'PAGADO';
                                badge.style.background = 'var(--green-l)';
                                badge.style.color = 'var(--green)';
                            } else {
                                badge.textContent = 'DEUDA';
                                badge.style.background = 'var(--red-l)';
                                badge.style.color = 'var(--red)';
                            }
                        }
                        document.addEventListener('DOMContentLoaded', calcularTotalIngreso);
                    </script>
                    @endpush

                    {{-- BOTONES DE ACCIÓN --}}
                    <div class="form-actions">
                        <a href="{{ route('propietarios.index') }}" class="btn-secondary">
                            Cancelar Operación
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-save"></i> Guardar Propietario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
