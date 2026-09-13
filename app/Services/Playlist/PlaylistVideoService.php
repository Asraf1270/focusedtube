<?php

namespace App\Services\Playlist;

use App\Models\Playlist;
use App\Models\Video;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlaylistVideoService
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {
    }

    /**
     * Attach a video at the end of the playlist.
     * Idempotent: attaching an already-attached video is a no-op.
     */
    public function attach(Playlist $playlist, Video $video): void
    {
        if ($playlist->videos()->where('video_id', $video->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($playlist, $video) {
            $next = (int) $playlist->videos()->max('position') + 1;

            $playlist->videos()->attach($video->id, ['position' => $next]);
        });

        $this->audit->record(
            action: 'playlist.video_attached',
            description: "Added \"{$video->title}\" to playlist \"{$playlist->title}\"",
            subject: $playlist,
        );
    }

    public function detach(Playlist $playlist, Video $video): void
    {
        DB::transaction(fn () => $playlist->videos()->detach($video->id));

        $this->audit->record(
            action: 'playlist.video_detached',
            description: "Removed \"{$video->title}\" from playlist \"{$playlist->title}\"",
            subject: $playlist,
        );
    }

    /**
     * Reorder the whole playlist in one shot.
     * @param  int[]  $orderedVideoIds  Array of video IDs in the desired order.
     */
    public function reorder(Playlist $playlist, array $orderedVideoIds): void
    {
        $attachedIds = $playlist->videos()->pluck('videos.id')->all();

        $providedIds = array_values(array_map('intval', $orderedVideoIds));

        // Every provided ID must belong to this playlist.
        foreach ($providedIds as $id) {
            if (! in_array($id, $attachedIds, true)) {
                throw new RuntimeException("Video {$id} is not in this playlist.");
            }
        }

        DB::transaction(function () use ($playlist, $providedIds) {
            foreach ($providedIds as $position => $videoId) {
                $playlist->videos()->updateExistingPivot($videoId, [
                    'position' => $position,
                ]);
            }
        });

        $this->audit->record(
            action: 'playlist.reordered',
            description: "Reordered playlist \"{$playlist->title}\"",
            subject: $playlist,
        );
    }
}