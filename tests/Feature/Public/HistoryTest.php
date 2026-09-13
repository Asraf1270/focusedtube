<?php

use App\Models\User;
use App\Models\Video;
use App\Models\WatchHistory;

it('lists history grouped by day', function () {
    $user = User::factory()->create();
    $v1   = Video::factory()->published()->create(['title' => 'Today Video']);
    $v2   = Video::factory()->published()->create(['title' => 'Yesterday Video']);

    WatchHistory::create(['user_id' => $user->id, 'video_id' => $v1->id, 'watched_at' => now()]);
    WatchHistory::create(['user_id' => $user->id, 'video_id' => $v2->id, 'watched_at' => now()->subDay()]);

    $this->actingAs($user)
        ->get('/history')
        ->assertOk()
        ->assertSee('Today')
        ->assertSee('Yesterday')
        ->assertSee('Today Video')
        ->assertSee('Yesterday Video');
});

it('removes a single history entry', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();
    $entry = WatchHistory::create(['user_id' => $user->id, 'video_id' => $video->id, 'watched_at' => now()]);

    $this->actingAs($user)
        ->delete("/history/{$entry->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('watch_histories', ['id' => $entry->id]);
});

it('clears the entire history', function () {
    $user = User::factory()->create();
    $v    = Video::factory()->published()->create();

    for ($i = 0; $i < 3; $i++) {
        WatchHistory::create([
            'user_id' => $user->id,
            'video_id' => $v->id,
            'watched_at' => now()->subMinutes($i),
        ]);
    }

    $this->actingAs($user)->delete('/history')->assertRedirect();

    expect(WatchHistory::where('user_id', $user->id)->count())->toBe(0);
});

it('does not let a user delete another user\'s history entry', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $v = Video::factory()->published()->create();

    $entry = WatchHistory::create(['user_id' => $b->id, 'video_id' => $v->id, 'watched_at' => now()]);

    $this->actingAs($a)->delete("/history/{$entry->id}");

    // Scoped query means nothing is deleted, but the request itself "succeeds".
    $this->assertDatabaseHas('watch_histories', ['id' => $entry->id]);
});