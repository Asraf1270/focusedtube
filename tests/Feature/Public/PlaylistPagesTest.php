<?php

use App\Models\Playlist;
use App\Models\Video;

it('lists only published playlists', function () {
    Playlist::factory()->published()->create(['title' => 'Published Playlist']);
    Playlist::factory()->create(['title' => 'Draft Playlist']);

    $this->get('/playlists')
        ->assertOk()
        ->assertSee('Published Playlist')
        ->assertDontSee('Draft Playlist');
});

it('renders a published playlist with only published videos', function () {
    $playlist = Playlist::factory()->published()->create(['title' => 'Laravel Beginner']);

    $a = Video::factory()->published()->create(['title' => 'Video A']);
    $b = Video::factory()->create(['title' => 'Draft B']);

    $playlist->videos()->attach($a->id, ['position' => 1]);
    $playlist->videos()->attach($b->id, ['position' => 2]);

    $this->get("/playlists/{$playlist->slug}")
        ->assertOk()
        ->assertSee('Video A')
        ->assertDontSee('Draft B');
});

it('404s on a draft playlist', function () {
    $playlist = Playlist::factory()->create();

    $this->get("/playlists/{$playlist->slug}")->assertNotFound();
});