<?php

namespace Database\Factories;

use App\Models\Nada;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nada>
 */
class NadaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nada' => fake()->unique()->word(),
        ];
    }
}
