<?php

use App\Models\Category;
use App\Models\Video;

it('returns no results when the term is empty', function () {
    $this->get('/search')
        ->assertOk()
        ->assertSee('Start typing to search');
});

it('searches only published videos', function () {
    Video::factory()->published()->create(['title' => 'Laravel Routing']);
    Video::factory()->create(['title' => 'Laravel Draft', 'status' => 'draft']);

    $this->get('/search?q=Laravel')
        ->assertOk()
        ->assertSee('Laravel Routing')
        ->assertDontSee('Laravel Draft');
});

it('filters by category', function () {
    $cat = Category::factory()->create();

    Video::factory()->published()->create([
        'title'       => 'Categorized Video',
        'category_id' => $cat->id,
    ]);
    Video::factory()->published()->create(['title' => 'Other Video']);

    $this->get("/search?category={$cat->id}")
        ->assertOk()
        ->assertSee('Categorized Video')
        ->assertDontSee('Other Video');
});

it('never leaks drafts with empty filters', function () {
    Video::factory()->published()->create(['title' => 'Good Video']);
    Video::factory()->create(['title' => 'Bad Draft']);

    $this->get('/search?q=Video')
        ->assertSee('Good Video')
        ->assertDontSee('Bad Draft');
});