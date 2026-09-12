<?php

use App\Support\YouTube\YouTubeUrlParser;

it('extracts id from standard watch URL', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube.com/watch?v=dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from watch URL with extra params', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s&list=ABC'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from m.youtube.com', function () {
    expect(YouTubeUrlParser::extract('https://m.youtube.com/watch?v=dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from youtu.be short link', function () {
    expect(YouTubeUrlParser::extract('https://youtu.be/dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from youtu.be with query params', function () {
    expect(YouTubeUrlParser::extract('https://youtu.be/dQw4w9WgXcQ?t=42'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from shorts', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube.com/shorts/dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from embed', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube.com/embed/dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from youtube-nocookie embed', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('extracts id from live URL', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube.com/live/dQw4w9WgXcQ'))
        ->toBe('dQw4w9WgXcQ');
});

it('accepts a raw 11-char id', function () {
    expect(YouTubeUrlParser::extract('dQw4w9WgXcQ'))->toBe('dQw4w9WgXcQ');
});

it('rejects non-youtube domains', function () {
    expect(YouTubeUrlParser::extract('https://vimeo.com/123456'))->toBeNull();
    expect(YouTubeUrlParser::extract('https://example.com/watch?v=dQw4w9WgXcQ'))->toBeNull();
});

it('rejects malformed ids', function () {
    expect(YouTubeUrlParser::extract('https://www.youtube.com/watch?v=short'))->toBeNull();
    expect(YouTubeUrlParser::extract('https://www.youtube.com/watch?v=has spaces'))->toBeNull();
    expect(YouTubeUrlParser::extract('https://www.youtube.com/watch?v='))->toBeNull();
});

it('rejects empty input', function () {
    expect(YouTubeUrlParser::extract(''))->toBeNull();
    expect(YouTubeUrlParser::extract(null))->toBeNull();
});