<?php

namespace App\Exceptions\YouTube;

class YouTubeVideoNotFoundException extends YouTubeException
{
    public static function forId(string $id): self
    {
        return new self("YouTube video [{$id}] was not found or is not publicly available.");
    }
}