<?php

use App\Models\Category;
use App\Models\Video;

it('lists active categories only', function () {
    Category::factory()->create(['name' => 'Programming', 'status' => 'active']);
    Category::factory()->create(['name' => 'Secret', 'status' => 'inactive']);

    $this->get('/categories')
        ->assertOk()
        ->assertSee('Programming')
        ->assertDontSee('Secret');
});

it('renders a category page with only published videos', function () {
    $category = Category::factory()->create(['name' => 'Programming']);

    Video::factory()->published()->create([
        'title'       => 'Laravel Basics',
        'category_id' => $category->id,
    ]);

    Video::factory()->create([
        'title'       => 'Laravel Draft',
        'status'      => 'draft',
        'category_id' => $category->id,
    ]);

    $this->get("/categories/{$category->slug}")
        ->assertOk()
        ->assertSee('Laravel Basics')
        ->assertDontSee('Laravel Draft');
});

it('404s on an inactive category', function () {
    $category = Category::factory()->create(['status' => 'inactive']);

    $this->get("/categories/{$category->slug}")->assertNotFound();
});

it('404s on a category with a fake slug', function () {
    $this->get('/categories/does-not-exist')->assertNotFound();
});