{{-- ══════════════════════════════════════════════════════════
     resources/views/users/conductor/cambiar-password.blade.php
══════════════════════════════════════════════════════════ --}}

@extends('layouts.conductor')

@section('title', 'Cambiar Contraseña')

@section('content')

    <div style="max-width: 400px; margin: 0 auto; padding-top: 20px;">

        {{-- Ícono --}}
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 48px; margin-bottom: 12px;">🔑</div>
            @if (auth()->user()->conductor?->primer_ingreso)
                <div style="font-size: 16px; font-weight: 700; margin-bottom: 6px;">Bienvenido al sistema</div>
                <div style="font-size: 13px; color: var(--text3);">
                    Por seguridad debes cambiar tu contraseña temporal antes de continuar.
                </div>
            @else
                <div style="font-size: 16px; font-weight: 700;">Cambiar contraseña</div>
            @endif
        </div>

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert"
                style="background: var(--red-l); color: var(--red); border: 1px solid rgba(220,38,38,.2); margin-bottom: 16px;">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body" style="padding: 24px;">
                <form method="POST" action="{{ route('conductor.cambiar-password.store') }}">
                    @csrf

                    <div class="field" style="margin-bottom: 16px;">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres"
                            autocomplete="new-password">
                    </div>

                    <div class="field" style="margin-bottom: 24px;">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control"
                            placeholder="Repite la contraseña" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        ✅ Guardar contraseña
                    </button>
                </form>
            </div>
        </div>

    </div>

@endsection
