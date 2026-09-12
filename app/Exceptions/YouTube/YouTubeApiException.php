<?php

namespace App\Exceptions\YouTube;

class YouTubeApiException extends YouTubeException
{
    public static function requestFailed(string $reason): self
    {
        return new self("YouTube API request failed: {$reason}");
    }

    public static function invalidResponse(string $reason): self
    {
        return new self("YouTube API returned an unexpected response: {$reason}");
    }

    public static function missingApiKey(): self
    {
        return new self('YOUTUBE_API_KEY is not configured.');
    }
}