<?php

use App\Exceptions\YouTube\YouTubeApiException;
use App\Exceptions\YouTube\YouTubeQuotaExceededException;
use App\Exceptions\YouTube\YouTubeVideoNotFoundException;
use App\Services\YouTube\YouTubeService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.youtube.key', 'test-key');
    config()->set('services.youtube.base_url', 'https://www.googleapis.com/youtube/v3');
    config()->set('services.youtube.timeout', 5);
    config()->set('services.youtube.retries', 0);

    // Fresh service with test config.
    $this->service = app(YouTubeService::class);
});

it('fetches and maps a single video', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id'      => 'dQw4w9WgXcQ',
                'snippet' => [
                    'title'        => 'Never Gonna Give You Up',
                    'description'  => 'The classic.',
                    'channelId'    => 'UCuAXFkgsw1L7xaCfnd5JJOw',
                    'channelTitle' => 'Rick Astley',
                    'publishedAt'  => '2009-10-25T06:57:33Z',
                    'thumbnails'   => [
                        'default' => ['url' => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/default.jpg'],
                        'high'    => ['url' => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg'],
                        'maxres'  => ['url' => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/maxresdefault.jpg'],
                    ],
                ],
                'contentDetails' => [
                    'duration' => 'PT3M33S',
                ],
                'statistics' => [
                    'viewCount' => '1000000',
                ],
            ]],
        ], 200),
    ]);

    $data = $this->service->fetchVideo('dQw4w9WgXcQ');

    expect($data->videoId)->toBe('dQw4w9WgXcQ')
        ->and($data->title)->toBe('Never Gonna Give You Up')
        ->and($data->channelId)->toBe('UCuAXFkgsw1L7xaCfnd5JJOw')
        ->and($data->channelName)->toBe('Rick Astley')
        ->and($data->durationSeconds)->toBe(213)
        ->and($data->thumbnailUrl)->toBe('https://i.ytimg.com/vi/dQw4w9WgXcQ/maxresdefault.jpg')
        ->and($data->publishedAt->toDateString())->toBe('2009-10-25');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/videos')
            && str_contains($request->url(), 'id=dQw4w9WgXcQ')
            && str_contains($request->url(), 'key=test-key')
            && str_contains($request->url(), 'part=snippet%2CcontentDetails%2Cstatistics');
    });
});

it('throws VideoNotFound when YouTube returns no items', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response(['items' => []], 200),
    ]);

    expect(fn () => $this->service->fetchVideo('abcdefghijk'))
        ->toThrow(YouTubeVideoNotFoundException::class);
});

it('throws QuotaExceeded when YouTube returns quota error', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'error' => [
                'errors' => [['reason' => 'quotaExceeded']],
            ],
        ], 403),
    ]);

    expect(fn () => $this->service->fetchVideo('dQw4w9WgXcQ'))
        ->toThrow(YouTubeQuotaExceededException::class);
});

it('throws ApiException for invalid api key', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'error' => ['errors' => [['reason' => 'keyInvalid']]],
        ], 400),
    ]);

    expect(fn () => $this->service->fetchVideo('dQw4w9WgXcQ'))
        ->toThrow(YouTubeApiException::class, 'API key problem');
});

it('throws ApiException when API key is missing', function () {
    config()->set('services.youtube.key', null);

    $service = new YouTubeService(
        apiKey: null,
        baseUrl: 'https://www.googleapis.com/youtube/v3',
        timeout: 5,
        retries: 0,
    );

    expect(fn () => $service->fetchVideo('dQw4w9WgXcQ'))
        ->toThrow(YouTubeApiException::class, 'YOUTUBE_API_KEY');
});

it('throws ApiException on network failure', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Timeout');
    });

    expect(fn () => $this->service->fetchVideo('dQw4w9WgXcQ'))
        ->toThrow(YouTubeApiException::class, 'Network error');
});

it('maps multiple videos in a single batch call', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [
                [
                    'id'      => 'aaaaaaaaaaa',
                    'snippet' => [
                        'title'        => 'A',
                        'channelTitle' => 'CA',
                        'thumbnails'   => ['default' => ['url' => 'https://img/a.jpg']],
                    ],
                    'contentDetails' => ['duration' => 'PT10S'],
                ],
                [
                    'id'      => 'bbbbbbbbbbb',
                    'snippet' => [
                        'title'        => 'B',
                        'channelTitle' => 'CB',
                        'thumbnails'   => ['default' => ['url' => 'https://img/b.jpg']],
                    ],
                    'contentDetails' => ['duration' => 'PT20S'],
                ],
            ],
        ], 200),
    ]);

    $results = $this->service->fetchVideos(['aaaaaaaaaaa', 'bbbbbbbbbbb']);

    expect($results)->toHaveCount(2)
        ->and($results['aaaaaaaaaaa']->durationSeconds)->toBe(10)
        ->and($results['bbbbbbbbbbb']->durationSeconds)->toBe(20);

    Http::assertSentCount(1);
});

it('rejects more than 50 ids in one call', function () {
    $ids = array_map(fn ($i) => str_pad((string) $i, 11, 'a'), range(1, 51));

    expect(fn () => $this->service->fetchVideos($ids))
        ->toThrow(YouTubeApiException::class, 'at most 50');
});