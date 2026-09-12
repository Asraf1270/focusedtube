<?php

namespace App\Support\YouTube;

use Carbon\CarbonImmutable;

/**
 * Immutable typed representation of a YouTube video's metadata.
 * This is the ONLY shape the rest of the app sees from YouTubeService.
 */
final readonly class YouTubeVideoData
{
    public function __construct(
        public string $videoId,
        public string $title,
        public ?string $description,
        public ?string $thumbnailUrl,
        public ?string $channelId,
        public ?string $channelName,
        public int $durationSeconds,
        public ?CarbonImmutable $publishedAt,
    ) {
    }

    /**
     * Convert to the attribute array our videos table expects.
     * Does NOT include status/publishing flags — those are admin choices.
     */
    public function toVideoAttributes(): array
    {
        return [
            'youtube_video_id'     => $this->videoId,
            'title'                => $this->title,
            'description'          => $this->description,
            'thumbnail_url'        => $this->thumbnailUrl,
            'channel_id'           => $this->channelId,
            'channel_name'         => $this->channelName,
            'duration_seconds'     => $this->durationSeconds,
            'youtube_published_at' => $this->publishedAt,
        ];
    }
}