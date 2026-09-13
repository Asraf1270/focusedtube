<?php

namespace App\Events;

use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Video $video,
        public readonly User $actor,
    ) {
    }
}