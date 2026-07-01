# Sprint 002 Phase 001 Review: Business Process Foundation

Project Atlas Sprint 002 Phase 001 added the database, model, relationship, factory, seeder, and test foundation for the Business Process Engine and Work Router. This phase intentionally does not add Process runtime behavior, Work Router execution, Assignment dispatch, Filament/HQ screens, AI execution, article publishing, billing, or Goal Engine behavior.

## What Was Implemented

- Added persistent Business Process Definition, Step, Run, Run Step, Event, and Log records.
- Added Assignment Templates as reusable instructions for work generated from Business Process Steps.
- Added Work Requests, Routing Decisions, and Department Queues as the first Work Router persistence layer.
- Added factories for all new Sprint 002 models.
- Added nullable Sprint 002 references to Assignments.
- Added Eloquent relationships between the new Sprint 002 models and the Sprint 001 Organizational Core models.
- Added idempotent seed data for the sample `Prepare Editorial Package` Business Process.
- Added feature tests for PostgreSQL schema compatibility, model relationship wiring, and seeded sample data.

## Database Tables Created

### `business_process_definitions`

Stores reusable Business Process Definitions scoped to an Organization.

Key concepts:

- Organization ownership
- optional owning Department
- optional manager Employee
- process key and version
- status
- purpose and trigger description
- input schema and completion criteria
- lifecycle timestamps

### `business_process_steps`

Stores ordered reusable Process Steps inside a Business Process Definition.

Key concepts:

- Organization and Process Definition ownership
- Department requirement
- optional SOP
- optional required Capability
- step key, status, and sort order
- expected output
- dependency, approval, retry, failure, and escalation rule payloads

### `assignment_templates`

Stores reusable Assignment creation templates for Business Process Steps.

Key concepts:

- Organization and Process Definition ownership
- Process Step ownership
- Department, optional SOP, and optional required Capability
- title template
- Assignment type and priority
- briefing template
- expected output
- input mapping
- review requirement and due offset

### `business_process_runs`

Stores executions of reusable Business Process Definitions.

Key concepts:

- Organization and Process Definition ownership
- optional Site context
- optional starting User
- optional current Run Step pointer
- run key, title, status, priority, progress, payloads, and lifecycle timestamps

### `business_process_run_steps`

Stores concrete runtime Step instances inside a Business Process Run.

Key concepts:

- Organization and Process Run ownership
- source Business Process Step
- optional Assignment pointer
- Department, optional Employee, optional SOP, and optional required Capability
- status, sort order, attempt number, approval status, payloads, and lifecycle timestamps

### `process_events`

Stores important Business Process events for auditability and operational history.

Key concepts:

- Organization ownership
- optional Process Definition, Run, Run Step, and Assignment references
- optional actor identity
- event type, event key, summary, payload, and occurrence timestamp

### `process_logs`

Stores technical or diagnostic Process logs attached to Process execution records.

Key concepts:

- Organization and Process Run ownership
- optional Run Step, Process Event, and Assignment references
- log level, message, context payload, and created timestamp

### `work_requests`

Stores router input records. A Work Request is not an Assignment.

Key concepts:

- Organization ownership
- optional Site context
- Department requirement
- optional Process Definition, Run, Run Step, Assignment Template, SOP, and required Capability
- source type and source id
- title, Assignment type, priority, status, routing strategy, briefing, expected output, and payload
- optional Assignment pointer after dispatch
- blocked, failed, escalation, routing, dispatch, cancellation, and due timestamps

### `routing_decisions`

Stores auditable Work Router decisions.

Key concepts:

- Organization, Work Request, and Department ownership
- optional Site, Assignment, and selected Employee
- strategy and status
- candidate and eligibility counts
- candidate snapshot and eligibility result payloads
- decision, failure, and manager override fields

### `department_queues`

Stores Department-level routing queues.

Key concepts:

- Organization and Department ownership
- optional Site context
- queue key and name
- status
- default routing strategy
- workload counters
- optional last selected Employee
- pause fields

## Relationships Added

### New Model Relationships

- `BusinessProcessDefinition` belongs to Organization, owning Department, and manager Employee.
- `BusinessProcessDefinition` has many Steps, Assignment Templates, Runs, Work Requests, and Process Events.
- `BusinessProcessStep` belongs to Organization, Business Process Definition, Department, SOP, and required Capability.
- `BusinessProcessStep` has many Assignment Templates and Run Steps.
- `AssignmentTemplate` belongs to Organization, Business Process Definition, Business Process Step, Department, SOP, and required Capability.
- `AssignmentTemplate` has many Work Requests.
- `BusinessProcessRun` belongs to Organization, Business Process Definition, Site, and current Run Step.
- `BusinessProcessRun` has many Run Steps, Assignments, Work Requests, Process Events, and Process Logs.
- `BusinessProcessRunStep` belongs to Organization, Business Process Run, Business Process Step, Assignment, Department, Employee, SOP, and required Capability.
- `BusinessProcessRunStep` has many Work Requests, Assignments, Process Events, and Process Logs.
- `ProcessEvent` belongs to Organization, Process Definition, Process Run, Process Run Step, and Assignment.
- `ProcessEvent` has many Process Logs.
- `ProcessLog` belongs to Organization, Process Run, Process Run Step, Process Event, and Assignment.
- `WorkRequest` belongs to Organization, Site, Department, Process Definition, Process Run, Process Run Step, Assignment Template, SOP, required Capability, and Assignment.
- `WorkRequest` has many Routing Decisions.
- `RoutingDecision` belongs to Organization, Work Request, Department, Site, Assignment, and selected Employee.
- `DepartmentQueue` belongs to Organization, Department, Site, and last selected Employee.

