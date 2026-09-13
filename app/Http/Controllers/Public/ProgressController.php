<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\Watch\WatchProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct(
        private readonly WatchProgressService $progress,
    ) {
    }

    public function __invoke(Request $request, Video $video): JsonResponse
    {
        abort_unless(
            $video->isPublished() && $video->visibility === Video::VISIBILITY_PUBLIC,
            404
        );

        $validated = $request->validate([
            'position' => ['required', 'integer', 'min:0', 'max:86400'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ]);

        $row = $this->progress->update(
            user: $request->user(),
            video: $video,
            positionSeconds: (int) $validated['position'],
            durationSeconds: isset($validated['duration']) ? (int) $validated['duration'] : null,
        );

        return response()->json([
            'ok'         => true,
            'position'   => $row->current_position_seconds,
            'percent'    => $row->progress_percentage,
            'completed'  => $row->completed,
        ]);
    }
}