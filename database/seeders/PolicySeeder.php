<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Policy;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();

        Policy::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'policy_key' => 'human-approval-required',
                'version' => 1,
            ],
            [
                'title' => 'Human Approval Required',
                'category' => 'editorial-governance',
                'status' => 'active',
                'body' => 'Employees may prepare drafts, recommendations, and metadata, but publishing decisions require human approval.',
                'effective_from' => now(),
                'effective_to' => null,
                'metadata' => [],
            ],
        );
    }
}
