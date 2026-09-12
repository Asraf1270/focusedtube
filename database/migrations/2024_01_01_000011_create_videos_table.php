<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            $table->string('youtube_video_id', 32)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('channel_name')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('youtube_published_at')->nullable();

            $table->foreignId('category_id')->nullable()
                ->constrained('categories')->nullOnDelete();

            $table->string('status')->default('draft');       // draft | published | archived
            $table->string('visibility')->default('public');  // public | private
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_daily_focus')->default(false);
            $table->unsignedInteger('display_order')->default(0);

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('completion_count')->default(0);

            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
            $table->index('category_id');
            $table->index('is_featured');
            $table->index('is_daily_focus');
            $table->index(['status', 'published_at']); // homepage feed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};