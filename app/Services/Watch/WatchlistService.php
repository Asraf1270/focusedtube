<?php

namespace App\Services\Watch;

use App\Models\User;
use App\Models\Video;
use App\Models\Watchlist;

class WatchlistService
{
    public function isSaved(User $user, Video $video): bool
    {
        return Watchlist::query()
            ->where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->exists();
    }

    /**
     * Idempotent: saving an already-saved video is a no-op.
     */
    public function save(User $user, Video $video): Watchlist
    {
        return Watchlist::firstOrCreate([
            'user_id'  => $user->id,
            'video_id' => $video->id,
        ]);
    }

    public function remove(User $user, Video $video): bool
    {
        return (bool) Watchlist::query()
            ->where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->delete();
    }

    /**
     * Paginated watchlist for /watchlist — most recently saved first.
     */
    public function paginateFor(User $user, int $perPage = 24)
    {
        return $user->watchlist()
            ->with(['video' => fn ($q) => $q->visibleToUsers()->with('category:id,name')])
            ->whereHas('video', fn ($q) => $q->visibleToUsers())
            ->latest('id')
            ->paginate($perPage);
    }
}