<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function __construct(private readonly ?Request $request = null)
    {
        //
    }

    /**
     * Record an admin action.
     *
     * @param  string       $action       e.g. "video.published"
     * @param  string|null  $description  Human-readable summary
     * @param  Model|null   $subject      Related model
     * @param  array        $meta         Extra context (never store secrets)
     */
    public function record(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $meta = [],
    ): AuditLog {
        $request = $this->request ?? request();

        return AuditLog::create([
            'user_id'      => optional($request->user())->id,
            'action'       => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id'   => $subject?->getKey(),
            'description'  => $description,
            'ip_address'   => $request->ip(),
            'user_agent'   => mb_substr((string) $request->userAgent(), 0, 500),
            'created_at'   => now(),
        ]);
    }
}