<?php

namespace Database\Factories;

use App\Models\Propietario;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropietarioFactory extends Factory
{
    protected $model = Propietario::class;

    public function definition(): array
    {
        return [
            'nombre'    => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'dni'       => fake()->numerify('########'), // 8 dígitos
            'telefono'  => '9' . fake()->numerify('########'), // Formato 9xxxxxxxx
            'email'     => fake()->unique()->safeEmail(),
            'direccion' => fake()->address(),
            'activo'    => true,
        ];
    }
}
