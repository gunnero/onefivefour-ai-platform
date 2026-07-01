<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@onefivefour.ai'],
            [
                'name' => 'OneFiveFour Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $this->call([
            OrganizationSeeder::class,
            SiteSeeder::class,
            DepartmentSeeder::class,
            CapabilitySeeder::class,
            EmployeeSeeder::class,
            EmployeeCapabilitySeeder::class,
            PolicySeeder::class,
            PolicyScopeSeeder::class,
            StandardOperatingProcedureSeeder::class,
            SopPolicySeeder::class,
            SopCapabilitySeeder::class,
            AssignmentSeeder::class,
            AuditLogSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}
