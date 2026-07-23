@extends('layouts.admin')

@php
    $pageTitle = 'Auditoría Global';
    $pageSubtitle = 'Seguimiento de cambios y acciones por empresa y usuario';
@endphp

@section('content')
<div style="display: grid; gap: 24px;">

    {{-- Filtros --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.auditoria.index') }}" method="GET" class="flex-h" style="gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600;">Empresa</label>
                    <select name="empresa_id" class="form-control">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600;">Evento</label>
                    <select name="event" class="form-control">
                        <option value="">Todos</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Creación</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Modificación</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Eliminación</option>
                    </select>
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600;">Módulo (Modelo)</label>
                    <input type="text" name="model" class="form-control" placeholder="Ej: Vehiculo" value="{{ request('model') }}">
                </div>

                <button type="submit" class="btn-primary" style="height: 42px;">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('superadmin.auditoria.index') }}" class="btn-secondary" style="height: 42px; display: flex; align-items: center;">
                    Limpiar
                </a>
            </form>
        </div>
    </div>

    {{-- Tabla de Auditoría --}}
    <div class="card">
        <div class="tbl-wrap">
            <table class="tbl tbl-modern">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Empresa</th>
                        <th>Usuario</th>
                        <th>Evento</th>
                        <th>Módulo / ID</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $audit->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 11px; color: var(--text3);">{{ $audit->created_at->format('h:i:s A') }}</div>
                            </td>
                            <td>
                                @if($audit->empresa)
                                    <span class="pill blue" style="font-size: 11px;">{{ $audit->empresa->nombre_comercial }}</span>
                                @else
                                    <span class="pill gray" style="font-size: 11px;">Sistema / Global</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $audit->user->name ?? 'Sistema' }}</div>
                                <div style="font-size: 10px; color: var(--text3);">{{ $audit->ip_address }}</div>
                            </td>
                            <td>
                                @php
                                    $color = match($audit->event) {
                                        'created' => 'green',
                                        'updated' => 'orange',
                                        'deleted' => 'red',
                                        default => 'gray'
                                    };
                                    $label = match($audit->event) {
                                        'created' => 'CREADO',
                                        'updated' => 'EDITADO',
                                        'deleted' => 'ELIMINADO',
                                        default => strtoupper($audit->event)
                                    };
                                @endphp
                                <span class="pill {{ $color }}" style="font-size: 10px; font-weight: 800;">{{ $label }}</span>
                            </td>
                            <td>
                                <div style="font-size: 12px; font-weight: 600; color: var(--accent);">
                                    {{ class_basename($audit->auditable_type) }}
                                </div>
                                <div style="font-size: 10px; color: var(--text3);">ID: {{ $audit->auditable_id }}</div>
                            </td>
                            <td style="text-align: right;">
                                <button onclick="verDetalle({{ $audit->id }})" class="btn-secondary" style="padding: 5px 10px; font-size: 11px;">
                                    <i class="fa-solid fa-eye"></i> Ver Cambios
                                </button>
                                
                                {{-- Contenedor oculto para el detalle (usaremos un modal o similar) --}}
                                <div id="detail-{{ $audit->id }}" style="display: none;">
                                    <div class="audit-details">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                            <div>
                                                <h4 style="font-size: 12px; color: var(--red); margin-bottom: 10px;">Valor Anterior</h4>
                                                <pre style="font-size: 11px; background: #fff5f5; padding: 10px; border-radius: 5px; overflow: auto;">{{ json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            <div>
                                                <h4 style="font-size: 12px; color: var(--green); margin-bottom: 10px;">Valor Nuevo</h4>
                                                <pre style="font-size: 11px; background: #f5fff5; padding: 10px; border-radius: 5px; overflow: auto;">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 50px; color: var(--text3);">
                                No hay registros de auditoría que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $audits->links() }}
        </div>
    </div>

</div>

{{-- Modal Simple para Detalles --}}
<div id="auditModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 900px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">Detalle de Cambios</div>
            <button onclick="cerrarModal()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <div class="card-body" id="modalBody" style="overflow-y: auto; background: var(--bg);">
            <!-- Contenido dinámico -->
        </div>
    </div>
</div>

<script>
function verDetalle(id) {
    const detail = document.getElementById('detail-' + id).innerHTML;
    document.getElementById('modalBody').innerHTML = detail;
    document.getElementById('auditModal').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('auditModal').style.display = 'none';
}

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    if (event.target == document.getElementById('auditModal')) {
        cerrarModal();
    }
}
</script>

<style>
.audit-details pre {
    white-space: pre-wrap;
    word-break: break-all;
    max-height: 400px;
}
</style>
@endsection
