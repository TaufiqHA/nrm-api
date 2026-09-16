<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicationcompany' => fake()->company(),
            'applicationname' => fake()->words(2, true).' Karaoke',
            'applicationads1' => fake()->imageUrl(),
            'applicationads2' => fake()->imageUrl(),
            'applicationadsactive' => 'Y',
            'applicationadsbottom' => fake()->imageUrl(),
            'applicationadsbottomactive' => 'Y',
        ];
    }
}
