<?php

namespace App\Services\Watch;

use App\Models\User;
use App\Models\Video;
use App\Models\WatchHistory;
use Illuminate\Support\Collection;

class WatchHistoryService
{
    /**
     * Append a watch event. Called once per player open (Step 10).
     * Deliberately append-only: one row per open.
     */
    public function record(User $user, Video $video): WatchHistory
    {
        return WatchHistory::create([
            'user_id'    => $user->id,
            'video_id'   => $video->id,
            'watched_at' => now(),
        ]);
    }

    /**
     * Group recent history by day, for the /history page.
     * Structure: [ 'Today' => Collection, 'Yesterday' => Collection, ... ]
     */
    public function groupedFor(User $user, int $days = 30): Collection
    {
        return WatchHistory::query()
            ->with(['video' => fn ($q) => $q->visibleToUsers()->with('category:id,name')])
            ->where('user_id', $user->id)
            ->where('watched_at', '>=', now()->subDays($days))
            ->whereHas('video', fn ($q) => $q->visibleToUsers())
            ->orderByDesc('watched_at')
            ->limit(500)
            ->get()
            ->groupBy(function (WatchHistory $row) {
                if ($row->watched_at->isToday())     return 'Today';
                if ($row->watched_at->isYesterday()) return 'Yesterday';
                return $row->watched_at->format('F j, Y');
            });
    }

    public function removeEntry(User $user, int $historyId): bool
    {
        return (bool) WatchHistory::query()
            ->where('user_id', $user->id)
            ->where('id', $historyId)
            ->delete();
    }

    public function clear(User $user): int
    {
        return WatchHistory::query()->where('user_id', $user->id)->delete();
    }
}