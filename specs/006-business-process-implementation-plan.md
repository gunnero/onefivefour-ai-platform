# Business Process Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Sprint 002 Business Process Engine and Work Router foundations without real AI execution, Goal Engine behavior, publishing integration, or external notifications.

**Architecture:** Business Processes orchestrate work by creating Process Runs and Run Steps. Run Steps create Work Requests from Assignment Templates, and the Work Router creates Assignments through deterministic routing. Employees execute Assignments through the existing Assignment lifecycle, and Process Runs advance from Assignment outcomes.

**Tech Stack:** Laravel 13, PostgreSQL, Eloquent models, service classes, Filament admin/HQ resources, PHPUnit feature tests, existing Activity and Audit Log services.

---

## Source Specifications

Use these documents as the implementation contract:

- `docs/018-domain-language.md`
- `docs/020-sprint-001-review.md`
- `specs/004-business-process-engine.md`
- `specs/005-work-router.md`
- `docs/014-decisions.md`

Important boundary: `specs/005-work-router.md` is authoritative for assignment dispatch. Process Run Steps create Work Requests. Business Processes do not choose Employees directly.

## Scope Guardrails

Sprint 002 must not implement:

- OpenAI
- real AI generation
- Razbudise integration
- article publishing
- billing
- Goal Engine implementation
- AI-assisted routing
- advanced memory/vector search
- external notifications
- real-time queue optimization
- complex visual workflow builder

Sprint 002 may seed a sample `Prepare Editorial Package` process, but that sample must remain generic orchestration data. It must not publish articles or call any external system.

## Implementation Order

Implement these records first:

1. Business Process Definition
2. Business Process Step
3. Assignment Template
4. Business Process Run
5. Business Process Run Step
6. Process Event
7. Process Log
8. Work Request
9. Routing Decision
10. Department Queue

Keep these documented but optional/deferred unless required for simple routing:

- Reassignment Event
- Employee Workload Snapshot

For `least_busy` routing in Sprint 002, calculate simple workload from existing Assignments instead of requiring `employee_workload_snapshots`.

## Future File Map

This plan describes files a later implementation should create or modify. Do not create these application files during this specification-only phase.

### Database And Models

- Create migrations for:
  - `business_process_definitions`
  - `business_process_steps`
  - `assignment_templates`
  - `business_process_runs`
  - `business_process_run_steps`
  - `process_events`
  - `process_logs`
  - `work_requests`
  - `routing_decisions`
  - `department_queues`
- Modify the existing `assignments` table to reference:
  - `business_process_run_id` nullable
  - `business_process_run_step_id` nullable
  - `work_request_id` nullable
  - `routing_decision_id` nullable
- Create Eloquent models under `app/Models/` for each new table.
- Update existing model relationships on `Organization`, `Department`, `Employee`, `Assignment`, `Site`, `Capability`, and `StandardOperatingProcedure`.

### Services

- Create `app/Services/BusinessProcess/ProcessRunService.php`.
- Create `app/Services/BusinessProcess/WorkRequestFactory.php`.
- Create `app/Services/BusinessProcess/WorkRouter.php`.
- Create `app/Services/BusinessProcess/AssignmentDispatchService.php`.
- Create `app/Services/BusinessProcess/ProcessEventRecorder.php`.
- Reuse existing `AssignmentLifecycleService`, `ActivityService`, and `AuditLogService`.

### Seeders

- Create or extend a Sprint 002 seeder for `Prepare Editorial Package`.
- Keep the sample connected to existing Organization, Departments, Employees, Capabilities, Policies, SOPs, Activity, and Audit Logs.

### Filament / HQ

- Create resources for Process Definitions, Process Runs, Work Requests, Routing Decisions, and Department Queues.
- Add HQ dashboard sections for current Process Runs, blocked/failed runs, active Work Requests, failed routing, and recent Process Events.
- Keep Reassignment Events and Employee Workload Snapshots out of the first HQ build unless they become necessary.

### Tests

- Add feature tests for schema, relationships, seed data, Process Run behavior, Work Router behavior, Assignment dispatch, Assignment completion advancement, blocked/failed/cancelled behavior, HQ loading, Activity, and Audit Logs.

## Phase 1: Database Migrations And Models

Goal: Add the minimum durable data model for Business Processes and deterministic routing.

