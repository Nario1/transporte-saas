@extends('layouts.admin')

@section('content')
<div class="panel">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em;">Copia de Seguridad (Backups)</h2>
            <p style="color: var(--text3); font-size: 15px;">Gestión de respaldos maestros globales y respaldos independientes por empresa.</p>
        </div>
        <form action="{{ route('superadmin.backups.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">
                <i class="fa-solid fa-database"></i> RESPALDO TOTAL GLOBAL
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert success" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert danger" style="margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 24px;">
        
        {{-- COLUMNA IZQUIERDA: EMPRESAS --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa-solid fa-building" style="color: var(--accent); margin-right: 8px;"></i> Respaldar por Empresa</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th style="text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($empresas as $emp)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; font-size: 13.5px; color: var(--text);">{{ $emp->nombre }}</div>
                                            <div style="font-size: 11px; color: var(--text3);">RUC: {{ $emp->ruc ?? '---' }}</div>
                                        </td>
                                        <td style="text-align: right;">
                                            <form action="{{ route('superadmin.backups.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="empresa_id" value="{{ $emp->id }}">
                                                <button type="submit" class="btn-primary btn-sm" style="padding: 6px 12px; font-size: 11px;">
                                                    <i class="fa-solid fa-plus"></i> RESPALDAR
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 20px; color: var(--text3);">
                                            No hay empresas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: HISTORIAL --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color: var(--accent); margin-right: 8px;"></i> Historial de Copias de Seguridad</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Ámbito</th>
                                    <th>Tamaño</th>
                                    <th>Nombre del Archivo</th>
                                    <th class="col-actions">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text);">{{ $backup->created_at->format('d/m/Y') }}</div>
                                            <div style="font-size: 11px; color: var(--text3);">{{ $backup->created_at->format('H:i:s') }}</div>
                                        </td>
                                        <td>
                                            @if($backup->empresa_id)
                                                <span class="badge blue" style="font-size: 10px; font-weight: 800; background-color: #e0f2fe; color: #0369a1;">
                                                    {{ $backup->empresa?->nombre ?? 'Empresa Eliminada' }}
                                                </span>
                                            @else
                                                <span class="badge gold" style="font-size: 10px; font-weight: 800; background-color: #fef3c7; color: #d97706;">
                                                    GLOBAL (TODAS)
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; font-size: 12px; color: var(--text2);">
                                                {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                            </span>
                                        </td>
                                        <td>
                                            <code class="mono" style="font-size: 11px; color: var(--text3); word-break: break-all;">{{ $backup->filename }}</code>
                                        </td>
                                        <td class="col-actions">
                                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                                <a href="{{ route('superadmin.backups.download', $backup) }}" class="btn-secondary btn-sm" style="color: var(--green); border-color: var(--green-l); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;" title="Descargar">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <form action="{{ route('superadmin.backups.destroy', $backup) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente este respaldo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-secondary btn-sm" style="color: var(--red); border-color: var(--red-l); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;" title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text3);">
                                            No se han generado copias de seguridad todavía.
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
@endsection
