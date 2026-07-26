@extends('layouts.admin')

@section('back_url', route('superadmin.permisos.index'))

@php
    $pageTitle = 'Detalle de Permiso Global';
    $pageSubtitle = 'Visualización y control de asignación';
@endphp

@section('content')
    <div style="max-width: 800px; margin: 0 auto; display: grid; gap: 20px;">
        
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">Datos del Permiso</div>
                <a href="{{ route('superadmin.permisos.edit', $permiso->id) }}" class="btn-primary" style="height: 35px; display: inline-flex; align-items: center; gap: 8px; padding: 0 15px; border-radius: 8px; font-weight: 700; text-decoration: none; background: var(--gold);">
                    <i class="fa-solid fa-pen-to-square"></i> Editar
                </a>
            </div>
            
            <div class="card-body" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-bottom: 1px solid var(--border); padding-bottom: 20px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 800; color: var(--text3); text-transform: uppercase;">Nombre del Permiso:</div>
                        <div style="font-size: 20px; font-weight: 900; color: var(--text); margin-top: 5px;">{{ $permiso->name }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 800; color: var(--text3); text-transform: uppercase;">Guard Name (Ámbito):</div>
                        <div style="margin-top: 5px;">
                            <code style="background: var(--border); padding: 5px 10px; border-radius: 6px; font-size: 14px; color: var(--text2);">{{ $permiso->guard_name }}</code>
                        </div>
                    </div>
                </div>

                <div style="padding-top: 20px;">
                    <div style="font-size: 11px; font-weight: 800; color: var(--text3); text-transform: uppercase; margin-bottom: 15px;">Roles que poseen este permiso:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($roles as $r)
                            <span class="pill blue" style="font-size: 12px; padding: 8px 12px; font-weight: 800;">
                                <i class="fa-solid fa-user-shield"></i> {{ $r->name }}
                            </span>
                        @empty
                            <span class="pill gray" style="font-size: 12px; padding: 8px 12px; font-weight: 800;">
                                Ningún rol tiene asignado este permiso actualmente
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
