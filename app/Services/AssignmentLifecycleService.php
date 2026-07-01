<?php

namespace App\Services;

use App\Models\Assignment;
use DomainException;
use Illuminate\Support\Facades\DB;

class AssignmentLifecycleService
{
    /**
     * @var array<string, array<int, string>>
     */
    private const TRANSITIONS = [
        'pending' => ['accepted', 'cancelled'],
        'accepted' => ['in_progress', 'cancelled'],
        'in_progress' => ['blocked', 'needs_review', 'completed', 'failed', 'cancelled'],
        'blocked' => ['in_progress', 'cancelled'],
        'needs_review' => ['completed', 'cancelled'],
    ];

    public function accept(Assignment $assignment): Assignment
    {
        return $this->transition($assignment, 'accepted');
    }

    public function start(Assignment $assignment): Assignment
    {
        return $this->transition($assignment, 'in_progress', [
            'started_at' => $assignment->started_at ?? now(),
        ]);
    }

    public function block(Assignment $assignment, bool $escalationRequired = false): Assignment
    {
        return $this->transition($assignment, 'blocked', [
            'escalation_required' => $escalationRequired,
        ]);
    }

    public function resume(Assignment $assignment): Assignment
    {
        return $this->transition($assignment, 'in_progress', [
            'started_at' => $assignment->started_at ?? now(),
        ]);
    }

    public function requestReview(Assignment $assignment): Assignment
    {
        return $this->transition($assignment, 'needs_review', [
            'review_required' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $outputPayload
     */
    public function complete(Assignment $assignment, array $outputPayload): Assignment
    {
        return $this->transition($assignment, 'completed', [
            'completed_at' => now(),
            'output_payload' => $outputPayload,
        ]);
    }

    public function fail(Assignment $assignment): Assignment
    {
        return $this->transition($assignment, 'failed');
    }

    public function cancel(Assignment $assignment): Assignment
    {
        return $this->transition($assignment, 'cancelled');
    }

    public function canAccept(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'accepted');
    }

    public function canStart(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'in_progress') && ($assignment->status === 'accepted');
    }

    public function canBlock(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'blocked');
    }

    public function canResume(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'in_progress') && ($assignment->status === 'blocked');
    }

    public function canRequestReview(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'needs_review');
    }

    public function canComplete(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'completed');
    }

    public function canFail(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'failed');
    }

    public function canCancel(Assignment $assignment): bool
    {
        return $this->canTransition($assignment, 'cancelled');
    }

    public function canTransition(Assignment $assignment, string $toStatus): bool
    {
        return in_array($toStatus, self::TRANSITIONS[$assignment->status] ?? [], true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transition(Assignment $assignment, string $toStatus, array $attributes = []): Assignment
    {
        if (! $this->canTransition($assignment, $toStatus)) {
            throw new DomainException("Cannot move Assignment from {$assignment->status} to {$toStatus}.");
        }

        return DB::transaction(function () use ($assignment, $toStatus, $attributes): Assignment {
            $assignment->fill([
                ...$attributes,
                'status' => $toStatus,
            ]);

            $assignment->save();

            return $assignment->refresh();
        });
    }
}
