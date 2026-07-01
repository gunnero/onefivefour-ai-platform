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

            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="space-y-2">
                    <p class="text-sm font-medium uppercase tracking-wide text-primary-600 dark:text-primary-400">
                        Operations Center
                    </p>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Quick Stats</h2>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
                    @foreach ([
                        'Running Processes' => $operationsCenter['quickStats']['running_processes'],
                        'Ready Steps' => $operationsCenter['quickStats']['ready_steps'],
                        'Pending Work Requests' => $operationsCenter['quickStats']['pending_work_requests'],
                        'Assignments' => $operationsCenter['quickStats']['assignments'],
                        'Blocked Runs' => $operationsCenter['quickStats']['blocked_runs'],
                        'Failed Runs' => $operationsCenter['quickStats']['failed_runs'],
                        'Waiting Approval' => $operationsCenter['quickStats']['waiting_approval'],
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
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Active Business Processes</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Process Definition</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Progress</th>
                                <th class="px-6 py-3">Started</th>
                                <th class="px-6 py-3">Current Step</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($operationsCenter['activeBusinessProcesses'] as $process)
                                @php
                                    $currentRun = $process->currentProcessRun;
                                @endphp
                                <tr>
                                    <td class="min-w-72 px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $process->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ str_replace('_', ' ', $process->status) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $currentRun?->progress_percent ?? 0 }}%</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                        {{ $currentRun?->started_at?->timezone($organization->timezone)->format('M j, Y H:i') ?? 'Not started' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                        {{ $currentRun?->currentRunStep?->businessProcessStep?->name ?? 'No current step' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="5">No Active Business Processes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Current Process Runs</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Progress</th>
                                <th class="px-6 py-3">Current Department</th>
                                <th class="px-6 py-3">Current Employee</th>
                                <th class="px-6 py-3">Current Assignment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($operationsCenter['currentProcessRuns'] as $run)
                                <tr>
                                    <td class="min-w-72 px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $run->title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ str_replace('_', ' ', $run->status) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $run->progress_percent }}%</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $run->currentRunStep?->department?->name ?? 'No Department' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">
                                        {{ $run->currentRunStep?->employee?->full_name ?? $run->currentRunStep?->assignment?->employee?->full_name ?? 'No Employee' }}
                                    </td>
                                    <td class="min-w-72 px-6 py-4 text-gray-700 dark:text-gray-200">{{ $run->currentRunStep?->assignment?->title ?? 'No Assignment' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="6">No Current Process Runs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Department Queues</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Department Queue</th>
                                <th class="px-6 py-3">Queue Status</th>
                                <th class="px-6 py-3 text-right">Pending Work Requests</th>
                                <th class="px-6 py-3 text-right">Routed</th>
                                <th class="px-6 py-3 text-right">Blocked</th>
                                <th class="px-6 py-3 text-right">Failed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($operationsCenter['departmentQueues'] as $queue)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-950 dark:text-white">{{ $queue->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 capitalize text-gray-700 dark:text-gray-200">{{ str_replace('_', ' ', $queue->status) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $queue->pending_work_requests }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $queue->routed_work_requests }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $queue->blocked_work_requests }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700 dark:text-gray-200">{{ $queue->failed_work_requests }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="6">No Department Queues found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Work Requests</h2>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ([
                        'Pending' => $operationsCenter['workRequestCounts']['pending'],
                        'Routed' => $operationsCenter['workRequestCounts']['routed'],
                        'Waiting Manual' => $operationsCenter['workRequestCounts']['waiting_for_manual_selection'],
                        'Blocked' => $operationsCenter['workRequestCounts']['blocked'],
                        'Cancelled' => $operationsCenter['workRequestCounts']['cancelled'],
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
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Routing Decisions</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Strategy</th>
                                <th class="px-6 py-3">Selected Employee</th>
                                <th class="px-6 py-3">Decision Reason</th>
                                <th class="px-6 py-3">Failure Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($operationsCenter['routingDecisions'] as $decision)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $decision->strategy }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-200">{{ $decision->selectedEmployee?->full_name ?? 'No Employee' }}</td>
                                    <td class="min-w-80 px-6 py-4 text-gray-600 dark:text-gray-300">{{ $decision->decision_reason ?? 'No decision reason' }}</td>
                                    <td class="min-w-80 px-6 py-4 text-gray-600 dark:text-gray-300">{{ $decision->failure_reason ?? 'No failure reason' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400" colspan="4">No Routing Decisions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Operations Feed</h2>
                </div>

                <ol class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($operationsCenter['operationsFeed'] as $feedItem)
                        <li class="grid gap-3 px-6 py-4 text-sm lg:grid-cols-[12rem_10rem_12rem_1fr]">
                            <time class="text-gray-500 dark:text-gray-400">
                                {{ $feedItem['occurred_at']->timezone($organization->timezone)->format('M j, Y H:i') }}
                            </time>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $feedItem['source_label'] }}</span>
                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $feedItem['type'] }}</span>
                            <div>
                                <p class="font-medium text-gray-950 dark:text-white">{{ $feedItem['title'] }}</p>
                                @if ($feedItem['related'])
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $feedItem['related'] }}</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No Operations Feed items found.</li>
                    @endforelse
                </ol>
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
