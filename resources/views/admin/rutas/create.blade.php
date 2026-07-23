@extends('layouts.admin')

@section('back_url', route('rutas.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 900px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Registrar Nueva Ruta</div>
            </div>
            <div class="card-body">
                <form action="{{ route('rutas.store') }}" method="POST" id="formRuta">
                    @csrf

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                        <div class="field" style="grid-column: span 2;">
                            <label for="nombre">Nombre de la Ruta (Ej: Huancayo - El Tambo)</label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required
                                pattern="[A-Za-zÀ-ÿ\s]{2,60}" title="Solo letras y espacios permitidos">
                            @error('nombre')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="codigo">Código Interno</label>
                            <input type="text" id="codigo" name="codigo" value="{{ old('codigo') }}"
                                placeholder="H-01">
                            @error('codigo')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="origen">Origen</label>
                            <input type="text" id="origen" name="origen" value="{{ old('origen') }}"
                                placeholder="Punto de inicio" required>
                            @error('origen')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="destino">Destino</label>
                            <input type="text" id="destino" name="destino" value="{{ old('destino') }}"
                                placeholder="Punto final" required>
                            @error('destino')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="duracion_min">Duración Est. (min)</label>
                            <input type="number" id="duracion_min" name="duracion_min" value="{{ old('duracion_min') }}">
                            @error('duracion_min')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" required>
                                <option value="activa" {{ old('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                                <option value="inactiva" {{ old('estado') == 'inactiva' ? 'selected' : '' }}>Inactiva
                                </option>
                            </select>
                            @error('estado')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field" style="grid-column: span 2;">
                            <label for="descripcion">Descripción</label>
                            <input type="text" id="descripcion" name="descripcion" value="{{ old('descripcion') }}">
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
                                {{-- Filas dinámicas --}}
                            </tbody>
                        </table>
                    </div>

                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('rutas.index') }}" class="btn-secondary"
                            style="text-decoration: none;">Cancelar</a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-route"></i> Guardar Ruta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let paraderoIndex = 0;

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

        window.onload = agregarFila;
    </script>
@endsection
