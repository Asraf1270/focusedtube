<?php

use App\Models\Video;

it('lists only published public videos', function () {
    Video::factory()->published()->create(['title' => 'Visible']);
    Video::factory()->create(['title' => 'Draft']);
    Video::factory()->published()->create(['title' => 'Hidden', 'visibility' => 'private']);

    $this->get('/videos')
        ->assertOk()
        ->assertSee('Visible')
        ->assertDontSee('Draft')
        ->assertDontSee('Hidden');
});

it('filters by search term', function () {
    Video::factory()->published()->create(['title' => 'Laravel Basics']);
    Video::factory()->published()->create(['title' => 'Physics 101']);

    $this->get('/videos?q=Laravel')
        ->assertSee('Laravel Basics')
        ->assertDontSee('Physics 101');
});