<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    public function definition(): array
    {
        return ['usuario' => fake()->unique()->userName(), 'password_hash' => password_hash('prueba-segura', PASSWORD_BCRYPT, ['cost' => 4]), 'nombre_completo' => fake()->name()];
    }
}
