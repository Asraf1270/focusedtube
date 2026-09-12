<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(4, true));
        $id    = Str::random(11);

        return [
            'youtube_video_id'     => $id,
            'title'                => $title,
            'slug'                 => Video::uniqueSlug($title),
            'description'          => fake()->paragraph(),
            'thumbnail_url'        => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
            'channel_id'           => 'UC'.Str::random(20),
            'channel_name'         => fake()->company(),
            'duration_seconds'     => fake()->numberBetween(60, 3600),
            'youtube_published_at' => fake()->dateTimeBetween('-2 years'),
            'category_id'          => Category::factory(),
            'status'               => Video::STATUS_DRAFT,
            'visibility'           => Video::VISIBILITY_PUBLIC,
            'created_by'           => User::factory()->admin(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => Video::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function dailyFocus(): static
    {
        return $this->state(fn () => ['is_daily_focus' => true]);
    }
}