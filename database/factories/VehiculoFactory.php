<?php

namespace Database\Factories;

use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    protected $model = Vehiculo::class;

    public function definition(): array
    {
        // Formato de placa peruana: 3 letras - 3 números o similar
        $placa = strtoupper(fake()->lexify('???')) . '-' . fake()->numerify('###');
        
        return [
            'placa'              => $placa,
            'numero_flota'       => fake()->unique()->numberBetween(1, 1000),
            'marca'              => fake()->randomElement(['Toyota', 'Hyundai', 'Mercedes-Benz', 'Mitsubishi', 'Volkswagen']),
            'modelo'             => fake()->randomElement(['Hilux', 'H1', 'Sprinter', 'L300', 'Transporter', 'Hiace']),
            'color'              => fake()->safeColorName(),
            'anio'               => fake()->numberBetween(2010, 2025),
            'soat_vence'         => fake()->dateTimeBetween('now', '+1 year'),
            'rev_tecnica_vence'  => fake()->dateTimeBetween('now', '+1 year'),
            'tarjeta_prop_vence' => fake()->dateTimeBetween('+1 year', '+5 years'),
            'estado'             => 'activo',
        ];
    }
}
