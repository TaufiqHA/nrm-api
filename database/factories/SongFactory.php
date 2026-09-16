<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Song>
 */
class SongFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'songtitle' => fake()->sentence(3),
            'songsinger' => fake()->name(),
            'songurl' => fake()->url(),
            'songcategory' => Category::factory(),
            'songnada' => fake()->randomElement(['C', 'D', 'E', 'F', 'G', 'A', 'B', 'Am', 'Em']),
            'songduration' => fake()->numerify('#:##'),
        ];
    }
}
