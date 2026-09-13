<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Video;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('forbids non-admins from editing videos', function () {
    $user  = User::factory()->create();
    $video = Video::factory()->published()->create();

    $this->actingAs($user)->get("/admin/videos/{$video->slug}/edit")->assertForbidden();
    $this->actingAs($user)->patch("/admin/videos/{$video->slug}", ['title' => 'X'])->assertForbidden();
});

it('renders the edit page for an admin', function () {
    $video = Video::factory()->published()->create();

    $this->actingAs($this->admin)
        ->get("/admin/videos/{$video->slug}/edit")
        ->assertOk()
        ->assertSee($video->title)
        ->assertSee($video->youtube_video_id);
});

it('updates editable fields but not YouTube-owned ones', function () {
    $category = Category::factory()->create();
    $video    = Video::factory()->create([
        'title'            => 'Original Title',
        'youtube_video_id' => 'aaaaaaaaaaa',
        'channel_name'     => 'Original Channel',
        'duration_seconds' => 100,
    ]);

    $this->actingAs($this->admin)->patch("/admin/videos/{$video->slug}", [
        'title'            => 'New Title',
        'description'      => 'New description',
        'category_id'      => $category->id,
        'visibility'       => 'public',
        'display_order'    => 7,
        // Attempts to override YouTube fields — must be ignored.
        'channel_name'     => 'Hacked',
        'duration_seconds' => 9999,
        'youtube_video_id' => 'bbbbbbbbbbb',
    ])->assertRedirect();

    $video->refresh();

    expect($video->title)->toBe('New Title')
        ->and($video->description)->toBe('New description')
        ->and($video->category_id)->toBe($category->id)
        ->and($video->display_order)->toBe(7)
        ->and($video->channel_name)->toBe('Original Channel')
        ->and($video->duration_seconds)->toBe(100)
        ->and($video->youtube_video_id)->toBe('aaaaaaaaaaa');
});

it('regenerates slug when the title changes', function () {
    $video = Video::factory()->create(['title' => 'Old Title']);

    $this->actingAs($this->admin)->patch("/admin/videos/{$video->slug}", [
        'title'      => 'Brand New Title',
        'visibility' => 'public',
    ]);

    expect($video->refresh()->slug)->toBe('brand-new-title');
});

it('publishes a draft', function () {
    $video = Video::factory()->create(['status' => Video::STATUS_DRAFT]);

    $this->actingAs($this->admin)
        ->post("/admin/videos/{$video->slug}/publish")
        ->assertRedirect();

    $video->refresh();
    expect($video->status)->toBe(Video::STATUS_PUBLISHED)
        ->and($video->published_at)->not->toBeNull();

    $this->assertDatabaseHas('audit_logs', ['action' => 'video.published']);
});

it('unpublishes a published video and clears published_at', function () {
    $video = Video::factory()->published()->create();

    $this->actingAs($this->admin)
        ->post("/admin/videos/{$video->slug}/unpublish")
        ->assertRedirect();

    $video->refresh();
    expect($video->status)->toBe(Video::STATUS_DRAFT)
        ->and($video->published_at)->toBeNull();
});

it('archives a video and clears daily focus', function () {
    $video = Video::factory()->published()->dailyFocus()->create();

    $this->actingAs($this->admin)
        ->post("/admin/videos/{$video->slug}/archive")
        ->assertRedirect();

    $video->refresh();
    expect($video->status)->toBe(Video::STATUS_ARCHIVED)
        ->and($video->is_daily_focus)->toBeFalse();
});

it('restores an archived video to draft', function () {
    $video = Video::factory()->create(['status' => Video::STATUS_ARCHIVED]);

    $this->actingAs($this->admin)
        ->post("/admin/videos/{$video->slug}/restore")
        ->assertRedirect();

    expect($video->refresh()->status)->toBe(Video::STATUS_DRAFT);
});

it('deletes a video and cascades related rows', function () {
    $video = Video::factory()->published()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/videos/{$video->slug}")
        ->assertRedirect(route('admin.videos.index'));

    expect(Video::find($video->id))->toBeNull();
    $this->assertDatabaseHas('audit_logs', ['action' => 'video.deleted']);
});

it('records an audit entry for updates', function () {
    $video = Video::factory()->create();

    $this->actingAs($this->admin)->patch("/admin/videos/{$video->slug}", [
        'title'      => 'Renamed',
        'visibility' => 'public',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'video.updated',
    ]);
});

/* ------------------------------ Bulk ------------------------------ */

it('bulk publishes selected videos', function () {
    $a = Video::factory()->create(['status' => Video::STATUS_DRAFT]);
    $b = Video::factory()->create(['status' => Video::STATUS_DRAFT]);
    $c = Video::factory()->published()->create(); // already published — should be a no-op

    $this->actingAs($this->admin)->post('/admin/videos/bulk', [
        'action' => 'publish',
        'ids'    => [$a->id, $b->id, $c->id],
    ])->assertRedirect();

    expect($a->refresh()->status)->toBe(Video::STATUS_PUBLISHED)
        ->and($b->refresh()->status)->toBe(Video::STATUS_PUBLISHED)
        ->and($c->refresh()->status)->toBe(Video::STATUS_PUBLISHED);
});

it('bulk deletes and reports skipped ids', function () {
    $a = Video::factory()->create();

    $this->actingAs($this->admin)->post('/admin/videos/bulk', [
        'action' => 'delete',
        'ids'    => [$a->id, 999999],
    ])->assertSessionHas('status');

    expect(Video::find($a->id))->toBeNull();
});

it('rejects unknown bulk actions', function () {
    $this->actingAs($this->admin)->post('/admin/videos/bulk', [
        'action' => 'invalid-action',
        'ids'    => [1],
    ])->assertSessionHasErrors('action');
});

it('forbids bulk actions for non-admins', function () {
    $user = User::factory()->create();
    $v    = Video::factory()->create();

    $this->actingAs($user)->post('/admin/videos/bulk', [
        'action' => 'delete',
        'ids'    => [$v->id],
    ])->assertForbidden();
});