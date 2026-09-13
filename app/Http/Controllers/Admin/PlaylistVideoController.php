<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderPlaylistRequest;
use App\Models\Playlist;
use App\Models\Video;
use App\Services\Playlist\PlaylistVideoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaylistVideoController extends Controller
{
    public function __construct(private readonly PlaylistVideoService $videos)
    {
    }

    public function attach(Request $request, Playlist $playlist): RedirectResponse
    {
        $this->authorize('manageVideos', $playlist);

        $validated = $request->validate([
            'video_id' => [
                'required', 'integer',
                Rule::exists('videos', 'id'),
            ],
        ]);

        $video = Video::findOrFail($validated['video_id']);

        $this->videos->attach($playlist, $video);

        return redirect()
            ->route('admin.playlists.edit', $playlist)
            ->with('status', "\"{$video->title}\" added to the playlist.");
    }

    public function detach(Request $request, Playlist $playlist, Video $video): RedirectResponse
    {
        $this->authorize('manageVideos', $playlist);

        $this->videos->detach($playlist, $video);

        return redirect()
            ->route('admin.playlists.edit', $playlist)
            ->with('status', "\"{$video->title}\" removed from the playlist.");
    }

    public function reorder(ReorderPlaylistRequest $request, Playlist $playlist): RedirectResponse
    {
        try {
            $this->videos->reorder($playlist, $request->validated('ids'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Playlist order saved.');
    }
}