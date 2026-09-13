<?php

use App\Models\User;
use App\Models\Video;
use App\Models\WatchProgress;

it('renders a published video', function () {
    $video = Video::factory()->published()->create(['title' => 'Laravel Routing']);

    $this->get("/videos/{$video->slug}")
        ->assertOk()
        ->assertSee('Laravel Routing')
        ->assertSee('youtube.com/embed/'.$video->youtube_video_id, false);
});

it('404s on a draft video', function () {
    $video = Video::factory()->create();

    $this->get("/videos/{$video->slug}")->assertNotFound();
});

it('404s on a private video', function () {
    $video = Video::factory()->published()->create(['visibility' => 'private']);

    $this->get("/videos/{$video->slug}")->assertNotFound();
});

it('embeds a resume start time when progress exists', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['duration_seconds' => 600]);

    WatchProgress::create([
        'user_id'                  => $user->id,
        'video_id'                 => $video->id,
        'current_position_seconds' => 123,
        'duration_seconds'         => 600,
        'progress_percentage'      => 20,
        'last_watched_at'          => now(),
        'completed'                => false,
    ]);

    $this->actingAs($user)
        ->get("/videos/{$video->slug}")
        ->assertSee('start=123', false);
});

it('never shows draft videos in related list', function () {
    $category = \App\Models\Category::factory()->create();

    $video  = Video::factory()->published()->create(['category_id' => $category->id]);
    Video::factory()->published()->create([
        'title'       => 'Related Published',
        'category_id' => $category->id,
    ]);
    Video::factory()->create([
        'title'       => 'Related Draft',
        'status'      => 'draft',
        'category_id' => $category->id,
    ]);

    $this->get("/videos/{$video->slug}")
        ->assertSee('Related Published')
        ->assertDontSee('Related Draft');
});

it('includes a VideoObject JSON-LD block', function () {
    $video = Video::factory()->published()->create(['title' => 'Structured Video']);

    $this->get("/videos/{$video->slug}")
        ->assertSee('"@type":"VideoObject"', false)
        ->assertSee('"name":"Structured Video"', false);
});