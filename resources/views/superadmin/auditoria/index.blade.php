@extends('layouts.admin')

@section('content')
<div class="panel">
    <div style="margin-bottom: 5px;">
        <h2 style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em;">Auditoría Global de Operaciones</h2>
        <p style="color: var(--text3); font-size: 15px;">Seguimiento detallado de todas las acciones realizadas en la plataforma.</p>
    </div>

    {{-- Filtros Avanzados --}}
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body">
            <form action="{{ route('superadmin.auditoria.index') }}" method="GET">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="field">
                        <label>Filtrar por Empresa</label>
                        <select name="empresa_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Todas las empresas</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Usuario Responsable</label>
                        <select name="user_id" class="form-control" onchange="this.form.submit()" {{ empty($users) ? 'disabled' : '' }}>
                            <option value="">{{ empty($users) ? 'Seleccione primero una empresa' : 'Todos los usuarios' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Evento / Acción</label>
                        <select name="event" class="form-control" onchange="this.form.submit()">
                            <option value="">Cualquier evento</option>
                            <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Creación (Created)</option>
                            <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Modificación (Updated)</option>
                            <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Eliminación (Deleted)</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Búsqueda Universal</label>
                        <input type="text" name="search" class="form-control" placeholder="Usuario, Empresa o Módulo..." value="{{ request('search') }}">
                    </div>

                    <div class="field" style="display: flex; flex-direction: row; gap: 10px; align-items: flex-end;">
                        <button type="submit" class="btn-primary" style="flex: 1; height: 42px; justify-content: center;">
                            <i class="fa-solid fa-search"></i> BUSCAR
                        </button>
                        <a href="{{ route('superadmin.auditoria.index') }}" class="btn-secondary" style="height: 42px; display: flex; align-items: center; justify-content: center; padding: 0 20px;">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Resultados --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bitácora de Cambios</h3>
        </div>
        <div class="tbl-wrap">
            <table class="tbl tbl-modern">
                <thead>
                    <tr>
                        <th>Momento</th>
                        <th>Origen (Empresa)</th>
                        <th>Autor (Usuario)</th>
                        <th colspan="2">Operación Realizada</th>
                        <th class="col-actions">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>
                                <span class="text-main">{{ $audit->created_at->format('d/m/Y') }}</span>
                                <span class="text-sub">{{ $audit->created_at->format('h:i:s A') }}</span>
                            </td>
                            <td>
                                @if($audit->empresa)
                                    <span class="pill blue" style="font-size: 11px;">{{ $audit->empresa->nombre }}</span>
                                @else
                                    <span class="pill gray" style="font-size: 11px;">SISTEMA GLOBAL</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-main">{{ $audit->user->name ?? 'Sistema' }}</span>
                                <span class="text-sub">{{ $audit->ip_address }}</span>
                            </td>
                            <td colspan="2">
                                {!! $audit->descripcion_accion !!}
                            </td>
                            <td class="col-actions">
                                <button onclick="verDetalle({{ $audit->id }})" class="btn-secondary btn-sm">
                                    <i class="fa-solid fa-eye"></i> VER
                                </button>
                                
                                <div id="detail-{{ $audit->id }}" style="display: none;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 10px;">
                                        <div>
                                            <div style="font-size: 11px; font-weight: 800; color: var(--red); margin-bottom: 12px; text-transform: uppercase; border-bottom: 1px solid var(--border); padding-bottom: 5px;">Estado Anterior</div>
                                            <div style="background: var(--bg); padding: 15px; border-radius: 10px; border: 1px solid var(--border); font-size: 12px; color: var(--text2); max-height: 350px; overflow-y: auto;">
                                                @if($audit->old_values)
                                                    @foreach($audit->old_values as $key => $value)
                                                        <div style="margin-bottom: 6px;">
                                                            <strong style="color: var(--text); text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}:</strong> 
                                                            <span style="font-family: 'JetBrains Mono', monospace;">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <em style="color: var(--text3);">Sin valores anteriores</em>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 11px; font-weight: 800; color: var(--green); margin-bottom: 12px; text-transform: uppercase; border-bottom: 1px solid var(--border); padding-bottom: 5px;">Estado Nuevo</div>
                                            <div style="background: var(--bg); padding: 15px; border-radius: 10px; border: 1px solid var(--border); font-size: 12px; color: var(--text2); max-height: 350px; overflow-y: auto;">
                                                @if($audit->new_values)
                                                    @foreach($audit->new_values as $key => $value)
                                                        <div style="margin-bottom: 6px;">
                                                            <strong style="color: var(--text); text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}:</strong> 
                                                            <span style="font-family: 'JetBrains Mono', monospace;">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <em style="color: var(--text3);">Sin nuevos valores</em>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 15px; padding: 15px; background: var(--card); border-radius: 8px; border-left: 4px solid var(--accent); font-size: 12px; color: var(--text2);">
                                        <div style="margin-bottom: 4px;"><strong><i class="fa-solid fa-link"></i> URL:</strong> <span style="color: var(--accent);">{{ $audit->url }}</span></div>
                                        <div><strong><i class="fa-solid fa-laptop-code"></i> Agente:</strong> <span style="color: var(--text3); font-size: 11px;">{{ $audit->user_agent }}</span></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px; color: var(--text3);">
                                <i class="fa-solid fa-folder-open" style="font-size: 30px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                No se encontraron registros de auditoría.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($audits->hasPages())
            <div class="card-footer">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Estilizado --}}
<div id="auditModal" class="modal-overlay" onclick="cerrarModal()">
    <div class="modal" style="width: 1000px;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Detalle de la Operación</h3>
            <button onclick="cerrarModal()" style="background: none; border: none; font-size: 24px; color: var(--text3); cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer" style="padding: 12px 24px;">
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar Ventana</button>
        </div>
    </div>
</div>

<script>
function verDetalle(id) {
    const detail = document.getElementById('detail-' + id).innerHTML;
    document.getElementById('modalBody').innerHTML = detail;
    document.getElementById('auditModal').classList.add('open');
}

function cerrarModal() {
    document.getElementById('auditModal').classList.remove('open');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') cerrarModal();
});
</script>

<style>
/* Quitar el punto de los pills en esta vista específica */
.pill::before {
    display: none !important;
}
</style>
@endsection
