<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();

        Site::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'slug' => 'razbudise-mk',
            ],
            [
                'name' => 'Razbudise.mk',
                'status' => 'active',
                'site_type' => 'publication',
                'primary_domain' => 'razbudise.mk',
                'default_locale' => 'mk',
                'timezone' => 'Europe/Skopje',
                'audience_notes' => 'Macedonian digital publishing audience.',
                'editorial_context' => 'First publication context for OneFiveFour operational modeling.',
                'metadata' => [],
            ],
        );
    }
}
