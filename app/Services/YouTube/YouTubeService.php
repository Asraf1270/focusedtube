<?php

namespace App\Services\YouTube;

use App\Exceptions\YouTube\YouTubeApiException;
use App\Exceptions\YouTube\YouTubeQuotaExceededException;
use App\Exceptions\YouTube\YouTubeVideoNotFoundException;
use App\Support\YouTube\YouTubeUrlParser;
use App\Support\YouTube\YouTubeVideoData;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $baseUrl = null,
        private readonly int $timeout = 8,
        private readonly int $retries = 1,
    ) {
    }

    /**
     * Fetch a single video's metadata by ID.
     *
     * @throws YouTubeApiException
     * @throws YouTubeVideoNotFoundException
     * @throws YouTubeQuotaExceededException
     */
    public function fetchVideo(string $videoId): YouTubeVideoData
    {
        $results = $this->fetchVideos([$videoId]);

        if (empty($results)) {
            throw YouTubeVideoNotFoundException::forId($videoId);
        }

        return reset($results);
    }

    /**
     * Fetch metadata for up to 50 videos in a single API call.
     *
     * @param  string[]  $videoIds
     * @return array<string, YouTubeVideoData>  Keyed by video ID.
     */
    public function fetchVideos(array $videoIds): array
    {
        $videoIds = array_values(array_unique(array_filter($videoIds)));

        if (empty($videoIds)) {
            return [];
        }

        if (count($videoIds) > 50) {
            throw YouTubeApiException::requestFailed('YouTube allows at most 50 IDs per request.');
        }

        $response = $this->call('/videos', [
            'part' => 'snippet,contentDetails,statistics',
            'id'   => implode(',', $videoIds),
            'maxResults' => 50,
        ]);

        $items = $response->json('items');

        if (! is_array($items)) {
            throw YouTubeApiException::invalidResponse('Missing "items" in response.');
        }

        $out = [];
        foreach ($items as $item) {
            $data = $this->mapItem($item);
            if ($data !== null) {
                $out[$data->videoId] = $data;
            }
        }

        return $out;
    }

    /**
     * Fetch metadata from a full YouTube URL (any supported format).
     *
     * @throws YouTubeApiException
     */
    public function fetchByUrl(string $url): YouTubeVideoData
    {
        $id = YouTubeUrlParser::extract($url);

        if ($id === null) {
            throw YouTubeApiException::requestFailed('Invalid YouTube URL.');
        }

        return $this->fetchVideo($id);
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                         */
    /* ------------------------------------------------------------------ */

    private function call(string $path, array $query): Response
    {
        $key = $this->apiKey ?? config('services.youtube.key');

        if (empty($key)) {
            throw YouTubeApiException::missingApiKey();
        }

        $url = rtrim($this->baseUrl ?? config('services.youtube.base_url'), '/').$path;

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retries, 250, throw: false)
                ->acceptJson()
                ->get($url, array_merge($query, ['key' => $key]));
        } catch (ConnectionException $e) {
            Log::warning('YouTube API connection failed', ['message' => $e->getMessage()]);
            throw YouTubeApiException::requestFailed('Network error.');
        }

        $this->assertSuccess($response);

        return $response;
    }

    private function assertSuccess(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $reason = $response->json('error.errors.0.reason')
            ?? $response->json('error.status')
            ?? ('HTTP '.$response->status());

        if (in_array($reason, ['quotaExceeded', 'dailyLimitExceeded', 'rateLimitExceeded'], true)) {
            throw YouTubeQuotaExceededException::make();
        }

        if (in_array($reason, ['videoNotFound', 'notFound'], true)) {
            throw YouTubeVideoNotFoundException::forId('unknown');
        }

        if (in_array($reason, ['keyInvalid', 'ipRefererBlocked', 'accessNotConfigured'], true)) {
            throw YouTubeApiException::requestFailed("API key problem ({$reason}).");
        }

        Log::warning('YouTube API error response', [
            'status' => $response->status(),
            'body'   => mb_substr((string) $response->body(), 0, 500),
        ]);

        throw YouTubeApiException::requestFailed((string) $reason);
    }

    /**
     * Convert one API item into a YouTubeVideoData DTO.
     * Returns null if the item is unusable.
     */
    private function mapItem(array $item): ?YouTubeVideoData
    {
        $videoId = $item['id'] ?? null;
        if (! is_string($videoId) || $videoId === '') {
            return null;
        }

        $snippet  = $item['snippet'] ?? [];
        $details  = $item['contentDetails'] ?? [];

        $title = trim((string) ($snippet['title'] ?? ''));
        if ($title === '') {
            // Deleted or private videos come back with an empty title.
            return null;
        }

        $thumbnail = $this->pickThumbnail($snippet['thumbnails'] ?? []);
        $duration  = self::parseIso8601Duration($details['duration'] ?? '');
        $published = ! empty($snippet['publishedAt'])
            ? CarbonImmutable::parse($snippet['publishedAt'])
            : null;

        return new YouTubeVideoData(
            videoId: $videoId,
            title: $title,
            description: isset($snippet['description']) ? (string) $snippet['description'] : null,
            thumbnailUrl: $thumbnail,
            channelId: isset($snippet['channelId']) ? (string) $snippet['channelId'] : null,
            channelName: isset($snippet['channelTitle']) ? (string) $snippet['channelTitle'] : null,
            durationSeconds: $duration,
            publishedAt: $published,
        );
    }

    /**
     * Pick the best available thumbnail URL from YouTube's thumbnail map.
     */
    private function pickThumbnail(array $thumbnails): ?string
    {
        foreach (['maxres', 'standard', 'high', 'medium', 'default'] as $key) {
            if (! empty($thumbnails[$key]['url'])) {
                return (string) $thumbnails[$key]['url'];
            }
        }

        return null;
    }

    /**
     * Parse an ISO 8601 duration like PT1H2M10S into seconds.
     */
    public static function parseIso8601Duration(string $iso): int
    {
        if ($iso === '' || ! preg_match('/^P(?:\d+D)?(?:T(?:\d+H)?(?:\d+M)?(?:\d+S)?)?$/', $iso)) {
            return 0;
        }

        try {
            $interval = new \DateInterval($iso);
        } catch (\Exception) {
            return 0;
        }

        $days    = $interval->d;
        $hours   = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;

        return ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}