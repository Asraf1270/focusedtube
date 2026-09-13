<?php

namespace App\Services\Video;

use App\Events\VideoArchived;
use App\Events\VideoCreated;
use App\Events\VideoDeleted;
use App\Events\VideoPublished;
use App\Events\VideoUpdated;
use App\Models\User;
use App\Models\Video;
use App\Services\AnalyticsService;
use App\Services\AuditLogService;
use App\Support\YouTube\YouTubeVideoData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class VideoService
{
    public const BULK_PUBLISH   = 'publish';
    public const BULK_UNPUBLISH = 'unpublish';
    public const BULK_ARCHIVE   = 'archive';
    public const BULK_DELETE    = 'delete';
    public const BULK_FEATURE   = 'feature';
    public const BULK_UNFEATURE = 'unfeature';

    public function __construct(
        private readonly AuditLogService $audit,
        private readonly AnalyticsService $analytics,
    ) {
    }

    /* ==================================================================== */
    /*  Creation (Step 6)                                                   */
    /* ==================================================================== */

    public function createFromYouTube(
        YouTubeVideoData $data,
        array $options,
        User $admin,
    ): Video {
        if (Video::query()->where('youtube_video_id', $data->videoId)->exists()) {
            throw new RuntimeException("Video {$data->videoId} is already in FocusedTube.");
        }

        $status = $this->normalizeStatus($options['status'] ?? Video::STATUS_DRAFT);

        $video = DB::transaction(function () use ($data, $options, $admin, $status) {
            return Video::create(array_merge(
                $data->toVideoAttributes(),
                [
                    'category_id'    => $options['category_id'] ?? null,
                    'status'         => $status,
                    'visibility'     => $options['visibility'] ?? Video::VISIBILITY_PUBLIC,
                    'is_featured'    => (bool) ($options['is_featured'] ?? false),
                    'is_daily_focus' => (bool) ($options['is_daily_focus'] ?? false),
                    'display_order'  => (int) ($options['display_order'] ?? 0),
                    'created_by'     => $admin->id,
                    'published_at'   => $status === Video::STATUS_PUBLISHED ? now() : null,
                ],
            ));
        });

        $this->audit->record(
            action: 'video.created',
            description: "Created video \"{$video->title}\"",
            subject: $video,
        );

        $this->analytics->forgetDashboardCache();
        Log::info('video.created', ['video_id' => $video->id, 'admin_id' => $admin->id]);

        event(new VideoCreated($video, $admin));

        return $video;
    }

    public function findByYouTubeId(string $youtubeId): ?Video
    {
        return Video::query()->where('youtube_video_id', $youtubeId)->first();
    }

    /* ==================================================================== */
    /*  Update (admin-editable fields only)                                 */
    /* ==================================================================== */

    /**
     * @param  array{
     *     title?: string,
     *     description?: string|null,
     *     category_id?: int|null,
     *     visibility?: string,
     *     is_featured?: bool,
     *     is_daily_focus?: bool,
     *     display_order?: int,
     * }  $data
     */
    public function update(Video $video, array $data, User $admin): Video
    {
        // Never allow YouTube-owned fields to be edited here.
        $allowed = [
            'title', 'description', 'category_id',
            'visibility', 'is_featured', 'is_daily_focus', 'display_order',
        ];

        $data = array_intersect_key($data, array_flip($allowed));

        // If the title changed, regenerate the slug.
        if (isset($data['title']) && $data['title'] !== $video->title) {
            $data['slug'] = Video::uniqueSlug($data['title'], $video->id);
        }

        $original = $video->only(array_keys($data));

        DB::transaction(function () use ($video, $data) {
            $video->fill($data)->save();
        });

        $changes = collect($video->getChanges())
            ->except(['updated_at'])
            ->all();

        if (! empty($changes)) {
            $this->audit->record(
                action: 'video.updated',
                description: "Updated video \"{$video->title}\" (".implode(', ', array_keys($changes)).')',
                subject: $video,
            );

            Log::info('video.updated', [
                'video_id' => $video->id,
                'admin_id' => $admin->id,
                'changes'  => array_keys($changes),
            ]);

            event(new VideoUpdated($video, $admin, $changes));
        }

        return $video->refresh();
    }

    /* ==================================================================== */
    /*  Lifecycle: publish / unpublish / archive / restore                  */
    /* ==================================================================== */

    public function publish(Video $video, User $admin): Video
    {
        if ($video->status === Video::STATUS_PUBLISHED) {
            return $video;
        }

        DB::transaction(function () use ($video) {
            $video->forceFill([
                'status'       => Video::STATUS_PUBLISHED,
                'published_at' => $video->published_at ?? now(),
            ])->save();
        });

        $this->audit->record(
            action: 'video.published',
            description: "Published video \"{$video->title}\"",
            subject: $video,
        );

        $this->analytics->forgetDashboardCache();
        Log::info('video.published', ['video_id' => $video->id, 'admin_id' => $admin->id]);

        event(new VideoPublished($video, $admin));

        return $video->refresh();
    }

    public function unpublish(Video $video, User $admin): Video
    {
        if ($video->status !== Video::STATUS_PUBLISHED) {
            return $video;
        }

        DB::transaction(function () use ($video) {
            $video->forceFill([
                'status'       => Video::STATUS_DRAFT,
                'published_at' => null,
            ])->save();
        });

        $this->audit->record(
            action: 'video.unpublished',
            description: "Unpublished video \"{$video->title}\"",
            subject: $video,
        );

        $this->analytics->forgetDashboardCache();
        Log::info('video.unpublished', ['video_id' => $video->id, 'admin_id' => $admin->id]);

        event(new VideoUpdated($video, $admin, ['status' => Video::STATUS_DRAFT]));

        return $video->refresh();
    }

    public function archive(Video $video, User $admin): Video
    {
        if ($video->status === Video::STATUS_ARCHIVED) {
            return $video;
        }

        DB::transaction(function () use ($video) {
            $video->forceFill([
                'status'         => Video::STATUS_ARCHIVED,
                'is_daily_focus' => false,
            ])->save();
        });

        $this->audit->record(
            action: 'video.archived',
            description: "Archived video \"{$video->title}\"",
            subject: $video,
        );

        $this->analytics->forgetDashboardCache();
        Log::info('video.archived', ['video_id' => $video->id, 'admin_id' => $admin->id]);

        event(new VideoArchived($video, $admin));

        return $video->refresh();
    }

    public function restore(Video $video, User $admin): Video
    {
        if ($video->status !== Video::STATUS_ARCHIVED) {
            return $video;
        }

        DB::transaction(function () use ($video) {
            $video->forceFill([
                'status'       => Video::STATUS_DRAFT,
                'published_at' => null,
            ])->save();
        });

        $this->audit->record(
            action: 'video.restored',
            description: "Restored video \"{$video->title}\" from archive",
            subject: $video,
        );

        $this->analytics->forgetDashboardCache();

        event(new VideoUpdated($video, $admin, ['status' => Video::STATUS_DRAFT]));

        return $video->refresh();
    }

    public function delete(Video $video, User $admin): void
    {
        $snapshot = $video->only(['id', 'title', 'youtube_video_id']);

        DB::transaction(function () use ($video) {
            // Pivot rows cascade via FK, watch_* rows cascade via FK.
            $video->delete();
        });

        $this->audit->record(
            action: 'video.deleted',
            description: "Deleted video \"{$snapshot['title']}\" ({$snapshot['youtube_video_id']})",
        );

        $this->analytics->forgetDashboardCache();

        Log::warning('video.deleted', ['video_id' => $snapshot['id'], 'admin_id' => $admin->id]);

        // Reconstruct a lightweight model for the event (the original is gone).
        $ghost = new Video($snapshot);
        $ghost->id = $snapshot['id'];

        event(new VideoDeleted($ghost, $admin));
    }

    /* ==================================================================== */
    /*  Bulk                                                                */
    /* ==================================================================== */

    /**
     * @param  int[]  $ids
     * @return array{processed:int, skipped:int}
     */
    public function bulkAction(string $action, array $ids, User $admin): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return ['processed' => 0, 'skipped' => 0];
        }

        $videos = Video::query()->whereIn('id', $ids)->get();

        $processed = 0;
        $skipped   = count($ids) - $videos->count();

        foreach ($videos as $video) {
            match ($action) {
                self::BULK_PUBLISH   => $this->publish($video, $admin),
                self::BULK_UNPUBLISH => $this->unpublish($video, $admin),
                self::BULK_ARCHIVE   => $this->archive($video, $admin),
                self::BULK_DELETE    => $this->delete($video, $admin),
                self::BULK_FEATURE   => $this->setFeatured($video, true, $admin),
                self::BULK_UNFEATURE => $this->setFeatured($video, false, $admin),
                default              => throw new RuntimeException("Unknown bulk action: {$action}"),
            };

            $processed++;
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /* ==================================================================== */
    /*  Helpers                                                             */
    /* ==================================================================== */

    private function setFeatured(Video $video, bool $value, User $admin): Video
    {
        if ($video->is_featured === $value) {
            return $video;
        }

        return $this->update($video, ['is_featured' => $value], $admin);
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, [
            Video::STATUS_DRAFT,
            Video::STATUS_PUBLISHED,
            Video::STATUS_ARCHIVED,
        ], true) ? $status : Video::STATUS_DRAFT;
    }
}