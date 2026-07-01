<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->updateOrCreate(
            ['slug' => 'onefivefour'],
            [
                'name' => 'OneFiveFour',
                'legal_name' => 'OneFiveFour',
                'status' => 'active',
                'timezone' => 'Europe/Skopje',
                'locale' => 'en',
                'primary_domain' => 'onefivefour.ai',
                'summary' => 'AI-native operating organization for digital publishing companies.',
                'metadata' => [],
            ],
        );
    }
}
