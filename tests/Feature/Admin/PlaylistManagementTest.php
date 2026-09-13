<?php

use App\Models\Category;
use App\Models\Playlist;
use App\Models\User;
use App\Models\Video;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('creates a playlist and lands on edit page', function () {
    $this->actingAs($this->admin)->post('/admin/playlists', [
        'title'  => 'Laravel Beginner',
        'status' => 'draft',
    ])->assertRedirect();

    $this->assertDatabaseHas('playlists', [
        'title' => 'Laravel Beginner',
        'slug'  => 'laravel-beginner',
    ]);
});

it('sets published_at when transitioning draft → published', function () {
    $playlist = Playlist::factory()->create(['status' => Playlist::STATUS_DRAFT]);

    $this->actingAs($this->admin)->patch("/admin/playlists/{$playlist->slug}", [
        'title'  => $playlist->title,
        'status' => 'published',
    ])->assertRedirect();

    $playlist->refresh();
    expect($playlist->status)->toBe(Playlist::STATUS_PUBLISHED)
        ->and($playlist->published_at)->not->toBeNull();
});

it('attaches videos to a playlist with incrementing positions', function () {
    $playlist = Playlist::factory()->create();
    $a = Video::factory()->create();
    $b = Video::factory()->create();

    $this->actingAs($this->admin)
        ->post("/admin/playlists/{$playlist->slug}/videos", ['video_id' => $a->id]);

    $this->actingAs($this->admin)
        ->post("/admin/playlists/{$playlist->slug}/videos", ['video_id' => $b->id]);

    $this->assertDatabaseHas('playlist_video', [
        'playlist_id' => $playlist->id,
        'video_id'    => $a->id,
        'position'    => 1,
    ]);
    $this->assertDatabaseHas('playlist_video', [
        'playlist_id' => $playlist->id,
        'video_id'    => $b->id,
        'position'    => 2,
    ]);
});

it('does not duplicate an attached video', function () {
    $playlist = Playlist::factory()->create();
    $video    = Video::factory()->create();

    $this->actingAs($this->admin)->post("/admin/playlists/{$playlist->slug}/videos", ['video_id' => $video->id]);
    $this->actingAs($this->admin)->post("/admin/playlists/{$playlist->slug}/videos", ['video_id' => $video->id]);

    expect($playlist->videos()->count())->toBe(1);
});

it('detaches a video', function () {
    $playlist = Playlist::factory()->create();
    $video    = Video::factory()->create();
    $playlist->videos()->attach($video->id, ['position' => 1]);

    $this->actingAs($this->admin)
        ->delete("/admin/playlists/{$playlist->slug}/videos/{$video->slug}")
        ->assertRedirect();

    expect($playlist->videos()->count())->toBe(0);
});

it('reorders videos', function () {
    $playlist = Playlist::factory()->create();
    $a = Video::factory()->create();
    $b = Video::factory()->create();
    $c = Video::factory()->create();

    $playlist->videos()->attach($a->id, ['position' => 1]);
    $playlist->videos()->attach($b->id, ['position' => 2]);
    $playlist->videos()->attach($c->id, ['position' => 3]);

    $this->actingAs($this->admin)->post("/admin/playlists/{$playlist->slug}/reorder", [
        'ids' => [$c->id, $a->id, $b->id],
    ])->assertRedirect();

    $order = $playlist->videos()->orderByPivot('position')->pluck('videos.id')->all();

    expect($order)->toBe([$c->id, $a->id, $b->id]);
});

it('rejects reorder ids not belonging to the playlist', function () {
    $playlist = Playlist::factory()->create();
    $a = Video::factory()->create();
    $b = Video::factory()->create();
    $playlist->videos()->attach($a->id, ['position' => 1]);

    $this->actingAs($this->admin)->post("/admin/playlists/{$playlist->slug}/reorder", [
        'ids' => [$b->id],
    ])->assertSessionHas('error');
});

it('deletes a playlist without deleting its videos', function () {
    $playlist = Playlist::factory()->create();
    $video    = Video::factory()->create();
    $playlist->videos()->attach($video->id, ['position' => 1]);

    $this->actingAs($this->admin)
        ->delete("/admin/playlists/{$playlist->slug}")
        ->assertRedirect(route('admin.playlists.index'));

    expect(Playlist::find($playlist->id))->toBeNull()
        ->and(Video::find($video->id))->not->toBeNull()
        ->and(\DB::table('playlist_video')->where('playlist_id', $playlist->id)->count())->toBe(0);
});