- [ ] Create migrations for the ten first-build tables listed in Implementation Order.
- [ ] Add Organization foreign keys to every new table.
- [ ] Add Department, Site, Employee, Capability, SOP, Assignment, Process Run, Process Run Step, Work Request, and Routing Decision foreign keys where specified in `specs/004` and `specs/005`.
- [ ] Add status columns as strings using the exact status values from `specs/004` and `specs/005`.
- [ ] Add JSON columns only for flexible inputs, outputs, metadata, assignment briefing templates, dependency rules, candidate snapshots, and eligibility results.
- [ ] Add indexes for Organization/status, Process Run/status, Run Step/status, Work Request/status, Department/status, Assignment references, and occurred/requested timestamps.
- [ ] Add nullable process/router references to the existing `assignments` table.
- [ ] Create model classes for all first-build records.
- [ ] Add Eloquent relationships for Organization ownership and core parent/child links.
- [ ] Add factories for the first-build records where tests need generated data.
- [ ] Do not create migrations, models, factories, or resources for Goals.
- [ ] Do not create Reassignment Event or Employee Workload Snapshot tables in the first pass unless a later phase proves they are required.

Verification:

```bash
php artisan migrate:fresh --seed
php artisan test tests/Feature/BusinessProcessSchemaTest.php
```

Expected result:

```text
PASS
```

## Phase 2: Seed Sample Prepare Editorial Package Process

Goal: Seed one reusable Process Definition that proves Sprint 002 orchestration without external publishing.

- [ ] Seed one active `Prepare Editorial Package` Business Process Definition for the existing Organization.
- [ ] Seed Process Steps in this order: Research, Writing, Localization, Fact Check, SEO, Editor Review, Human Approval, Delivery Ready.
- [ ] Connect each Process Step to the correct Department from Sprint 001 seed data.
- [ ] Connect each Process Step to the required Capability from Sprint 001 seed data.
- [ ] Create one Assignment Template per Process Step.
- [ ] Set dependency rules so each step waits for the previous step to complete.
- [ ] Set approval behavior for Human Approval and any Policy-sensitive Fact Check or Editor Review cases.
- [ ] Seed Department Queues for the Departments used by the process with deterministic default routing strategies.
- [ ] Keep seeded data generic and reusable for future Organizations.
- [ ] Do not seed article publishing, Razbudise integration, CMS payloads, or Goal records.

Verification:

```bash
php artisan migrate:fresh --seed
php artisan test tests/Feature/PrepareEditorialPackageSeederTest.php
```

Expected result:

```text
PASS
```

## Phase 3: Process Run Service

Goal: Start a Process Run from an active Process Definition and create Run Steps.

- [ ] Add tests for starting a Process Run from `Prepare Editorial Package`.
- [ ] Implement `ProcessRunService::start`.
- [ ] Require an active Process Definition before a run can start.
- [ ] Create the Process Run with Organization, optional Site, Process Definition, title, input payload, priority, and `running` status.
- [ ] Create one Process Run Step per active Process Step.
- [ ] Mark the first dependency-free Run Step as `ready`.
- [ ] Mark dependent Run Steps as `waiting_for_dependency`.
- [ ] Record `process_run_started` and `run_step_ready` Process Events.
- [ ] Record Process Logs for run creation and dependency evaluation.
- [ ] Create Activity and Audit Logs for Process Run start.
- [ ] Do not create Assignments directly from the Process Run service.

Verification:

```bash
php artisan test tests/Feature/ProcessRunServiceTest.php
```

Expected result:

```text
PASS
```

## Phase 4: Work Request Creation

Goal: Convert ready Run Steps into Work Requests using Assignment Templates.

- [ ] Add tests for Work Request creation from a ready Run Step.
- [ ] Implement `WorkRequestFactory::createFromRunStep`.
- [ ] Copy Organization, optional Site, Department, required Capability, optional SOP, Assignment Template, title, assignment type, priority, briefing, expected output, input payload, review flag, review path, and due date from the Run Step and Assignment Template.
- [ ] Link the Work Request to Business Process Definition, Process Run, Process Run Step, and Assignment Template.
- [ ] Set Work Request status to `pending`.
- [ ] Record a Process Event for Work Request creation.
- [ ] Record Process Log, Activity, and Audit Log entries for important Work Request creation.
- [ ] Keep Work Requests separate from Assignments.

Verification:

```bash
php artisan test tests/Feature/WorkRequestCreationTest.php
```

Expected result:

```text
PASS
```

## Phase 5: Work Router Deterministic Routing

Goal: Route Work Requests to eligible Employees without AI-assisted decisions.

