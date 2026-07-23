<?php

namespace Database\Factories;

use App\Models\Ruta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaFactory extends Factory
{
    protected $model = Ruta::class;

    public function definition(): array
    {
        $origen = fake()->city();
        $destino = fake()->city();
        
        return [
            'nombre'       => "Ruta {$origen} a {$destino}",
            'codigo'       => strtoupper(fake()->lexify('R-???')),
            'origen'       => $origen,
            'destino'      => $destino,
            'estado'       => 'activa',
            'duracion_min' => fake()->randomElement([30, 45, 60, 90, 120]),
            'descripcion'  => fake()->sentence(),
        ];
    }
}
