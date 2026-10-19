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
            'applicationads1type' => 'image',
            'applicationads1unit' => null,
            'applicationads1active' => 'Y',
            'applicationads1clickurl' => fake()->url(),
            'applicationads2' => fake()->imageUrl(),
            'applicationads2type' => 'image',
            'applicationads2unit' => null,
            'applicationads2active' => 'Y',
            'applicationads2clickurl' => fake()->url(),
            'applicationadsactive' => 'Y',
            'applicationadsbottom' => fake()->imageUrl(),
            'applicationadsbottomtype' => 'image',
            'applicationadsbottomunit' => null,
            'applicationadsbottomactive' => 'Y',
            'applicationadsbottomclickurl' => fake()->url(),
        ];
    }
}
