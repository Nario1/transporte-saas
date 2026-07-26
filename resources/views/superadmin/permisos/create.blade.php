@extends('layouts.admin')

@section('back_url', route('superadmin.permisos.index'))

@php
    $pageTitle = 'Nuevo Permiso Global';
    $pageSubtitle = 'Añade un nuevo permiso para todo el sistema';
@endphp

@section('content')
    <div style="max-width: 600px; margin: 0 auto;">
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">Registrar Nuevo Permiso</div>
            </div>
            
            <form action="{{ route('superadmin.permisos.store') }}" method="POST" class="card-body">
                @csrf
                
                @if ($errors->any())
                    <div style="background: #fff5f5; border-left: 5px solid var(--red); color: var(--red); padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; display: flex; flex-direction: column; gap: 5px;">
                        @foreach ($errors->all() as $error)
                            <div><i class="fa-solid fa-circle-exclamation"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="field" style="margin-bottom: 20px;">
                    <label style="font-weight: 800; font-size: 13px; text-transform: uppercase;">Nombre del Permiso:</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej: ver facturas, gestionar api" required
                           style="height: 48px; border-radius: 12px; border: 1.5px solid var(--border); padding: 0 15px; font-size: 15px; width: 100%;">
                    <small style="color: var(--text3); font-size: 11px; margin-top: 5px; display: block;">
                        * El nombre debe ser descriptivo, único y en minúsculas (preferiblemente).
                    </small>
                </div>

                <div class="field" style="margin-bottom: 20px;">
                    <label style="font-weight: 800; font-size: 13px; text-transform: uppercase;">Guard Name (Ámbito):</label>
                    <input type="text" value="web" disabled
                           style="height: 48px; border-radius: 12px; border: 1.5px solid var(--border); padding: 0 15px; font-size: 15px; width: 100%; background: #f1f3f5; cursor: not-allowed; color: var(--text3);">
                    <small style="color: var(--text3); font-size: 11px; margin-top: 5px; display: block;">
                        * Por defecto es 'web' para todas las rutas y paneles del sistema.
                    </small>
                </div>

                <div style="background: #e7f5ff; border-left: 5px solid var(--accent); padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <p style="font-size: 12.5px; color: #0b7285; line-height: 1.5; margin: 0; font-weight: 500;">
                        <i class="fa-solid fa-circle-info"></i> <b>Nota Importante:</b> Al guardar el permiso, este se asignará automáticamente al rol global <b>SUPER_ADMIN</b> para que puedas utilizarlo sin restricciones.
                    </p>
                </div>

                <div class="flex-h" style="gap: 12px; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 20px;">
                    <a href="{{ route('superadmin.permisos.index') }}" class="btn-secondary" style="height: 45px; display: inline-flex; align-items: center; padding: 0 20px; text-decoration: none; border-radius: 10px; font-weight: 700;">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary" style="height: 45px; padding: 0 25px; border-radius: 10px; font-weight: 700;">
                        💾 Guardar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
