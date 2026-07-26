@extends('layouts.admin')

@section('back_url', route('superadmin.permisos.index'))

@php
    $pageTitle = 'Editar Permiso Global';
    $pageSubtitle = 'Modifica el nombre del permiso registrado';
@endphp

@section('content')
    <div style="max-width: 600px; margin: 0 auto;">
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">Editar Permiso: {{ $permiso->name }}</div>
            </div>
            
            <form action="{{ route('superadmin.permisos.update', $permiso->id) }}" method="POST" class="card-body">
                @csrf
                @method('PUT')
                
                @if ($errors->any())
                    <div style="background: #fff5f5; border-left: 5px solid var(--red); color: var(--red); padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; display: flex; flex-direction: column; gap: 5px;">
                        @foreach ($errors->all() as $error)
                            <div><i class="fa-solid fa-circle-exclamation"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="field" style="margin-bottom: 20px;">
                    <label style="font-weight: 800; font-size: 13px; text-transform: uppercase;">Nombre del Permiso:</label>
                    <input type="text" name="name" value="{{ old('name', $permiso->name) }}" placeholder="Ej: ver facturas, gestionar api" required
                           style="height: 48px; border-radius: 12px; border: 1.5px solid var(--border); padding: 0 15px; font-size: 15px; width: 100%;">
                    <small style="color: var(--text3); font-size: 11px; margin-top: 5px; display: block;">
                        * Cambiar el nombre podría romper los filtros de permisos (can o middleware) que utilicen el nombre anterior. Procede con cuidado.
                    </small>
                </div>

                <div class="field" style="margin-bottom: 20px;">
                    <label style="font-weight: 800; font-size: 13px; text-transform: uppercase;">Guard Name (Ámbito):</label>
                    <input type="text" value="{{ $permiso->guard_name }}" disabled
                           style="height: 48px; border-radius: 12px; border: 1.5px solid var(--border); padding: 0 15px; font-size: 15px; width: 100%; background: #f1f3f5; cursor: not-allowed; color: var(--text3);">
                </div>

                <div class="flex-h" style="gap: 12px; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 20px;">
                    <a href="{{ route('superadmin.permisos.index') }}" class="btn-secondary" style="height: 45px; display: inline-flex; align-items: center; padding: 0 20px; text-decoration: none; border-radius: 10px; font-weight: 700;">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary" style="height: 45px; padding: 0 25px; border-radius: 10px; font-weight: 700;">
                        💾 Actualizar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
