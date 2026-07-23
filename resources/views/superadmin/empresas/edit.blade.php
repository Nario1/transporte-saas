@extends('layouts.admin')

@php
    $pageTitle = 'Editar Empresa (Master)';
    $pageSubtitle = 'Gestión Global de ' . $empresa->nombre;
@endphp

@section('back_url', route('superadmin.empresas.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-city"></i> Actualizar Información Master</div>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-section">
                        <div class="g-2">
                            <div class="field">
                                <label for="nombre">Nombre Comercial *</label>
                                <input type="text" id="nombre" name="nombre" value="{{ $empresa->nombre }}" required>
                                @error('nombre')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="ruc">RUC *</label>
                                <input type="text" id="ruc" name="ruc" value="{{ $empresa->ruc }}" maxlength="11" required>
                                @error('ruc')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="field" style="margin-top: 20px;">
                            <label for="razon_social">Razón Social</label>
                            <input type="text" id="razon_social" name="razon_social" value="{{ $empresa->razon_social }}">
                            @error('razon_social')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="{{ $empresa->telefono }}">
                                @error('telefono')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="{{ $empresa->direccion }}">
                                @error('direccion')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="g-2" style="margin-top: 20px;">
                            <div class="field">
                                <label for="plan">Plan SaaS *</label>
                                <select name="plan" id="plan" required>
                                    <option value="basico" {{ $empresa->plan == 'basico' ? 'selected' : '' }}>Plan Básico</option>
                                    <option value="pro" {{ $empresa->plan == 'pro' ? 'selected' : '' }}>Plan Pro</option>
                                    <option value="enterprise" {{ $empresa->plan == 'enterprise' ? 'selected' : '' }}>Plan Enterprise</option>
                                </select>
                                @error('plan')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="tributo_diario">Tributo Diario Base (S/) *</label>
                                <input type="number" step="0.01" id="tributo_diario" name="tributo_diario" value="{{ $empresa->tributo_diario }}" required>
                                @error('tributo_diario')
                                    <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="field" style="margin-top: 20px;">
                            <label for="logo">Logo de Empresa</label>
                            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                                @if($empresa->logo_path)
                                    <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo" style="width: 50px; height: 50px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border); background: var(--bg); padding: 4px;">
                                @endif
                                <input type="file" id="logo" name="logo" accept="image/*" style="flex: 1;">
                            </div>
                            <small style="color: var(--text3); display: block;">Sube el logo que se mostrará en el sidebar de este cliente.</small>
                            @error('logo')
                                <small style="color: var(--red); font-weight: 700;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 12px; color: var(--text3);">
                            Última actualización: {{ $empresa->updated_at->format('d/m/Y H:i') }}
                        </div>
                        <div style="display: flex; gap: 10px; width: 60%; justify-content: flex-end;">
                            <a href="{{ route('superadmin.empresas.index') }}" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                                Cancelar
                            </a>
                            <button type="submit" class="btn-primary" style="flex: 2;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Actualizar Empresa
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
