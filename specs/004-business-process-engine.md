# Sprint 002: Business Process Engine v0.1

## Status

Draft specification.

This document defines the Business Process Engine for OneFiveFour AI Platform. It uses the canonical language from `docs/018-domain-language.md`.

No Laravel, Filament, Next.js, Docker, migration, model, or application code is part of this document.

## 1. Purpose

Sprint 001 proved that OneFiveFour can represent the Organizational Core: Organizations, Sites, Departments, Employees, Capabilities, Policies, SOPs, Assignments, Activity, and Audit Logs.

Sprint 002 defines how that Organizational Core becomes a working company.

A Business Process is the company operation that coordinates repeatable work across Departments and Employees. It does not perform the work itself. It knows what should happen, in what order, which Department owns each step, which Capability is required, which SOP or Policy applies, when human approval is required, and how progress should be visible.

The Business Process Engine turns an Organization from a static structure into an operating system:

- Organizations provide the business boundary.
- Sites provide optional publication or brand context.
- Departments own the functional areas of work.
- Employees execute Assignments.
- Capabilities define which Employees are eligible for work.
- Policies set rules and approval boundaries.
- SOPs define how repeatable work should be performed.
- Business Processes orchestrate the sequence.
- Activity explains progress in a readable timeline.
- Audit Logs preserve formal evidence of important actions and state changes.

The engine must stay generic. It may later support Razbudise, OFFMI, OneFiveFour internal operations, and future Organizations without coupling the core model to articles, publishing, or any single customer.

## 2. Core Concepts

### Business Process

A Business Process is an end-to-end company operation inside an Organization. It coordinates Departments, Employees, SOPs, Assignments, approval points, retries, failures, escalations, events, Activity, and Audit Logs around a business outcome.

Examples may include editorial package preparation, translation and localization, content refresh, analytics review, customer onboarding, quality review, or internal operations. A Business Process is the product term. A technical workflow engine may support it later, but user-facing language should remain Business Process.

### Process Definition

A Process Definition is the reusable versioned blueprint for a Business Process.

It describes the purpose, trigger, inputs, steps, dependencies, assignment templates, approval gates, retry rules, failure rules, escalation rules, expected outputs, and completion criteria. Process Definitions are not executions. They are templates that can be started many times as Process Runs.

### Process Run

A Process Run is one execution of a Process Definition.

It belongs to an Organization, references the Process Definition version it is executing, stores the run input, tracks current status and progress, and owns the Run Steps, Process Events, Process Logs, generated Assignments, Activity, and Audit Logs for that execution.

### Process Step

A Process Step is a reusable step inside a Process Definition.

It defines the Department, required Capability, optional SOP, Assignment Template, expected output, dependency rules, approval requirement, retry behavior, failure behavior, and escalation behavior for one unit of process orchestration.

### Step Dependency

A Step Dependency defines when a Process Step may begin.

Dependencies may require one or more earlier steps to be completed, approved, skipped, or failed in a handled way. Sprint 002 should support simple prerequisite dependencies first. Complex branching, conditional paths, and visual process design are out of scope.

### Assignment Template

An Assignment Template is reusable Assignment briefing data owned by a Process Step.

It defines the Assignment title pattern, priority, Department, required Capability, optional SOP, expected output, review path, default due offset, and input mapping from Process Run data or previous step outputs. It creates Assignment Instances during a Process Run.

### Assignment Instance

An Assignment Instance is the actual Assignment created from an Assignment Template during a Process Run.

It is assigned to an Employee, has a real Assignment status, can be accepted, started, blocked, moved to review, completed, failed, or cancelled, and remains the canonical work item that Employees execute.

### Approval Gate

An Approval Gate is a process pause that requires a human supervisor or approved Manager decision before the Process Run continues.

Approval Gates may appear after a specific Run Step, before delivery, after a sensitive failure, or when a Policy requires review. Sprint 002 defines the gate behavior and data requirements; it does not build a full human approval product beyond Assignment review status and process pause semantics.

### Retry Rule

A Retry Rule defines whether a failed or blocked Run Step may be attempted again.

It may include maximum attempts, retry reason requirements, retry owner, and whether the same Employee or a different eligible Employee should receive the next Assignment Instance. Sprint 002 should specify retry behavior but may keep execution manual or mock-driven.

### Failure Rule

