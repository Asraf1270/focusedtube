<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Video;

test('videos index lists only what the filters ask for', function () {
    $admin = User::factory()->admin()->create();

    $catA = Category::factory()->create(['name' => 'Programming']);
    $catB = Category::factory()->create(['name' => 'Science']);

    Video::factory()->published()->create([
        'title'       => 'Laravel Basics',
        'category_id' => $catA->id,
    ]);

    Video::factory()->create([
        'title'       => 'Laravel Draft',
        'status'      => Video::STATUS_DRAFT,
        'category_id' => $catA->id,
    ]);

    Video::factory()->published()->create([
        'title'       => 'Physics 101',
        'category_id' => $catB->id,
    ]);

    // No filters — all three appear.
    $this->actingAs($admin)->get('/admin/videos')
        ->assertOk()
        ->assertSee('Laravel Basics')
        ->assertSee('Laravel Draft')
        ->assertSee('Physics 101');

    // Search by title fragment.
    $this->actingAs($admin)->get('/admin/videos?q=Laravel')
        ->assertSee('Laravel Basics')
        ->assertSee('Laravel Draft')
        ->assertDontSee('Physics 101');

    // Filter by status.
    $this->actingAs($admin)->get('/admin/videos?status=published')
        ->assertSee('Laravel Basics')
        ->assertSee('Physics 101')
        ->assertDontSee('Laravel Draft');

    // Filter by category.
    $this->actingAs($admin)->get('/admin/videos?category='.$catB->id)
        ->assertSee('Physics 101')
        ->assertDontSee('Laravel Basics');
});

test('videos index shows empty state when there are no videos', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/videos')
        ->assertOk()
        ->assertSee('No videos yet');
});