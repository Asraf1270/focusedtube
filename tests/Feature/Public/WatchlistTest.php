<?php

use App\Models\User;
use App\Models\Video;

it('redirects guests to login', function () {
    $video = Video::factory()->published()->create();

    $this->post("/videos/{$video->slug}/save")->assertRedirect('/login');
});

it('saves and unsaves a video for a logged-in user', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    $this->actingAs($user)->post("/videos/{$video->slug}/save")->assertRedirect();
    $this->assertDatabaseHas('watchlists', ['user_id' => $user->id, 'video_id' => $video->id]);

    $this->actingAs($user)->delete("/videos/{$video->slug}/save")->assertRedirect();
    $this->assertDatabaseMissing('watchlists', ['user_id' => $user->id, 'video_id' => $video->id]);
});

it('responds with JSON when requested', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    $this->actingAs($user)->postJson("/videos/{$video->slug}/save")
        ->assertOk()
        ->assertJson(['saved' => true]);

    $this->actingAs($user)->deleteJson("/videos/{$video->slug}/save")
        ->assertOk()
        ->assertJson(['saved' => false]);
});

it('hides unpublished videos from the watchlist page', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create(['title' => 'Will Be Unpublished']);

    $user->watchlist()->create(['video_id' => $video->id]);

    $video->update(['status' => 'draft']);

    $this->actingAs($user)
        ->get('/watchlist')
        ->assertOk()
        ->assertDontSee('Will Be Unpublished');
});