<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * @param  array<string, mixed>|null  $beforeState
     * @param  array<string, mixed>|null  $afterState
     */
    public function record(
        Model $auditable,
        string $eventType,
        string $action,
        string $summary,
        ?array $beforeState = null,
        ?array $afterState = null,
        ?Model $actor = null,
    ): AuditLog {
        $actor ??= Auth::user();

        return AuditLog::query()->create([
            'organization_id' => $auditable->getAttribute('organization_id'),
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => $actor?->getKey(),
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'event_type' => $eventType,
            'action' => $action,
            'summary' => $summary,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'occurred_at' => now(),
        ]);
    }
}
