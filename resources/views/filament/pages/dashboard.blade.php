<x-filament-panels::page>
    @if ($organization)
        <div class="space-y-6">
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-medium uppercase tracking-wide text-primary-600 dark:text-primary-400">
                            Organization Summary
                        </p>
                        <div>
                            <h2 class="text-2xl font-semibold tracking-normal text-gray-950 dark:text-white">
                                {{ $organization->name }}
                            </h2>
                            <p class="mt-1 max-w-3xl text-sm text-gray-600 dark:text-gray-300">
                                {{ $organization->summary }}
                            </p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-4 lg:text-right">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="font-medium capitalize text-gray-950 dark:text-white">{{ str_replace('_', ' ', $organization->status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Primary Domain</dt>
                            <dd class="font-medium text-gray-950 dark:text-white">{{ $organization->primary_domain ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Timezone</dt>
                            <dd class="font-medium text-gray-950 dark:text-white">{{ $organization->timezone }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Locale</dt>
                            <dd class="font-medium text-gray-950 dark:text-white">{{ $organization->locale }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    @foreach ([
                        'Sites' => $organization->sites_count,
                        'Departments' => $organization->departments_count,
                        'Employees' => $organization->employees_count,
                        'Active Assignments' => $organization->active_assignments_count,
                        'Active Policies' => $organization->active_policies_count,
                        'Active SOPs' => $organization->active_sops_count,
                    ] as $label => $value)
                        <div class="rounded-md border border-gray-200 px-4 py-3 dark:border-gray-700">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Departments Overview</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Department</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Purpose</th>
                                <th class="px-6 py-3 text-right">Employees</th>
                                <th class="px-6 py-3 text-right">Active Assignments</th>
                                <th class="px-6 py-3 text-right">Active SOPs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($departments as $department)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $department->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ str_replace('_', ' ', $department->status) }}</td>
                                    <td class="min-w-80 px-6 py-4 text-gray-600 dark:text-gray-300">{{ $department->purpose }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $department->employees_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $department->active_assignments_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $department->active_sops_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="6">No Departments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Employees Overview</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Role Title</th>
                                <th class="px-6 py-3">Department</th>
                                <th class="px-6 py-3">Employment Status</th>
                                <th class="px-6 py-3">Capability Summary</th>
                                <th class="px-6 py-3 text-right">Current Assignments</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $employee->full_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $employee->role_title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $employee->department?->name ?? 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ str_replace('_', ' ', $employee->employment_status) }}</td>
                                    <td class="min-w-64 px-6 py-4 text-gray-600 dark:text-gray-300">
                                        {{ $employee->capabilities->pluck('name')->join(', ') ?: 'No active Capabilities' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $employee->current_assignments_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="6">No Employees found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Current Assignments</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Assignment</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Priority</th>
                                <th class="px-6 py-3">Department</th>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Site</th>
                                <th class="px-6 py-3">SOP</th>
                                <th class="px-6 py-3">Due Date</th>
                                <th class="px-6 py-3">Escalation Flag</th>
                                <th class="px-6 py-3">Review Flag</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($assignments as $assignment)
                                <tr>
                                    <td class="min-w-72 px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $assignment->title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ str_replace('_', ' ', $assignment->status) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ $assignment->priority }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $assignment->department?->name ?? 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $assignment->employee?->full_name ?? 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $assignment->site?->name ?? 'No Site' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $assignment->standardOperatingProcedure?->title ?? 'No SOP' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                        {{ $assignment->due_at?->timezone($organization->timezone)->format('M j, Y H:i') ?? 'No due date' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                        {{ $assignment->escalation_required ? 'Escalation Required' : 'No Escalation' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                        {{ $assignment->review_required ? 'Review Required' : 'No Review' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="10">No Current Assignments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Activity Feed</h2>
                </div>

                <ol class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($activities as $activity)
                        @php
                            $related = collect([
                                $activity->employee?->full_name,
                                $activity->assignment?->title,
                                $activity->department?->name,
                            ])->filter()->join(' / ');
                        @endphp
                        <li class="grid gap-3 px-6 py-4 text-sm lg:grid-cols-[12rem_12rem_1fr]">
                            <time class="text-gray-500 dark:text-gray-400">
                                {{ $activity->occurred_at->timezone($organization->timezone)->format('M j, Y H:i') }}
                            </time>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $activity->activity_type }}</span>
                            <div>
                                <p class="font-medium text-gray-950 dark:text-white">{{ $activity->title }}</p>
                                @if ($related)
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $related }}</p>
                                @endif
                                @if ($activity->audit_log_id)
                                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Audit Log #{{ $activity->audit_log_id }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No Activity found.</li>
                    @endforelse
                </ol>
            </section>
        </div>
    @else
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Organization Summary</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">No Organization data is available.</p>
        </section>
    @endif
</x-filament-panels::page>
