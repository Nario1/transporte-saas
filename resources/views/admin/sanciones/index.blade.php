@extends('layouts.admin')

@php
    $pageTitle = 'Gestión de Sanciones';
    $pageSubtitle = 'Administración de multas y control disciplinario';
@endphp

@section('content')
    <div class="panel">
        
        {{-- 1. INDICADORES --}}
        <div class="stats-row g-3">
            <div class="stat red">
                <div class="stat-label">Pendiente de Cobro</div>
                <div class="stat-val">S/ {{ number_format($resumen['total_pendiente'], 2) }}</div>
                <div class="stat-sub">{{ $resumen['cantidad_pendiente'] }} multas activas por procesar</div>
                <span class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            </div>
            
            <div class="stat green">
                <div class="stat-label">Recaudado (Periodo)</div>
                <div class="stat-val">S/ {{ number_format($resumen['total_cobrado_rango'], 2) }}</div>
                <div class="stat-sub">Ingresos confirmados según filtros</div>
                <span class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
            </div>

            <div class="stat blue">
                <div class="stat-label">Total Sanciones</div>
                <div class="stat-val">{{ $pendientes->total() + $pagadas->total() }}</div>
                <div class="stat-sub">Total de incidencias registradas</div>
                <span class="stat-icon"><i class="fa-solid fa-hashtag"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES Y FILTROS --}}
        <div class="flex-between gap-24">
            <div class="card" style="flex: 1;">
                <form action="{{ route('sanciones.index') }}" method="GET" class="card-body g-filters">
                    <div class="field" style="flex: 1;">
                        <label>Desde</label>
                        <input type="date" name="from" value="{{ $from ?? '' }}">
                    </div>
                    <div class="field" style="flex: 1;">
                        <label>Hasta</label>
                        <input type="date" name="to" value="{{ $to ?? '' }}">
                    </div>
                    <div class="flex-h">
                        <button type="submit" class="btn-primary" style="height: 48px; width: 48px; justify-content: center; padding: 0;">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                        @if ((isset($from) && $from) || (isset($to) && $to))
                            <a href="{{ route('sanciones.index') }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <a href="{{ route('sanciones.create') }}" class="btn-primary" style="padding: 0 32px; height: 80px; border-radius: 20px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-plus-circle"></i> REGISTRAR SANCIÓN
            </a>
        </div>

        {{-- 3. DISTRIBUCIÓN EN REJILLA --}}
        <div class="g-2-1">
            
            {{-- Columna Principal: Por Cobrar --}}
            <div class="flex-v" style="gap: 24px;">
                <div class="card">
                    <div class="card-header" style="background: var(--red-l);">
                        <div class="card-title text-red"><i class="fa-solid fa-clock-rotate-left"></i> Sanciones por Cobrar</div>
                        <span class="pill red" style="font-size: 10px;">{{ $pendientes->total() }} PENDIENTES</span>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th class="col-id">Unidad</th>
                                    <th>Concepto de Sanción / Fecha</th>
                                    <th>Monto</th>
                                    <th class="col-actions">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendientes as $p)
                                    <tr>
                                        <td>
                                            <span class="text-main">#{{ $p->vehiculo?->numero_flota }}</span>
                                            <span class="text-sub">{{ $p->vehiculo?->placa }}</span>
                                        </td>
                                        <td>
                                            <span class="text-main">{{ $p->motivo }}</span>
                                            <span class="text-sub">
                                                Fecha: {{ $p->fecha->format('d/m/Y') }} 
                                                @if($p->pagoMp) • MP: {{ strtoupper($p->pagoMp->estado) }} @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-main" style="color: var(--red); font-size: 16px;">S/ {{ number_format($p->monto, 2) }}</span>
                                        </td>
                                        <td class="col-actions">
                                            <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                                <form action="{{ route('sanciones.pagar', $p->id) }}" method="POST" class="flex-h" style="gap: 4px;">
                                                    @csrf
                                                    <select name="metodo_pago" required class="filter-select" style="height: 32px; padding: 0 25px 0 8px; font-size: 10px; background: var(--bg);">
                                                        <option value="efectivo">EFECTIVO</option>
                                                        <option value="yape">YAPE</option>
                                                        <option value="plin">PLIN</option>
                                                    </select>
                                                    <button type="submit" class="btn-primary btn-sm" style="background: var(--green); height: 32px; font-size: 10px;">
                                                        COBRAR
                                                    </button>
                                                </form>
                                                <button type="button" class="btn-secondary btn-sm" 
                                                    style="height: 32px; width: 32px; padding: 0;" 
                                                    onclick="exonerarSancion({{ $p->id }}, '{{ $p->vehiculo?->placa }}', '{{ $p->motivo }}')" 
                                                    title="Exonerar (Anular)">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                                <form action="{{ route('sanciones.destroy', $p->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-icon-submit" onclick="return confirm('¿Eliminar permanentemente?')">
                                                        <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 50px; color: var(--text3);">
                                            Sin sanciones pendientes de cobro.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Columna Lateral: Cobros e Historial --}}
            <aside class="flex-v" style="gap: 24px;">
                <div class="card">
                    <div class="card-header" style="background: var(--green-l);">
                        <div class="card-title text-green">Recaudación</div>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Unidad/Concepto</th>
                                    <th style="text-align: right;">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagadas as $pag)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 800;">#{{ $pag->vehiculo?->numero_flota }}</div>
                                            <div style="font-size: 10px; color: var(--text3);">{{ $pag->motivo }}</div>
                                            <span class="pill gray" style="font-size: 8px; padding: 1px 4px;">{{ strtoupper($pag->metodo_pago) }}</span>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: var(--green);">
                                            S/ {{ number_format($pag->monto, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 40px; color: var(--text3); font-size: 12px;">Sin cobros registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($exoneradas->total() > 0)
                    <div class="card" style="opacity: 0.9;">
                        <div class="card-header" style="background: var(--border2);">
                            <div class="card-title" style="font-size: 14px; color: var(--text2); font-weight: 800;">
                                <i class="fa-solid fa-clock-rotate-left"></i> Historial de Sanciones Exoneradas
                            </div>
                        </div>
                        <div class="tbl-wrap">
                            <table class="tbl tbl-modern">
                                <tbody>
                                    @foreach($exoneradas as $e)
                                        <tr>
                                            <td style="padding: 15px;">
                                                <div class="flex-between">
                                                    <span style="font-size: 13px; font-weight: 800; color: var(--text);">#{{ $e->vehiculo?->numero_flota }} — {{ $e->vehiculo?->placa }}</span>
                                                    <span class="pill gray" style="font-size: 10px; font-weight: 700;">{{ $e->exonerado_at->format('d M, h:i A') }}</span>
                                                </div>
                                                <div style="font-size: 13px; color: var(--text2); margin-top: 5px; font-weight: 500;">{{ $e->motivo }}</div>
                                                
                                                <div style="background: var(--bg); border: 1px dashed var(--border); border-radius: 8px; padding: 10px; margin-top: 10px;">
                                                    <div style="font-size: 12px; color: var(--text2); line-height: 1.4;">
                                                        <b style="color: var(--text3); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Justificación:</b><br>
                                                        {{ $e->motivo_exoneracion }}
                                                    </div>
                                                    <div style="font-size: 11px; color: var(--text3); margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                                                        <i class="fa-solid fa-user-check" style="font-size: 10px; opacity: 0.5;"></i>
                                                        Autorizado por: <b style="color: var(--text2);">{{ $e->exonerador?->name ?? 'Sistema' }}</b>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>

    <script>
        function exonerarSancion(id, placa, motivo) {
            Swal.fire({
                title: 'Exonerar Sanción',
                html: `
                    <div style="text-align: left; font-size: 14px; margin-bottom: 15px;">
                        Estás a punto de exonerar la multa de la unidad <b>${placa}</b> por concepto de: <br>
                        <i style="color: var(--accent)">"${motivo}"</i>
                    </div>
                    <textarea id="motivo_exon" class="swal2-textarea" placeholder="Escribe el motivo de exoneración aquí..." style="height: 100px; font-size: 14px; margin: 0; width: 100%; box-sizing: border-box;"></textarea>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--red)',
                cancelButtonColor: 'var(--text3)',
                confirmButtonText: 'SÍ, EXONERAR',
                cancelButtonText: 'CANCELAR',
                preConfirm: () => {
                    const motivo = document.getElementById('motivo_exon').value;
                    if (!motivo || motivo.trim().length < 5) {
                        Swal.showValidationMessage('El motivo debe tener al menos 5 caracteres');
                        return false;
                    }
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear un formulario dinámico para enviar el motivo
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/sanciones/${id}/exonerar`;
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const inputMotivo = document.createElement('input');
                    inputMotivo.type = 'hidden';
                    inputMotivo.name = 'motivo_exoneracion';
                    inputMotivo.value = result.value;
                    form.appendChild(inputMotivo);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
