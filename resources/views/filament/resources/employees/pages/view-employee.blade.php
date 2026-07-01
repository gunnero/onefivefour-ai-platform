<x-filament-panels::page>
    @php
        $timezone = $organization?->timezone ?? config('app.timezone');
        $initials = collect(explode(' ', $employee->full_name))
            ->filter()
            ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->join('');
    @endphp

    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    @if ($employee->avatar_url)
                        <img
                            src="{{ $employee->avatar_url }}"
                            alt="{{ $employee->full_name }} avatar"
                            class="h-20 w-20 rounded-lg object-cover"
                        >
                    @else
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-2xl font-semibold text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                            {{ $initials }}
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-primary-600 dark:text-primary-400">Employee Profile</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">Identity</p>
                            <h2 class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $employee->full_name }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $employee->role_title }}</p>
                        </div>

                        <dl class="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Employee Code</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $employee->employee_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Department</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $employee->department?->name ?? 'Unassigned' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Manager</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $employee->manager?->full_name ?? 'No manager assigned' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $employee->employment_status }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Organization</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $organization?->name ?? 'No Organization' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Approval Authority</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $employee->approval_authority_level ?? 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Bio</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $employee->bio ?: 'No bio available.' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Mission</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $employee->mission ?: 'No mission available.' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Job Description</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $employee->job_description ?: 'No job description available.' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Stats</h2>

            <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                @foreach ($stats as $label => $value)
                    <div class="rounded-md border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Capabilities</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Active Capability</th>
                            <th class="px-6 py-3">Capability Level</th>
                            <th class="px-6 py-3">Granted At</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($capabilities as $employeeCapability)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-950 dark:text-white">
                                    {{ $employeeCapability->capability?->name ?? 'Unknown Capability' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $employeeCapability->level ?? 'Not set' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $employeeCapability->granted_at?->timezone($timezone)->format('M j, Y H:i') ?? 'Not recorded' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $employeeCapability->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="4">No active Capabilities found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Assignments</h2>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($assignmentSections as $sectionTitle => $assignments)
                    <div class="space-y-3 p-6">
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $sectionTitle }}</h3>

                        <div class="overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-3">Assignment</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Priority</th>
                                        <th class="px-4 py-3">Department</th>
                                        <th class="px-4 py-3">Site</th>
                                        <th class="px-4 py-3">SOP</th>
                                        <th class="px-4 py-3">Due Date</th>
                                        <th class="px-4 py-3">Escalation Flag</th>
                                        <th class="px-4 py-3">Review Flag</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse ($assignments as $assignment)
                                        <tr>
                                            <td class="min-w-72 px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $assignment->title }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">{{ $assignment->status }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">{{ $assignment->priority }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">{{ $assignment->department?->name ?? 'Unassigned' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">{{ $assignment->site?->name ?? 'No Site' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">{{ $assignment->standardOperatingProcedure?->title ?? 'No SOP' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                                {{ $assignment->due_at?->timezone($timezone)->format('M j, Y H:i') ?? 'No due date' }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                                {{ $assignment->escalation_required ? 'Escalation Required' : 'No Escalation' }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                                {{ $assignment->review_required ? 'Review Required' : 'No Review' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400" colspan="9">No {{ $sectionTitle }} found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Activity</h2>
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
                            {{ $activity->occurred_at->timezone($timezone)->format('M j, Y H:i') }}
                        </time>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $activity->activity_type }}</span>
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">{{ $activity->title }}</p>
                            @if ($activity->body)
                                <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $activity->body }}</p>
                            @endif
                            @if ($related)
                                <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $related }}</p>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No Activity found.</li>
                @endforelse
            </ol>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Audit History</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Time</th>
                            <th class="px-6 py-3">Event Type</th>
                            <th class="px-6 py-3">Action</th>
                            <th class="px-6 py-3">Summary</th>
                            <th class="px-6 py-3">Audit Log</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($auditLogs as $auditLog)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                    {{ $auditLog->occurred_at->timezone($timezone)->format('M j, Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $auditLog->event_type }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $auditLog->action }}</td>
                                <td class="min-w-80 px-6 py-4 text-gray-600 dark:text-gray-300">{{ $auditLog->summary }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">Audit Log #{{ $auditLog->id }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="5">No Audit Logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
