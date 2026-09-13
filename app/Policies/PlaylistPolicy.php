<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;

class PlaylistPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : false;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Playlist $playlist): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Playlist $playlist): bool { return false; }
    public function delete(User $user, Playlist $playlist): bool { return false; }
    public function manageVideos(User $user, Playlist $playlist): bool { return false; }
}