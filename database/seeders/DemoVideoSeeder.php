<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

class DemoVideoSeeder extends Seeder
{
    /**
     * Optional seed data for local dev.
     * Real metadata is populated via YouTubeService in Step 5.
     * We deliberately don't hardcode fake YouTube IDs here.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // Intentionally left minimal — real seeding flow is completed
        // once the admin "Add Video" workflow exists (Step 6).
    }
}