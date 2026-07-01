<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Policy;
use App\Models\PolicyScope;
use Illuminate\Database\Seeder;

class PolicyScopeSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $policy = Policy::query()->where('organization_id', $organization->id)->where('policy_key', 'human-approval-required')->firstOrFail();

        PolicyScope::query()->updateOrCreate(
            [
                'policy_id' => $policy->id,
                'scope_type' => 'organization',
                'scope_id' => null,
            ],
            [
                'organization_id' => $organization->id,
            ],
        );
    }
}
