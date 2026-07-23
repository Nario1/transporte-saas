@extends('layouts.admin')

@section('back_url', route('vueltas.index'))

@section('title', 'Registrar Vuelta Manual')

@section('content')
<div class="dashboard-wrapper" style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h2 style="font-size: 18px; font-weight: 800;"><i class="fa-solid fa-plus-circle"></i> Registrar Vuelta Manual</h2>
                <div style="font-size: 12px; color: var(--text3);">Registro de viaje por contingencia (sin celular)</div>
            </div>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert warning" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('vueltas.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="fecha">Fecha del Viaje</label>
                        <input type="date" id="fecha" name="fecha" value="{{ $fechaHoy }}" required>
                    </div>

                    <div class="field">
                        <label for="numero_vuelta">Número de Vuelta</label>
                        <select id="numero_vuelta" name="numero_vuelta" required>
                            @for($i=1; $i<=20; $i++)
                                <option value="{{ $i }}">Vuelta #{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="field">
                        <label for="vehiculo_id">Vehículo (Padrón / Placa)</label>
                        <select id="vehiculo_id" name="vehiculo_id" required onchange="updateDriver(this)">
                            <option value="">Seleccione Unidad...</option>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}" data-conductor="{{ $vehiculo->conductor_id }}">
                                    #{{ $vehiculo->numero_flota }} — {{ $vehiculo->placa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="conductor_id">Conductor Responsable</label>
                        <select id="conductor_id" name="conductor_id" required>
                            <option value="">Seleccione Conductor...</option>
                            @foreach($conductores as $conductor)
                                <option value="{{ $conductor->id }}">
                                    {{ $conductor->nombre }} {{ $conductor->apellido }} ({{ $conductor->numero_documento }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="ruta_id">Ruta Asignada</label>
                        <select id="ruta_id" name="ruta_id" required>
                            <option value="">Seleccione Ruta...</option>
                            @foreach($rutas as $ruta)
                                <option value="{{ $ruta->id }}">{{ $ruta->nombre }} ({{ $ruta->origen }} - {{ $ruta->destino }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="hora_salida">Hora de Salida</label>
                        <input type="time" id="hora_salida" name="hora_salida" value="{{ now()->format('H:i') }}" required>
                    </div>

                    <div class="field field-full">
                        <label for="observaciones">Observaciones / Motivo de registro manual</label>
                        <textarea id="observaciones" name="observaciones" rows="3" placeholder="Ej: Conductor sin batería, falla de GPS, etc."></textarea>
                    </div>

                    <input type="hidden" name="estado" value="activa">
                </div>

                <div style="margin-top: 30px; display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="{{ route('vueltas.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary" style="padding: 10px 30px; font-size: 14px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateDriver(select) {
    const conductorId = select.options[select.selectedIndex].getAttribute('data-conductor');
    if (conductorId) {
        document.getElementById('conductor_id').value = conductorId;
    }
}
</script>
@endsection
