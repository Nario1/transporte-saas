<?php

namespace Database\Factories;

use App\Models\Conductor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConductorFactory extends Factory
{
    protected $model = Conductor::class;

    public function definition(): array
    {
        return [
            'nombre'          => fake()->firstName(),
            'apellidos'       => fake()->lastName() . ' ' . fake()->lastName(),
            'dni'             => fake()->numerify('########'),
            'telefono'        => '9' . fake()->numerify('########'),
            'email'           => fake()->unique()->safeEmail(),
            'direccion'       => fake()->address(),
            'tipo_licencia'   => fake()->randomElement(['A-I', 'A-IIa', 'A-IIb', 'A-IIIa', 'A-IIIb', 'A-IIIc']),
            'licencia_vence'  => fake()->dateTimeBetween('now', '+5 years'),
            'estado'          => fake()->randomElement(['activo', 'activo', 'activo', 'suspendido', 'inactivo']),
            'primer_ingreso'  => true,
        ];
    }
}
