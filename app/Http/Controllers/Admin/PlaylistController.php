<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlaylistRequest;
use App\Http\Requests\Admin\UpdatePlaylistRequest;
use App\Models\Playlist;
use App\Models\Video;
use App\Services\Playlist\PlaylistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    public function __construct(private readonly PlaylistService $playlists)
    {
    }

    public function index(Request $request): View
    {
        $query = Playlist::query()->withCount('videos')->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($status = $request->input('status')) {
            if (in_array($status, [
                Playlist::STATUS_DRAFT,
                Playlist::STATUS_PUBLISHED,
                Playlist::STATUS_ARCHIVED,
            ], true)) {
                $query->where('status', $status);
            }
        }

        return view('admin.playlists.index', [
            'playlists' => $query->paginate(20)->withQueryString(),
            'filters'   => [
                'q'      => $request->input('q', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.playlists.create', [
            'playlist' => new Playlist(['status' => Playlist::STATUS_DRAFT]),
        ]);
    }

    public function store(StorePlaylistRequest $request): RedirectResponse
    {
        $playlist = $this->playlists->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.playlists.edit', $playlist)
            ->with('status', "Playlist \"{$playlist->title}\" created. Add some videos to it.");
    }

    public function edit(Playlist $playlist): View
    {
        $this->authorize('update', $playlist);

        // For the "add video" typeahead.
        $candidateVideos = Video::query()
            ->whereNotIn('id', $playlist->videos()->pluck('videos.id'))
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'title', 'youtube_video_id', 'thumbnail_url']);

        return view('admin.playlists.edit', [
            'playlist'       => $playlist->load(['videos.category:id,name']),
            'candidateVideos' => $candidateVideos,
        ]);
    }

    public function update(UpdatePlaylistRequest $request, Playlist $playlist): RedirectResponse
    {
        $this->playlists->update($playlist, $request->validated(), $request->user());

        return redirect()
            ->route('admin.playlists.edit', $playlist)
            ->with('status', "Playlist \"{$playlist->title}\" updated.");
    }

    public function destroy(Playlist $playlist, Request $request): RedirectResponse
    {
        $this->authorize('delete', $playlist);

        $title = $playlist->title;

        $this->playlists->delete($playlist, $request->user());

        return redirect()
            ->route('admin.playlists.index')
            ->with('status', "Playlist \"{$title}\" deleted.");
    }
}