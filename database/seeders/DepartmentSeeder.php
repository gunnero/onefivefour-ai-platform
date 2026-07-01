<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();

        $departments = [
            'Editorial' => 'Ensure editorial direction, review, and approval readiness.',
            'Research' => 'Find reliable sources and prepare research packages.',
            'Writing' => 'Draft original articles from approved briefs.',
            'Localization' => 'Adapt information into natural Macedonian context.',
            'SEO' => 'Improve search visibility, metadata, and discovery.',
            'Trust & Safety' => 'Verify claims, flag risk, and enforce safety boundaries.',
            'Creative' => 'Prepare creative and media-supporting briefs.',
            'Analytics' => 'Read performance signals and operational metrics.',
            'Operations' => 'Coordinate repeatable work and organizational support.',
        ];

        $sortOrder = 1;

        foreach ($departments as $name => $purpose) {
            Department::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'status' => 'active',
                    'purpose' => $purpose,
                    'sort_order' => $sortOrder++,
                    'metadata' => [],
                ],
            );
        }
    }
}
