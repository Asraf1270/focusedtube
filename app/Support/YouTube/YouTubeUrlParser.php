<?php

namespace App\Support\YouTube;

/**
 * Parses common YouTube URL formats into a canonical 11-character video ID.
 *
 * Supported inputs:
 *   - https://www.youtube.com/watch?v=VIDEO_ID
 *   - https://youtube.com/watch?v=VIDEO_ID&t=30s
 *   - https://m.youtube.com/watch?v=VIDEO_ID
 *   - https://youtu.be/VIDEO_ID
 *   - https://youtu.be/VIDEO_ID?t=30
 *   - https://www.youtube.com/shorts/VIDEO_ID
 *   - https://www.youtube.com/embed/VIDEO_ID
 *   - https://www.youtube.com/v/VIDEO_ID
 *   - https://www.youtube-nocookie.com/embed/VIDEO_ID
 *   - Raw 11-char ID
 */
class YouTubeUrlParser
{
    /** YouTube video IDs are 11 chars: [A-Za-z0-9_-] */
    public const ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/';

    /**
     * @return string|null The extracted video ID, or null if the input is not a valid YouTube URL/ID.
     */
    public static function extract(?string $input): ?string
    {
        if (! is_string($input)) {
            return null;
        }

        $input = trim($input);

        if ($input === '') {
            return null;
        }

        // Raw 11-char ID.
        if (preg_match(self::ID_PATTERN, $input)) {
            return $input;
        }

        // Normalize missing scheme.
        if (! preg_match('#^https?://#i', $input)) {
            $input = 'https://'.$input;
        }

        $parts = parse_url($input);

        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);

        // Strip a leading www. / m. for easier matching.
        $host = preg_replace('/^(www|m)\./', '', $host);

        $allowedHosts = [
            'youtube.com',
            'youtube-nocookie.com',
            'youtu.be',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            return null;
        }

        // youtu.be/<id>
        if ($host === 'youtu.be') {
            $path = ltrim($parts['path'] ?? '', '/');
            $candidate = explode('/', $path)[0] ?? '';

            return self::validate($candidate);
        }

        // youtube.com/watch?v=<id>
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            if (! empty($query['v']) && is_string($query['v'])) {
                return self::validate($query['v']);
            }
        }

        // youtube.com/(shorts|embed|v|live)/<id>
        $path = trim($parts['path'] ?? '', '/');
        if ($path !== '') {
            $segments = explode('/', $path);

            if (count($segments) >= 2 && in_array($segments[0], ['shorts', 'embed', 'v', 'live'], true)) {
                return self::validate($segments[1]);
            }
        }

        return null;
    }

    public static function isValid(?string $input): bool
    {
        return self::extract($input) !== null;
    }

    private static function validate(string $candidate): ?string
    {
        // Strip any query fragment like "VIDEO_ID?si=..."
        $candidate = explode('?', $candidate)[0];
        $candidate = explode('#', $candidate)[0];

        return preg_match(self::ID_PATTERN, $candidate) ? $candidate : null;
    }
}