- [ ] Add tests for `manual`, `first_available`, `least_busy`, `round_robin`, and `capability_match`.
- [ ] Implement `WorkRouter::route`.
- [ ] Load Candidate Employees from the same Organization as the Work Request.
- [ ] Filter candidates by target Department.
- [ ] Filter candidates to `active` employment status.
- [ ] Exclude paused, retired, and archived Employees.
- [ ] Require active Employee Capability when the Work Request has a required Capability.
- [ ] Calculate simple workload from existing Assignments for overload checks.
- [ ] Create a Routing Decision for every routing attempt.
- [ ] Store candidate count, eligible count, selected Employee, exclusion reasons, strategy, status, and decision reason.
- [ ] Implement `first_available` with a stable deterministic order.
- [ ] Implement `least_busy` using simple active Assignment counts.
- [ ] Implement `round_robin` using `department_queues.last_selected_employee_id`.
- [ ] Implement `capability_match` as required Capability filtering plus deterministic tie-break.
- [ ] Set `manual` Work Requests to `waiting_for_manual_selection`.
- [ ] On no eligible Employee, set Routing Decision to `no_eligible_employee` and Work Request to `blocked` or `escalated`.
- [ ] Create Activity and Audit Logs for selected, manual-required, blocked, escalated, and failed routing decisions.
- [ ] Do not call OpenAI or any AI-assisted routing service.

Verification:

```bash
php artisan test tests/Feature/WorkRouterTest.php
```

Expected result:

```text
PASS
```

## Phase 6: Assignment Creation From Work Request

Goal: Dispatch an Assignment only after the Router has selected an eligible Employee.

- [ ] Add tests for Assignment creation from a successful Routing Decision.
- [ ] Implement `AssignmentDispatchService::dispatch`.
- [ ] Create the Assignment with Organization, optional Site, Department, selected Employee, optional SOP, title, assignment type, priority, status `pending`, briefing, expected output, input payload, required Capability, review flag, review path, and due date.
- [ ] Set Assignment process/router references: Process Run, Process Run Step, Work Request, Routing Decision.
- [ ] Set Work Request status to `assignment_created`.
- [ ] Link Work Request and Routing Decision back to the created Assignment.
- [ ] Set Process Run Step status to `assignment_created`.
- [ ] Record `assignment_created` Process Event.
- [ ] Record Process Log, Activity, and Audit Log entries for dispatch.
- [ ] Confirm the Business Process layer never writes `employee_id` directly except through the Router-selected Assignment.

Verification:

```bash
php artisan test tests/Feature/AssignmentDispatchFromWorkRequestTest.php
```

Expected result:

```text
PASS
```

## Phase 7: Assignment Completion Advances Process Run

Goal: Use the existing Assignment lifecycle to advance dependent Process Run Steps.

- [ ] Add tests where Research Assignment completion makes Writing ready.
- [ ] Add tests where the final Delivery Ready Assignment completion completes the Process Run.
- [ ] Extend the Assignment completion path so a completed Assignment updates the linked Process Run Step.
- [ ] Copy Assignment output payload to the Run Step output payload when present.
- [ ] Set the completed Run Step status to `completed` unless an approval gate requires `waiting_for_approval`.
- [ ] Re-evaluate dependent Run Steps after completion.
- [ ] Create Work Requests for newly ready Run Steps.
- [ ] Keep Process Run status as `running` or `waiting_for_assignment` while downstream work exists.
- [ ] Set Process Run status to `completed` when all required Run Steps are completed.
- [ ] Record Process Events, Process Logs, Activity, and Audit Logs for advancement and completion.

Verification:

```bash
php artisan test tests/Feature/ProcessRunAdvancementTest.php
```

Expected result:

```text
PASS
```

## Phase 8: Blocked, Failed, And Cancelled Behavior

Goal: Make process/runtime failure modes explicit, visible, and auditable.

- [ ] Add tests for blocked Assignment behavior.
- [ ] Add tests for failed Assignment behavior.
- [ ] Add tests for Work Request cancellation before dispatch.
- [ ] Add tests for Process Run cancellation after dispatch.
- [ ] When an Assignment becomes `blocked`, set the linked Run Step to `blocked` and Process Run to `blocked` unless another independent Run Step can continue.
- [ ] When an Assignment becomes `failed`, apply the Run Step failure rule and set Run Step and Process Run status to `failed` or `blocked` as appropriate.
- [ ] When a Work Request is cancelled before dispatch, cancel pending Routing Decisions and do not create an Assignment.
- [ ] When a Process Run is cancelled, cancel eligible pending Run Steps, Work Requests, Routing Decisions, and active Assignments allowed by the Assignment lifecycle.
- [ ] Preserve completed Assignments as completed.
- [ ] Record cancellation, failure, blocker, and escalation reasons.
- [ ] Create Process Events, Process Logs, Activity, and Audit Logs for every important blocked, failed, or cancelled transition.

Verification:

```bash
php artisan test tests/Feature/BusinessProcessFailureModesTest.php
```

Expected result:

```text
PASS
```

## Phase 9: Filament Resources And HQ Views

Goal: Make Sprint 002 operational state visible in HQ.

