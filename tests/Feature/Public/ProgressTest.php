<?php

use App\Models\User;
use App\Models\Video;
use App\Models\WatchProgress;

it('requires authentication', function () {
    $video = Video::factory()->published()->create();

    $this->postJson("/videos/{$video->slug}/progress", ['position' => 10])
        ->assertUnauthorized();
});

it('creates a progress row on first update', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['duration_seconds' => 600]);

    $this->actingAs($user)
        ->postJson("/videos/{$video->slug}/progress", ['position' => 120, 'duration' => 600])
        ->assertOk()
        ->assertJson(['ok' => true, 'position' => 120, 'percent' => 20, 'completed' => false]);

    $this->assertDatabaseHas('watch_progress', [
        'user_id'                  => $user->id,
        'video_id'                 => $video->id,
        'current_position_seconds' => 120,
        'progress_percentage'      => 20,
        'completed'                => false,
    ]);
});

it('updates an existing progress row', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['duration_seconds' => 600]);

    $this->actingAs($user)->postJson("/videos/{$video->slug}/progress", ['position' => 100, 'duration' => 600]);
    $this->actingAs($user)->postJson("/videos/{$video->slug}/progress", ['position' => 300, 'duration' => 600]);

    expect(WatchProgress::where('user_id', $user->id)->where('video_id', $video->id)->count())->toBe(1);
    expect(WatchProgress::first()->current_position_seconds)->toBe(300);
});

it('marks the video as completed at 95% or more', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['duration_seconds' => 100]);

    $this->actingAs($user)->postJson("/videos/{$video->slug}/progress", [
        'position' => 96,
        'duration' => 100,
    ])->assertJson(['completed' => true]);

    $row = WatchProgress::first();
    expect($row->completed)->toBeTrue()
        ->and($row->completed_at)->not->toBeNull();
});

it('clamps the position to the duration', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['duration_seconds' => 100]);

    $this->actingAs($user)->postJson("/videos/{$video->slug}/progress", [
        'position' => 99999,
        'duration' => 100,
    ])->assertJson(['position' => 100]);
});

it('rejects negative positions', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    $this->actingAs($user)
        ->postJson("/videos/{$video->slug}/progress", ['position' => -5])
        ->assertStatus(422);
});

it('is rate limited at 60 requests per minute', function () {
    \Illuminate\Support\Facades\RateLimiter::clear('');

    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($user)->postJson("/videos/{$video->slug}/progress", ['position' => $i]);
    }

    $this->actingAs($user)
        ->postJson("/videos/{$video->slug}/progress", ['position' => 999])
        ->assertStatus(429);
});