<?php
// app/Events/CategoryDeleted.php

namespace App\Events;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Category $category,
        public readonly User $actor,
    ) {}
}