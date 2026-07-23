<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupService
{
    public function generate($type = 'manual', $empresa_id = null)
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $prefix = $empresa_id ? "empresa_{$empresa_id}_" : "global_";
        $filename = "backup_{$prefix}{$timestamp}.sql";
        $folder = 'backups';
        
        if (!Storage::disk('local')->exists($folder)) {
            Storage::disk('local')->makeDirectory($folder);
        }

        $diskPath = "{$folder}/{$filename}";
        $filePath = Storage::disk('local')->path($diskPath);

        $mysqldump = '"C:\xampp\mysql\bin\mysqldump.exe"'; 
        
        if ($empresa_id) {
            // BACKUP FILTRADO POR EMPRESA
            $tenantTables = ['ajustes', 'backups', 'conductores', 'paradero_checkins', 'propietarios', 'rutas', 'sanciones', 'tributos', 'users', 'vehiculos', 'vueltas'];
            $tablesStr = implode(' ', $tenantTables);

            // 1. Volcar solo estructura de toda la base de datos
            $cmdSchema = sprintf(
                '%s --user=%s --password=%s --host=%s --no-data %s > %s',
                $mysqldump, escapeshellarg($dbUser), escapeshellarg($dbPass), escapeshellarg($dbHost), escapeshellarg($dbName), escapeshellarg($filePath)
            );
            
            // 2. Volcar solo datos de las tablas del tenant con filtro WHERE
            $cmdData = sprintf(
                '%s --user=%s --password=%s --host=%s --no-create-info --where=%s %s %s >> %s',
                $mysqldump, escapeshellarg($dbUser), escapeshellarg($dbPass), escapeshellarg($dbHost),
                escapeshellarg("empresa_id = $empresa_id"),
                escapeshellarg($dbName),
                $tablesStr,
                escapeshellarg($filePath)
            );

            exec($cmdSchema, $out1, $ret1);
            exec($cmdData, $out2, $ret2);
            $returnVar = ($ret1 === 0 && $ret2 === 0) ? 0 : 1;
        } else {
            // BACKUP GLOBAL (SUPER ADMIN)
            $command = sprintf(
                '%s --user=%s --password=%s --host=%s %s > %s',
                $mysqldump, escapeshellarg($dbUser), escapeshellarg($dbPass), escapeshellarg($dbHost), escapeshellarg($dbName), escapeshellarg($filePath)
            );
            exec($command, $output, $returnVar);
        }

        if ($returnVar === 0) {
            $size = filesize($filePath);
            
            return Backup::create([
                'empresa_id' => $empresa_id,
                'filename'   => $filename,
                'path'       => $diskPath,
                'size'       => $size,
                'type'       => $type
            ]);
        }

        Log::error("Error en Backup: Código " . $returnVar);
        return false;
    }
}
