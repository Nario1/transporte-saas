@extends('layouts.admin')

@php
    $pageTitle = 'Rutas de Transporte';
    $pageSubtitle = 'Gestión de red vial y puntos de control';
@endphp

@section('content')
    <div class="panel">

        {{-- 1. INDICADORES --}}
        <div class="stats-row g-3">
            <div class="stat blue">
                <div class="stat-label">Rutas Totales</div>
                <div class="stat-val">{{ $resumen['total'] }}</div>
                <div class="stat-sub">{{ $resumen['activas'] }} sectores operativos</div>
                <span class="stat-icon"><i class="fa-solid fa-route"></i></span>
            </div>

            <div class="stat green">
                <div class="stat-label">Eficiencia Operativa</div>
                <div class="stat-val">
                    {{ number_format($resumen['total'] > 0 ? ($resumen['activas'] / $resumen['total']) * 100 : 0, 0) }}%
                </div>
                <div class="stat-sub">Cobertura de red de transporte</div>
                <span class="stat-icon"><i class="fa-solid fa-map-location-dot"></i></span>
            </div>
        </div>

        {{-- 2. ACCIONES Y FILTROS --}}
        <div class="flex-between gap-24">
            <div class="card" style="flex: 1;">
                <form method="GET" action="{{ route('rutas.index') }}" class="card-body"
                    style="padding: 15px 24px; position: relative; display: flex; align-items: center;">
                    <i class="fa-solid fa-magnifying-glass"
                        style="position: absolute; left: 35px; color: var(--text3);"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Buscar por código o nombre de ruta..."
                        style="width: 100%; padding-left: 45px; height: 50px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none;">
                </form>
            </div>

            <a href="{{ route('rutas.create') }}" class="btn-primary"
                style="padding: 0 32px; height: 80px; border-radius: 20px; font-weight: 800; background: var(--sidebar);">
                <i class="fa-solid fa-plus-circle"></i> NUEVA RUTA
            </a>
        </div>

        <div class="g-2" style="margin-top: 30px;">
            @forelse($rutas as $r)
                <div class="card" style="padding: 24px; position: relative;">
                    <div class="card-body">
                        <div class="flex-between mb16">
                            <div class="flex-h">
                                <div class="user-av"
                                    style="background: var(--sidebar); color: var(--text-inv); font-size: 14px; width: 40px; height: 40px;">
                                    <i class="fa-solid fa-route"></i>
                                </div>
                                <div>
                                    <div style="font-size: 16px; font-weight: 800;">{{ $r->nombre }}</div>
                                    <div style="font-size: 11px; color: var(--text3);">Código: {{ $r->codigo ?? '---' }}
                                    </div>
                                </div>
                            </div>
                            <span class="pill {{ $r->estado === 'activa' ? 'green' : 'red' }}" style="font-size: 10px;">
                                {{ strtoupper($r->estado) }}
                            </span>
                        </div>

                        <div class="flex-v" style="gap: 15px; margin-top: 20px;">
                            @php $totalParaderos = $r->paraderos->count(); @endphp
                            @forelse($r->paraderos->sortBy('orden') as $index => $p)
                                <div class="flex-h" style="gap: 15px; align-items: flex-start;">
                                    <div class="flex-v" style="align-items: center; width: 12px; position: relative;">
                                        @php
                                            $dotColor = 'var(--accent)';
                                            if ($p->tipo === 'origen') {
                                                $dotColor = 'var(--green)';
                                            }
                                            if ($p->tipo === 'destino') {
                                                $dotColor = 'var(--red)';
                                            }
                                        @endphp
                                        <div
                                            style="width: 12px; height: 12px; border-radius: 50%; background: {{ $dotColor }}; z-index: 2; border: 3px solid var(--card);">
                                        </div>
                                        @if ($index < $totalParaderos - 1)
                                            <div
                                                style="width: 2px; height: calc(100% + 12px); background: var(--border); position: absolute; top: 10px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-v" style="gap: 2px;">
                                        <div style="font-size: 13px; font-weight: 800; color: var(--text);">
                                            {{ $p->nombre }}</div>
                                        <div
                                            style="font-size: 9px; font-weight: 800; color: var(--text3); text-transform: uppercase;">
                                            {{ $p->tipo }}
                                            {{ $loop->first ? '• PUNTO INICIAL' : ($loop->last ? '• RETORNO FINAL' : '') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div
                                    style="text-align: center; padding: 20px; background: var(--bg); border: 1px dashed var(--border); border-radius: 12px; font-size: 12px; color: var(--text3);">
                                    <i class="fa-solid fa-map-location-dot"
                                        style="display: block; font-size: 24px; opacity: 0.1; margin-bottom: 10px;"></i>
                                    Red de paraderos no configurada.
                                </div>
                            @endforelse
                        </div>

                        <hr style="margin: 20px 0; border: 0; border-top: 1px dashed var(--border);">

                        {{-- Footer Actions --}}
                        <div class="flex-between">
                            <div class="flex-h" style="gap: 8px;">
                                <a href="{{ route('rutas.show', $r->id) }}" class="action-icon show-icon"
                                    title="Rendimiento de Ruta">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </a>
                                <a href="{{ route('rutas.edit', $r->id) }}" class="action-icon edit-icon"
                                    title="Editar Itinerario">
                                    <i class="fa-solid fa-sliders"></i>
                                </a>
                            </div>

                            <form action="{{ route('rutas.destroy', $r->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-submit"
                                    onclick="return confirm('{{ $r->vehiculos_activos_count > 0 ? '⚠️ Esta ruta tiene vehículos asignados. ¿Confirmas la eliminación definitiva?' : '¿Eliminar la ruta ' . $r->nombre . '?' }}')">
                                    <i class="fa-solid fa-trash-can action-icon delete-icon"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card" style="grid-column: 1 / -1; padding: 80px; text-align: center; color: var(--text3);">
                    <i class="fa-solid fa-route" style="font-size: 60px; margin-bottom: 20px; opacity: 0.1;"></i>
                    <p style="font-weight: 700;">No se detectaron rutas configuradas en el sector.</p>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if ($rutas->hasPages())
            <div style="margin-top: 30px;">
                {{ $rutas->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
