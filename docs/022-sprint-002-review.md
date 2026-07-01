# Sprint 002 Review: Operations Engine

Project Atlas Sprint 002 built the first working Operations Engine for OneFiveFour AI Platform. The sprint turns the Sprint 001 Organizational Core into observable operational flow: Business Process Definitions can start Process Runs, create Run Steps, create Work Requests, route work deterministically, dispatch Assignments, advance from Assignment outcomes, and expose operational state in HQ.

Sprint 002 stays generic. It does not implement OpenAI, real AI generation, Razbudise integration, article publishing, billing, Brain Engine, Knowledge Engine, or Goal Engine behavior.

## 1. Sprint Goal

Sprint 002 proves that an Organization can coordinate repeatable company work without coupling the platform to one customer, one publishing operation, or one AI provider.

The goal was to implement the Operations Engine foundation:

- Business Processes orchestrate work.
- Work Requests describe work that needs routing.
- The Work Router decides which eligible Employee should receive the work.
- Assignments remain the concrete execution unit.
- Employees execute Assignments.
- Activity, Audit Logs, Process Events, and Process Logs make important runtime state visible and reviewable.

## 2. What Was Implemented

- Added the Business Process persistence foundation: definitions, steps, runs, run steps, events, and logs.
- Added Assignment Templates as reusable work instructions for Process Steps.
- Added Work Requests, Routing Decisions, and Department Queues for deterministic routing.
- Added nullable Operations Engine references to Assignments.
- Added Eloquent relationships between Sprint 002 records and Sprint 001 Organization, Site, Department, Employee, Capability, SOP, Assignment, Activity, and Audit Log records.
- Seeded the sample `Prepare Editorial Package` Business Process.
- Implemented `ProcessRunService` for starting runs, creating run steps, advancement, blocked/failed behavior, and process cancellation.
- Implemented `WorkRequestFactory` for creating Work Requests from ready Run Steps.
- Implemented `WorkRouter` with deterministic routing strategies.
- Implemented `AssignmentDispatchService` for creating Assignments from selected Routing Decisions.
- Implemented `WorkRequestCancellationService` for cancelling undispatched Work Requests.
- Extended the Assignment lifecycle so completed, blocked, and failed process-linked Assignments update linked Process Runs.
- Added the read-only Operations Center projection and HQ dashboard sections.
- Added feature tests for schema, relationships, seed data, services, routing, dispatch, advancement, failure modes, cancellation, and HQ visibility.

## 3. Operations Engine Architecture Summary

Sprint 002 separates orchestration from assignment and assignment from execution.

Business Processes decide what work exists and in what order. They do not directly choose Employees.

The Work Router receives Work Requests and creates auditable Routing Decisions. It uses Organization, Department, Employee status, Capability, workload, and Department Queue context to select an eligible Employee.

Assignments are created only after a Routing Decision selects an eligible Employee. Employees remain the execution layer. Important runtime changes create Process Events, Process Logs, Activity, and Audit Logs so operators can understand what happened and architecture reviewers can trace decisions.

The implemented engine is service-layer driven. It does not run background workers, external notifications, real AI execution, publishing actions, or integration callbacks.

## 4. Business Process Flow

```text
Business Process Definition
-> Process Run
-> Run Step
-> Work Request
-> Work Router
-> Routing Decision
-> Assignment
-> Employee
```

Runtime meaning:

- A Business Process Definition is the reusable versioned blueprint.
- A Process Run is one execution of that blueprint.
- A Run Step is one concrete step inside a Process Run.
- A Work Request is router input created from a ready Run Step and Assignment Template.
- The Work Router evaluates candidates and records a Routing Decision.
- A selected Routing Decision can dispatch an Assignment.
- The selected Employee executes the Assignment through the Assignment lifecycle.
- Assignment completion, blocker, or failure updates the linked Run Step and Process Run.

## 5. Services Implemented

### `ProcessRunService`

Starts active Business Process Definitions, creates Process Runs and Run Steps, marks the first dependency-free Run Step ready, records Process Events and Process Logs, creates Activity and Audit Logs, advances Process Runs from completed Assignments, handles blocked/failed linked Assignments, and cancels Process Runs.

