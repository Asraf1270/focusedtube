<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\Watch\WatchlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function __construct(private readonly WatchlistService $watchlist)
    {
    }

    public function index(Request $request): View
    {
        return view('pages.watchlist', [
            'items' => $this->watchlist->paginateFor($request->user()),
        ]);
    }

    public function store(Request $request, Video $video): RedirectResponse
    {
        abort_unless($video->isPublished() && $video->visibility === Video::VISIBILITY_PUBLIC, 404);

        $this->watchlist->save($request->user(), $video);

        if ($request->expectsJson()) {
            return response()->json(['saved' => true]);
        }

        return back()->with('status', 'Saved to your watchlist.');
    }

    public function destroy(Request $request, Video $video): RedirectResponse
    {
        $this->watchlist->remove($request->user(), $video);

        if ($request->expectsJson()) {
            return response()->json(['saved' => false]);
        }

        return back()->with('status', 'Removed from your watchlist.');
    }
}