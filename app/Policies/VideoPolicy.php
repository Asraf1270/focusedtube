<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    /**
     * Admins bypass all checks. Non-admin checks all return false.
     * This is the single source of truth — every controller action calls
     * authorize() against one of these methods.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : false;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Video $video): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Video $video): bool
    {
        return false;
    }

    public function publish(User $user, Video $video): bool
    {
        return false;
    }

    public function archive(User $user, Video $video): bool
    {
        return false;
    }

    public function delete(User $user, Video $video): bool
    {
        return false;
    }
}