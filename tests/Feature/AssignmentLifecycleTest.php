<?php

namespace Tests\Feature;

use App\Filament\Resources\Assignments\Pages\ListAssignments;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\User;
use App\Services\AssignmentLifecycleService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AssignmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_lifecycle_transitions_update_status_and_side_effects(): void
    {
        Carbon::setTestNow('2026-07-01 09:00:00');

        $assignment = $this->assignment(['status' => 'pending']);
        $service = app(AssignmentLifecycleService::class);

        $service->accept($assignment);

        $this->assertSame('accepted', $assignment->refresh()->status);

        $service->start($assignment);

        $assignment->refresh();
        $this->assertSame('in_progress', $assignment->status);
        $this->assertTrue($assignment->started_at->equalTo(Carbon::parse('2026-07-01 09:00:00')));
        $firstStartedAt = $assignment->started_at;

        Carbon::setTestNow('2026-07-01 10:00:00');

        $service->block($assignment, escalationRequired: true);

        $assignment->refresh();
        $this->assertSame('blocked', $assignment->status);
        $this->assertTrue($assignment->escalation_required);

        Carbon::setTestNow('2026-07-01 11:00:00');

        $service->resume($assignment);

        $assignment->refresh();
        $this->assertSame('in_progress', $assignment->status);
        $this->assertTrue($assignment->started_at->equalTo($firstStartedAt));

        $service->requestReview($assignment);

        $assignment->refresh();
        $this->assertSame('needs_review', $assignment->status);
        $this->assertTrue($assignment->review_required);

        Carbon::setTestNow('2026-07-01 12:00:00');

        $service->complete($assignment, ['summary' => 'Human-ready review package.']);

        $assignment->refresh();
        $this->assertSame('completed', $assignment->status);
        $this->assertTrue($assignment->completed_at->equalTo(Carbon::parse('2026-07-01 12:00:00')));
        $this->assertSame(['summary' => 'Human-ready review package.'], $assignment->output_payload);

        Carbon::setTestNow();
    }

    public function test_direct_completion_failure_and_cancellation_paths_are_supported(): void
    {
        $service = app(AssignmentLifecycleService::class);

        $directCompletion = $this->assignment(['status' => 'in_progress']);
        $service->complete($directCompletion, ['summary' => 'Completed directly.']);

        $this->assertSame('completed', $directCompletion->refresh()->status);
        $this->assertSame(['summary' => 'Completed directly.'], $directCompletion->output_payload);
        $this->assertNotNull($directCompletion->completed_at);

        $failed = $this->assignment(['status' => 'in_progress']);
        $service->fail($failed);

        $this->assertSame('failed', $failed->refresh()->status);

        foreach (['pending', 'accepted', 'in_progress', 'blocked', 'needs_review'] as $status) {
            $assignment = $this->assignment(['status' => $status]);

            $service->cancel($assignment);

            $this->assertSame('cancelled', $assignment->refresh()->status);
        }
    }

    public function test_invalid_lifecycle_transitions_are_rejected_without_mutating_assignment(): void
    {
        $assignment = $this->assignment(['status' => 'pending']);
        $service = app(AssignmentLifecycleService::class);
        $auditLogCount = $assignment->auditLogs()->count();
        $activityCount = $assignment->activities()->count();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot move Assignment from pending to in_progress.');

        try {
            $service->start($assignment);
        } finally {
            $assignment->refresh();

            $this->assertSame('pending', $assignment->status);
            $this->assertSame($auditLogCount, $assignment->auditLogs()->count());
            $this->assertSame($activityCount, $assignment->activities()->count());
        }
    }

    public function test_status_transition_creates_audit_activity_and_is_visible_in_hq_feed(): void
    {
        $assignment = $this->assignment([
            'title' => 'Accept launch package',
            'status' => 'pending',
        ]);

        app(AssignmentLifecycleService::class)->accept($assignment);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'assignment_status_changed',
            'auditable_type' => Assignment::class,
            'auditable_id' => $assignment->id,
            'before_state->status' => 'pending',
            'after_state->status' => 'accepted',
        ]);

        $this->assertDatabaseHas('activities', [
            'activity_type' => 'assignment_status_changed',
            'status' => 'visible',
            'title' => 'Assignment accepted: Accept launch package',
            'assignment_id' => $assignment->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('Activity Feed')
            ->assertSeeText('assignment_status_changed')
            ->assertSeeText('Assignment accepted: Accept launch package');
    }

    public function test_filament_assignment_lifecycle_actions_are_visible_for_matching_statuses(): void
    {
        $this->actingAs(User::factory()->create());

        $pending = $this->assignment(['status' => 'pending']);
        $accepted = $this->assignment(['status' => 'accepted']);
        $inProgress = $this->assignment(['status' => 'in_progress']);
        $blocked = $this->assignment(['status' => 'blocked']);
        $needsReview = $this->assignment(['status' => 'needs_review']);
        $completed = $this->assignment(['status' => 'completed']);

        Livewire::test(ListAssignments::class)
            ->assertTableActionVisible('accept', $pending)
            ->assertTableActionVisible('cancel', $pending)
            ->assertTableActionHidden('start', $pending)
            ->assertTableActionHidden('accept', $accepted)
            ->assertTableActionVisible('start', $accepted)
            ->assertTableActionVisible('block', $inProgress)
            ->assertTableActionVisible('request_review', $inProgress)
            ->assertTableActionVisible('complete', $inProgress)
            ->assertTableActionVisible('fail', $inProgress)
            ->assertTableActionVisible('cancel', $inProgress)
            ->assertTableActionVisible('resume', $blocked)
            ->assertTableActionHidden('start', $blocked)
            ->assertTableActionVisible('complete', $needsReview)
            ->assertTableActionVisible('cancel', $needsReview)
            ->assertTableActionHidden('accept', $completed)
            ->assertTableActionHidden('start', $completed)
            ->assertTableActionHidden('block', $completed)
            ->assertTableActionHidden('resume', $completed)
            ->assertTableActionHidden('request_review', $completed)
            ->assertTableActionHidden('complete', $completed)
            ->assertTableActionHidden('fail', $completed)
            ->assertTableActionHidden('cancel', $completed);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function assignment(array $attributes = []): Assignment
    {
        $organization = Organization::factory()->create(['timezone' => 'Europe/Skopje']);
        $site = Site::factory()->for($organization)->create();
        $department = Department::factory()->for($organization)->create();
        $employee = Employee::factory()->for($organization)->for($department)->create();
        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create();

        return Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->create($attributes);
    }
}
