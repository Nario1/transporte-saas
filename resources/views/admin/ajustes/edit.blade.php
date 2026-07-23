@extends('layouts.admin')

@section('back_url', route('ajustes.index'))

@php
    $pageTitle = 'Ajustes';
    $pageSubtitle = 'Configuración de la empresa';
@endphp

@section('content')
    <div style="max-width:860px; margin:0 auto;">
        <form action="{{ route('ajustes.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ── Sección 1: Datos principales ── --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-building"></i> Datos Principales</span>
                </div>
                <div class="card-body">
                    <div class="form-grid-3">
                        <div class="field">
                            <label>Nombre Comercial *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $empresa->nombre) }}" maxlength="120" required>
                        </div>
                        <div class="field">
                            <label>RUC *</label>
                            <input type="text" name="ruc" value="{{ old('ruc', $empresa->ruc) }}" maxlength="11" required>
                        </div>
                        <div class="field">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}" maxlength="15">
                        </div>
                    </div>
                    <div class="form-grid" style="margin-top:14px;">
                        <div class="field">
                            <label>Razón Social</label>
                            <input type="text" name="razon_social"
                                value="{{ old('razon_social', $empresa->razon_social) }}" maxlength="160">
                        </div>
                        <div class="field">
                            <label>Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}" maxlength="255">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Sección 2: Ajustes operativos ── --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-sliders"></i> Ajustes Operativos</span>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="field">
                            <label>Tributo Diario Base (S/) *</label>
                            <input type="number" step="0.01" min="0" name="tributo_diario"
                                value="{{ old('tributo_diario', $empresa->tributo_diario) }}" required>
                            <small style="color:var(--text3); font-size:11px; margin-top:4px; display:block;">
                                Monto por defecto al generar tributos diarios.
                            </small>
                        </div>
                        <div class="field">
                            <label>Logo de la Empresa</label>
                            <div style="display:flex; align-items:center; gap:12px;">
                                @if ($empresa->logo_path)
                                    <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo"
                                        style="width:44px; height:44px; object-fit:contain;
                                           border-radius:8px; border:1px solid var(--border); flex-shrink:0;">
                                @endif
                                <input type="file" name="logo" accept="image/*">
                            </div>
                            <small style="color:var(--text3); font-size:11px; margin-top:4px; display:block;">
                                Formatos: JPG, PNG. Máx 2MB.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Acciones ── --}}
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <a href="{{ route('ajustes.index') }}" class="btn-secondary" style="text-decoration:none;">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    💾 Guardar Ajustes
                </button>
            </div>

        </form>
    </div>
@endsection
