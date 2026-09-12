<?php

namespace App\Console\Commands;

use App\Exceptions\YouTube\YouTubeApiException;
use App\Exceptions\YouTube\YouTubeQuotaExceededException;
use App\Exceptions\YouTube\YouTubeVideoNotFoundException;
use App\Services\YouTube\YouTubeService;
use App\Support\YouTube\YouTubeUrlParser;
use Illuminate\Console\Command;

class YouTubeCheckCommand extends Command
{
    protected $signature = 'youtube:check {url : A YouTube URL or 11-character video ID}';

    protected $description = 'Fetch metadata for a YouTube URL/ID to verify API wiring. Does not write to the database.';

    public function handle(YouTubeService $service): int
    {
        $input = (string) $this->argument('url');
        $id    = YouTubeUrlParser::extract($input);

        if ($id === null) {
            $this->error('Could not extract a video ID from that input.');
            return self::FAILURE;
        }

        $this->info("Video ID: {$id}");

        try {
            $data = $service->fetchVideo($id);
        } catch (YouTubeVideoNotFoundException) {
            $this->error('Video not found or not publicly available.');
            return self::FAILURE;
        } catch (YouTubeQuotaExceededException) {
            $this->error('YouTube API quota exceeded.');
            return self::FAILURE;
        } catch (YouTubeApiException $e) {
            $this->error('YouTube API error: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['Video ID',      $data->videoId],
                ['Title',         $data->title],
                ['Channel',       $data->channelName ?? '—'],
                ['Channel ID',    $data->channelId ?? '—'],
                ['Duration (s)',  $data->durationSeconds],
                ['Duration (h:m:s)', gmdate('H:i:s', $data->durationSeconds)],
                ['Published at',  $data->publishedAt?->toDateTimeString() ?? '—'],
                ['Thumbnail',     $data->thumbnailUrl ?? '—'],
                ['Description',   \Illuminate\Support\Str::limit((string) $data->description, 120)],
            ]
        );

        return self::SUCCESS;
    }
}