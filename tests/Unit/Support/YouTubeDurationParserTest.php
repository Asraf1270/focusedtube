<?php

use App\Services\YouTube\YouTubeService;

it('parses iso8601 durations to seconds', function () {
    expect(YouTubeService::parseIso8601Duration('PT1H2M10S'))->toBe(3730);
    expect(YouTubeService::parseIso8601Duration('PT15M33S'))->toBe(933);
    expect(YouTubeService::parseIso8601Duration('PT45S'))->toBe(45);
    expect(YouTubeService::parseIso8601Duration('P1DT1H'))->toBe(90000);
});

it('returns zero for invalid durations', function () {
    expect(YouTubeService::parseIso8601Duration(''))->toBe(0);
    expect(YouTubeService::parseIso8601Duration('not-a-duration'))->toBe(0);
    expect(YouTubeService::parseIso8601Duration('PXYZ'))->toBe(0);
});