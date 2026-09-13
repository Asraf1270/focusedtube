<?php

namespace App\Services\Playlist;

use App\Events\PlaylistCreated;
use App\Events\PlaylistDeleted;
use App\Events\PlaylistUpdated;
use App\Models\Playlist;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlaylistService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly AnalyticsService $analytics,
    ) {
    }

    /**
     * @param  array{title:string, slug?:string|null, description?:string|null, thumbnail_url?:string|null, status?:string}  $data
     */
    public function create(array $data, User $admin): Playlist
    {
        $playlist = DB::transaction(fn () => Playlist::create([
            'title'         => $data['title'],
            'slug'          => $data['slug'] ?: null,
            'description'   => $data['description'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'status'        => $data['status'] ?? Playlist::STATUS_DRAFT,
            'created_by'    => $admin->id,
            'published_at'  => ($data['status'] ?? null) === Playlist::STATUS_PUBLISHED ? now() : null,
        ]));

        $this->audit->record(
            action: 'playlist.created',
            description: "Created playlist \"{$playlist->title}\"",
            subject: $playlist,
        );

        Log::info('playlist.created', ['playlist_id' => $playlist->id, 'admin_id' => $admin->id]);

        event(new PlaylistCreated($playlist, $admin));

        return $playlist;
    }

    public function update(Playlist $playlist, array $data, User $admin): Playlist
    {
        // Slug regeneration on title change, unless the admin set a slug explicitly.
        if (
            ! empty($data['title'])
            && $data['title'] !== $playlist->title
            && empty($data['slug'])
        ) {
            $data['slug'] = Playlist::uniqueSlug($data['title'], $playlist->id);
        }

        // If status is transitioning to published for the first time, set published_at.
        if (
            isset($data['status'])
            && $data['status'] === Playlist::STATUS_PUBLISHED
            && $playlist->published_at === null
        ) {
            $data['published_at'] = now();
        }

        DB::transaction(fn () => $playlist->fill($data)->save());

        $changes = collect($playlist->getChanges())->except(['updated_at'])->all();

        if (! empty($changes)) {
            $this->audit->record(
                action: 'playlist.updated',
                description: "Updated playlist \"{$playlist->title}\" (".implode(', ', array_keys($changes)).')',
                subject: $playlist,
            );

            event(new PlaylistUpdated($playlist, $admin, $changes));
        }

        return $playlist->refresh();
    }

    public function delete(Playlist $playlist, User $admin): void
    {
        $title = $playlist->title;

        DB::transaction(fn () => $playlist->delete()); // pivot cascades

        $this->audit->record(
            action: 'playlist.deleted',
            description: "Deleted playlist \"{$title}\"",
        );

        $ghost = new Playlist(['title' => $title, 'slug' => $playlist->slug]);
        $ghost->id = $playlist->id;

        event(new PlaylistDeleted($ghost, $admin));
    }
}