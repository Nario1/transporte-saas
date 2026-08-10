@extends('layouts.admin')

@php
    $pageTitle = 'Ficha de Unidad';
    $pageSubtitle = "Placa: {$vehiculo->placa}";
@endphp

@section('back_url', route('vehiculos.index'))

@section('content')
    <div class="panel">
        {{-- Cabecera con Acciones --}}
        <div class="flex-between" style="margin-bottom: 25px;">
            <div class="flex-h">
                <div class="brand-icon {{ $vehiculo->estado === 'activo' ? '' : ($vehiculo->estado === 'mantenimiento' ? 'brand-icon-sa' : 'brand-icon-tj') }}" style="width: 50px; height: 50px; font-size: 24px;">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div>
                    <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ $vehiculo->placa }}</h2>
                    <div class="flex-h" style="gap: 10px;">
                        <span class="pill {{ $vehiculo->estado === 'activo' ? 'green' : ($vehiculo->estado === 'mantenimiento' ? 'orange' : 'red') }}">
                            {{ strtoupper($vehiculo->estado) }}
                        </span>
                        <span style="font-size: 13px; color: var(--text3);">Padrón #{{ $vehiculo->numero_flota }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-h">
                <a href="{{ route('vehiculos.edit', $vehiculo->id) }}" class="btn-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Editar Información
                </a>
            </div>
        </div>

        <div class="g-2-1">
            {{-- COLUMNA IZQUIERDA: Detalles Principales --}}
            <div class="flex-v" style="gap: 25px;">
                
                {{-- Bloque 1: Especificaciones Técnicas --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Especificaciones Técnicas</div>
                    </div>
                    <div class="card-body">
                        <div class="g-3">
                            @if($vehiculo->marca)
                                <div class="field">
                                    <label>Marca</label>
                                    <div style="font-weight: 700;">{{ $vehiculo->marca }}</div>
                                </div>
                            @endif
                            @if($vehiculo->modelo)
                                <div class="field">
                                    <label>Modelo</label>
                                    <div style="font-weight: 700;">{{ $vehiculo->modelo }}</div>
                                </div>
                            @endif
                            @if($vehiculo->anio)
                                <div class="field">
                                    <label>Año</label>
                                    <div class="mono">{{ $vehiculo->anio }}</div>
                                </div>
                            @endif
                            @if($vehiculo->color)
                                <div class="field">
                                    <label>Color</label>
                                    <div style="font-weight: 700;">{{ $vehiculo->color }}</div>
                                </div>
                            @endif
                            @if($vehiculo->numero_motor)
                                <div class="field">
                                    <label>Motor / Serie</label>
                                    <div class="mono" style="font-size: 12px;">{{ $vehiculo->numero_motor }}</div>
                                </div>
                            @endif
                            @if($vehiculo->combustible)
                                <div class="field">
                                    <label>Tipo Combustible</label>
                                    <div style="font-weight: 700;">{{ $vehiculo->combustible }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bloque 2: Personal y Operación --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Asignación y Rutas</div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="tbl">
                            <tbody>
                                <tr>
                                    <td style="width: 200px; color: var(--text3); font-weight: 600;">Propietario</td>
                                    <td>
                                        @if ($vehiculo->propietario)
                                            <a href="{{ route('propietarios.show', $vehiculo->propietario_id) }}" class="flex-h" style="text-decoration: none; color: var(--accent);">
                                                <i class="fa-solid fa-user-tie"></i>
                                                <span style="font-weight: 700;">{{ $vehiculo->propietario->nombre }} {{ $vehiculo->propietario->apellidos }}</span>
                                            </a>
                                        @else
                                            <span class="pill red">Sin Propietario Asignado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 600;">Conductor Habitual</td>
                                    <td>
                                        @if ($vehiculo->conductor)
                                            <a href="{{ route('conductores.show', $vehiculo->conductor_id) }}" class="flex-h" style="text-decoration: none; color: var(--accent);">
                                                <i class="fa-solid fa-id-card-clip"></i>
                                                <span style="font-weight: 700;">{{ $vehiculo->conductor->nombre }} {{ $vehiculo->conductor->apellidos }}</span>
                                            </a>
                                        @else
                                            <span class="pill gold">Sin Conductor Asignado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 600;">Rutas Autorizadas</td>
                                    <td>
                                        <div class="flex-h" style="flex-wrap: wrap; gap: 6px;">
                                            @forelse($vehiculo->rutas as $ruta)
                                                <span class="pill blue" style="font-size: 11px;">{{ $ruta->nombre }}</span>
                                            @empty
                                                <span style="font-size: 13px; color: var(--text3); font-style: italic;">Sin rutas asignadas</span>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bloque 3: Historial de Cambios de Carro / Placas Anteriores --}}
                @php
                    $placasAnt = $vehiculo->historial_placas;
                @endphp
                @if(count($placasAnt) > 0)
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Vehículos (Placas Anteriores)</div>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th style="padding-left: 20px;">Placa Anterior</th>
                                        <th>Vehículo Anterior</th>
                                        <th>Datos Modificados</th>
                                        <th>Fecha del Cambio</th>
                                        <th>Modificado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($placasAnt as $cambio)
                                        <tr>
                                            <td style="padding-left: 20px; vertical-align: top;">
                                                <span class="mono" style="font-weight: 800; background: var(--bg); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 12px; color: var(--text);">
                                                    {{ strtoupper($cambio['placa_anterior']) }}
                                                </span>
                                            </td>
                                            <td style="vertical-align: top;">
                                                @if($cambio['marca'] || $cambio['modelo'] || $cambio['anio'])
                                                    <span style="font-weight: 700; color: var(--text);">{{ trim(($cambio['marca'] ?? '') . ' ' . ($cambio['modelo'] ?? '') . ' ' . ($cambio['anio'] ?? '')) }}</span>
                                                    @if($cambio['color'])
                                                        <br><small style="color: var(--text3);">Color: {{ $cambio['color'] }}</small>
                                                    @endif
                                                @else
                                                    <span style="color: var(--text3); font-style: italic;">Detalles no disponibles en esta auditoría</span>
                                                @endif
                                            </td>
                                            <td style="vertical-align: top;">
                                                <div style="font-size: 12px; color: var(--text);">
                                                    @foreach($cambio['modificaciones'] as $mod)
                                                        @if($mod['campo'] !== 'Placa') {{-- Ya la mostramos en la primera columna --}}
                                                            <div style="margin-bottom: 6px;">
                                                                <strong style="color: var(--text2);">{{ $mod['campo'] }}:</strong> 
                                                                <span style="text-decoration: line-through; color: var(--red); opacity: 0.85;">{{ $mod['anterior'] }}</span> 
                                                                <i class="fa-solid fa-arrow-right" style="font-size: 9px; color: var(--text3); margin: 0 4px;"></i> 
                                                                <span style="color: var(--green); font-weight: 700;">{{ $mod['nuevo'] }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td style="vertical-align: top;">
                                                <span style="font-weight: 600;">{{ $cambio['fecha_cambio'] instanceof \Carbon\Carbon ? $cambio['fecha_cambio']->format('d/m/Y') : \Carbon\Carbon::parse($cambio['fecha_cambio'])->format('d/m/Y') }}</span>
                                                <br><small style="color: var(--text3);">{{ $cambio['fecha_cambio'] instanceof \Carbon\Carbon ? $cambio['fecha_cambio']->format('h:i A') : \Carbon\Carbon::parse($cambio['fecha_cambio'])->format('h:i A') }}</small>
                                            </td>
                                            <td style="vertical-align: top;">
                                                <span style="font-size: 13px; font-weight: 600;">{{ $cambio['usuario'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- COLUMNA DERECHA: Documentación y Alertas --}}
            <aside class="flex-v" style="gap: 24px;">
                
                {{-- Alarma de Documentación --}}
                <div class="card" style="border-top: 4px solid var(--accent);">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-file-shield"></i> Documentos Legales</div>
                    </div>
                    <div class="card-body flex-v" style="gap: 20px;">
                        
                        @php
                            $docs = [
                                ['label' => 'SOAT', 'date' => $vehiculo->soat_vence],
                                ['label' => 'REV. TÉCNICA', 'date' => $vehiculo->rev_tecnica_vence],
                                ['label' => 'TARJETA PROP.', 'date' => $vehiculo->tarjeta_prop_vence]
                            ];
                        @endphp

                        @foreach($docs as $doc)
                            @php
                                $vence = $doc['date'] ? \Carbon\Carbon::parse($doc['date']) : null;
                                $diff = $vence ? (int) today()->diffInDays($vence, false) : null;
                                $statusClass = $vence ? ($vence->isPast() ? 'date-expired' : ($diff < 30 ? 'date-warning' : 'date-valid')) : '';
                            @endphp
                            <div class="flex-v" style="gap: 6px;">
                                <div class="flex-between">
                                    <span style="font-size: 12px; font-weight: 800; color: var(--text2);">{{ $doc['label'] }}</span>
                                    <span class="mono {{ $statusClass }}" style="font-size: 11px;">
                                        {{ $vence ? $vence->format('d/m/Y') : 'NO REGISTRADO' }}
                                    </span>
                                </div>
                                @if($vence)
                                    <div class="progress-track">
                                        <div class="progress-fill {{ $statusClass === 'date-expired' ? 'bg-red' : ($statusClass === 'date-warning' ? 'bg-orange' : 'bg-green') }}" 
                                             style="width: {{ $diff < 0 ? '100%' : ($diff > 365 ? '100%' : ($diff/365)*100) }}%;
                                                    background: {{ $statusClass === 'date-expired' ? 'var(--red)' : ($statusClass === 'date-warning' ? 'var(--orange)' : 'var(--green)') }};">
                                        </div>
                                    </div>
                                    <div style="font-size: 10px; color: var(--text3); text-align: right;">
                                        @if($diff < 0)
                                            ⚠️ VENCIDO HACE {{ (int) abs($diff) }} DÍAS
                                        @else
                                            Vence en {{ (int) $diff }} días
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