A Failure Rule defines what happens when a Run Step or Process Run cannot continue.

It may mark a Run Step as failed, block the Process Run, cancel downstream steps, require human review, or escalate to a Manager. Failure Rules must create Process Events, Activity, and Audit Logs when important state changes occur.

### Escalation Rule

An Escalation Rule defines who is responsible when the process needs attention.

Escalations may be caused by missing Capabilities, no eligible Employee, blocked Assignments, repeated failures, low confidence, Policy requirements, missed due dates, or human approval gates. Escalation targets may include a Department manager, Employee manager, human supervisor, or Organization operator.

### Process Event

A Process Event is a meaningful occurrence in a Process Definition or Process Run.

Examples include Process Run started, Run Step became ready, Assignment created, Assignment completed, Approval Gate opened, Approval Gate approved, Run Step failed, retry scheduled, escalation created, Process Run cancelled, and Process Run completed.

Process Events are operational facts that may create Activity, Audit Logs, or later notifications.

### Process Log

A Process Log is the detailed operational record for process engine behavior.

Process Logs are lower-level than Activity and may store routing decisions, dependency checks, input/output snapshots, selected Employee reasoning, retry attempt context, and engine notes. Process Logs help operators debug Process Runs without replacing Audit Logs.

## 3. Relationship To Sprint 001

Business Processes build directly on the Sprint 001 Organizational Core.

### Organizations

Business Processes belong to Organizations. Every Process Definition, Process Run, Process Step, Assignment Template, Process Event, and Process Log must be Organization-scoped.

### Sites

Business Processes may reference a Site when the run needs publication, brand, audience, language, or integration context. Site context is optional because the engine must support non-publication operations too.

### Departments

Process Steps are owned by or routed to Departments. Departments make the Business Process feel like company work rather than isolated automation.

### Employees

Employees execute the Assignments created by Process Steps. The Business Process Engine orchestrates work; Employees perform work.

### Capabilities

Process Steps and Assignment Templates declare required Capabilities. An Employee should be eligible for an Assignment only when they belong to the same Organization, fit the Department context, and have the required active Capability.

### Policies

Policies constrain Business Processes. A Policy may require human approval, escalation, restricted handling, extra review, or cancellation. Important Policy-related decisions must be visible in Process Events and Audit Logs.

### SOPs

SOPs guide how each Assignment should be performed. A Process Step may reference an SOP so the created Assignment has a clear operating procedure, expected output, success criteria, and review path.

### Assignments

Assignments remain the canonical unit of executable work. Process Steps create Assignments. Employees complete Assignments. The Process Run advances based on Assignment status changes and approval outcomes.

### Activity

Activity provides a readable timeline for HQ and operational views. Important Business Process events should create Activity items so a human operator can understand progress without reading low-level logs.

### Audit Logs

Audit Logs preserve formal evidence for important Business Process actions, decisions, and state changes. Process Definition activation, Process Run start, Assignment creation, approval gate decisions, cancellation, failure, and completion should create Audit Logs.

## 4. Domain Rules

- Business Processes belong to Organizations.
- Process Definitions are reusable and versioned.
- Process Runs are executions of Process Definitions.
- Process Runs must reference the Process Definition version they started from.
- Process Steps belong to Process Definitions.
- Process Steps create Assignments through Assignment Templates.
- Assignments are performed by Employees.
- Employees must belong to the same Organization as the Process Run.
- Business Processes orchestrate work; Employees execute work.
- A Process Run may have optional Site context but must not require Site context.
- A Process Step may require a Department, Capability, SOP, approval gate, retry rule, failure rule, and escalation rule.
- A Process Step should not create an Assignment until its dependencies are satisfied.
- A Run Step represents the runtime state of one Process Step inside one Process Run.
- Completing one Assignment may make dependent Run Steps ready.
- Blocked or failed Assignments may block, retry, escalate, or fail the Process Run according to rules.
- Approval Gates pause the Process Run until approved, rejected, cancelled, or escalated.
- Every important Process event creates Audit Logs and Activity.
- Process Logs may capture detailed engine behavior but do not replace Audit Logs.
- Sprint 002 may define mock or manual runtime behavior, but no real AI execution happens in Sprint 002.
- The engine must not rename Assignment to Task.
- The engine must not couple Business Processes to articles, publishing, Razbudise, or any single Organization.

