<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService)
    {
    }

    public function index()
    {
        $empresa_id = auth()->user()->empresa_id;
        
        $backups = Backup::where('empresa_id', $empresa_id)->latest()->get();
        
        if (request()->routeIs('superadmin.*')) {
            return view('superadmin.backup.index', compact('backups'));
        }

        return view('admin.backups.index', compact('backups'));
    }

    public function store()
    {
        $empresa_id = auth()->user()->empresa_id;
        $backup = $this->backupService->generate('manual', $empresa_id);

        if ($backup) {
            return redirect()->back()->with('success', 'Copia de seguridad generada correctamente.');
        }

        return redirect()->back()->with('error', 'Error al generar la copia de seguridad.');
    }

    public function download(Backup $backup)
    {
        // Seguridad: Verificar que el backup pertenezca al usuario o sea Super Admin
        if ($backup->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($backup->path)) {
            return redirect()->back()->with('error', 'El archivo físico no existe en el servidor.');
        }

        return Storage::disk('local')->download($backup->path, $backup->filename);
    }

    public function destroy(Backup $backup)
    {
        // Seguridad: Verificar que el backup pertenezca al usuario o sea Super Admin
        if ($backup->empresa_id !== auth()->user()->empresa_id) {
            abort(403);
        }

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
