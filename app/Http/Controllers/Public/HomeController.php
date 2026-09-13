<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use App\Services\Watch\WatchProgressService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly WatchProgressService $progress,
    ) {
    }

    public function __invoke(): View
    {
        $user = auth()->user();

        $continueWatching = $user
            ? $this->progress->continueWatchingFor($user, 6)
            : collect();

        $featured = Video::query()
            ->visibleToUsers()
            ->featured()
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $dailyFocus = Video::query()
            ->visibleToUsers()
            ->dailyFocus()
            ->orderByDesc('published_at')
            ->first();

        $recent = Video::query()
            ->visibleToUsers()
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        $popular = Video::query()
            ->visibleToUsers()
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        $categories = Category::query()
            ->active()
            ->ordered()
            ->withCount(['videos' => fn ($q) => $q->visibleToUsers()])
            ->limit(8)
            ->get();

        return view('pages.home', compact(
            'featured',
            'dailyFocus',
            'recent',
            'popular',
            'categories',
            'continueWatching',
        ));
    }
}