## 5. Example Process: Prepare Editorial Package

This sample demonstrates orchestration only. It is not a publishing integration and does not assume article publishing.

Process Definition: `Prepare Editorial Package`

Purpose: coordinate research, writing, localization, fact checking, SEO, editorial review, human approval, and delivery readiness for an editorial package.

Trigger: human operator starts a Process Run with a brief, Organization context, optional Site context, language requirements, and delivery expectations.

Completion: the final package is marked delivery ready after human approval.

| Step | Department | Required Capability | Assignment Title | Expected Output | Dependency | Approval Requirement |
| --- | --- | --- | --- | --- | --- | --- |
| Research | Research | Research | Prepare research package | Source-backed research notes, key facts, risks, and open questions | None | Not required unless Policy flags sensitive subject |
| Writing | Writing | Writing | Draft editorial package | Draft package based on approved research input | Research completed | Not required |
| Localization | Localization | Localization | Localize editorial package | Natural localized version with context notes and preserved meaning | Writing completed | Not required |
| Fact Check | Trust & Safety | Fact Checking | Verify editorial package claims | Claim verification notes, unsupported claims, source conflicts, and risk flags | Localization completed | Required if claims are unsupported or sensitive |
| SEO | SEO | SEO | Prepare SEO package | Search title options, description, keyword notes, internal-link suggestions, and structured metadata recommendations | Fact Check completed or approved with notes | Not required |
| Editor Review | Editorial | Editing | Review editorial package | Editorial review notes, requested changes, quality decision, and approval recommendation | SEO completed | Required when review flags changes or Policy requires escalation |
| Human Approval | Editorial | Editing | Request human approval | Human decision record with approval, rejection, or change request | Editor Review completed | Required |
| Delivery Ready | Operations | Editing | Mark editorial package delivery ready | Delivery-ready package summary with links to outputs and approval evidence | Human Approval approved | Not required |

Runtime notes:

- If Fact Check blocks the package, the Process Run should move to `blocked` or open a retry path based on the Failure Rule.
- If Human Approval rejects the package, the Process Run should pause or route back to the appropriate prior step. Sprint 002 should support recording the rejection and blocking the run; complex conditional routing can be deferred.
- If no active Employee has the required Capability for a step, the Run Step should become blocked and create an escalation.

## 6. Database Proposal

This section proposes the Sprint 002 data model. It is not a migration plan and does not authorize code changes.

Use explicit foreign keys for core relationships. Use JSON only where process configuration needs flexibility before the model stabilizes.

### `business_process_definitions`

Represents reusable versioned Business Process blueprints.

Fields:

