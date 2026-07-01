<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Policy;
use App\Models\SopPolicy;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Seeder;

class SopPolicySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $sop = StandardOperatingProcedure::query()->where('organization_id', $organization->id)->where('sop_key', 'editorial-review')->firstOrFail();
        $policy = Policy::query()->where('organization_id', $organization->id)->where('policy_key', 'human-approval-required')->firstOrFail();

        SopPolicy::query()->updateOrCreate(
            [
                'standard_operating_procedure_id' => $sop->id,
                'policy_id' => $policy->id,
            ],
            [
                'organization_id' => $organization->id,
            ],
        );
    }
}
