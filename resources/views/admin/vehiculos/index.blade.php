@extends('layouts.admin')

@php
    $pageTitle = 'Vehículos';
    $pageSubtitle = 'Gestión integral de flota';
@endphp

@section('content')
    <div class="panel">
        {{-- 1. INDICADORES --}}
        <div class="stats-row g-3">
            <div class="stat blue">
                <div class="stat-label">Flota Total</div>
                <div class="stat-val">{{ $resumen['total'] }}</div>
                <div class="stat-sub">{{ $resumen['activos'] }} unidades activas</div>
                <span class="stat-icon"><i class="fa-solid fa-bus"></i></span>
            </div>
            <div class="stat green">
                <div class="stat-label">Operatividad</div>
                <div class="stat-val">
                    {{ number_format($resumen['total'] > 0 ? ($resumen['activos'] / $resumen['total']) * 100 : 0, 0) }}%
                </div>
                <div class="stat-sub">Disponibilidad de flota</div>
                <span class="stat-icon"><i class="fa-solid fa-gauge-high"></i></span>
            </div>
            <div class="stat red" style="flex: 1.5;">
                <div class="stat-label">Alertas Documentarias</div>
                <div class="stat-val" style="font-size: 20px;">
                    <div class="flex-h" style="gap: 15px; align-items: center;">
                        <div title="SOAT Vencidos"><span style="font-weight: 800; color: var(--red);">{{ $resumen['soat_vencidos'] }}</span> <span style="font-size: 11px; font-weight: 700; color: var(--text3);">SOAT</span></div>
                        <div style="width: 1px; height: 15px; background: var(--border);"></div>
                        <div title="Rev. Técnicas Vencidas"><span style="font-weight: 800; color: var(--red);">{{ $resumen['rev_vencidos'] }}</span> <span style="font-size: 11px; font-weight: 700; color: var(--text3);">REV.</span></div>
                        <div style="width: 1px; height: 15px; background: var(--border);"></div>
                        <div title="Tarjetas Prop. Vencidas"><span style="font-weight: 800; color: var(--red);">{{ $resumen['tar_vencidos'] }}</span> <span style="font-size: 11px; font-weight: 700; color: var(--text3);">TARJ.</span></div>
                        <div style="width: 1px; height: 15px; background: var(--border);"></div>
                        <div title="Licencias Vencidas"><span style="font-weight: 800; color: var(--red);">{{ $resumen['lic_vencidas'] }}</span> <span style="font-size: 11px; font-weight: 700; color: var(--text3);">LIC.</span></div>
                    </div>
                </div>
                <div class="stat-sub">Flota activa con documentos vencidos</div>
                <span class="stat-icon"><i class="fa-solid fa-file-circle-exclamation"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES Y FILTROS --}}
        <div class="flex-between gap-24">
            <div class="card" style="flex: 1;">
                <form method="GET" action="{{ route('vehiculos.index') }}" class="card-body g-filters">
                    <div class="field" style="flex: 2;">
                        <label>Buscar Unidad</label>
                        <input type="text" name="q" placeholder="Placa o N° Flota..." value="{{ request('q') }}">
                    </div>
                    <div class="field" style="flex: 1;">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">TODOS</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>ACTIVO</option>
                            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>INACTIVO</option>
                            <option value="mantenimiento" {{ request('estado') === 'mantenimiento' ? 'selected' : '' }}>MANTENIMIENTO</option>
                        </select>
                    </div>
                    <div class="field" style="flex: 1;">
                        <label>Ruta</label>
                        <select name="ruta_id">
                            <option value="">TODAS LAS RUTAS</option>
                            @foreach ($rutas as $ruta)
                                <option value="{{ $ruta->id }}" {{ request('ruta_id') == $ruta->id ? 'selected' : '' }}>
                                    {{ $ruta->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-h">
                        <button type="submit" class="btn-primary" style="height: 48px; width: 48px; justify-content: center; padding:0;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        @if (request()->hasAny(['q', 'estado', 'ruta_id']))
                            <a href="{{ route('vehiculos.index') }}" class="btn-secondary" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <a href="{{ route('vehiculos.create') }}" class="btn-primary" style="padding: 0 32px; height: 70px; border-radius: 20px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-plus-circle"></i> NUEVO VEHÍCULO
            </a>
        </div>

        {{-- ── Tabla ── --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Listado de Flota</div>
                    <div style="font-size:12px; color:var(--text3); margin-top:2px;">
                        {{ $vehiculos->total() }} vehículo(s) en sistema
                    </div>
                </div>
            </div>
            <div class="tbl-wrap">
                <table class="tbl tbl-modern">
                    <thead>
                        <tr>
                            <th>PLACA / N° FLOTA</th>
                            <th>VEHICULO / MODELO</th>
                            <th>PROPIETARIO</th>
                            <th>CONDUCTOR</th>
                            <th>RUTA</th>
                            <th style="text-align: center;">DOCUMENTOS</th>
                            <th class="col-status">ESTADO</th>
                            <th class="col-actions">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehiculos as $v)
                            @php
                                $hoy      = now();
                                $soatVence = $v->soat_vence;
                                $revVence  = $v->rev_tecnica_vence;
                                $tarVence  = $v->tarjeta_prop_vence;
                                $licVence  = $v->conductor ? $v->conductor->licencia_vence : null;

                                $soatDiff = $soatVence ? $hoy->diffInDays($soatVence, false) : null;
                                $revDiff  = $revVence  ? $hoy->diffInDays($revVence,  false) : null;
                                $tarDiff  = $tarVence  ? $hoy->diffInDays($tarVence,  false) : null;
                                $licDiff  = $licVence  ? $hoy->diffInDays($licVence,  false) : null;

                                $soatColor = $soatVence ? ($soatDiff < 0 ? 'var(--red)' : ($soatDiff < 30 ? 'var(--orange)' : 'var(--green)')) : 'var(--text3)';
                                $revColor  = $revVence  ? ($revDiff  < 0 ? 'var(--red)' : ($revDiff  < 30 ? 'var(--orange)' : 'var(--green)')) : 'var(--text3)';
                                $tarColor  = $tarVence  ? ($tarDiff  < 0 ? 'var(--red)' : ($tarDiff  < 30 ? 'var(--orange)' : 'var(--green)')) : 'var(--text3)';
                                $licColor  = $licVence  ? ($licDiff  < 0 ? 'var(--red)' : ($licDiff  < 30 ? 'var(--orange)' : 'var(--green)')) : 'var(--text3)';
                            @endphp
                            <tr>
                                <td>
                                    <span class="text-main">{{ $v->placa }}</span>
                                    <span class="text-sub">F-{{ $v->numero_flota ?? 'S/N' }}</span>
                                </td>
                                <td>
                                    <span class="text-main">{{ $v->marca }} {{ $v->modelo }}</span>
                                    <span class="text-sub">{{ $v->anio }} • {{ $v->color }}</span>
                                </td>
                                <td>
                                    @if($v->propietario)
                                        <span class="text-main">{{ $v->propietario->nombre_completo }}</span>
                                        <span class="text-sub">DNI: {{ $v->propietario->dni ?? '---' }}</span>
                                    @else
                                        <span class="text-sub">Sin Propietario</span>
                                    @endif
                                </td>
                                <td>
                                    @if($v->conductor)
                                        <div class="flex-h" style="gap: 6px; align-items: center;">
                                            <span class="text-main">{{ $v->conductor->nombre_completo }}</span>
                                            @if($v->conductor->tiene_acceso)
                                                <i class="fa-solid fa-mobile-screen-button" style="font-size: 11px; color: var(--green);" title="Conductor tiene acceso a la App"></i>
                                            @endif
                                        </div>
                                        <span class="text-sub">Cat. {{ $v->conductor->tipo_licencia ?? '---' }}</span>
                                    @else
                                        <span class="text-sub">Sin Conductor</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $rutasActivas = $v->rutas->where('pivot.activo', true);
                                    @endphp
                                    @if($rutasActivas->isNotEmpty())
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @foreach($rutasActivas as $rt)
                                                <span style="display:inline-block; background:var(--accent-l); color:var(--accent); font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; white-space:nowrap;">
                                                    {{ $rt->nombre }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sub">---</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div class="flex-v" style="align-items: center; gap: 6px;">
                                        <div class="flex-h" style="justify-content: center; gap: 8px; font-size: 10px;">
                                            <i class="fa-solid fa-circle" style="color: {{ $soatColor }};" title="SOAT: {{ $soatVence ? $soatVence->format('d/m/Y') : 'No registrado' }}"></i>
                                            <i class="fa-solid fa-circle" style="color: {{ $revColor }};" title="Rev. Técnica: {{ $revVence ? $revVence->format('d/m/Y') : 'No registrado' }}"></i>
                                            <i class="fa-solid fa-circle" style="color: {{ $tarColor }};" title="T. Propiedad: {{ $tarVence ? $tarVence->format('d/m/Y') : 'No registrado' }}"></i>
                                            <i class="fa-solid fa-circle" style="color: {{ $licColor }};" title="Licencia: {{ $licVence ? $licVence->format('d/m/Y') : 'No registrado' }}"></i>
                                        </div>
                                        @php
                                            $docData = json_encode([
                                                "placa" => $v->placa,
                                                "flota" => $v->numero_flota ?? "S/N",
                                                "url" => route("vehiculos.show", $v->id),
                                                "docs" => [
                                                    [
                                                        "label" => "SOAT",
                                                        "date" => $soatVence ? $soatVence->format("d/m/Y") : null,
                                                        "diff" => $soatDiff
                                                    ],
                                                    [
                                                        "label" => "REV. TÉCNICA",
                                                        "date" => $revVence ? $revVence->format("d/m/Y") : null,
                                                        "diff" => $revDiff
                                                    ],
                                                    [
                                                        "label" => "TARJETA PROP.",
                                                        "date" => $tarVence ? $tarVence->format("d/m/Y") : null,
                                                        "diff" => $tarDiff
                                                    ],
                                                    [
                                                        "label" => "LICENCIA COND.",
                                                        "date" => $licVence ? $licVence->format("d/m/Y") : null,
                                                        "diff" => $licDiff
                                                    ]
                                                ]
                                            ]);
                                        @endphp
                                        <button type="button" class="btn-secondary btn-sm" 
                                            style="font-size: 10px; padding: 2px 8px; font-weight: 800; border-radius: 6px;"
                                            data-info="{{ $docData }}"
                                            onclick="openDocModal(JSON.parse(this.getAttribute('data-info')))">
                                            VER
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="pill {{ $v->estado === 'activo' ? 'green' : ($v->estado === 'mantenimiento' ? 'orange' : 'red') }}" style="font-size: 11px;">
                                        {{ strtoupper($v->estado) }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <div class="flex-h" style="justify-content: flex-end; gap: 4px;">
                                        <a href="{{ route('vehiculos.show', $v->id) }}" class="action-icon show-icon" title="Ver Detalle">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('vehiculos.edit', $v->id) }}" class="action-icon edit-icon" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('vehiculos.destroy', $v->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-icon-submit" onclick="return confirm('¿Eliminar unidad {{ $v->placa }}?')" title="Eliminar">
                                                <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:60px; color:var(--text3);">
                                    <i class="fa-solid fa-bus-slash" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                                    @if (request()->hasAny(['q', 'estado', 'ruta_id']))
                                        No se encontraron vehículos con esos filtros.
                                        <a href="{{ route('vehiculos.index') }}" style="color:var(--accent); text-decoration:none; font-weight: 700;">Limpiar búsqueda</a>
                                    @else
                                        No hay vehículos registrados en la flota.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($vehiculos->hasPages())
                <div style="padding:20px; border-top:1px solid var(--border);">
                    {{ $vehiculos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL VISTA RÁPIDA DOCUMENTOS --}}
    <div id="docModal" class="modal-overlay" onclick="closeDocModal(event)">
        <div class="modal" style="width: 450px;" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="flex-v">
                    <div class="card-title" id="modalPlaca">---</div>
                    <div style="font-size: 11px; color: var(--text3); font-weight: 700;" id="modalFlota">UNIDAD #--</div>
                </div>
                <button onclick="closeDocModal()" style="background:none; border:none; cursor:pointer; color:var(--text3); font-size: 20px;">&times;</button>
            </div>
            <div class="modal-body flex-v" style="gap: 16px;" id="modalDocsContainer">
                {{-- Contenido dinámico renderizado por JS --}}
            </div>
            <div class="modal-footer" style="background: var(--bg); border-radius: 0 0 16px 16px;">
                <button onclick="closeDocModal()" class="btn-secondary" style="font-size: 13px;">Cerrar</button>
                <a href="#" id="modalUrl" class="btn-primary" style="font-size: 13px; font-weight: 700;">
                    VER DETALLE COMPLETO
                </a>
            </div>
        </div>
    </div>

    <script>
        function openDocModal(data) {
            document.getElementById('modalPlaca').innerText = 'Vehículo ' + data.placa;
            document.getElementById('modalFlota').innerText = 'UNIDAD #' + data.flota;
            document.getElementById('modalUrl').href = data.url;

            const container = document.getElementById('modalDocsContainer');
            container.innerHTML = '';

            data.docs.forEach(doc => {
                let statusClass = '';
                let bgColor = '';
                let width = '0%';
                let subText = 'NO REGISTRADO';

                if (doc.date) {
                    if (doc.diff < 0) {
                        statusClass = 'date-expired';
                        bgColor = 'var(--red)';
                        width = '100%';
                        subText = '⚠️ VENCIDO HACE ' + Math.abs(Math.floor(doc.diff)) + ' DÍAS';
                    } else if (doc.diff < 30) {
                        statusClass = 'date-warning';
                        bgColor = 'var(--orange)';
                        width = ((doc.diff / 365) * 100) + '%';
                        subText = 'Vence en ' + Math.floor(doc.diff) + ' días';
                    } else {
                        statusClass = 'date-valid';
                        bgColor = 'var(--green)';
                        width = ((doc.diff / 365) * 100) + '%';
                        if (doc.diff > 365) width = '100%';
                        subText = 'Vence en ' + Math.floor(doc.diff) + ' días';
                    }
                }

                const dateText = doc.date ? doc.date : 'NO REGISTRADO';

                let html = `
                    <div class="flex-v" style="gap: 6px; padding-bottom: 12px; border-bottom: 1px solid var(--border-l);">
                        <div class="flex-between">
                            <span style="font-size: 12px; font-weight: 800; color: var(--text2);">${doc.label}</span>
                            <span class="mono ${statusClass}" style="font-size: 11px;">${dateText}</span>
                        </div>`;
                
                if (doc.date) {
                    html += `
                        <div class="progress-track" style="background: var(--border); height: 6px; border-radius: 3px; overflow: hidden; margin-top: 4px;">
                            <div class="progress-fill" style="width: ${width}; background: ${bgColor}; height: 100%; border-radius: 3px; transition: width 0.3s ease;"></div>
                        </div>
                        <div style="font-size: 10px; color: var(--text3); text-align: right;">${subText}</div>`;
                }
                html += `</div>`;
                container.innerHTML += html;
            });

            document.getElementById('docModal').classList.add('open');
        }

        function closeDocModal(event) {
            document.getElementById('docModal').classList.remove('open');
        }
    </script>
@endsection