- `id`
- `organization_id`
- `owning_department_id` nullable
- `manager_employee_id` nullable
- `process_key`
- `name`
- `status`
- `version`
- `purpose`
- `trigger_description` nullable
- `input_schema` json nullable
- `completion_criteria` json nullable
- `default_site_required` boolean
- `metadata` json nullable
- `activated_at` nullable
- `retired_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may belong to an owning Department
- may have a manager Employee
- has many Process Steps
- has many Process Runs
- has many Assignment Templates through Process Steps
- may appear in Activity and Audit Logs

Statuses:

- `draft`
- `active`
- `paused`
- `superseded`
- `retired`
- `archived`

Indexes and constraints:

- unique `organization_id`, `process_key`, `version`
- index `organization_id`, `status`
- index `organization_id`, `process_key`
- index `owning_department_id`
- index `manager_employee_id`

Notes:

- Only active Process Definitions should be startable.
- Updating an active definition should create a new version or a documented revision event.
- Existing Process Runs must continue to reference the definition version they started from.

### `business_process_steps`

Represents reusable step definitions inside a Process Definition.

Fields:

- `id`
- `organization_id`
- `business_process_definition_id`
- `department_id`
- `standard_operating_procedure_id` nullable
- `required_capability_id` nullable
- `step_key`
- `name`
- `status`
- `sort_order`
- `description` nullable
- `expected_output` nullable
- `dependency_rules` json nullable
- `approval_required`
- `approval_rule` json nullable
- `retry_rule` json nullable
- `failure_rule` json nullable
- `escalation_rule` json nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Process Definition
- belongs to a Department
- may reference an SOP
- may reference a required Capability
- has one or many Assignment Templates
- creates Run Steps when a Process Run starts

Statuses:

- `draft`
- `active`
- `paused`
- `retired`

Indexes and constraints:

- unique `business_process_definition_id`, `step_key`
- index `organization_id`, `business_process_definition_id`
- index `organization_id`, `department_id`
- index `required_capability_id`
- index `standard_operating_procedure_id`
- index `status`

Notes:

- A Process Step is not executable by itself. It becomes executable as a Run Step inside a Process Run.
- Dependency rules should begin simple: all listed prerequisite steps must complete before this step becomes ready.
- If multiple Assignment Templates are allowed later, Sprint 002 should still begin with one primary Assignment Template per step.

### `business_process_runs`

Represents one execution of a Process Definition.

Fields:

- `id`
- `organization_id`
- `business_process_definition_id`
- `site_id` nullable
- `started_by_user_id` nullable
- `current_run_step_id` nullable
- `run_key` nullable
- `title`
- `status`
- `priority`
- `input_payload` json nullable
- `output_payload` json nullable
- `progress_percent`
- `started_at` nullable
- `completed_at` nullable
- `cancelled_at` nullable
- `failed_at` nullable
- `blocked_at` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Process Definition
- may reference a Site
- has many Run Steps
- has many Assignments through Run Steps
- has many Process Events
- has many Process Logs
- may create Activity and Audit Logs

Statuses:

- `pending`
- `running`
- `waiting_for_assignment`
- `waiting_for_approval`
- `blocked`
- `failed`
- `cancelled`
- `completed`

Indexes and constraints:

- unique `organization_id`, `run_key` when `run_key` is present
- index `organization_id`, `status`
- index `organization_id`, `business_process_definition_id`
- index `organization_id`, `site_id`, `status`
- index `current_run_step_id`
- index `started_at`
- index `completed_at`

Notes:

- A Process Run should keep stable reference to the Process Definition version it started from.
- Progress should be derived from Run Steps where practical; `progress_percent` can be stored as a denormalized display value if useful.
- `site_id` is optional because not every Business Process is tied to a Site.

### `business_process_run_steps`

Represents runtime state for one Process Step inside one Process Run.

Fields:

- `id`
- `organization_id`
- `business_process_run_id`
- `business_process_step_id`
- `assignment_id` nullable
- `department_id`
- `employee_id` nullable
- `standard_operating_procedure_id` nullable
- `required_capability_id` nullable
- `status`
- `sort_order`
- `attempt_number`
- `approval_required`
- `approval_status` nullable
- `blocked_reason` nullable
- `failure_reason` nullable
- `input_payload` json nullable
- `output_payload` json nullable
- `ready_at` nullable
- `started_at` nullable
- `completed_at` nullable
- `blocked_at` nullable
- `failed_at` nullable
- `cancelled_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Process Run
- belongs to a Process Step
- may create one Assignment Instance
- may reference the assigned Employee
- has many Process Events
- has many Process Logs

Statuses:

- `pending`
- `waiting_for_dependency`
- `ready`
- `assignment_created`
- `in_progress`
- `waiting_for_approval`
- `blocked`
- `failed`
- `cancelled`
- `skipped`
- `completed`

Indexes and constraints:

- unique `business_process_run_id`, `business_process_step_id`, `attempt_number`
- index `organization_id`, `business_process_run_id`, `status`
- index `organization_id`, `department_id`, `status`
- index `organization_id`, `employee_id`, `status`
- index `assignment_id`
- index `ready_at`

Notes:

- Run Step status should be driven by dependency readiness, Assignment lifecycle, approval status, cancellation, and failure rules.
- `assignment_id` links the Run Step to the Assignment Instance created for Employee execution.
- Retries may create additional Run Step attempts or additional Assignment Instances. Sprint 002 should choose the simplest auditable approach before implementation.

### `assignment_templates`

Represents reusable Assignment creation rules for Process Steps.

Fields:

- `id`
- `organization_id`
- `business_process_definition_id`
- `business_process_step_id`
- `department_id`
- `standard_operating_procedure_id` nullable
- `required_capability_id` nullable
- `template_key`
- `title_template`
- `assignment_type`
- `priority`
- `briefing_template` json
- `expected_output` nullable
- `input_mapping` json nullable
- `review_required`
- `review_path` nullable
- `due_offset_minutes` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Process Definition
- belongs to a Process Step
- belongs to a Department
- may reference an SOP
- may reference a required Capability
- creates Assignments during Process Runs

