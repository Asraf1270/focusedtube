<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\Watch\WatchHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StartedController extends Controller
{
    public function __construct(
        private readonly WatchHistoryService $history,
    ) {
    }

    public function __invoke(Request $request, Video $video): JsonResponse
    {
        abort_unless(
            $video->isPublished() && $video->visibility === Video::VISIBILITY_PUBLIC,
            404
        );

        $user = $request->user();

        // History row: one per open. Only for authenticated users —
        // guests are not tracked (privacy + no user_id).
        if ($user) {
            $this->history->record($user, $video);
        }

        // View count: only count once per user/video/day. Guests are not
        // counted at all (bot-resistant by default, and honest).
        $counted = false;
        if ($user) {
            $key = "video-viewed:{$user->id}:{$video->id}:".now()->toDateString();
            $counted = Cache::add($key, 1, now()->addDay());

            if ($counted) {
                DB::table('videos')
                    ->where('id', $video->id)
                    ->increment('views_count');
            }
        }

        return response()->json([
            'ok'      => true,
            'counted' => $counted,
        ]);
    }
}