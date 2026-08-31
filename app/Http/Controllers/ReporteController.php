<?php

namespace App\Http\Controllers;

use App\Models\Tributo;
use App\Models\Vuelta;
use App\Models\Sancion;
use App\Models\Vehiculo;
use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $empresaId = $user->empresa_id;
        $hoy       = today();

        // Resumen general para la página de reportes
        $resumen = [
            'tributos_mes'    => Tributo::where('empresa_id', $empresaId)
                ->where('estado', 'pagado')
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->sum('monto'),
            'vueltas_mes'     => Vuelta::where('empresa_id', $empresaId)
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->count(),
            'sanciones_mes'   => Sancion::where('empresa_id', $empresaId)
                ->whereMonth('fecha', $hoy->month)
                ->whereYear('fecha', $hoy->year)
                ->sum('monto'),
            'deuda_total'     => Tributo::where('empresa_id', $empresaId)
                ->where('estado', 'pendiente')
                ->sum('monto'),
        ];

        return view('admin.reportes.index', compact('resumen'));
    }

    // ── Reporte de Tributos ───────────────────────────────────────
    public function tributos(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        [$desde, $hasta] = $this->rango($request);

        // Asegurar que los tributos estén generados para el rango consultado
        $daysDiff = today()->diffInDays($desde) + 1;
        Tributo::ensureGenerados($user->empresa_id, max(30, $daysDiff));

        if ($request->has('flota')) {
            $flota = $request->input('flota') ?? '';
        } elseif ($request->has('page')) {
            $flota = '';
        } else {
            $flota = '1';
        }

        // Resumen por día
        $porDiaQuery = Tributo::where('empresa_id', $user->empresa_id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);

        if ($flota) {
            $porDiaQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $porDia = $porDiaQuery->selectRaw("
                fecha,
                COUNT(*) as total_autos,
                SUM(CASE WHEN estado = 'pagado'    THEN 1 ELSE 0 END) as pagados,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'exonerado' THEN 1 ELSE 0 END) as exonerados,
                SUM(CASE WHEN estado = 'pagado'    THEN monto ELSE 0 END) as total_cobrado,
                SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as monto_pendiente
            ")
            ->groupBy('fecha')
            ->orderByDesc('fecha')
            ->get();

        // Detalle de todos los registros en el rango (para la tabla detallada)
        $detalle = Tributo::where('empresa_id', $user->empresa_id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($flota, function ($q) use ($flota) {
                return $q->whereHas('vehiculo', function ($vQ) use ($flota) {
                    $vQ->where('numero_flota', $flota);
                });
            })
            ->with(['vehiculo', 'conductor', 'cobrador.roles', 'pagoMp'])
            ->orderByDesc('fecha')
            ->orderByDesc('cobrado_at')
            ->paginate($request->input('print') == 1 ? 99999 : 20)
            ->withQueryString();

        // Resumen por método de pago
        $porMetodoQuery = Tributo::where('empresa_id', $user->empresa_id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where('estado', 'pagado');

        if ($flota) {
            $porMetodoQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $porMetodo = $porMetodoQuery->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(monto) as total')
            ->groupBy('metodo_pago')
            ->get();

        // Vehículos con más deuda
        $conDeudaQuery = Tributo::where('empresa_id', $user->empresa_id)
            ->where('estado', 'pendiente');

        if ($flota) {
            $conDeudaQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $conDeuda = $conDeudaQuery->with(['vehiculo', 'conductor'])
            ->selectRaw('vehiculo_id, conductor_id, SUM(monto) as total_deuda, COUNT(*) as dias_deuda')
            ->groupBy('vehiculo_id', 'conductor_id')
            ->orderByDesc('total_deuda')
            ->limit(10)
            ->get();

        $totales = [
            'cobrado'    => $porDia->sum('total_cobrado'),
            'pendiente'  => $porDia->sum('monto_pendiente'),
            'exonerados' => $porDia->sum('exonerados'),
            'dias'       => $porDia->count(),
        ];

        return view('admin.reportes.tributos', compact(
            'porDia', 'porMetodo', 'conDeuda', 'detalle', 'totales', 'desde', 'hasta', 'flota'
        ));
    }

    // ── Reporte de Vueltas ────────────────────────────────────────
    public function vueltas(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        [$desde, $hasta] = $this->rango($request);
        if ($request->has('flota')) {
            $flota = $request->input('flota') ?? '';
        } elseif ($request->has('page')) {
            $flota = '';
        } else {
            $flota = '1';
        }

        // Vueltas por día
        $porDiaQuery = Vuelta::where('empresa_id', $user->empresa_id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);

        if ($flota) {
            $porDiaQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $porDia = $porDiaQuery->selectRaw('fecha, COUNT(*) as total_vueltas, COUNT(DISTINCT vehiculo_id) as vehiculos')
            ->groupBy('fecha')
            ->orderByDesc('fecha')
            ->get();

        // Todos los vehículos con conteo de vueltas (para listar todos los de la empresa e indicar si dieron o no vueltas)
        $porVehiculo = Vehiculo::where('empresa_id', $user->empresa_id)
            ->when($flota, function ($q) use ($flota) {
                return $q->where('numero_flota', $flota);
            })
            ->withCount(['vueltas' => function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha', [$desde, $hasta]);
            }])
            ->with(['conductor'])
            ->orderByDesc('vueltas_count')
            ->get();

        // Vueltas por ruta
        $porRutaQuery = Vuelta::where('empresa_id', $user->empresa_id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->whereNotNull('ruta_id');

        if ($flota) {
            $porRutaQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $porRuta = $porRutaQuery->with('ruta')
            ->selectRaw('ruta_id, COUNT(*) as total_vueltas')
            ->groupBy('ruta_id')
            ->orderByDesc('total_vueltas')
            ->get();

        // Detalle individual de vueltas (con paginación para evitar sobrecarga)
        $detalleQuery = Vuelta::where('empresa_id', $user->empresa_id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);

        if ($flota) {
            $detalleQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $detalle = $detalleQuery->with(['vehiculo', 'conductor', 'ruta'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora_salida')
            ->paginate($request->input('print') == 1 ? 99999 : 20)
            ->withQueryString();

        $totales = [
            'vueltas'   => $porDia->sum('total_vueltas'),
            'vehiculos' => $porVehiculo->count(),
            'dias'      => $porDia->count(),
        ];

        return view('admin.reportes.vueltas', compact(
            'porDia', 'porVehiculo', 'porRuta', 'detalle', 'totales', 'desde', 'hasta', 'flota'
        ));
    }

    // ── Reporte de Sanciones ──────────────────────────────────────
    public function sanciones(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        [$desde, $hasta] = $this->rango($request);
        if ($request->has('flota')) {
            $flota = $request->input('flota') ?? '';
        } elseif ($request->has('page')) {
            $flota = '';
        } else {
            $flota = '1';
        }

        $sancionesQuery = Sancion::where('empresa_id', $user->empresa_id)
            ->where('estado', 'pagado')
            ->whereBetween('cobrado_at', [
                $desde->startOfDay()->toDateTimeString(),
                $hasta->endOfDay()->toDateTimeString()
            ]);

        if ($flota) {
            $sancionesQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $sancionesQuery = $sancionesQuery->with(['vehiculo', 'conductor', 'registrador', 'pagoMp'])->orderByDesc('cobrado_at');

        $allSanciones = $sancionesQuery->clone()->get();

        // Resumen por estado financiero correcto en el rango
        $porEstado = [
            'pendiente' => 0.00,
            'pagado' => $allSanciones->sum('monto'),
            'cantidad_pendiente' => 0,
            'cantidad_pagada'    => $allSanciones->count(),
        ];

        // Conductores con más sanciones
        $porConductor = $allSanciones->groupBy('conductor_id')->map(fn($s) => [
            'conductor'  => $s->first()->conductor,
            'cantidad'   => $s->count(),
            'total'      => $s->sum('monto'),
        ])->sortByDesc('cantidad')->take(10);

        // Paginar para la tabla (ya ordenados y con relaciones cargadas en el query builder)
        $sanciones = $sancionesQuery->paginate($request->input('print') == 1 ? 99999 : 20)->withQueryString();

        return view('admin.reportes.sanciones', compact(
            'sanciones', 'porEstado', 'porConductor', 'desde', 'hasta', 'flota'
        ));
    }

    // ── Reporte de Documentos ─────────────────────────────────────
    public function documentos(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $hoy = today();
        
        if ($request->has('flota')) {
            $flota = $request->input('flota') ?? '';
        } elseif ($request->has('page')) {
            $flota = '';
        } else {
            $flota = '1';
        }

        // Query Base para Vehículos
        $vQuery = Vehiculo::where('empresa_id', $user->empresa_id)->with('conductor');

        if ($flota) {
            $vQuery->where('numero_flota', $flota);
        }

        $vehiculos = $vQuery->get();
        $alertas = collect();

        foreach ($vehiculos as $v) {
            if ($v->soat_vence) {
                $alertas->push((object)[
                    'placa'     => $v->placa,
                    'conductor' => $v->conductor->nombre_completo ?? 'Sin Conductor',
                    'documento' => 'SOAT',
                    'fecha'     => \Carbon\Carbon::parse($v->soat_vence),
                ]);
            }
            if ($v->rev_tecnica_vence) {
                $alertas->push((object)[
                    'placa'     => $v->placa,
                    'conductor' => $v->conductor->nombre_completo ?? 'Sin Conductor',
                    'documento' => 'Revisión Técnica',
                    'fecha'     => \Carbon\Carbon::parse($v->rev_tecnica_vence),
                ]);
            }
            if ($v->tarjeta_prop_vence) {
                $alertas->push((object)[
                    'placa'     => $v->placa,
                    'conductor' => $v->conductor->nombre_completo ?? 'Sin Conductor',
                    'documento' => 'Tarjeta de Propiedad',
                    'fecha'     => \Carbon\Carbon::parse($v->tarjeta_prop_vence),
                ]);
            }
            if ($v->conductor && $v->conductor->licencia_vence) {
                $alertas->push((object)[
                    'placa'     => $v->placa,
                    'conductor' => $v->conductor->nombre_completo,
                    'documento' => 'Licencia de Conducir',
                    'fecha'     => \Carbon\Carbon::parse($v->conductor->licencia_vence),
                ]);
            }
            if ($v->conductor && $v->conductor->carnet_habilitacion_vence) {
                $alertas->push((object)[
                    'placa'     => $v->placa,
                    'conductor' => $v->conductor->nombre_completo,
                    'documento' => 'Carnet de Habilitación' . ($v->conductor->carnet_habilitacion_tipo ? ' (' . $v->conductor->carnet_habilitacion_tipo . ')' : ''),
                    'fecha'     => \Carbon\Carbon::parse($v->conductor->carnet_habilitacion_vence),
                ]);
            }
        }

        $alertas = $alertas->sortBy('fecha')->values();

        // Agrupación para resumen estadístico
        $resumen = [
            'criticos' => $alertas->filter(fn($a) => $hoy->diffInDays($a->fecha, false) <= 7 && $hoy->diffInDays($a->fecha, false) >= 0)->count(),
            'mes_actual' => $alertas->filter(fn($a) => $a->fecha->isCurrentMonth())->count(),
            'vencidos' => $alertas->filter(fn($a) => $a->fecha->isPast() && !$a->fecha->isToday())->count(),
        ];

        // Paginación manual de alertas (20 por página)
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = $request->input('print') == 1 ? 99999 : 20;
        $currentPageItems = $alertas->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedAlertas = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $alertas->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $paginatedAlertas->withQueryString();

        return view('admin.reportes.documentos', compact(
            'paginatedAlertas', 'hoy', 'flota', 'resumen'
        ));
    }

    // ── Reporte de Deuda por Vehículo ─────────────────────────────
    public function deudas(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        [$desde, $hasta] = $this->rango($request);

        // Asegurar que los tributos estén generados para el rango consultado
        $daysDiff = today()->diffInDays($desde) + 1;
        Tributo::ensureGenerados($user->empresa_id, max(30, $daysDiff));

        $tipo = $request->input('tipo', 'todos');
        $propietarioId = $request->input('propietario_id');
        $propietariosList = \App\Models\Propietario::where('empresa_id', $user->empresa_id)->orderBy('nombre')->get();

        $filtrarPorFecha = true;
        if ($tipo === 'monto_ingreso' && !$request->filled('desde') && !$request->filled('hasta')) {
            $filtrarPorFecha = false;
        }

        if ($request->has('flota')) {
            $flota = $request->input('flota') ?? '';
        } elseif ($request->has('page') || $tipo === 'monto_ingreso') {
            $flota = '';
        } else {
            $flota = '1';
        }

        $tributosQuery = Tributo::where('empresa_id', $user->empresa_id)
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
                  ->orWhere(function ($q2) use ($desde, $hasta) {
                      $q2->where('estado', 'pagado')
                         ->whereBetween('cobrado_at', [
                             $desde->startOfDay()->toDateTimeString(),
                             $hasta->endOfDay()->toDateTimeString()
                         ]);
                  });
            });

        $sancionesQuery = Sancion::where('empresa_id', $user->empresa_id)
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
                  ->orWhere(function ($q2) use ($desde, $hasta) {
                      $q2->where('estado', 'pagado')
                         ->whereBetween('cobrado_at', [
                             $desde->startOfDay()->toDateTimeString(),
                             $hasta->endOfDay()->toDateTimeString()
                         ]);
                  });
            });

        if ($flota) {
            $tributosQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
            $sancionesQuery->whereHas('vehiculo', function ($vQ) use ($flota) {
                $vQ->where('numero_flota', $flota);
            });
        }

        $items = collect();

        if ($tipo === 'todos' || $tipo === 'tributo') {
            $tributos = $tributosQuery->with(['vehiculo', 'conductor', 'cobrador', 'pagoMp'])->get()->map(function($t) {
                $t->tipo_obligacion = 'TRIBUTO';
                $t->concepto = 'Tributo Diario';
                return $t;
            });
            $items = $items->concat($tributos);
        }

        if ($tipo === 'todos' || $tipo === 'sancion') {
            $sanciones = $sancionesQuery->with(['vehiculo', 'conductor', 'cobrador', 'pagoMp'])->get()->map(function($s) {
                $s->tipo_obligacion = 'SANCIÓN';
                $s->concepto = $s->motivo . ($s->descripcion ? " - " . $s->descripcion : "");
                return $s;
            });
            $items = $items->concat($sanciones);
        }

        if ($tipo === 'todos' || $tipo === 'monto_ingreso') {
            $propietariosQuery = \App\Models\Propietario::where('empresa_id', $user->empresa_id);

            if ($flota && $tipo !== 'monto_ingreso') {
                $propietariosQuery->whereHas('vehiculos', function ($vQ) use ($flota) {
                    $vQ->where('numero_flota', $flota);
                });
            }

            if ($tipo === 'monto_ingreso' && $propietarioId) {
                $propietariosQuery->where('id', $propietarioId);
            }

            $propietarios = $propietariosQuery->with(['vehiculos'])->get();
            foreach ($propietarios as $p) {
                $pFecha = $p->created_at ? \Carbon\Carbon::parse($p->created_at) : today();

                if ($p->vehiculos->count() === 0) {
                    if (!$filtrarPorFecha || $pFecha->between($desde->startOfDay(), $hasta->endOfDay())) {
                        $item = new \stdClass();
                        $item->id = 'ingreso_prop_' . $p->id;
                        $item->fecha = $pFecha;
                        $item->monto = $p->monto_ingreso_total;
                        $item->monto_deuda = $p->monto_ingreso_deuda;
                        $item->estado = $p->monto_ingreso_deuda > 0 ? 'pendiente' : 'pagado';
                        $item->tipo_obligacion = 'MONTO DE INGRESO';
                        $item->concepto = 'Ingreso de Socio: ' . $p->nombre_completo;
                        $item->vehiculo = (object)['numero_flota' => '---', 'placa' => '---'];
                        $item->conductor = null;
                        $item->cobrado_at = $p->updated_at ? \Carbon\Carbon::parse($p->updated_at) : today();
                        $item->created_at = $p->created_at ?? today();
                        $item->metodo_pago = 'efectivo';
                        $item->pagoMp = null;
                        $item->motivo_exoneracion = null;
                        $item->monto_inicial = $p->monto_inicial;
                        $item->cuota_1 = $p->cuota_1;
                        $item->cuota_2 = $p->cuota_2;
                        $item->cuota_3 = $p->cuota_3;
                        
                        $items->push($item);
                    }
                } else {
                    foreach ($p->vehiculos as $v) {
                        if ($flota && $v->numero_flota != $flota) {
                            continue;
                        }

                        $vFecha = $v->created_at ? \Carbon\Carbon::parse($v->created_at) : $pFecha;
                        if (!$filtrarPorFecha || $vFecha->between($desde->startOfDay(), $hasta->endOfDay())) {
                            $item = new \stdClass();
                            $item->id = 'ingreso_veh_' . $v->id;
                            $item->fecha = $vFecha;
                            $item->monto = $v->monto_ingreso_total;
                            $item->monto_deuda = $v->monto_ingreso_deuda;
                            $item->estado = $v->monto_ingreso_deuda > 0 ? 'pendiente' : 'pagado';
                            $item->tipo_obligacion = 'MONTO DE INGRESO';
                            $item->concepto = 'Ingreso de Socio: ' . $p->nombre_completo;
                            $item->vehiculo = $v;
                            $item->conductor = null;
                            $item->cobrado_at = $v->updated_at ? \Carbon\Carbon::parse($v->updated_at) : today();
                            $item->created_at = $v->created_at ?? today();
                            $item->metodo_pago = 'efectivo';
                            $item->pagoMp = null;
                            $item->motivo_exoneracion = null;
                            $item->monto_inicial = $v->monto_inicial;
                            $item->cuota_1 = $v->cuota_1;
                            $item->cuota_2 = $v->cuota_2;
                            $item->cuota_3 = $v->cuota_3;
                            
                            $items->push($item);
                        }
                    }
                }
            }
        }

        // Ordenar por fecha desc, y cobrado_at desc
        $itemsSorted = $items->sortByDesc(function($item) {
            $timeStr = $item->cobrado_at ? $item->cobrado_at->format('H:i:s') : ($item->created_at ? $item->created_at->format('H:i:s') : '00:00:00');
            return $item->fecha->format('Y-m-d') . '_' . $timeStr;
        });

        $totalDeuda = $itemsSorted->filter(function($item) use ($desde, $hasta) {
            if ($item->tipo_obligacion === 'MONTO DE INGRESO') {
                return false;
            }
            return $item->estado === 'pendiente' 
                && $item->fecha->between($desde->startOfDay(), $hasta->endOfDay());
        })->sum('monto');

        $totalCobrado = $itemsSorted->filter(function($item) use ($desde, $hasta) {
            if ($item->tipo_obligacion === 'MONTO DE INGRESO') {
                return false;
            }
            return $item->estado === 'pagado' 
                && $item->cobrado_at 
                && $item->cobrado_at->between($desde->startOfDay(), $hasta->endOfDay());
        })->sum('monto');

        // Sumar montos de ingresos a los totales
        foreach ($itemsSorted as $item) {
            if ($item->tipo_obligacion === 'MONTO DE INGRESO') {
                $totalCobrado += $item->monto;
                $totalDeuda += $item->monto_deuda;
            }
        }

        // Paginación manual
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = $request->input('print') == 1 ? 99999 : 20;
        $currentPageItems = $itemsSorted->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $itemsSorted->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $paginatedItems->withQueryString();

        return view('admin.reportes.deudas', compact('paginatedItems', 'totalDeuda', 'totalCobrado', 'desde', 'hasta', 'flota', 'propietariosList', 'propietarioId', 'filtrarPorFecha'));
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function rango(Request $request): array
    {
        $desde = $request->filled('desde')
            ? \Carbon\Carbon::parse($request->input('desde'))->startOfDay()
            : today()->startOfDay();

        $hasta = $request->filled('hasta')
            ? \Carbon\Carbon::parse($request->input('hasta'))->endOfDay()
            : today()->endOfDay();

        // Asegurar que desde no sea mayor que hasta
        if ($desde->gt($hasta)) {
            $desde = $hasta->copy()->startOfMonth();
        }

        return [$desde, $hasta];
    }
}