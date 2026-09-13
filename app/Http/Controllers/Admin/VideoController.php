<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\YouTube\YouTubeApiException;
use App\Exceptions\YouTube\YouTubeQuotaExceededException;
use App\Exceptions\YouTube\YouTubeVideoNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkVideoActionRequest;
use App\Http\Requests\Admin\FetchVideoRequest;
use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use App\Models\Category;
use App\Models\Video;
use App\Services\Video\VideoService;
use App\Services\YouTube\YouTubeService;
use App\Support\YouTube\YouTubeUrlParser;
use App\Support\YouTube\YouTubeVideoData;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    private const SESSION_KEY = 'admin.pending_video';

    public function __construct(
        private readonly YouTubeService $youtube,
        private readonly VideoService $videos,
    ) {
    }

    /* ==================================================================== */
    /*  Index                                                               */
    /* ==================================================================== */

    public function index(Request $request): View
    {
        $query = Video::query()
            ->with(['category:id,name', 'creator:id,name'])
            ->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            if (in_array($status, [Video::STATUS_DRAFT, Video::STATUS_PUBLISHED, Video::STATUS_ARCHIVED], true)) {
                $query->where('status', $status);
            }
        }

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        $videos = $query->paginate(20)->withQueryString();

        return view('admin.videos.index', [
            'videos'     => $videos,
            'categories' => Category::query()->ordered()->get(['id', 'name']),
            'filters'    => [
                'q'        => $request->input('q', ''),
                'status'   => $request->input('status', ''),
                'category' => $request->input('category', ''),
            ],
        ]);
    }

    /* ==================================================================== */
    /*  Add flow (Step 6)                                                   */
    /* ==================================================================== */

    public function create(Request $request): View
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        return view('admin.videos.create', [
            'pending' => $pending ? $this->dtoFromSession($pending) : null,
        ]);
    }

    public function fetch(FetchVideoRequest $request): RedirectResponse
    {
        $url = (string) $request->validated('url');
        $id  = YouTubeUrlParser::extract($url);

        if ($id === null) {
            return back()->withErrors(['url' => 'Invalid YouTube URL.'])->withInput();
        }

        if ($existing = $this->videos->findByYouTubeId($id)) {
            return redirect()
                ->route('admin.videos.index')
                ->with('error', "That video is already in FocusedTube: \"{$existing->title}\".");
        }

        try {
            $data = $this->youtube->fetchVideo($id);
        } catch (YouTubeVideoNotFoundException) {
            return back()->withErrors(['url' => 'This video was not found or is not publicly available.'])->withInput();
        } catch (YouTubeQuotaExceededException) {
            return back()->withErrors(['url' => 'YouTube API quota exceeded. Try again later.'])->withInput();
        } catch (YouTubeApiException $e) {
            return back()->withErrors(['url' => 'Could not reach YouTube: '.$e->getMessage()])->withInput();
        }

        $request->session()->put(self::SESSION_KEY, $this->dtoToSession($data));

        return redirect()->route('admin.videos.review');
    }

    public function review(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('admin.videos.create')
                ->with('error', 'No video is pending review. Start by pasting a YouTube URL.');
        }

        return view('admin.videos.review', [
            'data'       => $this->dtoFromSession($pending),
            'categories' => Category::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('admin.videos.create')
                ->with('error', 'Your session expired. Please paste the URL again.');
        }

        $sessionData = $this->dtoFromSession($pending);
        $formData    = $request->validated();

        if ($formData['youtube_video_id'] !== $sessionData->videoId) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('admin.videos.create')
                ->with('error', 'The pending video does not match. Please start over.');
        }

        if ($this->videos->findByYouTubeId($sessionData->videoId)) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('admin.videos.index')
                ->with('error', 'Someone just added this video. Nothing was created.');
        }

        try {
            $video = $this->videos->createFromYouTube(
                data: $sessionData,
                options: [
                    'category_id'    => $formData['category_id'] ?? null,
                    'status'         => $formData['status'],
                    'visibility'     => $formData['visibility'],
                    'is_featured'    => $formData['is_featured'] ?? false,
                    'is_daily_focus' => $formData['is_daily_focus'] ?? false,
                    'display_order'  => $formData['display_order'] ?? 0,
                ],
                admin: $request->user(),
            );
        } catch (\RuntimeException $e) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('admin.videos.index')
                ->with('error', $e->getMessage());
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('admin.videos.index')
            ->with('status', "Video \"{$video->title}\" was saved as {$video->status}.");
    }

    public function cancelFetch(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('admin.videos.create')
            ->with('status', 'Pending video discarded.');
    }

    /* ==================================================================== */
    /*  Edit / update                                                       */
    /* ==================================================================== */

    public function edit(Video $video): View
    {
        $this->authorize('update', $video);

        return view('admin.videos.edit', [
            'video'      => $video->load(['category:id,name', 'creator:id,name']),
            'categories' => Category::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function update(UpdateVideoRequest $request, Video $video): RedirectResponse
    {
        $this->videos->update($video, $request->validated(), $request->user());

        return redirect()
            ->route('admin.videos.edit', $video)
            ->with('status', "Video \"{$video->title}\" updated.");
    }

    /* ==================================================================== */
    /*  Lifecycle                                                           */
    /* ==================================================================== */

    public function publish(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('publish', $video);

        $this->videos->publish($video, $request->user());

        return back()->with('status', "Video \"{$video->title}\" published.");
    }

    public function unpublish(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('publish', $video);

        $this->videos->unpublish($video, $request->user());

        return back()->with('status', "Video \"{$video->title}\" moved to drafts.");
    }

    public function archive(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('archive', $video);

        $this->videos->archive($video, $request->user());

        return back()->with('status', "Video \"{$video->title}\" archived.");
    }

    public function restore(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('archive', $video);

        $this->videos->restore($video, $request->user());

        return back()->with('status', "Video \"{$video->title}\" restored to drafts.");
    }

    public function destroy(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('delete', $video);

        $title = $video->title;

        $this->videos->delete($video, $request->user());

        return redirect()
            ->route('admin.videos.index')
            ->with('status', "Video \"{$title}\" deleted.");
    }

    /* ==================================================================== */
    /*  Bulk                                                                */
    /* ==================================================================== */

    public function bulk(BulkVideoActionRequest $request): RedirectResponse
    {
        $result = $this->videos->bulkAction(
            action: $request->validated('action'),
            ids: $request->validated('ids'),
            admin: $request->user(),
        );

        $message = "{$result['processed']} video(s) affected.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} were skipped (already in the target state or not found).";
        }

        return back()->with('status', $message);
    }

    /* ==================================================================== */
    /*  DTO helpers                                                         */
    /* ==================================================================== */

    private function dtoToSession(YouTubeVideoData $data): array
    {
        return [
            'video_id'         => $data->videoId,
            'title'            => $data->title,
            'description'      => $data->description,
            'thumbnail_url'    => $data->thumbnailUrl,
            'channel_id'       => $data->channelId,
            'channel_name'     => $data->channelName,
            'duration_seconds' => $data->durationSeconds,
            'published_at'     => $data->publishedAt?->toIso8601String(),
        ];
    }

    private function dtoFromSession(array $row): YouTubeVideoData
    {
        return new YouTubeVideoData(
            videoId: $row['video_id'],
            title: $row['title'],
            description: $row['description'] ?? null,
            thumbnailUrl: $row['thumbnail_url'] ?? null,
            channelId: $row['channel_id'] ?? null,
            channelName: $row['channel_name'] ?? null,
            durationSeconds: (int) ($row['duration_seconds'] ?? 0),
            publishedAt: ! empty($row['published_at']) ? CarbonImmutable::parse($row['published_at']) : null,
        );
    }
}