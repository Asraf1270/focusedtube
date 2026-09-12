<?php

namespace App\Exceptions\YouTube;

class YouTubeQuotaExceededException extends YouTubeException
{
    public static function make(): self
    {
        return new self('YouTube API quota exceeded. Try again later or check your Google Cloud console.');
    }
}