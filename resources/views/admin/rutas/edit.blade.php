@extends('layouts.admin')

@section('back_url', route('rutas.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 900px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Editar Ruta: {{ $ruta->nombre }}</div>
            </div>
            <div class="card-body">
                {{-- Bloque de Errores --}}
                @if ($errors->any())
                    <div class="alert warning">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('rutas.update', $ruta->id) }}" method="POST" id="formRuta">
                    @csrf
                    @method('PUT')

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                        <div class="field" style="grid-column: span 2;">
                            <label for="nombre">Nombre de la Ruta</label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $ruta->nombre) }}"
                                required>
                            @error('nombre')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="codigo">Código Interno</label>
                            <input type="text" id="codigo" name="codigo" value="{{ old('codigo', $ruta->codigo) }}"
                                placeholder="H-01">
                            @error('codigo')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="origen">Origen</label>
                            <input type="text" id="origen" name="origen" value="{{ old('origen', $ruta->origen) }}"
                                required>
                            @error('origen')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="destino">Destino</label>
                            <input type="text" id="destino" name="destino" value="{{ old('destino', $ruta->destino) }}"
                                required>
                            @error('destino')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="duracion_min">Duración Est. (min)</label>
                            <input type="number" id="duracion_min" name="duracion_min"
                                value="{{ old('duracion_min', $ruta->duracion_min) }}">
                            @error('duracion_min')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" required>
                                <option value="activa" {{ old('estado', $ruta->estado) == 'activa' ? 'selected' : '' }}>
                                    Activa</option>
                                <option value="inactiva"
                                    {{ old('estado', $ruta->estado) == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                            </select>
                            @error('estado')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field" style="grid-column: span 2;">
                            <label for="descripcion">Descripción</label>
                            <input type="text" id="descripcion" name="descripcion"
                                value="{{ old('descripcion', $ruta->descripcion) }}">
                            @error('descripcion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <hr style="margin: 30px 0; border: 0; border-top: 1px solid var(--border);">

                    <div id="section-paraderos">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h3 style="font-size: 14px; font-weight: 700; color: var(--text);">Secuencia de Paraderos</h3>
                            <button type="button" onclick="agregarFila()" class="btn-secondary"
                                style="padding: 5px 15px; font-size: 12px;">
                                <i class="fa-solid fa-plus"></i> Añadir Paradero
                            </button>
                        </div>

                        <table class="tbl" id="tablaParaderos">
                            <thead>
                                <tr>
                                    <th>Nombre del Paradero</th>
                                    <th width="200">Tipo</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Cargar paraderos existentes --}}
                                @foreach ($ruta->paraderos->sortBy('orden') as $index => $paradero)
                                    <tr>
                                        <td>
                                            <div class="field" style="gap:0">
                                                <input type="text" name="paraderos[{{ $index }}][nombre]"
                                                    value="{{ $paradero->nombre }}" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="field" style="gap:0">
                                                <select name="paraderos[{{ $index }}][tipo]" required>
                                                    <option value="intermedio"
                                                        {{ $paradero->tipo == 'intermedio' ? 'selected' : '' }}>Intermedio
                                                    </option>
                                                    <option value="origen"
                                                        {{ $paradero->tipo == 'origen' ? 'selected' : '' }}>Origen</option>
                                                    <option value="destino"
                                                        {{ $paradero->tipo == 'destino' ? 'selected' : '' }}>Destino
                                                    </option>
                                                </select>
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" onclick="this.closest('tr').remove()"
                                                class="action-icon delete-icon" style="border:none; background:none;">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('rutas.index') }}" class="btn-secondary"
                            style="text-decoration: none;">Cancelar</a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-rotate"></i> Actualizar Ruta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Inicializamos el índice basándonos en la cantidad de paraderos existentes
        let paraderoIndex = {{ $ruta->paraderos->count() }};

        function agregarFila() {
            const tbody = document.querySelector('#tablaParaderos tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>
                <div class="field" style="gap:0">
                    <input type="text" name="paraderos[${paraderoIndex}][nombre]" required placeholder="Nombre del punto...">
                </div>
            </td>
            <td>
                <div class="field" style="gap:0">
                    <select name="paraderos[${paraderoIndex}][tipo]" required>
                        <option value="intermedio">Intermedio</option>
                        <option value="origen">Origen</option>
                        <option value="destino">Destino</option>
                    </select>
                </div>
            </td>
            <td style="text-align: center;">
                <button type="button" onclick="this.closest('tr').remove()" class="action-icon delete-icon" style="border:none; background:none;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
            tbody.appendChild(tr);
            paraderoIndex++;
        }
    </script>
@endsection
