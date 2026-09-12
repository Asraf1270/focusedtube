<?php

use App\Models\User;
use App\Models\Video;
use App\Services\AuditLogService;

test('audit log service records an action with subject and actor', function () {
    $admin = User::factory()->admin()->create();
    $video = Video::factory()->create();

    $this->actingAs($admin);

    $log = app(AuditLogService::class)->record(
        action: 'video.published',
        description: "Published video #{$video->id}",
        subject: $video,
    );

    expect($log->user_id)->toBe($admin->id)
        ->and($log->action)->toBe('video.published')
        ->and($log->subject_type)->toBe($video->getMorphClass())
        ->and($log->subject_id)->toBe($video->id)
        ->and($log->ip_address)->not->toBeNull();
});