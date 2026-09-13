<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use App\Services\Watch\WatchProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __construct(
        private readonly WatchProgressService $progress,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Video::query()
            ->visibleToUsers()
            ->with('category:id,name');

        if ($term = trim((string) $request->input('q'))) {
            $query->search($term);
        }

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($channel = trim((string) $request->input('channel'))) {
            $query->where('channel_name', 'like', '%'.$channel.'%');
        }

        match ($request->input('sort')) {
            'popular' => $query->orderByDesc('views_count')->orderByDesc('published_at'),
            'oldest'  => $query->orderBy('published_at'),
            default   => $query->orderByDesc('published_at'),
        };

        return view('pages.videos.index', [
            'videos'     => $query->paginate(24)->withQueryString(),
            'categories' => Category::query()->active()->ordered()->get(['id', 'name']),
            'filters'    => [
                'q'        => $request->input('q', ''),
                'category' => $request->input('category', ''),
                'channel'  => $request->input('channel', ''),
                'sort'     => $request->input('sort', 'recent'),
            ],
        ]);
    }

    public function show(Request $request, Video $video): View
{
    abort_unless($video->isPublished() && $video->visibility === Video::VISIBILITY_PUBLIC, 404);

    $video->load('category:id,name,slug');   // <-- added :slug

    $user = $request->user();
    $progress = $user ? $this->progress->for($user, $video) : null;

    $related = Video::query()
        ->visibleToUsers()
        ->with('category:id,name,slug')      // <-- added :slug
        ->when($video->category_id, fn ($q) => $q->where('category_id', $video->category_id))
        ->where('id', '!=', $video->id)
        ->orderByDesc('published_at')
        ->limit(6)
        ->get();

    return view('pages.videos.show', [
        'video'    => $video,
        'progress' => $progress,
        'related'  => $related,
        'resumeAt' => $progress && ! $progress->completed
            ? (int) $progress->current_position_seconds
            : 0,
    ]);
}
}