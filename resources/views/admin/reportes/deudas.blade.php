@extends('layouts.admin')

@section('back_url', route('reportes.index'))

@section('content')
    @php
        $isMontoIngreso = request('tipo') === 'monto_ingreso';
    @endphp
    <div style="display: grid; gap: 20px;">
        {{-- Filtros --}}
        <div class="card no-print">
            <form action="{{ route('reportes.deudas') }}" method="GET" class="card-body g-filters">
                <div class="field" id="field-desde">
                    <label>Desde:</label>
                    <input type="date" name="desde" value="{{ $desde->toDateString() }}">
                </div>
                <div class="field" id="field-hasta">
                    <label>Hasta:</label>
                    <input type="date" name="hasta" value="{{ $hasta->toDateString() }}">
                </div>
                <div class="field" id="field-flota">
                    <label>N° Flota:</label>
                    <input type="text" name="flota" value="{{ $flota }}" placeholder="Ej: 1" style="font-weight: 800; font-size: 15px;">
                </div>
                <div class="field" id="field-propietario" style="display: none;">
                    <label>Propietario:</label>
                    <select name="propietario_id" style="font-weight: 800; font-size: 14px; height: 48px; border-radius: 12px; border: 1px solid var(--border); padding: 0 15px; background: white;">
                        <option value="">-- Todos --</option>
                        @foreach($propietariosList as $p)
                            <option value="{{ $p->id }}" {{ ($propietarioId ?? '') == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Tipo de Obligación:</label>
                    <select name="tipo" style="font-weight: 800; font-size: 14px; height: 48px; border-radius: 12px; border: 1px solid var(--border); padding: 0 15px; background: white;">
                        <option value="todos" {{ request('tipo') === 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="tributo" {{ request('tipo') === 'tributo' ? 'selected' : '' }}>Tributos</option>
                        <option value="sancion" {{ request('tipo') === 'sancion' ? 'selected' : '' }}>Sanciones</option>
                        <option value="monto_ingreso" {{ request('tipo') === 'monto_ingreso' ? 'selected' : '' }}>Monto de Ingreso</option>
                    </select>
                </div>
                <div class="field" id="field-dia-especifico" style="border-left: 1px solid var(--border); padding-left: 20px;">
                    <label>Día Específico:</label>
                    <input type="date" value="{{ $desde->toDateString() === $hasta->toDateString() ? $desde->toDateString() : '' }}" onchange="if(this.value){ document.getElementsByName('desde')[0].value=this.value; document.getElementsByName('hasta')[0].value=this.value; this.form.submit(); }">
                </div>
                <div class="flex-h" style="gap: 10px; margin-top: auto;">
                    <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">📊 FILTRAR</button>
                    @if($flota !== '')
                        <a href="{{ route('reportes.deudas', ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'flota' => '', 'tipo' => request('tipo', 'todos')]) }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none;" title="Ver todas las deudas">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                    <button type="button" onclick="window.print()" class="btn-secondary" style="height: 48px; border-radius: 12px; width: 48px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
                <div style="width: 100%; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 15px;">
                    <div style="background: #e0f2fe; color: #0369a1; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 14px;">
                        Total Recaudado (en rango): S/ {{ number_format($totalCobrado, 2) }}
                    </div>
                    <div style="background: #fee2e2; color: #b91c1c; padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 14px;">
                        Deuda Pendiente (en rango): S/ {{ number_format($totalDeuda, 2) }}
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    Estado de Cuentas / Obligaciones por Unidad
                    <small style="color: var(--text3); font-weight: 400; font-size: 13px;">({{ $desde->format('d/m/Y') }} - {{ $hasta->format('d/m/Y') }})</small>
                </div>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Vehículo / Flota</th>
                            <th>Concepto / Motivo</th>
                            @if($isMontoIngreso)
                                <th>Monto Inicial</th>
                                <th>Cuota 1</th>
                                <th>Cuota 2</th>
                                <th>Cuota 3</th>
                            @else
                                <th>Monto</th>
                            @endif
                            <th style="text-align:center;">Estado</th>
                            <th>Detalle de Pago / Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paginatedItems as $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 700;">{{ $item->fecha->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    @if($item->tipo_obligacion === 'TRIBUTO')
                                        <span class="pill blue" style="font-size: 9px; font-weight: 800;">TRIBUTO</span>
                                    @elseif($item->tipo_obligacion === 'SANCIÓN')
                                        <span class="pill gold" style="font-size: 9px; font-weight: 800;">SANCIÓN</span>
                                    @else
                                        <span class="pill purple" style="font-size: 9px; font-weight: 800; background: #faf5ff; color: #7e22ce; display: inline-block;">INGRESO</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: var(--accent);">#{{ $item->vehiculo->numero_flota ?? '---' }}</div>
                                    <div class="mono" style="font-size: 10px; color: var(--text3);">{{ $item->vehiculo->placa ?? '---' }}</div>
                                </td>
                                <td style="font-size: 12.5px;">
                                    <div style="font-weight: 600;">{{ $item->concepto }}</div>
                                    @if(isset($item->conductor) && $item->conductor)
                                        <div style="font-size: 10px; color: var(--text3);">Conductor: {{ $item->conductor->nombre ?? '---' }}</div>
                                    @endif
                                    @if(!$isMontoIngreso && $item->tipo_obligacion === 'MONTO DE INGRESO')
                                        <div style="font-size: 10.5px; color: var(--text3); margin-top: 4px; display: flex; gap: 8px; flex-wrap: wrap;">
                                            <span><strong>Inicial:</strong> S/ {{ number_format($item->monto_inicial ?? 0, 2) }}</span>
                                            <span><strong>Cuota 1:</strong> S/ {{ number_format($item->cuota_1 ?? 0, 2) }}</span>
                                            <span><strong>Cuota 2:</strong> S/ {{ number_format($item->cuota_2 ?? 0, 2) }}</span>
                                            <span><strong>Cuota 3:</strong> S/ {{ number_format($item->cuota_3 ?? 0, 2) }}</span>
                                        </div>
                                    @endif
                                </td>
                                @if($isMontoIngreso)
                                    <td style="font-weight: 800; color: var(--text);">
                                        S/ {{ number_format($item->monto_inicial ?? 0, 2) }}
                                    </td>
                                    <td style="font-weight: 800; color: var(--text);">
                                        S/ {{ number_format($item->cuota_1 ?? 0, 2) }}
                                    </td>
                                    <td style="font-weight: 800; color: var(--text);">
                                        S/ {{ number_format($item->cuota_2 ?? 0, 2) }}
                                    </td>
                                    <td style="font-weight: 800; color: var(--text);">
                                        S/ {{ number_format($item->cuota_3 ?? 0, 2) }}
                                    </td>
                                @else
                                    <td style="font-weight: 800; color: var(--text);">
                                        S/ {{ number_format($item->monto, 2) }}
                                    </td>
                                @endif
                                <td style="text-align: center;">
                                    @if($item->estado === 'pagado')
                                        <span class="pill green" style="font-size: 9px; font-weight: 800;">PAGADO</span>
                                    @elseif($item->estado === 'exonerado')
                                        <span class="pill gray" style="font-size: 9px; font-weight: 800;">EXONERADO</span>
                                    @else
                                        <span class="pill red" style="font-size: 9px; font-weight: 800;">PENDIENTE</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->tipo_obligacion === 'MONTO DE INGRESO')
                                        @if(($item->monto_deuda ?? 0) > 0)
                                            <div style="font-size: 11px;">
                                                <span style="color: var(--red); font-weight: 800; font-size: 12.5px;"><i class="fa-solid fa-circle-exclamation"></i> Deuda: S/ {{ number_format($item->monto_deuda, 2) }}</span>
                                                @if(($item->monto ?? 0) > 0)
                                                    <div style="font-size: 10px; color: var(--green); font-weight: 600; margin-top: 3px;"><i class="fa-regular fa-clock"></i> Recaudado: S/ {{ number_format($item->monto, 2) }} cobrado</div>
                                                @endif
                                            </div>
                                        @else
                                            <div style="font-size: 11px;">
                                                <div style="font-weight: 600; color: var(--green);"><i class="fa-regular fa-clock"></i> {{ $item->cobrado_at ? $item->cobrado_at->format('d/m/Y h:i A') : '---' }}</div>
                                                <div style="font-size: 10px; color: var(--text3);">Vía: {{ strtoupper($item->metodo_pago ?? 'EFECTIVO') }}</div>
                                            </div>
                                        @endif
                                    @elseif($item->estado === 'pagado')
                                        <div style="font-size: 11px;">
                                            <div style="font-weight: 600; color: var(--green);"><i class="fa-regular fa-clock"></i> {{ $item->cobrado_at ? $item->cobrado_at->format('d/m/Y h:i A') : '---' }}</div>
                                             @if($item->metodo_pago === 'mercadopago')
                                                 <div style="font-size: 10px; color: var(--text3);">Vía: <span style="color:#009ee3; font-weight:700;">MERCADOPAGO ({{ strtoupper($item->pagoMp?->metodo ?? 'WEB') }})</span></div>
                                             @else
                                                 <div style="font-size: 10px; color: var(--text3);">Vía: {{ strtoupper($item->metodo_pago ?? 'EFECTIVO') }}</div>
                                             @endif
                                        </div>
                                    @elseif($item->estado === 'exonerado')
                                        <div style="font-size: 11px; color: var(--text3); font-style: italic;">
                                            <i class="fa-solid fa-info-circle"></i> Exonerado: {{ $item->motivo_exoneracion ?? 'Sin motivo' }}
                                        </div>
                                    @else
                                        <span style="font-size: 11px; color: var(--red); font-weight: 600;"><i class="fa-solid fa-hourglass-half"></i> Sin pago registrado</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isMontoIngreso ? 10 : 7 }}" style="text-align:center; padding: 40px; color: var(--text3);">No hay registros de obligaciones en este rango.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($paginatedItems->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);" class="no-print">
                    {{ $paginatedItems->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tipoSelect = document.getElementsByName('tipo')[0];
        const fieldFlota = document.getElementById('field-flota');
        const fieldPropietario = document.getElementById('field-propietario');
        const fieldDesde = document.getElementById('field-desde');
        const fieldHasta = document.getElementById('field-hasta');
        const fieldDia = document.getElementById('field-dia-especifico');

        function toggleFields() {
            if (tipoSelect && tipoSelect.value === 'monto_ingreso') {
                if (fieldFlota) fieldFlota.style.display = 'none';
                if (fieldPropietario) fieldPropietario.style.display = 'block';
                if (fieldDesde) fieldDesde.style.display = 'none';
                if (fieldHasta) fieldHasta.style.display = 'none';
                if (fieldDia) fieldDia.style.display = 'none';
            } else {
                if (fieldFlota) fieldFlota.style.display = 'block';
                if (fieldPropietario) fieldPropietario.style.display = 'none';
                if (fieldDesde) fieldDesde.style.display = 'block';
                if (fieldHasta) fieldHasta.style.display = 'block';
                if (fieldDia) fieldDia.style.display = 'block';
            }
        }

        if (tipoSelect) {
            tipoSelect.addEventListener('change', toggleFields);
            toggleFields();
        }
    });
</script>
@endpush