### `WorkRequestFactory`

Creates Work Requests from ready Business Process Run Steps. It copies Organization, Site, Department, Capability, SOP, Process Definition, Process Run, Run Step, Assignment Template, title, assignment type, priority, briefing, expected output, input payload, review fields, due date, and routing strategy.

### `WorkRouter`

Routes pending Work Requests using deterministic strategies:

- `manual`
- `first_available`
- `least_busy`
- `round_robin`
- `capability_match`

It records candidate counts, eligible counts, candidate snapshots, eligibility results, selected Employee, decision reason, failure reason, Activity, and Audit Logs. It does not create Assignments.

### `AssignmentDispatchService`

Creates an Assignment only from a selected Routing Decision when the Work Request is routed and no Assignment already exists. It copies Work Request data into the Assignment, links Work Request, Routing Decision, Run Step, and Assignment records, and records process-specific Process Event and Process Log entries.

### `WorkRequestCancellationService`

Cancels Work Requests before dispatch, cancels pending/evaluating Routing Decisions, records the cancellation reason, and emits Process Event, Process Log, Activity, and Audit Log records.

### `OperationsCenterProjection`

Creates a read-only HQ projection for Operations Center visibility. It aggregates active Business Processes, current Process Runs, Department Queue pressure, Work Request counts, recent Routing Decisions, Quick Stats, and a chronological Operations Feed that merges Process Events and Activity without replacing Activity.

## 6. Database Tables Added

Sprint 002 added these tables:

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

The tables are Organization-scoped where appropriate and include status indexes, relationship indexes, and timestamp indexes for operational views and process history.

## 7. Assignment Table Changes

Assignments now have these nullable references:

- `business_process_run_id`
- `business_process_run_step_id`
- `work_request_id`
- `routing_decision_id`

These references let a generated Assignment be traced back to the Process Run, concrete Run Step, Work Request, and Routing Decision that produced it. They remain nullable so manually created Assignments and Sprint 001 seed data continue to work.

## 8. Seed Data Added

Added `BusinessProcessSeeder` and wired it into the main database seeder.

Seeded Business Process Definition:

- Name: `Prepare Editorial Package`
- Key: `prepare-editorial-package`
- Version: `1`
- Status: `active`
- Owning Department: `Editorial`

Seeded Process Steps:

| Step | Department | Required Capability | Approval Required |
| --- | --- | --- | --- |
| Research | Research | Research | No |
| Writing | Writing | Writing | No |
| Localization | Localization | Localization | No |
| Fact Check | Trust & Safety | Fact Checking | Yes |
| SEO | SEO | SEO | No |
| Editor Review | Editorial | Editing | Yes |
| Human Approval | Editorial | Editing | Yes |
| Delivery Ready | Operations | Editing | No |

Each Process Step has one Assignment Template. Department Queues were seeded for Research, Writing, Localization, Trust & Safety, SEO, Editorial, and Operations.

The seed data remains generic and does not publish articles, call Razbudise, or perform real AI work.

## 9. HQ / Operations Center Changes

HQ remains read-only for Operations Engine visibility.

Added Operations Center sections to the HQ dashboard:

- Quick Stats
- Active Business Processes
- Current Process Runs
- Department Queues
- Work Requests
- Routing Decisions
- Operations Feed

Quick Stats show:

- Running Processes
- Ready Steps
- Pending Work Requests
- Assignments
- Blocked Runs
- Failed Runs
- Waiting Approval

The Operations Feed is a projection that merges Process Events and Activity in chronological order. It does not replace Activity and does not add runtime control buttons.

No UI redesign, editing controls, runtime controls, OpenAI controls, publishing controls, or integration controls were added.

## 10. Test And Verification Results

Sprint 002 added feature coverage for:

- PostgreSQL schema and migration compatibility
- Sprint 002 model relationships
- `Prepare Editorial Package` seed data
- Process Run start behavior
- Run Step readiness and dependency state
- Work Request creation
- Work Router strategies
- no eligible Employee routing failure
- Assignment dispatch
- Assignment completion advancement
- blocked, failed, and cancelled runtime behavior
- Process Events and Process Logs
- Activity and Audit Logs
- Operations Center loading and counts