Indexes and constraints:

- unique `business_process_step_id`, `template_key`
- index `organization_id`, `business_process_definition_id`
- index `organization_id`, `business_process_step_id`
- index `department_id`
- index `required_capability_id`

Notes:

- Assignment Templates must create Assignments, not a separate work concept.
- Assignment Template fields should map cleanly to existing Assignment fields from Sprint 001.
- The created Assignment should retain references back to the Process Run and Run Step in the Sprint 002 implementation design.

### `process_events`

Represents meaningful Business Process events.

Fields:

- `id`
- `organization_id`
- `business_process_definition_id` nullable
- `business_process_run_id` nullable
- `business_process_run_step_id` nullable
- `assignment_id` nullable
- `actor_type` nullable
- `actor_id` nullable
- `event_type`
- `event_key` nullable
- `summary`
- `payload` json nullable
- `occurred_at`
- `created_at`

Relationships:

- belongs to an Organization
- may reference a Process Definition
- may reference a Process Run
- may reference a Run Step
- may reference an Assignment
- may reference an actor such as a human user, Employee, or system process
- may create Activity
- may create Audit Logs

Indexes and constraints:

- index `organization_id`, `occurred_at`
- index `organization_id`, `event_type`
- index `business_process_run_id`, `occurred_at`
- index `business_process_run_step_id`, `occurred_at`
- index `assignment_id`, `occurred_at`
- index `actor_type`, `actor_id`

Notes:

- Process Events are facts that something happened.
- Events should use stable event types such as `process_run_started`, `run_step_ready`, `assignment_created`, `approval_gate_opened`, `run_step_completed`, `run_step_blocked`, `run_step_failed`, `process_run_completed`, and `process_run_cancelled`.

### `process_logs`

Represents detailed engine logs for Business Process operations.

Fields:

- `id`
- `organization_id`
- `business_process_run_id`
- `business_process_run_step_id` nullable
- `process_event_id` nullable
- `assignment_id` nullable
- `log_level`
- `message`
- `context` json nullable
- `created_at`

Relationships:

- belongs to an Organization
- belongs to a Process Run
- may reference a Run Step
- may reference a Process Event
- may reference an Assignment

Indexes and constraints:

- index `organization_id`, `created_at`
- index `business_process_run_id`, `created_at`
- index `business_process_run_step_id`, `created_at`
- index `process_event_id`
- index `assignment_id`
- index `log_level`

Notes:

- Process Logs are operational diagnostics.
- Process Logs should not be used as the only formal record of important decisions.
- Important state changes still require Audit Logs.

## 7. Statuses

### Process Definition Statuses

- `draft`: definition is being designed and cannot be started.
- `active`: definition can be started as a Process Run.
- `paused`: definition is temporarily not startable, but existing runs may continue unless separately cancelled or blocked.
- `superseded`: definition version was replaced by a newer version and should not be used for new runs.
- `retired`: definition has ended active use while preserving history.
- `archived`: definition is hidden from normal operations while preserving history.

### Process Run Statuses

- `pending`: run has been created but has not started orchestration.
- `running`: run is actively progressing.
- `waiting_for_assignment`: run is waiting on an active Assignment.
- `waiting_for_approval`: run is paused at an Approval Gate.
- `blocked`: run cannot continue until a blocker is resolved.
- `failed`: run ended unsuccessfully.
- `cancelled`: run was intentionally stopped.
- `completed`: run finished successfully.

### Process Step Statuses

- `draft`: step is being designed.
- `active`: step can be used by active Process Definitions.
- `paused`: step is temporarily not used for new runs.
- `retired`: step has ended active use while preserving history.

### Run Step Statuses

- `pending`: run step exists but is not ready.
- `waiting_for_dependency`: run step is waiting for prerequisite Run Steps.
- `ready`: dependencies are satisfied and the step can create an Assignment.
- `assignment_created`: the Assignment Instance exists but has not started.
- `in_progress`: the Assignment Instance is being performed.
- `waiting_for_approval`: the step is paused at an Approval Gate.
- `blocked`: the step cannot continue until a blocker is resolved.
- `failed`: the step failed according to its Failure Rule.
- `cancelled`: the step was stopped because the Process Run or step was cancelled.
- `skipped`: the step was intentionally skipped by a valid rule or operator decision.
- `completed`: the step finished successfully.

