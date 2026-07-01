<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrganizationalCoreTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, string> */
    private array $models = [
        'Organization' => 'App\\Models\\Organization',
        'Site' => 'App\\Models\\Site',
        'Department' => 'App\\Models\\Department',
        'Employee' => 'App\\Models\\Employee',
        'Capability' => 'App\\Models\\Capability',
        'EmployeeCapability' => 'App\\Models\\EmployeeCapability',
        'Policy' => 'App\\Models\\Policy',
        'PolicyScope' => 'App\\Models\\PolicyScope',
        'StandardOperatingProcedure' => 'App\\Models\\StandardOperatingProcedure',
        'SopPolicy' => 'App\\Models\\SopPolicy',
        'SopCapability' => 'App\\Models\\SopCapability',
        'Assignment' => 'App\\Models\\Assignment',
        'Activity' => 'App\\Models\\Activity',
        'AuditLog' => 'App\\Models\\AuditLog',
    ];

    /** @var array<string, string> */
    private array $filamentResources = [
        'Organization' => 'App\\Filament\\Resources\\Organizations\\OrganizationResource',
        'Site' => 'App\\Filament\\Resources\\Sites\\SiteResource',
        'Department' => 'App\\Filament\\Resources\\Departments\\DepartmentResource',
        'Employee' => 'App\\Filament\\Resources\\Employees\\EmployeeResource',
        'Capability' => 'App\\Filament\\Resources\\Capabilities\\CapabilityResource',
        'Policy' => 'App\\Filament\\Resources\\Policies\\PolicyResource',
        'StandardOperatingProcedure' => 'App\\Filament\\Resources\\StandardOperatingProcedures\\StandardOperatingProcedureResource',
        'Assignment' => 'App\\Filament\\Resources\\Assignments\\AssignmentResource',
        'Activity' => 'App\\Filament\\Resources\\Activities\\ActivityResource',
        'AuditLog' => 'App\\Filament\\Resources\\AuditLogs\\AuditLogResource',
    ];

    public function test_organizational_core_classes_exist(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());

        foreach ($this->models as $modelName => $modelClass) {
            $this->assertTrue(class_exists($modelClass), "{$modelName} model exists.");
            $this->assertTrue(method_exists($modelClass, 'factory'), "{$modelName} factory is available.");
            $this->assertTrue(class_exists("App\\Policies\\{$modelName}Policy"), "{$modelName} policy exists.");
        }

        foreach ($this->filamentResources as $modelName => $resourceClass) {
            $this->assertTrue(class_exists($resourceClass), "{$modelName} Filament resource exists.");
            $this->assertSame($this->models[$modelName], $resourceClass::getModel());
        }
    }

    public function test_factories_can_create_the_core_relationship_graph(): void
    {
        foreach ($this->models as $modelClass) {
            $this->assertTrue(class_exists($modelClass), "{$modelClass} exists before creating graph.");
        }

        $organization = $this->models['Organization']::factory()->create();
        $site = $this->models['Site']::factory()->for($organization)->create();
        $department = $this->models['Department']::factory()->for($organization)->create();
        $employee = $this->models['Employee']::factory()
            ->for($organization)
            ->for($department)
            ->create();
        $capability = $this->models['Capability']::factory()->create();
        $employeeCapability = $this->models['EmployeeCapability']::factory()
            ->for($organization)
            ->for($employee)
            ->for($capability)
            ->create();
        $policy = $this->models['Policy']::factory()->for($organization)->create();
        $policyScope = $this->models['PolicyScope']::factory()
            ->for($organization)
            ->for($policy)
            ->create(['scope_type' => 'organization', 'scope_id' => null]);
        $sop = $this->models['StandardOperatingProcedure']::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create();
        $sopPolicy = $this->models['SopPolicy']::factory()
            ->for($organization)
            ->for($sop, 'standardOperatingProcedure')
            ->for($policy)
            ->create();
        $sopCapability = $this->models['SopCapability']::factory()
            ->for($organization)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability)
            ->create();
        $assignment = $this->models['Assignment']::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->create();
        $auditLog = $this->models['AuditLog']::factory()
            ->for($organization)
            ->create([
                'actor_type' => $employee::class,
                'actor_id' => $employee->id,
                'auditable_type' => $assignment::class,
                'auditable_id' => $assignment->id,
            ]);
        $activity = $this->models['Activity']::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($assignment)
            ->for($auditLog)
            ->create();

        $this->assertTrue($organization->sites->contains($site));
        $this->assertTrue($organization->departments->contains($department));
        $this->assertTrue($organization->employees->contains($employee));
        $this->assertTrue($department->employees->contains($employee));
        $this->assertTrue($employee->capabilities->contains($capability));
        $this->assertTrue($policy->scopes->contains($policyScope));
        $this->assertTrue($sop->policies->contains($policy));
        $this->assertTrue($sop->capabilities->contains($capability));
        $this->assertTrue($sop->sopPolicies->contains($sopPolicy));
        $this->assertTrue($sop->sopCapabilities->contains($sopCapability));
        $this->assertTrue($employee->assignments->contains($assignment));
        $this->assertTrue($assignment->activities->contains($activity));
        $this->assertTrue($organization->auditLogs->contains($auditLog));
        $this->assertTrue($organization->activities->contains($activity));
        $this->assertTrue($employeeCapability->employee->is($employee));
    }

    public function test_database_seeder_creates_the_initial_company_structure(): void
    {
        foreach ($this->models as $modelClass) {
            $this->assertTrue(class_exists($modelClass), "{$modelClass} exists before seeding.");
        }

        $this->seed();

        $organization = $this->models['Organization']::query()->where('slug', 'onefivefour')->first();

        $this->assertNotNull($organization);
        $this->assertSame('OneFiveFour', $organization->name);
        $this->assertTrue($organization->sites()->where('name', 'Razbudise.mk')->exists());

        $this->assertSame(9, $organization->departments()->count());
        $this->assertTrue($organization->departments()->where('name', 'Trust & Safety')->exists());

        foreach (['Elena Markova', 'Martin Nikolovski', 'Mila Andonova', 'Sara Ilieva', 'Viktor Petrov', 'David Kostovski'] as $employeeName) {
            $this->assertTrue($organization->employees()->where('full_name', $employeeName)->exists(), "{$employeeName} is seeded.");
        }

        foreach (['Research', 'Writing', 'Localization', 'SEO', 'Fact Checking', 'Editing'] as $capabilityName) {
            $this->assertTrue($this->models['Capability']::query()->where('name', $capabilityName)->exists(), "{$capabilityName} capability is seeded.");
        }

        $this->assertSame(1, $organization->policies()->count());
        $this->assertSame(1, $organization->standardOperatingProcedures()->count());
        $this->assertSame(1, $organization->assignments()->count());
        $this->assertSame(1, $organization->activities()->count());
        $this->assertSame(1, $organization->auditLogs()->count());
    }

    public function test_seeded_admin_user_can_open_filament(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@onefivefour.ai')->first();

        $this->assertNotNull($user);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_seeded_admin_user_can_open_filament_resource_indexes(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@onefivefour.ai')->firstOrFail();

        foreach ($this->filamentResources as $modelName => $resourceClass) {
            $this->actingAs($user)
                ->get($resourceClass::getUrl())
                ->assertOk("{$modelName} resource index opens.");
        }
    }
}