Latest local verification captured on July 1, 2026:

| Command | Result |
| --- | --- |
| `php artisan test` | Passed: 46 tests, 744 assertions |
| `./vendor/bin/pint` | Passed |
| `npm run build` | Passed: Vite production build completed |
| `composer validate` | Passed: `./composer.json is valid` |
| `git diff --check` | Passed |

## 11. Architecture Debt

- Status values remain strings. Shared enums or value objects should be considered before more runtime states spread.
- Process Event and Process Log creation is duplicated across services. A small recorder service may reduce drift.
- Activity, Audit Logs, Process Events, and Process Logs are coordinated synchronously. A future domain-event pipeline may be cleaner for queues, retries, notifications, and external integrations.
- Operations Center is a dashboard projection, not a full set of Filament resources for Process Definitions, Process Runs, Work Requests, Routing Decisions, and Department Queues.
- Next Work Request creation after a dependent Run Step becomes ready remains an explicit service call. It is not automatically chained in the advancement service.
- Approval gates can pause a Run Step as `waiting_for_approval`, but the full human approval decision workflow is not implemented.
- Department Queue counters exist in storage, but the current Operations Center derives counts from Work Requests rather than treating counters as authoritative.
- `least_busy` uses live Assignment counts. Employee Workload Snapshots remain deferred.
- Process Logs are stored but not yet exposed as a dedicated operator/support view.
- Authorization remains broad for the admin/HQ context and should be revisited before multi-user customer access.

## 12. Intentionally Deferred Work

- OpenAI integration
- real AI generation
- Razbudise integration
- article publishing
- billing
- Brain Engine
- Knowledge Engine
- Goal Engine implementation
- AI-assisted routing
- advanced memory/vector search
- external notifications
- real-time queue optimization
- Reassignment Events
- Employee Workload Snapshots
- complex visual workflow builder
- full human approval workflow
- dedicated Filament CRUD/detail resources for all Operations Engine records

ADR-0004 remains future-scope only: Goals are intended to become the top operational layer later, but Sprint 002 does not implement Goal tables, Goal services, Goal UI, or Goal routing behavior.

## 13. Definition Of Done Checklist

- [x] Business Process Definitions are stored and Organization-scoped.
- [x] Process Steps define repeatable ordered work.
- [x] Assignment Templates define reusable Assignment creation data.
- [x] Process Runs can start from active Business Process Definitions.
- [x] Run Steps are created from active Process Steps.
- [x] First dependency-free Run Step becomes ready.
- [x] Dependent Run Steps wait for dependencies.
- [x] Ready Run Steps can create Work Requests.
- [x] Work Requests remain separate from Assignments.
- [x] Work Router evaluates eligible Employees.
- [x] Work Router records auditable Routing Decisions.
- [x] Deterministic strategies are implemented for manual, first available, least busy, round robin, and capability match.
- [x] Assignment dispatch requires a selected Routing Decision.
- [x] Assignments link back to Process Run, Run Step, Work Request, and Routing Decision.
- [x] Assignment completion advances linked Process Runs.
- [x] blocked, failed, and cancelled behavior is explicit and auditable.
- [x] Activity and Audit Logs are created for important Operations Engine events.
- [x] Process Events and Process Logs are recorded for important process behavior.
- [x] Operations Center shows active processes, current runs, queues, Work Requests, Routing Decisions, feed, and quick stats.
- [x] Seed data includes `Prepare Editorial Package`.
- [x] No OpenAI integration was implemented.
- [x] No real AI generation was implemented.
- [x] No Razbudise integration was implemented.
- [x] No article publishing was implemented.
- [x] No billing was implemented.
- [x] No Brain Engine, Knowledge Engine, or Goal Engine was implemented.
- [x] `php artisan test` passes.
- [x] `./vendor/bin/pint` passes.
- [x] `npm run build` passes.
- [x] `composer validate` passes.
- [x] `git diff --check` passes.
