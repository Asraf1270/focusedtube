<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'status', 'starts_at', 'ends_at', 'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $q): Builder
    {
        $now = now();

        return $q->where('status', 'published')
            ->where(fn ($sub) => $sub->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($sub) => $sub->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }
}