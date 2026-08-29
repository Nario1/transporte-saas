<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\Empresa;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService)
    {
    }

    /**
     * Vista de copias de seguridad (Superadmin)
     */
    public function index()
    {
        // 1. Listar todas las empresas para poder generar backups independientes
        $empresas = Empresa::orderBy('nombre')->get();

        // 2. Listar todas las copias de seguridad generadas en el sistema (cargando relación empresa)
        $backups = Backup::with('empresa')->latest()->get();

        return view('superadmin.backup.index', compact('empresas', 'backups'));
    }

    /**
     * Generar copia de seguridad (Superadmin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'nullable|exists:empresas,id'
        ]);

        $empresa_id = $request->empresa_id;
        
        // Generar backup independiente para la empresa (o global si empresa_id es null)
        $backup = $this->backupService->generate('manual', $empresa_id);

        if ($backup) {
            $nombreEmpresa = $empresa_id ? Empresa::find($empresa_id)?->nombre : 'Global';
            return redirect()->back()->with('success', "Copia de seguridad para '{$nombreEmpresa}' generada correctamente.");
        }

        return redirect()->back()->with('error', 'Error al generar la copia de seguridad.');
    }

    /**
     * Descargar copia de seguridad (Superadmin)
     */
    public function download(Backup $backup)
    {
        if (!Storage::disk('local')->exists($backup->path)) {
            return redirect()->back()->with('error', 'El archivo físico no existe en el servidor.');
        }

        return Storage::disk('local')->download($backup->path, $backup->filename);
    }

    /**
     * Eliminar copia de seguridad (Superadmin)
     */
    public function destroy(Backup $backup)
    {
        try {
            if (Storage::disk('local')->exists($backup->path)) {
                Storage::disk('local')->delete($backup->path);
            }
            $backup->delete();

            return redirect()->back()->with('success', 'Copia de seguridad eliminada.');
        } catch (\Exception $e) {
            Log::error("Error eliminando backup: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el archivo.');
        }
    }
}
