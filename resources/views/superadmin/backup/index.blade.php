@extends('layouts.admin')

@section('content')
<div class="panel">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em;">Respaldo Maestro del Sistema</h2>
            <p style="color: var(--text3); font-size: 15px;">Gestión de copias de seguridad globales de toda la infraestructura.</p>
        </div>
        <form action="{{ route('superadmin.backups.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">
                <i class="fa-solid fa-plus"></i> GENERAR RESPALDO TOTAL
            </button>
        </form>
    </div>

    <div class="alert info" style="margin-bottom: 24px;">
        <i class="fa-solid fa-circle-info"></i>
        Este módulo genera una copia completa de la base de datos (todas las empresas). Las copias automáticas se ejecutan semanalmente.
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de Respaldos Globales</h3>
        </div>
        <div class="tbl-wrap">
            <table class="tbl tbl-modern">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Nombre del Archivo</th>
                        <th>Tamaño</th>
                        <th>Tipo</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>
                                <span class="text-main">{{ $backup->created_at->format('d/m/Y') }}</span>
                                <span class="text-sub">{{ $backup->created_at->format('H:i:s') }}</span>
                            </td>
                            <td>
                                <code class="mono" style="font-size: 12px;">{{ $backup->filename }}</code>
                            </td>
                            <td>
                                <span class="pill blue" style="font-size: 11px;">
                                    {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                </span>
                            </td>
                            <td>
                                @if($backup->type === 'manual')
                                    <span class="pill gold" style="font-size: 11px;">Manual</span>
                                @else
                                    <span class="pill green" style="font-size: 11px;">Automático</span>
                                @endif
                            </td>
                            <td class="col-actions">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('superadmin.backups.download', $backup) }}" class="btn-secondary btn-sm" style="color: var(--green); border-color: var(--green-l);">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <form action="{{ route('superadmin.backups.destroy', $backup) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente este respaldo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary btn-sm" style="color: var(--red); border-color: var(--red-l);">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text3);">
                                No se han generado respaldos globales todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
