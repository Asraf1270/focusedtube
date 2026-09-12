<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    public const VISIBILITY_PUBLIC  = 'public';
    public const VISIBILITY_PRIVATE = 'private';

    protected $fillable = [
        'youtube_video_id', 'title', 'slug', 'description',
        'thumbnail_url', 'channel_id', 'channel_name',
        'duration_seconds', 'youtube_published_at',
        'category_id', 'status', 'visibility',
        'is_featured', 'is_daily_focus', 'display_order',
        'views_count', 'completion_count',
        'created_by', 'published_at',
    ];

    protected $casts = [
        'youtube_published_at' => 'datetime',
        'published_at'         => 'datetime',
        'is_featured'          => 'boolean',
        'is_daily_focus'       => 'boolean',
        'duration_seconds'     => 'integer',
        'views_count'          => 'integer',
        'completion_count'     => 'integer',
        'display_order'        => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Video $video) {
            if (empty($video->slug)) {
                $video->slug = static::uniqueSlug($video->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'video';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---- Relationships ---- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_video')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(WatchProgress::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /* ---- Scopes ---- */

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PUBLISHED)
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
    }

    public function scopePublicVisibility(Builder $q): Builder
    {
        return $q->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopeVisibleToUsers(Builder $q): Builder
    {
        return $q->published()->publicVisibility();
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeDailyFocus(Builder $q): Builder
    {
        return $q->where('is_daily_focus', true);
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $q->where(function ($sub) use ($like) {
            $sub->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('channel_name', 'like', $like);
        });
    }

    /* ---- Helpers ---- */

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function watchUrl(): string
    {
        return route('videos.show', $this->slug);
    }

    public function youtubeWatchUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_video_id;
    }

    public function durationForHumans(): string
    {
        $s = $this->duration_seconds;
        if ($s <= 0) return '—';

        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        $sec = $s % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $sec)
            : sprintf('%d:%02d', $m, $sec);
    }
}