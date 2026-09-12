<?php

namespace Database\Factories;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true));

        return [
            'title'       => $title,
            'slug'        => Playlist::uniqueSlug($title),
            'description' => fake()->sentence(),
            'status'      => Playlist::STATUS_DRAFT,
            'created_by'  => User::factory()->admin(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => Playlist::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }
}