### Existing Core Model Relationships

- `Organization` now exposes Business Process Definitions, Steps, Assignment Templates, Runs, Run Steps, Process Events, Process Logs, Work Requests, Routing Decisions, and Department Queues.
- `Site` now exposes Business Process Runs, Work Requests, Routing Decisions, and Department Queues.
- `Department` now exposes owned Business Process Definitions, Steps, Assignment Templates, Run Steps, Work Requests, Routing Decisions, and Department Queues.
- `Employee` now exposes managed Business Process Definitions, Run Steps, selected Routing Decisions, and last selected Department Queues.
- `Capability` now exposes Business Process Steps, Assignment Templates, Run Steps, and Work Requests where it is the required Capability.
- `StandardOperatingProcedure` now exposes Business Process Steps, Assignment Templates, Run Steps, and Work Requests.
- `Assignment` now exposes Business Process Run, Business Process Run Step, Work Request, Routing Decision, Process Events, and Process Logs.

## Assignment Table Changes

Assignments received four nullable references:

- `business_process_run_id`
- `business_process_run_step_id`
- `work_request_id`
- `routing_decision_id`

These references let future runtime behavior trace an Assignment back to the Business Process Run, concrete Run Step, Work Request, and Routing Decision that produced it. They are nullable so existing manually-created Assignments and Sprint 001 seed data remain valid.

No Assignment lifecycle behavior was changed in this phase.

## Seed Data Added

Added `BusinessProcessSeeder` and wired it into `DatabaseSeeder` after SOP and Capability setup.

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

Seeded one Assignment Template per Process Step.

Seeded Department Queues for:

- Research
- Writing
- Localization
- Trust & Safety
- SEO
- Editorial
- Operations

## Model Relationship Summary

The implemented model graph follows the Sprint 002 target architecture at the persistence level:

```text
Business Process Definition
  -> Business Process Step
  -> Assignment Template
  -> Work Request
  -> Routing Decision
  -> Assignment
```

The execution side is represented but not automated:

```text
Business Process Definition
  -> Business Process Run
  -> Business Process Run Step
  -> Work Request
  -> Routing Decision
  -> Assignment
```

Employees remain executors of Assignments. Business Processes and Work Requests do not directly perform work.

## Assumptions Made

- Business Processes are always scoped to an Organization.
- Assignment Templates are owned by Process Steps and are reusable across future Process Runs.
- Work Requests may originate from Business Processes now and from humans, APIs, or future integrations later.
- Work Requests and Assignments are distinct records.
- Department Queues can exist without Site scope when routing is Organization-wide.
- SOP references are optional for Process Steps, Assignment Templates, Run Steps, and Work Requests.
- Capabilities remain the existing global Capability catalog from Sprint 001.
- The sample process should remain generic and should not couple the engine to article publishing or Razbudise.

## Compromises Made

- Status values remain strings for now, matching the current codebase style. Shared enums or value objects should be revisited before runtime behavior expands.
- Rule payloads such as dependency, approval, retry, failure, escalation, candidate snapshot, and eligibility results are stored as JSON for flexibility at this foundation stage.
- `business_process_runs.current_run_step_id` is stored as a nullable indexed pointer without adding runtime behavior around it in this phase.
- Work Request and Assignment records can reference each other after dispatch, but ownership and synchronization rules are intentionally deferred to the runtime implementation.
- Department Queue counters are persisted as simple integer fields, but no runtime process updates them yet.

## Architectural Questions For Review

- Should Business Process status values become centralized enums before Sprint 002 runtime services are implemented?
- Should Work Request status values and Assignment status values remain separate, or should there be a shared lifecycle vocabulary?
- Should Capability remain global, or should Organization-scoped Capability variants be introduced before multi-Organization usage grows?
- Should `ProcessEvent` remain separate from Sprint 001 `Activity` and `AuditLog`, or should future runtime behavior emit all three through a single domain-event pipeline?
- Should Department Queues be Organization-wide by default, or should Site-specific queues be required for some Organizations?
- Should Process Logs be operator-visible in HQ, or reserved for diagnostics and support?
- Should current Run Step be a denormalized pointer on `business_process_runs`, or derived from Run Step statuses during runtime?

## Intentionally Deferred

- Process Run service
- Work Router execution
- Assignment dispatch
- Assignment advancement
- blocked, failed, and cancelled runtime behavior
- approval gate runtime behavior
- Reassignment Events
- Employee Workload Snapshots
- Filament resources
- HQ dashboard changes
- Activity and Audit Log emission for Process events
- OpenAI integration
- real AI generation
- Razbudise integration
- article publishing
- billing
- Goal Engine implementation
- AI-assisted routing
- advanced memory or vector search
- external notifications

## Verification

Latest passing results captured on July 1, 2026:

| Command | Result |
| --- | --- |
| `php artisan migrate:fresh --seed` | Passed on PostgreSQL |
| `php artisan test` | Passed: 21 tests, 388 assertions |
| `./vendor/bin/pint --test` | Passed |
| `npm run build` | Passed: Vite production build completed |
| `composer validate` | Passed: `./composer.json is valid` |
| `git diff --check` | Passed |

