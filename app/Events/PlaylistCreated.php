<?php
// app/Events/PlaylistCreated.php

namespace App\Events;

use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaylistCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Playlist $playlist,
        public readonly User $actor,
    ) {}
}