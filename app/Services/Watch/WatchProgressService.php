<?php

namespace App\Services\Watch;

use App\Models\User;
use App\Models\Video;
use App\Models\WatchProgress;
use Illuminate\Support\Collection;

class WatchProgressService
{
    public const COMPLETION_THRESHOLD = 95;

    public function for(User $user, Video $video): ?WatchProgress
    {
        return WatchProgress::query()
            ->where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();
    }

    public function update(
        User $user,
        Video $video,
        int $positionSeconds,
        ?int $durationSeconds = null,
    ): WatchProgress {
        $duration = $durationSeconds ?: $video->duration_seconds;
        $position = max(0, $positionSeconds);
        if ($duration > 0) {
            $position = min($position, $duration);
        }

        $percent = $duration > 0
            ? (int) round(($position / $duration) * 100)
            : 0;

        $completed = $percent >= self::COMPLETION_THRESHOLD;

        $progress = WatchProgress::query()->firstOrNew([
            'user_id'  => $user->id,
            'video_id' => $video->id,
        ]);

        $progress->fill([
            'current_position_seconds' => $position,
            'duration_seconds'         => $duration,
            'progress_percentage'      => $percent,
            'last_watched_at'          => now(),
            'completed'                => $completed,
        ]);

        if (! $progress->exists) {
            $progress->started_at = now();
        }

        if ($completed && $progress->completed_at === null) {
            $progress->completed_at = now();
        }

        $progress->save();

        return $progress;
    }

    /**
     * @return Collection<int, WatchProgress>
     */
    public function continueWatchingFor(User $user, int $limit = 6): Collection
    {
        return WatchProgress::query()
            ->with(['video' => fn ($q) => $q->visibleToUsers()->with('category:id,name')])
            ->where('user_id', $user->id)
            ->where('completed', false)
            ->where('progress_percentage', '>', 0)
            ->whereHas('video', fn ($q) => $q->visibleToUsers())
            ->orderByDesc('last_watched_at')
            ->limit($limit)
            ->get();
    }
}