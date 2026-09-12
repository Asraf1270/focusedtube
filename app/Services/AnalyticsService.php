<?php

namespace App\Services;

use App\Models\User;
use App\Models\Video;
use App\Models\WatchProgress;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Dashboard overview — cheap counters, cached briefly.
     */
    public function dashboardOverview(): array
    {
        return Cache::remember('admin.dashboard.overview', now()->addMinutes(5), function () {
            $publishedCount = Video::query()->where('status', Video::STATUS_PUBLISHED)->count();
            $draftCount     = Video::query()->where('status', Video::STATUS_DRAFT)->count();

            $totalViews = (int) Video::query()->sum('views_count');
            $totalCompleted = (int) WatchProgress::query()->where('completed', true)->count();

            // Watch time is an approximation: sum of durations for completed videos.
            // (Real per-second tracking arrives in Step 15.)
            $totalWatchSeconds = (int) WatchProgress::query()->sum('current_position_seconds');

            return [
                'total_users'      => User::query()->count(),
                'active_users'     => User::query()->where('status', User::STATUS_ACTIVE)->count(),
                'total_videos'     => Video::query()->count(),
                'published_videos' => $publishedCount,
                'draft_videos'     => $draftCount,
                'total_views'      => $totalViews,
                'total_completed'  => $totalCompleted,
                'total_watch_time' => $totalWatchSeconds,
            ];
        });
    }

    public function forgetDashboardCache(): void
    {
        Cache::forget('admin.dashboard.overview');
    }
}