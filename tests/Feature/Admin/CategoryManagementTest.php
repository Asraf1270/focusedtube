<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Video;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('forbids non-admins from categories', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/admin/categories')->assertForbidden();
});

it('lists categories', function () {
    Category::factory()->create(['name' => 'Programming']);

    $this->actingAs($this->admin)
        ->get('/admin/categories')
        ->assertOk()
        ->assertSee('Programming');
});

it('creates a category and generates slug', function () {
    $this->actingAs($this->admin)->post('/admin/categories', [
        'name'       => 'Mathematics',
        'status'     => 'active',
        'sort_order' => 3,
    ])->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Mathematics',
        'slug' => 'mathematics',
    ]);

    $this->assertDatabaseHas('audit_logs', ['action' => 'category.created']);
});

it('updates a category and regenerates slug when name changes', function () {
    $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

    $this->actingAs($this->admin)->patch("/admin/categories/{$category->slug}", [
        'name'   => 'New Name',
        'status' => 'active',
    ])->assertRedirect();

    expect($category->refresh()->slug)->toBe('new-name');
});

it('deletes a category and leaves its videos uncategorized', function () {
    $category = Category::factory()->create();
    $video    = Video::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->delete("/admin/categories/{$category->slug}")
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::find($category->id))->toBeNull()
        ->and($video->refresh()->category_id)->toBeNull();
});