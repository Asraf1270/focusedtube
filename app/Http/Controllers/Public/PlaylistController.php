<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    public function index(): View
    {
        $playlists = Playlist::query()
            ->published()
            ->withCount(['videos' => fn ($q) => $q->visibleToUsers()])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('pages.playlists.index', [
            'playlists' => $playlists,
        ]);
    }

    public function show(Request $request, Playlist $playlist): View
    {
        abort_unless($playlist->status === Playlist::STATUS_PUBLISHED, 404);

        // Only published videos in the playlist are visible to users.
        $videos = $playlist->videos()
            ->visibleToUsers()
            ->orderByPivot('position')
            ->get();

        return view('pages.playlists.show', [
            'playlist' => $playlist,
            'videos'   => $videos,
            'firstVideo' => $videos->first(),
        ]);
    }
}