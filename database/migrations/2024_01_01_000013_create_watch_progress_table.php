<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('watch_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('current_position_seconds')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedTinyInteger('progress_percentage')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('completed')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'video_id']);
            $table->index(['user_id', 'completed', 'last_watched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_progress');
    }
};