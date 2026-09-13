<?php
// app/Events/PlaylistUpdated.php

namespace App\Events;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaylistUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Playlist $playlist,
        public readonly User $actor,
        public readonly array $changes,
    ) {}
}