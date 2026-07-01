<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCapability;
use App\Models\Organization;
use App\Models\Policy;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditActivityAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_changes_create_audit_logs_and_activity_items(): void
    {
        [$organization, $department] = $this->organizationAndDepartment();

        $employee = Employee::factory()
            ->for($organization)
            ->for($department)
            ->create([
                'full_name' => 'Automation Employee',
                'role_title' => 'Research Employee',
                'employment_status' => 'active',
            ]);

        $this->assertAuditLogged('employee_created', $employee, [
            'after_state->full_name' => 'Automation Employee',
            'after_state->employment_status' => 'active',
        ]);
        $this->assertActivityVisible('employee_created', 'Employee created: Automation Employee', $employee);

        $employee->update(['role_title' => 'Senior Research Employee']);

        $this->assertAuditLogged('employee_updated', $employee, [
            'before_state->role_title' => 'Research Employee',
            'after_state->role_title' => 'Senior Research Employee',
        ]);

        $employee->update(['employment_status' => 'paused']);

        $this->assertAuditLogged('employee_status_changed', $employee, [
            'before_state->employment_status' => 'active',
            'after_state->employment_status' => 'paused',
        ]);
        $this->assertActivityVisible('employee_status_changed', 'Employee paused: Automation Employee', $employee);
    }

    public function test_employee_capability_grants_and_revocations_create_audit_logs(): void
    {
        [$organization, $department] = $this->organizationAndDepartment();
        $employee = Employee::factory()->for($organization)->for($department)->create();
        $capability = Capability::factory()->create(['name' => 'Source Research']);

        $employeeCapability = EmployeeCapability::factory()
            ->for($organization)
            ->for($employee)
            ->for($capability)
            ->create(['status' => 'active']);

        $this->assertAuditLogged('employee_capability_granted', $employeeCapability, [
            'after_state->status' => 'active',
            'after_state->capability_id' => $capability->id,
        ]);

        $employeeCapability->update([
            'status' => 'retired',
            'revoked_at' => now(),
        ]);

        $this->assertAuditLogged('employee_capability_revoked', $employeeCapability, [
            'before_state->status' => 'active',
            'after_state->status' => 'retired',
        ]);
    }

    public function test_policy_and_sop_changes_create_audit_logs_and_activation_activity(): void
    {
        [$organization, $department] = $this->organizationAndDepartment();

        $policy = Policy::factory()
            ->for($organization)
            ->create([
                'title' => 'Human Review Policy',
                'status' => 'draft',
            ]);

        $this->assertAuditLogged('policy_created', $policy, [
            'after_state->title' => 'Human Review Policy',
            'after_state->status' => 'draft',
        ]);

        $policy->update(['body' => 'Human approval is required before publishing.']);

        $this->assertAuditLogged('policy_updated', $policy, [
            'after_state->body' => 'Human approval is required before publishing.',
        ]);

        $policy->update(['status' => 'active']);

        $this->assertAuditLogged('policy_status_changed', $policy, [
            'before_state->status' => 'draft',
            'after_state->status' => 'active',
        ]);
        $this->assertActivityVisible('policy_activated', 'Policy activated: Human Review Policy');

        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->create([
                'title' => 'Editorial Review SOP',
                'status' => 'draft',
            ]);

        $this->assertAuditLogged('sop_created', $sop, [
            'after_state->title' => 'Editorial Review SOP',
            'after_state->status' => 'draft',
        ]);

        $sop->update(['purpose' => 'Prepare editorial packages for human review.']);

        $this->assertAuditLogged('sop_updated', $sop, [
            'after_state->purpose' => 'Prepare editorial packages for human review.',
        ]);

        $sop->update(['status' => 'active']);

        $this->assertAuditLogged('sop_status_changed', $sop, [
            'before_state->status' => 'draft',
            'after_state->status' => 'active',
        ]);
        $this->assertActivityVisible('sop_activated', 'SOP activated: Editorial Review SOP');
    }

    public function test_assignment_changes_create_audit_logs_activity_and_update_hq_feed(): void
    {
        [$organization, $department] = $this->organizationAndDepartment();
        $site = Site::factory()->for($organization)->create(['name' => 'Atlas Site']);
        $employee = Employee::factory()->for($organization)->for($department)->create(['full_name' => 'Elena Operator']);
        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create(['title' => 'Assignment Review SOP']);

        $assignment = Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->create([
                'title' => 'Prepare launch research package',
                'status' => 'pending',
                'priority' => 'normal',
            ]);

        $this->assertAuditLogged('assignment_created', $assignment, [
            'after_state->title' => 'Prepare launch research package',
            'after_state->status' => 'pending',
        ]);
        $this->assertActivityVisible('assignment_created', 'Assignment created: Prepare launch research package', $employee, $assignment);

        $assignment->update(['priority' => 'high']);

        $this->assertAuditLogged('assignment_updated', $assignment, [
            'before_state->priority' => 'normal',
            'after_state->priority' => 'high',
        ]);

        $assignment->update(['status' => 'in_progress']);

        $this->assertAuditLogged('assignment_status_changed', $assignment, [
            'before_state->status' => 'pending',
            'after_state->status' => 'in_progress',
        ]);
        $this->assertActivityVisible('assignment_status_changed', 'Assignment in progress: Prepare launch research package', $employee, $assignment);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('Activity Feed')
            ->assertSeeText('assignment_status_changed')
            ->assertSeeText('Assignment in progress: Prepare launch research package');
    }

    /**
     * @return array{0: Organization, 1: Department}
     */
    private function organizationAndDepartment(): array
    {
        $organization = Organization::factory()->create(['timezone' => 'Europe/Skopje']);
        $department = Department::factory()->for($organization)->create(['name' => 'Editorial']);

        return [$organization, $department];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function assertAuditLogged(string $eventType, object $auditable, array $attributes = []): void
    {
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => $eventType,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->id,
            ...$attributes,
        ]);
    }

    private function assertActivityVisible(
        string $activityType,
        string $title,
        ?Employee $employee = null,
        ?Assignment $assignment = null,
    ): void {
        $this->assertDatabaseHas('activities', [
            'activity_type' => $activityType,
            'status' => 'visible',
            'title' => $title,
            'employee_id' => $employee?->id,
            'assignment_id' => $assignment?->id,
        ]);
    }
}
