@extends('layouts.admin')

@section('back_url', route('sanciones.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-gavel" style="color: var(--red); margin-right: 8px;"></i>
                    Registrar Nueva Sanción / Multa
                </div>
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

                <form action="{{ route('sanciones.store') }}" method="POST">
                    @csrf

                    <div class="form-grid">

                        {{-- VEHÍCULO --}}
                        <div class="field">
                            <label for="vehiculo_select">Vehículo (Unidad)</label>
                            <select name="vehiculo_id" id="vehiculo_select" required>
                                <option value="">-- Seleccionar Unidad --</option>
                                @foreach ($vehiculos as $v)
                                    <option value="{{ $v->id }}" data-conductor="{{ $v->conductor_id }}">
                                        Padrón #{{ $v->numero_flota }} - {{ $v->placa }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehiculo_id')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CONDUCTOR --}}
                        <div class="field">
                            <label for="conductor_select">Conductor Responsable</label>
                            <select name="conductor_id" id="conductor_select">
                                <option value="">-- Seleccionar Conductor --</option>
                                @foreach ($conductores as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }} {{ $c->apellidos }}</option>
                                @endforeach
                            </select>
                            @error('conductor_id')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- FECHA --}}
                        <div class="field">
                            <label for="fecha">Fecha de la Infracción</label>
                            <input type="date" id="fecha" name="fecha" value="{{ $fechaHoy }}" required>
                            @error('fecha')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MONTO --}}
                        <div class="field">
                            <label for="monto">Monto de la Sanción (S/)</label>
                            <input type="number" id="monto" name="monto" step="0.50" placeholder="0.00" required>
                            @error('monto')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MOTIVO --}}
                        <div class="field field-full">
                            <label for="motivo">Motivo Principal</label>
                            <select name="motivo" id="motivo" required>
                                <option value="">-- Seleccionar Motivo --</option>
                                <option value="Incumplimiento de Ruta">Incumplimiento de Ruta</option>
                                <option value="Exceso de Velocidad">Exceso de Velocidad</option>
                                <option value="Paradero Omitido">Paradero Omitido</option>
                                <option value="Maltrato al Usuario">Maltrato al Usuario</option>
                                <option value="Falta de Documentación">Falta de Documentación</option>
                                <option value="Otro">Otro (Especificar abajo)</option>
                            </select>
                            @error('motivo')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- DESCRIPCIÓN --}}
                        <div class="field field-full">
                            <label for="descripcion">Descripción / Detalles Adicionales</label>
                            <textarea id="descripcion" name="descripcion" rows="3" style="resize: none;"
                                placeholder="Detalle lo ocurrido de forma clara..."></textarea>
                            @error('descripcion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- BOTONES DE ACCIÓN --}}
                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('sanciones.index') }}" class="btn-secondary" style="text-decoration: none;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary" style="background: var(--red); border: none;">
                            <i class="fa-solid fa-circle-check"></i> Registrar Sanción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Seleccionar automáticamente al conductor asignado al vehículo
        document.getElementById('vehiculo_select').addEventListener('change', function() {
            let conductorId = this.options[this.selectedIndex].getAttribute('data-conductor');
            if (conductorId) {
                document.getElementById('conductor_select').value = conductorId;
            }
        });
    </script>
@endsection
