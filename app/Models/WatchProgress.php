<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchProgress extends Model
{
    protected $table = 'watch_progress';

    protected $fillable = [
        'user_id', 'video_id',
        'current_position_seconds', 'duration_seconds', 'progress_percentage',
        'started_at', 'last_watched_at', 'completed_at', 'completed',
    ];

    protected $casts = [
        'started_at'      => 'datetime',
        'last_watched_at' => 'datetime',
        'completed_at'    => 'datetime',
        'completed'       => 'boolean',
        'progress_percentage' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}