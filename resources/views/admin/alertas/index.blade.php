@extends('layouts.admin')

@section('content')
@can('gestionar alertas')

<div class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
        <div>
            <h2 style="font-size:20px;font-weight:800;">Alertas de Operativos en la Ruta</h2>
            <div style="font-size:12px;color:var(--text3);">
                <i class="fa-solid fa-bullhorn" style="color:var(--accent);"></i> Centro de Notificación y Prevención de Inspecciones
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert success" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert error" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start;">
        
        {{-- COLUMNA 1: FORMULARIO REGISTRO & PUNTOS DE CONTROL --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- REPORTAR OPERATIVO --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-plus-circle" style="color:var(--accent); margin-right:5px;"></i> Reportar Nuevo Operativo</span>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form action="{{ route('admin.alertas.store') }}" method="POST">
                        @csrf
                        
                        <div class="field" style="margin-bottom: 20px;">
                            <label style="font-weight:700; font-size:13px; color:var(--text); margin-bottom:8px; display:block;">Punto Crítico / Ubicación</label>
                            <select name="punto" required class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:14px; font-weight:700; color:var(--text);">
                                <option value="">-- Seleccionar punto --</option>
                                @foreach($puntos as $punto)
                                    <option value="{{ $punto->nombre }}">{{ $punto->nombre }}</option>
                                @endforeach
                            </select>
                            <span style="font-size: 11px; color: var(--text3); display: block; margin-top: 4px;">
                                La alerta se enviará en tiempo real a las pantallas de todos los conductores y expirará en 1 hora.
                            </span>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; height: 44px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 700; border-radius: 8px;" @if($puntos->isEmpty()) disabled title="Agrega opciones primero" @endif>
                            <i class="fa-solid fa-paper-plane"></i> Emitir Alerta Real-Time
                        </button>
                    </form>
                </div>
            </div>

            {{-- GESTIÓN DE PUNTOS DE CONTROL --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-map-pin" style="color:var(--accent); margin-right:5px;"></i> Opciones de Puntos de Control</span>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form action="{{ route('admin.puntos.store') }}" method="POST" style="margin-bottom: 20px; display: flex; gap: 8px;">
                        @csrf
                        <div class="field" style="margin: 0; flex: 1;">
                            <input type="text" name="nombre" placeholder="Ej: Óvalo Sumar" required class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:13px; font-weight:700; height: 38px;">
                        </div>
                        <button type="submit" class="btn-primary" style="height: 38px; padding: 0 14px; font-size: 12px; font-weight: 700; border-radius: 8px; flex-shrink:0;">
                            + Agregar
                        </button>
                    </form>

                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 5px;">
                        @forelse($puntos as $pt)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-weight: 700; font-size: 13px; color: var(--text);">{{ $pt->nombre }}</span>
                                <form action="{{ route('admin.puntos.destroy', $pt->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--red); cursor: pointer; font-size: 12px;" onclick="return confirm('¿Seguro que deseas eliminar este punto de control?')" title="Eliminar punto">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text3); font-size: 12px; padding: 15px;">
                                No hay opciones registradas.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA 2: LISTADOS --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- ALERTAS ACTIVAS --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color:var(--red); margin-right:5px;"></i> Controles Activos Ahora</span>
                    <span class="badge" style="background:var(--red-l); color:var(--red); font-weight:800; font-size:11px; padding:3px 8px; border-radius:6px;">{{ $activas->count() }}</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th>Reportado por</th>
                                    <th>Hora Reporte</th>
                                    <th>Expira en</th>
                                    <th class="col-actions">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activas as $alerta)
                                    <tr>
                                        <td style="font-weight: 800; color: var(--red); font-size: 14px;">
                                            <i class="fa-solid fa-warning" style="margin-right: 5px;"></i> {{ $alerta->punto }}
                                        </td>
                                        <td style="font-weight: 700;">
                                            @if($alerta->conductor)
                                                <span style="color:var(--accent);">Conductor:</span> {{ $alerta->conductor->nombre_completo }}
                                            @else
                                                <span style="color:var(--green);">Admin:</span> {{ $alerta->user->name }}
                                            @endif
                                        </td>
                                        <td>{{ $alerta->created_at->format('h:i A') }}</td>
                                        <td style="font-family: monospace; font-weight: 800;">
                                            @php
                                                $diffSeconds = now()->diffInSeconds($alerta->expires_at, false);
                                                if ($diffSeconds > 0) {
                                                    $mins = floor($diffSeconds / 60);
                                                    $secs = $diffSeconds % 60;
                                                    echo sprintf("%02dm %02ds", $mins, $secs);
                                                } else {
                                                    echo 'Expirando...';
                                                }
                                            @endphp
                                        </td>
                                        <td class="col-actions">
                                            <form action="{{ route('admin.alertas.finalizar', $alerta) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-secondary" style="font-size: 11px; padding: 6px 12px; background: var(--green); color: white; border: none; font-weight: 700; border-radius: 6px; cursor: pointer;">
                                                    <i class="fa-solid fa-check-double"></i> Retirado
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:40px; color:var(--text3);">
                                            <div style="font-size:32px; margin-bottom:10px;">🛡️</div>
                                            <div style="font-weight:700; font-size:14px; color:var(--text);">No hay reportes de operativos activos</div>
                                            <div style="font-size:11px; margin-top:2px;">Todo parece estar libre en la ruta por ahora.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- HISTORIAL RECIENTE --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--text3); margin-right:5px;"></i> Historial Reciente (Últimos Reportes)</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th>Reportado por</th>
                                    <th>Fecha y Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $alerta)
                                    <tr>
                                        <td style="font-weight: 700;">{{ $alerta->punto }}</td>
                                        <td>
                                            @if($alerta->conductor)
                                                Conductor: {{ $alerta->conductor->nombre_completo }}
                                            @else
                                                Admin: {{ $alerta->user->name }}
                                            @endif
                                        </td>
                                        <td>{{ $alerta->created_at->format('d/m/Y h:i A') }}</td>
                                        <td>
                                            @if($alerta->estado === 'finalizado')
                                                <span class="pill green" style="font-size: 10px; font-weight:800; padding:2px 6px;">Retirado</span>
                                            @else
                                                <span class="pill gray" style="font-size: 10px; font-weight:800; padding:2px 6px;">Expirado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding:30px; color:var(--text3); font-size:12px;">
                                            Sin registros anteriores.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@endcan
@endsection
