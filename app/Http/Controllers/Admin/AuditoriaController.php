<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Empresa;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with(['user', 'empresa'])->latest();

        // Búsqueda Universal (Usuario, Empresa o Módulo)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($qu) use ($search) {
                    $qu->where('name', 'LIKE', "%$search%");
                })
                ->orWhereHas('empresa', function($qe) use ($search) {
                    $qe->where('nombre', 'LIKE', "%$search%");
                })
                ->orWhere('auditable_type', 'LIKE', "%$search%");
            });
        }

        // Filtro por Empresa
        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        // Filtro por Usuario
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por Evento (created, updated, deleted)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $audits = $query->paginate(50);
        $empresas = Empresa::where('activa', 1)->get();
        
        $users = [];
        if ($request->filled('empresa_id')) {
            $users = \App\Models\User::where('empresa_id', $request->empresa_id)->get();
        }

        return view('superadmin.auditoria.index', compact('audits', 'empresas', 'users'));
    }

    public function show(Audit $audit)
    {
        return view('admin.auditoria.show', compact('audit'));
    }
}
