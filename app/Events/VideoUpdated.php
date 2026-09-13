<?php

namespace App\Events;

use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $changes  Attribute name => new value (only changed attributes)
     */
    public function __construct(
        public readonly Video $video,
        public readonly User $actor,
        public readonly array $changes,
    ) {
    }
}