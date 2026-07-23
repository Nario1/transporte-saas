<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run-db';
    protected $description = 'Genera una copia de seguridad de la base de datos (Semanal)';

    public function handle(\App\Services\BackupService $backupService)
    {
        $this->info('Iniciando copia de seguridad...');
        
        $backup = $backupService->generate('automatico');

        if ($backup) {
            $this->info("Copia de seguridad exitosa: {$backup->filename}");
        } else {
            $this->error('Error al generar la copia de seguridad. Ver logs.');
        }
    }
}