## 8. Runtime Behavior

Sprint 002 defines the runtime behavior before real AI execution exists.

### Starting A Process Run

1. A human operator or internal system selects an active Process Definition.
2. The starter provides required input payload values and optional Site context.
3. The system creates a Process Run in `pending` or `running`.
4. The system creates Run Steps from the active Process Steps in the selected Process Definition version.
5. The system records a `process_run_started` Process Event.
6. The system creates Activity and an Audit Log for the run start.
7. The system evaluates dependencies and marks first eligible Run Steps as `ready`.

### Creating The First Assignment

1. The first Run Step becomes `ready`.
2. The engine reads the Run Step Assignment Template.
3. The engine finds an eligible Employee based on Organization, Department, Employee status, required Capability, and any future routing rules.
4. If an eligible Employee exists, the engine creates an Assignment.
5. The Assignment is linked to the Process Run and Run Step.
6. The Run Step becomes `assignment_created` or `in_progress` depending on the Assignment lifecycle.
7. Process Event, Activity, Process Log, and Audit Log records are created.
8. If no eligible Employee exists, the Run Step becomes `blocked` and escalation rules apply.

### Advancing After Assignment Completion

1. An Employee completes an Assignment through the Assignment lifecycle.
2. The Assignment completion updates the linked Run Step output.
3. If no approval is required, the Run Step becomes `completed`.
4. The engine records Process Event, Process Log, Activity, and Audit Log records.
5. The engine re-evaluates dependent Run Steps.
6. Any dependent Run Step whose dependencies are satisfied becomes `ready`.
7. The next Assignment is created from the next Run Step's Assignment Template.
8. If all required Run Steps are completed and no Approval Gate remains open, the Process Run becomes `completed`.

### Blocked Or Failed Assignments

When a linked Assignment becomes `blocked`:

- the Run Step should become `blocked`;
- the Process Run should become `blocked` unless another independent Run Step can continue;
- the engine should create a Process Event, Activity, Process Log, and Audit Log;
- the Escalation Rule should identify the responsible owner.

When a linked Assignment becomes `failed`:

- the Run Step should evaluate its Retry Rule;
- if retry is allowed, the Run Step records an attempt and creates or prepares a retry Assignment;
- if retry is not allowed, the Run Step becomes `failed`;
- the Process Run follows the Failure Rule, such as `blocked`, `failed`, or `waiting_for_approval`;
- important changes create Process Events, Activity, Process Logs, and Audit Logs.

### Approval Gates

An Approval Gate pauses a Run Step or Process Run until a human supervisor or approved Manager decision is recorded.

When an Approval Gate opens:

- the Run Step becomes `waiting_for_approval`;
- the Process Run becomes `waiting_for_approval`;
- the related Assignment should be in `needs_review` or `completed` with review required;
- the gate records required reviewer context, reason, and expected decision;
- Process Event, Activity, Process Log, and Audit Log records are created.

Approval outcomes:

- approved: the Run Step may become `completed` and dependent Run Steps may become ready.
- rejected: the Run Step should become `blocked` or `failed` according to the Failure Rule.
- changes requested: the Run Step should return to a prior Assignment or create a retry Assignment if allowed.
- cancelled: the Run Step or Process Run should become `cancelled`.

### Cancellation

A Process Run may be cancelled by an authorized human operator or future system rule.

Cancellation behavior:

- the Process Run becomes `cancelled`;
- pending, ready, waiting, and blocked Run Steps become `cancelled`;
- active Assignments created by the Process Run should be cancelled when allowed by Assignment lifecycle rules;
- completed Assignments remain completed and are not rewritten;
- the cancellation reason must be recorded;
- Process Event, Activity, Process Log, and Audit Log records must be created.

## 9. Filament/HQ Requirements

Sprint 002 HQ should make Business Processes visible as operating company work, not as low-level automation machinery.

### Process Definitions

HQ should show:

- Process Definition name
- Organization
- owning Department
- status
- version
- purpose
- number of Process Steps
- required Departments
- required Capabilities
- whether approval gates exist
- last updated timestamp
- activation or retirement timestamp when present

