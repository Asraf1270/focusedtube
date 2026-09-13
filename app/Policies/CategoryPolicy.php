<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : false;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Category $category): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Category $category): bool { return false; }
    public function delete(User $user, Category $category): bool { return false; }
}