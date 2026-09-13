<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use App\Services\YouTube\YouTubeService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.youtube.key', 'test-key');
    config()->set('services.youtube.base_url', 'https://www.googleapis.com/youtube/v3');

    $this->admin = User::factory()->admin()->create();
});

function fakeYouTubeSuccess(string $videoId = 'dQw4w9WgXcQ'): void
{
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id'      => $videoId,
                'snippet' => [
                    'title'        => 'Never Gonna Give You Up',
                    'description'  => 'The classic.',
                    'channelId'    => 'UCuAXFkgsw1L7xaCfnd5JJOw',
                    'channelTitle' => 'Rick Astley',
                    'publishedAt'  => '2009-10-25T06:57:33Z',
                    'thumbnails'   => [
                        'maxres' => ['url' => 'https://img.example/max.jpg'],
                    ],
                ],
                'contentDetails' => ['duration' => 'PT3M33S'],
                'statistics'     => ['viewCount' => '1000000'],
            ]],
        ], 200),
    ]);
}

it('requires authentication for the add-video flow', function () {
    $this->get('/admin/videos/create')->assertRedirect('/login');
    $this->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])
        ->assertRedirect('/login');
});

it('forbids non-admins from adding videos', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/videos/create')->assertForbidden();
    $this->actingAs($user)->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])
        ->assertForbidden();
});

it('renders the create page', function () {
    $this->actingAs($this->admin)
        ->get('/admin/videos/create')
        ->assertOk()
        ->assertSee('Paste a YouTube URL');
});

it('rejects an invalid URL before hitting YouTube', function () {
    Http::fake(); // any request would fail the test

    $this->actingAs($this->admin)
        ->from('/admin/videos/create')
        ->post('/admin/videos/fetch', ['url' => 'https://vimeo.com/123456'])
        ->assertRedirect('/admin/videos/create')
        ->assertSessionHasErrors('url');

    Http::assertNothingSent();
});

it('fetches metadata and lands on the review screen', function () {
    fakeYouTubeSuccess();

    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])
        ->assertRedirect(route('admin.videos.review'));

    $this->actingAs($this->admin)
        ->get('/admin/videos/review')
        ->assertOk()
        ->assertSee('Never Gonna Give You Up')
        ->assertSee('Rick Astley')
        ->assertSee('dQw4w9WgXcQ');
});

it('redirects to index when the video already exists (no API call)', function () {
    Video::factory()->create(['youtube_video_id' => 'dQw4w9WgXcQ', 'title' => 'Already Here']);

    Http::fake(); // must not be called

    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])
        ->assertRedirect(route('admin.videos.index'))
        ->assertSessionHas('error');

    Http::assertNothingSent();
});

it('handles a YouTube not-found response gracefully', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response(['items' => []], 200),
    ]);

    $this->actingAs($this->admin)
        ->from('/admin/videos/create')
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/aaaaaaaaaaa'])
        ->assertRedirect('/admin/videos/create')
        ->assertSessionHasErrors('url');
});

it('handles quota exceeded gracefully', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'error' => ['errors' => [['reason' => 'quotaExceeded']]],
        ], 403),
    ]);

    $this->actingAs($this->admin)
        ->from('/admin/videos/create')
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ'])
        ->assertRedirect('/admin/videos/create')
        ->assertSessionHasErrors('url');
});

it('redirects to create with an error if review is accessed without a pending video', function () {
    $this->actingAs($this->admin)
        ->get('/admin/videos/review')
        ->assertRedirect(route('admin.videos.create'))
        ->assertSessionHas('error');
});

it('persists a video from the review screen', function () {
    fakeYouTubeSuccess();
    $category = Category::factory()->create();

    // Step 1: fetch
    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ']);

    // Step 2: save
    $response = $this->actingAs($this->admin)->post('/admin/videos', [
        'youtube_video_id' => 'dQw4w9WgXcQ',
        'title'            => 'Ignored — server uses session DTO',
        'status'           => 'published',
        'visibility'       => 'public',
        'category_id'      => $category->id,
        'is_featured'      => '1',
        'is_daily_focus'   => '0',
        'display_order'    => 5,
    ]);

    $response->assertRedirect(route('admin.videos.index'))
             ->assertSessionHas('status');

    $video = Video::where('youtube_video_id', 'dQw4w9WgXcQ')->first();

    expect($video)->not->toBeNull()
        ->and($video->title)->toBe('Never Gonna Give You Up') // session DTO, not form
        ->and($video->status)->toBe(Video::STATUS_PUBLISHED)
        ->and($video->published_at)->not->toBeNull()
        ->and($video->is_featured)->toBeTrue()
        ->and($video->category_id)->toBe($category->id)
        ->and($video->created_by)->toBe($this->admin->id)
        ->and($video->duration_seconds)->toBe(213);
});

it('saves as draft without published_at', function () {
    fakeYouTubeSuccess();

    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $this->actingAs($this->admin)->post('/admin/videos', [
        'youtube_video_id' => 'dQw4w9WgXcQ',
        'status'           => 'draft',
        'visibility'       => 'public',
    ]);

    $video = Video::where('youtube_video_id', 'dQw4w9WgXcQ')->first();

    expect($video->status)->toBe(Video::STATUS_DRAFT)
        ->and($video->published_at)->toBeNull();
});

it('rejects a save with a mismatched youtube_video_id', function () {
    fakeYouTubeSuccess();

    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $this->actingAs($this->admin)
        ->from('/admin/videos/review')
        ->post('/admin/videos', [
            'youtube_video_id' => 'aaaaaaaaaaa', // tampered
            'status'           => 'draft',
            'visibility'       => 'public',
        ])
        ->assertRedirect(route('admin.videos.create'))
        ->assertSessionHas('error');

    expect(Video::where('youtube_video_id', 'aaaaaaaaaaa')->exists())->toBeFalse();
});

it('records an audit log entry when a video is created', function () {
    fakeYouTubeSuccess();

    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $this->actingAs($this->admin)->post('/admin/videos', [
        'youtube_video_id' => 'dQw4w9WgXcQ',
        'status'           => 'published',
        'visibility'       => 'public',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $this->admin->id,
        'action'  => 'video.created',
    ]);
});

it('cancels a pending video from the create page', function () {
    fakeYouTubeSuccess();

    $this->actingAs($this->admin)
        ->post('/admin/videos/fetch', ['url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $this->actingAs($this->admin)
        ->delete('/admin/videos/create')
        ->assertRedirect(route('admin.videos.create'))
        ->assertSessionHas('status');

    // Review now bounces to create with an error.
    $this->actingAs($this->admin)
        ->get('/admin/videos/review')
        ->assertRedirect(route('admin.videos.create'));
});