<?php

namespace Database\Seeders;

use App\Models\Capability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CapabilitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Research', 'Writing', 'Localization', 'SEO', 'Fact Checking', 'Editing'] as $name) {
            Capability::query()->updateOrCreate(
                ['capability_key' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => "{$name} capability for Employee work.",
                    'category' => 'organizational-core',
                    'status' => 'active',
                    'metadata' => [],
                ],
            );
        }
    }
}
