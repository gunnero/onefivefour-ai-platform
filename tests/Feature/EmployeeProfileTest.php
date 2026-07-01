<?php

namespace Tests\Feature;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_employee_profile_loads_identity_work_history_and_governance_data(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@onefivefour.ai')->firstOrFail();
        $employee = Employee::query()->where('employee_code', 'ELENA-MARKOVA')->firstOrFail();

        $this->actingAs($user)
            ->get(EmployeeResource::getUrl('view', ['record' => $employee]))
            ->assertOk()
            ->assertSeeText('Employee Profile')
            ->assertSeeText('Identity')
            ->assertSeeText('Elena Markova')
            ->assertSeeText('ELENA-MARKOVA')
            ->assertSeeText('Editor-in-Chief AI')
            ->assertSeeText('Editorial')
            ->assertSeeText('No manager assigned')
            ->assertSeeText('active')
            ->assertSeeText('Ensure editorial quality and prepare content for human approval.')
            ->assertSeeText('Stats')
            ->assertSeeText('Total Assignments')
            ->assertSeeText('Active Assignments')
            ->assertSeeText('Completed Assignments')
            ->assertSeeText('Blocked Assignments')
            ->assertSeeText('Needs Review Assignments')
            ->assertSeeText('Capabilities Count')
            ->assertSeeText('Capabilities')
            ->assertSeeText('Editing')
            ->assertSeeText('standard')
            ->assertSeeText('Assignments')
            ->assertSeeText('Current Assignments')
            ->assertSeeText('Review first Razbudise editorial package')
            ->assertSeeText('pending')
            ->assertSeeText('Activity')
            ->assertSeeText('assignment_created')
            ->assertSeeText('Assignment created for Elena Markova')
            ->assertSeeText('Audit History')
            ->assertSeeText('employee_created');
    }
}
