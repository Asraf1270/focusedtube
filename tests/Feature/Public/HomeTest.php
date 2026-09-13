<?php

use App\Models\Category;
use App\Models\Video;

it('renders the home page for guests', function () {
    $this->get('/')->assertOk()->assertSee('Learn without the noise');
});

it('never shows draft videos on the home page', function () {
    Video::factory()->published()->create(['title' => 'Visible Video']);
    Video::factory()->create(['title' => 'Hidden Draft']); // status = draft

    $this->get('/')->assertSee('Visible Video')->assertDontSee('Hidden Draft');
});

it('never shows private videos on the home page', function () {
    Video::factory()->published()->create([
        'title'      => 'Public Video',
        'visibility' => 'public',
    ]);
    Video::factory()->published()->create([
        'title'      => 'Private Video',
        'visibility' => 'private',
    ]);

    $this->get('/')->assertSee('Public Video')->assertDontSee('Private Video');
});

it('never shows archived videos on the home page', function () {
    Video::factory()->published()->create(['title' => 'Live Video']);
    Video::factory()->create(['title' => 'Archived Video', 'status' => 'archived']);

    $this->get('/')->assertSee('Live Video')->assertDontSee('Archived Video');
});

it('shows the daily focus when set', function () {
    Video::factory()->published()->dailyFocus()->create(['title' => 'Today Focus Video']);

    $this->get('/')->assertSee("Today's Focus")->assertSee('Today Focus Video');
});

it('shows an empty state when there are no videos', function () {
    $this->get('/')->assertSee('FocusedTube is empty right now');
});