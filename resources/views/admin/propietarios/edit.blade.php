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
                                    Activo
                                </option>
                                <option value="0" {{ old('activo', $propietario->activo) == 0 ? 'selected' : '' }}>
                                    Inactivo
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

                    {{-- SECCIÓN 4: Control de Monto de Ingreso --}}
                    <div class="form-section" style="margin-top: 30px; border-top: 1px dashed var(--border); padding-top: 20px;">
                        <h4 style="font-weight: 800; font-size: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-hand-holding-dollar" style="color: var(--accent);"></i> Control de Monto de Ingreso (Total Obligado: S/. 600.00)
                        </h4>
                        <div class="g-4" style="grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; display: grid;">
                            <div class="field">
                                <label for="monto_inicial">Monto Inicial (S/.)</label>
                                <input type="number" id="monto_inicial" name="monto_inicial" step="0.01" min="0" max="600" value="{{ old('monto_inicial', $propietario->monto_inicial) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('monto_inicial')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="cuota_1">Cuota 1 (S/.)</label>
                                <input type="number" id="cuota_1" name="cuota_1" step="0.01" min="0" max="600" value="{{ old('cuota_1', $propietario->cuota_1) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('cuota_1')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="cuota_2">Cuota 2 (S/.)</label>
                                <input type="number" id="cuota_2" name="cuota_2" step="0.01" min="0" max="600" value="{{ old('cuota_2', $propietario->cuota_2) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                @error('cuota_2')
                                    <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="cuota_3">Cuota 3 (S/.)</label>
                                <input type="number" id="cuota_3" name="cuota_3" step="0.01" min="0" max="600" value="{{ old('cuota_3', $propietario->cuota_3) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
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