- [ ] Create Filament resources for Business Process Definitions.
- [ ] Create Filament resources for Business Process Runs.
- [ ] Create Filament resources for Work Requests.
- [ ] Create Filament resources for Routing Decisions.
- [ ] Create Filament resources for Department Queues.
- [ ] Add relation managers or detail sections for Process Steps, Assignment Templates, Run Steps, Process Events, Process Logs, related Assignments, and Routing Decisions.
- [ ] Add HQ dashboard sections for active Process Runs, current Run Step, progress, related Assignments, recent Process Events, failed/blocked runs, active Work Requests, failed routing, and Department Queue pressure.
- [ ] Keep UI labels canonical: Business Process, Process Definition, Process Run, Run Step, Assignment Template, Work Request, Work Router, Assignment, Employee, Activity, Audit Log.
- [ ] Do not use Task as the product term for Assignment.
- [ ] Do not add Goal UI in Sprint 002.

Verification:

```bash
php artisan test tests/Feature/BusinessProcessFilamentTest.php
php artisan test tests/Feature/HqBusinessProcessDashboardTest.php
```

Expected result:

```text
PASS
```

## Phase 10: Tests And Regression Gate

Goal: Prove Sprint 002 behavior without relying on a dirty workspace.

- [ ] Add schema and relationship tests for all first-build tables.
- [ ] Add seed tests for `Prepare Editorial Package`.
- [ ] Add Process Run service tests.
- [ ] Add Work Request creation tests.
- [ ] Add Work Router deterministic routing tests.
- [ ] Add Assignment dispatch tests.
- [ ] Add Assignment completion advancement tests.
- [ ] Add blocked, failed, and cancelled behavior tests.
- [ ] Add Activity and Audit Log tests for Process Run, Work Request, Routing Decision, Assignment dispatch, advancement, blocker, failure, and cancellation events.
- [ ] Add HQ/Filament access and data-loading tests.
- [ ] Run the full backend and frontend verification gate.

Verification:

```bash
php artisan test
./vendor/bin/pint
npm run build
composer validate
```

Expected results:

```text
PASS
```

```text
./composer.json is valid
```

## Phase 11: Documentation Updates

Goal: Make Sprint 002 understandable after implementation.

- [ ] Update `README.md` only if setup, seeded admin use, or module list changes.
- [ ] Update `docs/006-database.md` with the implemented Sprint 002 table names and relationships.
- [ ] Update `docs/009-roadmap.md` with Sprint 002 implementation status.
- [ ] Update `docs/020-sprint-001-review.md` only if it needs a forward link to Sprint 002 notes.
- [ ] Add a Sprint 002 review document after implementation, such as `docs/021-sprint-002-review.md`.
- [ ] Document the final verification commands and results.
- [ ] Keep `docs/014-decisions.md` ADR-0004 unchanged unless Goal scope changes in a future sprint.
- [ ] Do not document Goal Engine as implemented.

Verification:

```bash
rg -n "Goal Engine implementation|OpenAI|AI-assisted routing|article publishing|Razbudise integration" README.md docs specs
git diff --check
```

Expected result:

```text
Only explicit out-of-scope or future-scope references are present.
No whitespace errors.
```

## Deferred Items

These remain documented for future implementation but should not block the first Sprint 002 build:

- Reassignment Events: defer until reassignment is required by real operator workflow or failure handling.
- Employee Workload Snapshots: defer while simple live Assignment counts are enough for `least_busy`.
- Goal Engine: defer entirely. ADR-0004 establishes future direction only.
- Advanced routing: defer `performance_weighted`, `cost_aware`, `deadline_aware`, and `AI-assisted`.
- External notifications: defer until the platform has an approved notification model.

## Completion Criteria

Sprint 002 implementation is complete when:

- Business Process Definitions can be stored and viewed.
- Process Steps and Assignment Templates can define repeatable work.
- A Process Run can start from `Prepare Editorial Package`.
- Run Steps are created and dependency state is visible.
- A ready Run Step creates a Work Request.
- The Work Router deterministically creates a Routing Decision.
- A successful Routing Decision creates an Assignment.
- Assignment completion advances the Process Run.
- Blocked, failed, and cancelled states are explicit and auditable.
- HQ shows Process Runs, Work Requests, Routing Decisions, Department Queues, related Assignments, recent Process Events, and failed/blocked states.
- Activity and Audit Logs exist for important Sprint 002 events.
- Reassignment Events and Employee Workload Snapshots are either clearly deferred or implemented only if required by simple routing.
- No application behavior calls OpenAI, performs real AI generation, publishes articles, integrates Razbudise, bills customers, implements Goals, performs AI-assisted routing, or uses advanced memory/vector search.
- `php artisan test`, `./vendor/bin/pint`, `npm run build`, `composer validate`, and `git diff --check` pass from the committed implementation state.
