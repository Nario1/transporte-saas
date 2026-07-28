<?php

namespace App\Services;

use App\Models\Conductor;
use App\Models\ConductorRostro;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReconocimientoFacialService
{
    /**
     * Guarda un nuevo perfil facial desactivando los anteriores.
     */
    public function guardarPerfilFacial(Conductor $conductor, array $embedding, string $fotoB64): ConductorRostro
    {
        // Desactivar rostros anteriores
        ConductorRostro::where('conductor_id', $conductor->id)
            ->update(['activo' => false]);

        $fotoPath = $this->guardarFoto($fotoB64, $conductor->id);

        return ConductorRostro::create([
            'conductor_id' => $conductor->id,
            'embedding'    => $embedding,
            'foto_path'    => $fotoPath,
            'activo'       => true,
        ]);
    }

    public function eliminarPerfilFacial(Conductor $conductor): void
    {
        $rostros = ConductorRostro::where('conductor_id', $conductor->id)->get();
        foreach ($rostros as $r) {
            if ($r->foto_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($r->foto_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($r->foto_path);
            }
            $r->delete();
        }
        $conductor->update(['primer_ingreso' => true]);
    }

    private function guardarFoto(string $base64, int $conductorId): string
    {
        // Limpiar prefijo data URL: "data:image/jpeg;base64,..."
        if (str_contains($base64, ',')) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }

        $bytes    = base64_decode($base64);
        $filename = "rostros/conductor_{$conductorId}_" . Str::random(8) . '.jpg';

        Storage::disk('public')->put($filename, $bytes);

        return $filename;
    }
}