### Process Runs

HQ should show:

- Process Run title
- Organization
- optional Site
- Process Definition name and version
- status
- priority
- current step
- progress
- started timestamp
- completed, cancelled, failed, or blocked timestamp when present
- responsible Department or Manager when available

### Current Step

The Process Run detail view should show:

- current Run Step name
- Department
- assigned Employee when an Assignment exists
- required Capability
- status
- dependency state
- approval state
- blocker or failure reason when present

### Progress

Progress should be understandable without reading logs:

- total Run Steps
- completed Run Steps
- blocked Run Steps
- failed Run Steps
- waiting-for-approval Run Steps
- percentage complete
- next eligible step when available

### Related Assignments

HQ should show all Assignments created by a Process Run:

- Assignment title
- status
- Department
- Employee
- required Capability
- priority
- due date when present
- review flag
- escalation flag
- linked Run Step

### Recent Process Events

HQ should show recent Process Events for a run:

- occurred time
- event type
- readable summary
- linked Run Step
- linked Assignment
- actor when present

### Failed And Blocked Runs

HQ should provide an operational view for attention:

- failed Process Runs
- blocked Process Runs
- runs waiting for approval
- blocked Run Steps
- failed Run Steps
- latest event summary
- escalation owner when present
- age of blocker or approval wait

## 10. Acceptance Criteria

Sprint 002 is complete when the approved implementation can satisfy the checks below. This specification phase is complete when this document is reviewed and approved for implementation planning.

### Domain Model Checks

- Business Process Definitions belong to Organizations.
- Process Definitions are reusable and versioned.
- Process Definitions contain ordered Process Steps.
- Process Steps can declare Department, required Capability, optional SOP, dependency rules, approval requirement, retry rule, failure rule, escalation rule, and Assignment Template.
- Process Runs execute a specific Process Definition version.
- Process Runs create Run Steps from Process Steps.
- Run Steps can create Assignments.
- Assignments remain the canonical work item executed by Employees.
- Process Runs can track status, current step, progress, and related Assignments.
- Process Events and Process Logs can be stored for a Process Run.
- Important Process Events create Activity and Audit Logs.

### Runtime Checks

- Starting a Process Run creates Run Steps.
- The first eligible Run Step creates an Assignment.
- Assignment completion advances the Process Run to the next eligible Run Step.
- Blocked Assignments block or escalate the Run Step and Process Run.
- Failed Assignments trigger retry or failure behavior according to rules.
- Approval Gates pause the Process Run.
- Approval decisions can continue, block, fail, or cancel a Process Run according to rules.
- Cancelling a Process Run cancels eligible pending and active Run Steps and Assignments.
- No real AI execution is required or allowed in Sprint 002.

### HQ Checks

- HQ shows Process Definitions.
- HQ shows Process Runs.
- HQ shows current step and progress.
- HQ shows related Assignments.
- HQ shows recent Process Events.
- HQ highlights failed, blocked, and waiting-for-approval runs.
- HQ uses Business Process, Process Definition, Process Run, Process Step, Assignment, Employee, Activity, and Audit Log consistently.
- HQ does not rename Assignment to Task.

### Test And Documentation Checks

- Automated tests cover Process Definition storage and Organization scoping.
- Automated tests cover Process Step relationships and dependency metadata.
- Automated tests cover Process Run creation from a Process Definition.
- Automated tests cover first Assignment creation from an Assignment Template.
- Automated tests cover Assignment completion advancing dependent Run Steps.
- Automated tests cover blocked, failed, cancelled, and waiting-for-approval behavior.
- Automated tests cover Activity and Audit Log creation for important process events.
- Seed data or fixtures include the `Prepare Editorial Package` sample process.
- README or sprint review documentation is updated after implementation.

## 11. Out Of Scope

Sprint 002 must not include:

- OpenAI
- real AI generation
- Razbudise integration
- article publishing
- billing
- advanced memory/vector search
- external notifications
- complex visual workflow builder
- direct CMS publishing
- autonomous internet browsing
- provider cost tracking
- multi-Organization self-service onboarding
- complex conditional branching
- drag-and-drop process editing
- real-time event streaming
- background queue optimization

Business Process Engine behavior may be mocked, manually driven, or service-layer driven in Sprint 002, but it must not perform real AI work or external publishing actions.
