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
        
        {{-- COLUMNA 1: FORMULARIO REGISTRO --}}
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
                            <option value="Punto A">Punto A</option>
                            <option value="Punto B">Punto B</option>
                            <option value="Punto C">Punto C</option>
                        </select>
                        <span style="font-size: 11px; color: var(--text3); display: block; margin-top: 4px;">
                            La alerta se enviará en tiempo real a las pantallas de todos los conductores y expirará en 1 hora.
                        </span>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; height: 44px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 700; border-radius: 8px;">
                        <i class="fa-solid fa-paper-plane"></i> Emitir Alerta Real-Time
                    </button>
                </form>
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
                                                $diff = now()->diffInMinutes($alerta->expires_at, false);
                                                echo $diff > 0 ? "{$diff} min" : 'Expirando...';
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
