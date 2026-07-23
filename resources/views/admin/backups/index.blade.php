@extends('layouts.admin')

@php
    $pageTitle = 'Copias de Seguridad';
    $pageSubtitle = 'Gestión y automatización de respaldos de la base de datos';
@endphp

@php
    $routePrefix = request()->routeIs('superadmin.*') ? 'superadmin.' : '';
@endphp

@section('content')
<div style="display: grid; gap: 24px;">

    {{-- Resumen y Acción --}}
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div class="alert info" style="margin-bottom: 0; flex: 1; margin-right: 20px;">
            <i class="fa-solid fa-circle-info"></i>
            {{ $routePrefix ? 'Copia de seguridad global del sistema.' : 'Copia de seguridad exclusiva de su empresa.' }}
        </div>
        <form action="{{ route($routePrefix . 'backups.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px;">
                <i class="fa-solid fa-plus"></i> GENERAR COPIA AHORA
            </button>
        </form>
    </div>

    {{-- Tabla de Backups --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Historial de Respaldos</div>
        </div>
        <div class="tbl-wrap">
            <table class="tbl tbl-modern">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Fecha de Creación</th>
                        <th>Tamaño</th>
                        <th>Tipo</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--accent);">
                                    <i class="fa-solid fa-file-export"></i> {{ $backup->filename }}
                                </div>
                                <small style="color: var(--text3);">{{ $backup->path }}</small>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $backup->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 11px; color: var(--text3);">{{ $backup->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <span class="mono" style="font-size: 12px; background: var(--bg); padding: 2px 6px; border-radius: 4px;">
                                    {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                </span>
                            </td>
                            <td>
                                <span class="pill {{ $backup->type === 'manual' ? 'blue' : 'green' }}" style="font-size: 10px;">
                                    {{ strtoupper($backup->type) }}
                                </span>
                            </td>
                            <td class="col-actions">
                                <div class="flex-h" style="justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route($routePrefix . 'backups.download', $backup) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--green); border-color: var(--green);">
                                        <i class="fa-solid fa-download"></i> Descargar
                                    </a>
                                    
                                    <form action="{{ route($routePrefix . 'backups.destroy', $backup) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta copia de seguridad permanente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--red); border-color: var(--red);">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px; color: var(--text3);">
                                <i class="fa-solid fa-database" style="font-size: 40px; margin-bottom: 15px; opacity: 0.3; display: block;"></i>
                                No se han generado copias de seguridad todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
