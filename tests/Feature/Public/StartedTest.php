<?php

use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('requires authentication', function () {
    $video = Video::factory()->published()->create();

    $this->postJson("/videos/{$video->slug}/started")->assertUnauthorized();
});

it('records a history row for the user', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    $this->actingAs($user)
        ->postJson("/videos/{$video->slug}/started")
        ->assertOk()
        ->assertJson(['ok' => true, 'counted' => true]);

    $this->assertDatabaseHas('watch_histories', [
        'user_id'  => $user->id,
        'video_id' => $video->id,
    ]);
});

it('increments views_count only once per user per day', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['views_count' => 0]);

    $this->actingAs($user)->postJson("/videos/{$video->slug}/started");
    $this->actingAs($user)->postJson("/videos/{$video->slug}/started");
    $this->actingAs($user)->postJson("/videos/{$video->slug}/started");

    expect($video->refresh()->views_count)->toBe(1);
});

it('writes one history row per open even when view is not counted', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    $this->actingAs($user)->postJson("/videos/{$video->slug}/started");
    $this->actingAs($user)->postJson("/videos/{$video->slug}/started");

    expect(\App\Models\WatchHistory::where('user_id', $user->id)
        ->where('video_id', $video->id)->count())->toBe(2);
});

it('404s for a draft video', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)
        ->postJson("/videos/{$video->slug}/started")
        ->assertNotFound();
});