<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mp3_url' => 'https://archive.org/download/work_2307.poem_librivox/work_anderson_alp.mp3',
            'name' => $this->faker->name,
            'author'=> $this->faker->name,
        ];
    }
}
