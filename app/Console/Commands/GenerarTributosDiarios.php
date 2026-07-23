<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Tributo;
use Carbon\Carbon;

class GenerarTributosDiarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tributos:generar {--days=7 : Días a revisar hacia atrás}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los tributos pendientes para todos los vehículos activos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diasAtras = $this->option('days');
        $empresas = Empresa::where('activa', true)->get();
        $totalGenerados = 0;

        $this->info("Iniciando generación de tributos para " . $empresas->count() . " empresas...");

        foreach ($empresas as $empresa) {
            $montoDiario = $empresa->tributo_diario ?? 0.00;

            if ($montoDiario <= 0) {
                $this->line("- {$empresa->nombre}: Saltando (Tributo diario no configurado).");
                continue;
            }

            $vehiculos = $empresa->vehiculos()->where('estado', 'activo')->get();
            $generadosEmpresa = 0;

            for ($i = 0; $i < $diasAtras; $i++) {
                $fecha = today()->subDays($i);
                $fechaStr = $fecha->toDateString();

                foreach ($vehiculos as $vehiculo) {
                    // Solo generamos si el vehículo ya existía en esa fecha
                    if ($vehiculo->created_at->isAfter($fecha->endOfDay())) {
                        continue;
                    }

                    $creado = Tributo::firstOrCreate(
                        [
                            'vehiculo_id' => $vehiculo->id,
                            'fecha'       => $fechaStr,
                        ],
                        [
                            'empresa_id'   => $empresa->id,
                            'conductor_id' => $vehiculo->conductor_id,
                            'monto'        => $montoDiario,
                            'estado'       => 'pendiente',
                        ]
                    );

                    if ($creado->wasRecentlyCreated) {
                        $generadosEmpresa++;
                    }
                }
            }

            $this->line("- {$empresa->nombre}: {$generadosEmpresa} nuevos tributos.");
            $totalGenerados += $generadosEmpresa;
        }

        $this->info("Proceso finalizado. Total de tributos generados: {$totalGenerados}.");
    }
}
