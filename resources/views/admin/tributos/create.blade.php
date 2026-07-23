@extends('layouts.admin')

@section('back_url', route('tributos.index'))

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <div class="card-title">Registrar Cobro Manual</div>
        </div>
        <div class="card-body">
            <form action="{{ route('tributos.store') }}" method="POST">
                @csrf
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label>Vehículo / Unidad</label>
                        <select name="vehiculo_id" class="form-control" required id="vehiculo_select">
                            <option value="">-- Seleccionar Unidad --</option>
                            @foreach ($vehiculos as $v)
                                <option value="{{ $v->id }}" data-conductor="{{ $v->conductor_id }}">
                                    Padron #{{ $v->numero_flota }} - {{ $v->placa }}
                                </option>
                            @endforeach
                        </select>
                    
                        @error('vehiculo_id')
                            <span style="color: #dc2626; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Campo oculto para el conductor automático --}}
                    <input type="hidden" name="conductor_id" id="conductor_id">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Fecha del Tributo</label>
                            <input type="date" name="fecha" value="{{ $fechaHoy }}" class="form-control" required>
                        
                        @error('fecha')
                            <span style="color: #dc2626; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                        <div class="form-group">
                            <label>Monto (S/)</label>
                            <input type="number" name="monto" value="24.00" step="0.50" class="form-control"
                                required>
                        
                        @error('monto')
                            <span style="color: #dc2626; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                    </div>

                    <div class="form-group">
                        <label>Método de Pago</label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                        </select>
                    
                        @error('metodo_pago')
                            <span style="color: #dc2626; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"
                            placeholder="Ej. Pago adelantado, billete roto, etc."></textarea>
                    
                        @error('observaciones')
                            <span style="color: #dc2626; font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button type="submit" class="btn-primary" style="flex: 1; padding: 15px;">REGISTRAR PAGO</button>
                        <a href="{{ route('tributos.index') }}" class="btn-secondary"
                            style="padding: 15px; text-decoration:none;">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script simple para asignar el conductor automáticamente al seleccionar vehículo
        document.getElementById('vehiculo_select').addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            document.getElementById('conductor_id').value = selectedOption.getAttribute('data-conductor');
        });
    </script>
@endsection
