<?php

namespace Database\Seeders;

use App\Models\Capability;
use App\Models\Organization;
use App\Models\SopCapability;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Seeder;

class SopCapabilitySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $sop = StandardOperatingProcedure::query()->where('organization_id', $organization->id)->where('sop_key', 'editorial-review')->firstOrFail();
        $capability = Capability::query()->where('name', 'Editing')->firstOrFail();

        SopCapability::query()->updateOrCreate(
            [
                'standard_operating_procedure_id' => $sop->id,
                'capability_id' => $capability->id,
            ],
            [
                'organization_id' => $organization->id,
                'required_level' => 'standard',
            ],
        );
    }
}
