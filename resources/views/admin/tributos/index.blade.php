@extends('layouts.admin')
@php
    $pageTitle = 'Tributos';
    $pageSubtitle = 'Administración de recaudación diaria';
@endphp
@section('content')
    <div class="panel">

        {{-- 1. INDICADORES FINANCIEROS --}}
        <div class="stats-row g-4">
            <div class="stat green" style="border-bottom: 4px solid var(--green);">
                <div class="stat-label">Recaudado Hoy</div>
                <div class="stat-val">S/ {{ number_format($resumen['total_cobrado'], 2) }}</div>
                <div class="stat-sub"><i class="fa-solid fa-check"></i> {{ $resumen['autos_pagaron'] }} UNIDADES AL DÍA</div>
                <span class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
            </div>

            <div class="stat red" style="border-bottom: 4px solid var(--red);">
                <div class="stat-label">Pendiente Hoy</div>
                <div class="stat-val">S/ {{ number_format($resumen['total_pendiente'], 2) }}</div>
                <div class="stat-sub"><i class="fa-solid fa-clock"></i> {{ $resumen['autos_pendientes'] }} POR COBRAR</div>
                <span class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
            </div>

            <div class="stat red">
                <div class="stat-label">Deuda Global Sistema</div>
                <div class="stat-val">S/ {{ number_format($deudas->sum('total_deuda'), 2) }}</div>
                <div class="stat-sub">{{ $deudas->count() }} UNIDADES MOROSAS</div>
                <span class="stat-icon"><i class="fa-solid fa-sack-xmark"></i></span>
            </div>

            <div class="stat gold">
                <div class="stat-label">Eficiencia Operativa</div>
                @php
                    $total_autos = $resumen['autos_pagaron'] + $resumen['autos_pendientes'];
                    $ratio = $total_autos > 0 ? ($resumen['autos_pagaron'] / $total_autos) * 100 : 0;
                @endphp
                <div class="stat-val">{{ number_format($ratio, 1) }}%</div>
                <div class="progress-track" style="margin-top: 12px; background: var(--border); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div class="progress-fill" style="width: {{ $ratio }}%; background: var(--gold); height: 100%; transition: width 0.3s ease;"></div>
                </div>
                <div style="font-size: 11px; margin-top: 5px; font-weight: 600; color: var(--text3);">OBJETIVO DIARIO: 95%
                </div>
                <span class="stat-icon"><i class="fa-solid fa-chart-line"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES RÁPIDAS Y FECHA --}}
        <div class="flex-between" style="margin-bottom: 25px; gap: 20px; align-items: stretch;">
            
            {{-- FECHA DE GESTIÓN (PEQUEÑO) --}}
            <div class="card" style="padding: 15px 25px; display: flex; align-items: center; gap: 15px; border-radius: 12px; border-left: 4px solid var(--accent); flex-shrink: 0; box-shadow: var(--shadow-sm);">
                <i class="fa-solid fa-calendar-day" style="font-size: 24px; color: var(--accent);"></i>
                <div class="flex-v">
                    <span style="font-size: 10px; font-weight: 800; color: var(--text3); text-transform: uppercase;">Seleccionar Fecha</span>
                    <form action="{{ route('tributos.index') }}" method="GET" id="fechaForm">
                        <input type="date" name="fecha" value="{{ $fecha }}"
                            onchange="document.getElementById('fechaForm').submit()"
                            style="border:none; background:transparent; font-family:inherit; font-weight:800; font-size:16px; color:var(--text); cursor:pointer; outline:none; padding:0;">
                    </form>
                </div>
            </div>

            {{-- EXONERACIÓN MASIVA --}}
            <div class="card" style="border: 2px dashed var(--red); background: transparent; flex: 1;">
                <div class="card-body flex-between" style="padding: 15px 25px;">
                    <div class="flex-h">
                        <div class="user-av" style="background: var(--red-l); color: var(--red);"><i
                                class="fa-solid fa-ban"></i></div>
                        <div>
                            <div style="font-weight: 800; font-size: 15px;">Exoneración Masiva</div>
                            <div style="font-size: 11px; color: var(--text3);">Anular todos los pendientes del día
                                <b>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</b></div>
                        </div>
                    </div>
                    <button type="button"
                        onclick="confirmarExoneracionMasiva('{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}')"
                        class="btn-primary"
                        style="background: var(--red); color: white; font-weight: 800; font-size: 12px; height: 38px; padding: 0 25px;">
                        EXONERAR TODO EL DÍA
                    </button>
                    <form id="form-exonerar-masivo" action="{{ route('tributos.exonerar-todo') }}" method="POST"
                        style="display: none;">
                        @csrf
                        <input type="hidden" name="fecha" value="{{ $fecha }}">
                    </form>
                </div>
            </div>
        </div>

        <div class="g-2-1">

            {{-- COLUMNA IZQUIERDA: GESTIÓN OPERATIVA --}}
            <div class="flex-v" style="gap: 24px;">

                {{-- TABLA: PENDIENTES --}}
                <div class="card">
                    <div class="card-header" style="background: var(--red-l);">
                        <div class="card-title text-red">
                            <i class="fa-solid fa-triangle-exclamation"></i> Pendientes de Cobro —
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        </div>
                        <span class="pill red">{{ $pendientes->total() }} UNIDADES</span>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th class="col-id">Unidad</th>
                                    <th>Responsable / Conductor</th>
                                    <th>Importe</th>
                                    <th class="col-actions">Acciones de Cobro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendientes as $p)
                                    <tr>
                                        <td>
                                            <span class="text-main">#{{ $p->vehiculo?->numero_flota ?? '???' }}</span>
                                            <span class="text-sub">{{ $p->vehiculo?->placa ?? 'S/P' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-main">{{ $p->conductor?->nombre ?? 'Sin Asignar' }}</span>
                                            <span class="text-sub">Cargo diario del {{ $fecha }}</span>
                                        </td>
                                        <td>
                                            <span class="text-main" style="font-size: 16px;">S/
                                                {{ number_format($p->monto, 2) }}</span>
                                        </td>
                                        <td class="col-actions">
                                            <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                                <form action="{{ route('tributos.cobrar', $p->id) }}" method="POST"
                                                    class="flex-h" style="gap: 4px;">
                                                    @csrf
                                                    <select name="metodo_pago" required class="filter-select"
                                                        style="height: 32px; padding: 0 25px 0 8px; font-size: 10px; background-color: var(--bg);">
                                                        <option value="efectivo">EFECTIVO</option>
                                                        <option value="yape">YAPE</option>
                                                        <option value="plin">PLIN</option>
                                                    </select>
                                                    <button type="submit" class="btn-primary"
                                                        style="height: 32px; background: var(--green); font-size: 10px; padding: 0 10px;">
                                                        PAGAR
                                                    </button>
                                                </form>
                                                <button type="button" class="btn-secondary"
                                                    style="height: 32px; width: 32px; padding: 0;"
                                                    onclick="exonerarTributo({{ $p->id }}, '{{ $p->vehiculo?->placa }}', '{{ $fecha }}')"
                                                    title="Exonerar">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            style="text-align: center; padding: 50px; color: var(--text3);">
                                            <i class="fa-solid fa-cloud-sun"
                                                style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                            Sin tributos pendientes para hoy.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pendientes->hasPages())
                        <div style="padding:20px; border-top:1px solid var(--border);">
                            {{ $pendientes->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                {{-- TABLA: COBRADOS --}}
                <div class="card">
                    <div class="card-header" style="background: var(--green-l);">
                        <div class="card-title text-green">
                            <i class="fa-solid fa-circle-check"></i> Recaudado Exitosamente —
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                        </div>
                        <span class="pill green">{{ $pagados->total() }} COBROS</span>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th class="col-id">Unidad</th>
                                    <th>Conductor Asignado</th>
                                    <th>Detalle / Operación</th>
                                    <th class="col-actions">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagados as $pag)
                                    <tr>
                                        <td>
                                            <span class="text-main">#{{ $pag->vehiculo?->numero_flota ?? '???' }}</span>
                                            <span class="text-sub">{{ $pag->vehiculo?->placa ?? '---' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-main">{{ $pag->conductor?->nombre ?? '---' }}</span>
                                            <span class="text-sub">Cobrado:
                                                {{ $pag->cobrado_at ? $pag->cobrado_at->format('h:i A') : '---' }}</span>
                                        </td>
                                        <td>
                                            @if($pag->metodo_pago === 'mercadopago')
                                                <span class="pill blue" style="font-size: 9px; padding: 2px 8px; font-weight: 800; background-color: #00bef0; color: #fff;">
                                                    MERCADOPAGO ({{ strtoupper($pag->pagoMp?->metodo ?? 'WEB') }})
                                                </span>
                                            @else
                                                <span class="pill blue" style="font-size: 9px; padding: 2px 8px; font-weight: 800;">
                                                    {{ strtoupper($pag->metodo_pago ?? '---') }}
                                                </span>
                                            @endif
                                            <span class="text-sub" style="display:inline; margin-left: 5px;">Recibe:
                                                {{ explode(' ', $pag->cobrador?->name)[0] }}</span>
                                        </td>
                                        <td class="col-actions">
                                            <span class="text-main" style="color: var(--green); font-size: 16px;">S/
                                                {{ number_format($pag->monto, 2) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- ... --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pagados->hasPages())
                        <div class="pagination-wrapper" style="padding:20px; border-top:1px solid var(--border);">
                            {{ $pagados->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                {{-- TABLA: EXONERADOS (HISTORIAL) --}}
                @if ($exonerados->total() > 0)
                    <div class="card" style="opacity: 0.9;">
                        <div class="card-header" style="background: var(--border2);">
                            <div class="card-title" style="color: var(--text2); font-size: 13px;">
                                <i class="fa-solid fa-stamp"></i> Unidades Exoneradas del Día
                            </div>
                            <span class="pill gray" style="font-size: 10px;">{{ $exonerados->total() }}
                                ANULACIONES</span>
                        </div>
                        <div class="tbl-wrap">
                            <table class="tbl tbl-modern">
                                <thead>
                                    <tr style="background: transparent;">
                                        <th style="padding-left: 20px; font-size: 10px; color: var(--text3);">UNIDAD</th>
                                        <th style="font-size: 10px; color: var(--text3);">DETALLE DE EXONERACIÓN</th>
                                        <th
                                            style="text-align: right; padding-right: 20px; font-size: 10px; color: var(--text3);">
                                            ESTADO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($exonerados as $e)
                                        <tr>
                                            <td style="padding-left: 20px; vertical-align: middle;">
                                                <div style="font-weight: 800; color: var(--text);">
                                                    #{{ $e->vehiculo?->numero_flota ?? '???' }}</div>
                                                <div style="font-size: 10px; color: var(--text3);">
                                                    {{ $e->vehiculo?->placa ?? '---' }}</div>
                                            </td>
                                            <td style="padding: 12px 10px;">
                                                <div class="flex-v" style="gap: 4px;">
                                                    <div
                                                        style="font-size: 11px; color: var(--text2); font-style: italic; line-height: 1.4;">
                                                        <b style="color: var(--text); font-style: normal;">MOTIVO:</b>
                                                        {{ $e->motivo_exoneracion ?? $e->observaciones }}
                                                    </div>
                                                    <div class="flex-h" style="gap: 15px; margin-top: 4px;">
                                                        <div style="font-size: 9px; color: var(--text3);">
                                                            <i class="fa-solid fa-user-tie"
                                                                style="margin-right: 4px; opacity: 0.5;"></i>
                                                            Autor: <b
                                                                style="color: var(--text2);">{{ $e->exonerador?->name ?? 'Sistema' }}</b>
                                                        </div>
                                                        <div style="font-size: 9px; color: var(--text3);">
                                                            <i class="fa-solid fa-clock"
                                                                style="margin-right: 4px; opacity: 0.5;"></i>
                                                            {{ $e->exonerado_at ? $e->exonerado_at->format('h:i A') : '---' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align: right; vertical-align: middle; padding-right: 20px;">
                                                <span class="pill gray"
                                                    style="font-size: 11px; font-weight: 800; opacity: 0.7; letter-spacing: 0.5px;">EXONERADO</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($exonerados->hasPages())
                            <div class="pagination-wrapper" style="padding:15px; border-top:1px solid var(--border);">
                                {{ $exonerados->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- COLUMNA DERECHA: DEUDAS Y PERFORMANCE --}}
            <div class="flex-v" style="gap: 24px;">

                <div class="card" style="border-top: 4px solid var(--sidebar);">
                    <div class="card-header">
                        <div class="card-title text-red"><i class="fa-solid fa-sack-xmark"></i> Morosidad Crítica</div>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Unidad</th>
                                    <th style="text-align: center;">Días</th>
                                    <th style="text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deudas as $deuda)
                                    <tr class="debt-row" data-vehiculo="{{ $deuda->vehiculo_id }}"
                                        style="cursor: pointer;">
                                        <td>
                                            <div style="font-weight: 700;">#{{ $deuda->vehiculo?->numero_flota ?? '???' }}
                                            </div>
                                            <div style="font-size: 10px; color: var(--text3);">
                                                {{ $deuda->vehiculo?->placa ?? '---' }}</div>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="pill {{ $deuda->dias_deuda > 3 ? 'red' : 'orange' }}"
                                                style="font-size: 11px;">
                                                {{ $deuda->dias_deuda }}d
                                            </span>
                                        </td>
                                        <td
                                            style="font-weight: 800; color: var(--red); text-align: right; font-size: 14px;">
                                            S/ {{ number_format($deuda->total_deuda, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>



            </div>
        </div>
    </div>

    {{-- MODAL DE DETALLE DE DEUDA (ESTILO APPLE) --}}
    <div id="modalDeuda" class="modal-overlay">
        <div class="modal" style="width: 480px; border-radius: 20px; border: 1px solid var(--border);">
            <div class="modal-header" style="background: var(--bg);">
                <div>
                    <div class="modal-title" id="modalDeudaTitle" style="font-size: 20px; font-weight: 800;">Detalle
                        Morosidad</div>
                    <div id="modalDeudaSub" style="font-size: 12px; color: var(--text3); margin-top: 2px;">Cargando...
                    </div>
                </div>
                <button class="tb-avatar-btn" style="background: var(--card); color: var(--text3);"
                    onclick="cerrarModalDeuda()">&times;</button>
            </div>
            <div class="tbl-wrap" style="max-height: 400px; padding: 0;">
                <table class="tbl" style="margin: 0;">
                    <thead style="position: sticky; top: 0; background: var(--bg); z-index: 1;">
                        <tr>
                            <th style="padding: 12px 20px;">Fecha Vencimiento</th>
                            <th style="text-align: right; padding: 12px 20px;">Monto Adeudado</th>
                        </tr>
                    </thead>
                    <tbody id="modalDeudaBody"></tbody>
                </table>
            </div>
            <div class="modal-footer flex-between" style="padding: 20px; background: var(--card);">
                <div style="font-size: 12px; font-weight: 800; color: var(--text3);">TOTAL ACUMULADO</div>
                <div id="modalDeudaTotal" style="font-size: 24px; font-weight: 800; color: var(--red);">S/ 0.00</div>
            </div>
        </div>
    </div>

    <script>
        function exonerarTributo(id, placa, fecha) {
            Swal.fire({
                title: 'Exonerar Tributo',
                html: `
                    <div style="text-align: left; font-size: 14px; margin-bottom: 15px;">
                        Estás a punto de exonerar el tributo de la unidad <b>${placa}</b> correspondiente al día <b>${fecha}</b>.
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
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/tributos/${id}/exonerar`;

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

        function confirmarExoneracionMasiva(fecha) {
            Swal.fire({
                title: `¿Exonerar todo el ${fecha}?`,
                html: `
                    <div style="text-align: left; font-size: 14px; margin-bottom: 15px;">
                        Esta acción afectará a todas las unidades con tributos pendientes para el día <b>${fecha}</b>.
                        <br><br>
                        Para confirmar, escribe un motivo global y presiona el botón.
                    </div>
                    <input type="text" id="motivo_masivo" class="swal2-input" placeholder="Ej. Unidades no operaron por feriado" style="font-size: 14px; margin: 0; width: 100%; box-sizing: border-box;">
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--red)',
                confirmButtonText: 'CONFIRMAR MASIVO',
                cancelButtonText: 'CANCELAR',
                preConfirm: () => {
                    const motivo = document.getElementById('motivo_masivo').value;
                    if (!motivo || motivo.trim().length < 5) {
                        Swal.showValidationMessage('El motivo debe tener al menos 5 caracteres');
                        return false;
                    }
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-exonerar-masivo');

                    const inputMotivo = document.createElement('input');
                    inputMotivo.type = 'hidden';
                    inputMotivo.name = 'motivo_exoneracion';
                    inputMotivo.value = result.value;
                    form.appendChild(inputMotivo);

                    form.submit();
                }
            });
        }

        document.querySelectorAll('.debt-row').forEach(row => {
            row.addEventListener('click', function() {
                abrirModalDeuda(this.dataset.vehiculo);
            });
        });

        function abrirModalDeuda(vehiculoId) {
            const modal = document.getElementById('modalDeuda');
            const body = document.getElementById('modalDeudaBody');
            modal.classList.add('open');
            body.innerHTML =
                '<tr><td colspan="2" style="text-align:center; padding:40px;"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>';

            fetch(`/admin/tributos/vehiculo/${vehiculoId}/detalle`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalDeudaTitle').innerText = `#${data.vehiculo.numero_flota} — Historial`;
                    document.getElementById('modalDeudaSub').innerText =
                        `${data.vehiculo.placa} • ${data.vehiculo.propietario}`;
                    document.getElementById('modalDeudaTotal').innerText = `S/ ${parseFloat(data.total).toFixed(2)}`;
                    body.innerHTML = data.detalles.map(d => `
                        <tr>
                            <td style="padding: 12px 20px; font-weight: 600;">${formatearFecha(d.fecha)}</td>
                            <td style="text-align: right; padding: 12px 20px; font-weight: 800; color: var(--red);">S/ ${parseFloat(d.monto).toFixed(2)}</td>
                        </tr>
                    `).join('') ||
                        '<tr><td colspan="2" style="text-align:center; padding:20px;">Sin registros.</td></tr>';
                });
        }

        function cerrarModalDeuda() {
            document.getElementById('modalDeuda').classList.remove('open');
        }

        function formatearFecha(f) {
            return new Date(f + 'T00:00:00').toLocaleDateString('es-ES', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    </script>
@endsection
