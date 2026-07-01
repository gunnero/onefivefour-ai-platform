# Sprint 002: Work Router v0.1

## Status

Draft specification.

This document defines the Work Router for OneFiveFour AI Platform. It uses the canonical language from `docs/018-domain-language.md` and extends `specs/004-business-process-engine.md`.

No Laravel, Filament, Next.js, Docker, migration, model, or application code is part of this document.

## Target Architecture

The Work Router separates orchestration from assignment.

```text
Business Process
  -> Run Step
  -> Assignment Template
  -> Work Request
  -> Work Router
  -> Assignment
  -> Employee
```

Business Processes decide what work exists.

The Work Router decides who should receive the work.

Employees execute Assignments.

## 1. Purpose

Sprint 002 defines the Business Process Engine as the orchestration layer for repeatable company work. That engine should know what step is ready, which Department owns the work, which Capability is required, which SOP applies, and what output is expected.

It should not directly choose Employees.

Employee selection is a separate operational concern. Routing needs its own rules, evidence, retry behavior, override path, queue visibility, workload awareness, failure handling, reassignment history, Activity, and Audit Logs. If Business Processes choose Employees directly, process definitions become too tightly coupled to current staffing, current workload, and temporary operational conditions.

The Work Router exists so OneFiveFour can keep these responsibilities clean:

- Business Processes orchestrate work.
- Assignment Templates describe the shape of work.
- Work Requests ask the organization to dispatch work.
- The Work Router evaluates eligible Employees.
- Assignments are created only after a routing decision.
- Employees execute Assignments.

This separation keeps Business Processes reusable across Razbudise, OFFMI, AI Day Trader, OneFiveFour internal operations, and future Organizations. A Process Definition can stay stable while each Organization changes Departments, Employees, Capabilities, workload rules, manager overrides, and routing strategies.

## 2. Core Concepts

### Work Request

A Work Request is a request to create an Assignment.

It contains the Organization, optional Site context, Department, required Capability, optional SOP, Assignment Template data, priority, briefing, expected output, input payload, review requirements, and source context.

A Work Request is not an Assignment. It has no assigned Employee until the Router makes a Routing Decision and dispatches an Assignment.

Work Requests may come from Business Processes, humans, APIs, or future integrations.

### Router

The Router is the platform component responsible for turning Work Requests into Assignments.

It evaluates eligibility, applies the selected Routing Strategy, records a Routing Decision, creates the Assignment when routing succeeds, and records failure, escalation, or cancellation when routing cannot proceed.

The Router is not an AI Employee and does not execute work.

### Routing Strategy

A Routing Strategy is the rule used to choose an Employee from eligible candidates.

Sprint 002 should define simple deterministic strategies first: `manual`, `first_available`, `least_busy`, `round_robin`, and `capability_match`. More advanced strategies may be added later after the operating model is stable.

### Candidate Employee

A Candidate Employee is an Employee considered by the Router for a Work Request.

Candidate Employees must belong to the same Organization as the Work Request. They may be included or excluded based on Department, employment status, Capability, workload, Site context, Policy, manager override, or other eligibility rules.

### Eligibility Rule

An Eligibility Rule decides whether a Candidate Employee may receive a specific Work Request.

Eligibility Rules are evaluated before the Routing Strategy selects a final Employee. An Employee who fails required eligibility must not receive the Assignment unless a documented manager override allows it.

### Routing Decision

A Routing Decision records the Router's evaluation and outcome.

It should capture the Work Request, Routing Strategy, candidate count, eligible count, selected Employee when present, exclusion reasons, decision reason, override reason when present, failure reason when present, actor context, and timestamp.

Routing Decisions must be auditable.

### Routing Failure

A Routing Failure occurs when the Router cannot create an Assignment from a Work Request.

Common causes include no eligible Employee, missing Department, missing required Capability, paused Department Queue, overloaded candidates, invalid Work Request data, Policy restriction, or cancelled source context.

Routing Failure must create Activity and Audit Logs.

### Department Queue

A Department Queue is an Organization-scoped operational queue for Work Requests routed to a Department.

It helps HQ see incoming work by Department, default routing strategy, paused or active routing state, waiting Work Requests, failed routing, and queue pressure.

### Employee Workload

Employee Workload is a snapshot or calculated view of an Employee's current Assignment load.

For Sprint 002, workload can be simple: active Assignment count, pending Assignment count, blocked Assignment count, needs-review Assignment count, capacity limit, and overloaded flag. It should not depend on real AI execution or performance scoring.

### Assignment Dispatch

Assignment Dispatch is the act of creating an Assignment from a routed Work Request.

Dispatch should copy the appropriate briefing, expected output, priority, Department, Employee, optional Site, optional SOP, required Capability, review path, and source references onto the Assignment.

### Reassignment

Reassignment moves work from one Employee to another after an Assignment was already dispatched.

Reassignment may be needed when an Employee becomes paused, overloaded, blocked, ineligible, retired, or when a manager requests a change. It must preserve history and create a Reassignment Event, Activity, and Audit Log.

### Escalation

Escalation asks a Manager, human supervisor, Department owner, or Organization operator to resolve a routing problem.

Escalation may be triggered by failed routing, repeated reassignment failure, missing Capability, overloaded Department, Policy restriction, or manual routing waiting too long.

## 3. Relationship To Existing Core

### Organization

Work Requests, Routing Decisions, Department Queues, Employee Workload snapshots, Reassignment Events, Assignments, Activity, and Audit Logs belong to Organizations.

The Router must never route work across Organizations.

### Site

A Work Request may include optional Site context. Site context may influence eligibility when a Department, Employee, SOP, Policy, or future permission is Site-specific.

Site context must remain optional because the Router must support non-publication work.

### Department

Every Work Request should target a Department unless it is intentionally manual or administrative.

Department Queues group Work Requests by Department and provide default routing behavior. Candidate Employees usually come from the target Department unless a manager override or future cross-Department rule allows otherwise.

### Employee

Employees receive Assignments after routing succeeds. Employees do not receive Work Requests directly.

Employees must be active, eligible, and not overloaded unless a documented manager override allows the dispatch.

### Capability

Work Requests may require a Capability. Candidate Employees must have the required active Capability before receiving the Assignment.

Capability matching is eligibility first and strategy second. A strategy may prefer stronger matches later, but Sprint 002 only requires active required Capability.

### SOP

A Work Request may reference an SOP. The created Assignment should preserve the SOP reference so the Employee knows how repeatable work should be performed.

The Router does not interpret SOP steps. It uses SOP context for eligibility, dispatch, and auditability.

### Policy

Policies may restrict routing, require human approval, block assignment to specific Departments or Employees, or require escalation.

The Router must record Policy-related routing decisions in Activity and Audit Logs when they affect dispatch.

### Assignment

An Assignment is the concrete work item created by the Router.

Work Requests are not Assignments. Routing Decisions are not Assignments. Assignments are created only when routing succeeds or when a valid manual selection dispatches the work.

### Activity

Activity should show readable routing events: Work Request created, routed, Assignment dispatched, routing failed, Work Request escalated, Assignment reassigned, and Work Request cancelled.

### Audit Log

Audit Logs preserve formal evidence for routing decisions, failed routing, manager overrides, reassignment, cancellation, and Assignment dispatch.

### Business Process Definition

A Process Definition may define Assignment Templates and routing expectations, such as target Department, required Capability, default priority, and preferred Routing Strategy.

It must not hard-code a specific Employee for normal routing.

### Process Run

A Process Run may create Work Requests from ready Run Steps.

The Process Run waits for routing to dispatch an Assignment or report failure, blocked state, or escalation.

### Process Run Step

A Process Run Step is the runtime source for a Work Request when Business Process orchestration creates work.

The Run Step should reference the Work Request and later the Assignment, but it should not choose the Employee directly.

### Assignment Template

An Assignment Template provides the default Assignment data used to create a Work Request.

The Router consumes Work Request data derived from the Assignment Template, makes a Routing Decision, and then creates the Assignment.

## 4. Domain Rules

- Work Requests belong to Organizations.
- Work Requests may come from Business Processes, humans, APIs, or future integrations.
- Work Requests are not Assignments.
- The Router creates Assignments.
- Business Processes do not choose Employees directly.
- Process Run Steps create Work Requests, not direct Employee assignments.
- Assignment Templates describe work, not the final Employee.
- Employees must be eligible before receiving Assignments.
- Candidate Employees must belong to the same Organization as the Work Request.
- Candidate Employees should usually belong to the target Department.
- Candidate Employees must have active employment status.
- Candidate Employees must not be paused, retired, or archived.
- Candidate Employees must have the required active Capability when one is required.
- Candidate Employees should not be overloaded unless a manager override allows it.
- Routing decisions must be auditable.
- Failed routing must create Activity and Audit Logs.
- Reassignment must preserve Assignment and routing history.
- Department Queues may be paused without pausing the Department itself.
- No real AI routing is included in Sprint 002.
- The Router must not rename Assignment to Task.
- The Router must stay generic and must not couple routing to Razbudise, publishing, articles, billing, or any single Organization.

## 5. Routing Strategies

Sprint 002 should define simple routing strategies that can run deterministically without real AI.

### `manual`

The Work Request waits for a human operator or Manager to choose the Employee.

Use when work is sensitive, exceptional, Policy-constrained, or when no automatic strategy should decide. The Routing Decision should show `manual_required` until selection is made.

### `first_available`

The Router selects the first eligible Employee in a stable order.

The stable order may be Department sort order, Employee name, Employee id, or a future configured queue order. The implementation plan should choose one deterministic order before code is written.

### `least_busy`

The Router selects the eligible Employee with the lowest current workload.

Sprint 002 workload should use simple active Assignment counts and overload flags. It should not use performance metrics, AI judgment, or external signals.

### `round_robin`

The Router rotates work across eligible Employees in a Department Queue.

The queue should remember the last selected Employee or enough routing history to choose the next eligible Employee deterministically.

### `capability_match`

The Router selects from Employees with the required active Capability.

For Sprint 002, this is a simple required Capability filter with deterministic tie-breaking. Future versions may rank by capability level, certifications, quality score, or policy trust level.

### Future Strategies

Future routing strategies may include:

- `performance_weighted`
- `cost_aware`
- `deadline_aware`
- `AI-assisted`

These are out of scope for Sprint 002.

## 6. Employee Eligibility

Employee eligibility should be evaluated before any Routing Strategy selects an Employee.

### Required Eligibility

An Employee is eligible when:

- the Employee belongs to the same Organization as the Work Request;
- the Employee belongs to the target Department, unless a manager override allows cross-Department routing;
- the Employee employment status is `active`;
- the Employee is not paused, retired, or archived;
- the Employee has the required active Capability when the Work Request requires one;
- the Employee is not overloaded according to the current workload rule;
- the Employee is allowed by relevant Policy constraints;
- the Employee can receive Assignments in the optional Site context when Site context is relevant.

### Optional Manager Override

A Manager or authorized human supervisor may override some eligibility checks.

Override may allow:

- cross-Department assignment;
- routing to an Employee who is near workload capacity;
- manual selection when automatic strategies fail;
- assigning work without optional Site specialization.

Override must not silently bypass Organization scoping, archived Employee status, or retired Employee status. Any override must record the actor, reason, skipped checks, Routing Decision, Activity, and Audit Log.

### Optional Site Context

Site context may be used when work is publication-specific, brand-specific, language-specific, or governed by Site-scoped Policies or SOPs.

Sprint 002 should record Site context and allow eligibility checks to reference it, but should not require complex Site staffing rules yet.

## 7. Database Proposal

This section proposes the Work Router data model. It is not a migration plan and does not authorize code changes.

Use explicit foreign keys for core relationships. Use JSON only for flexible routing snapshots, candidate lists, and rule results that may change before the model stabilizes.

### `work_requests`

Represents a request to route work and create an Assignment.

Fields:

- `id`
- `organization_id`
- `site_id` nullable
- `department_id`
- `business_process_definition_id` nullable
- `business_process_run_id` nullable
- `business_process_run_step_id` nullable
- `assignment_template_id` nullable
- `standard_operating_procedure_id` nullable
- `required_capability_id` nullable
- `requested_by_user_id` nullable
- `source_type`
- `source_id` nullable
- `work_request_key` nullable
- `title`
- `assignment_type`
- `priority`
- `status`
- `routing_strategy`
- `briefing` json
- `expected_output` nullable
- `input_payload` json nullable
- `review_required`
- `review_path` nullable
- `due_at` nullable
- `assignment_id` nullable
- `blocked_reason` nullable
- `failure_reason` nullable
- `escalation_reason` nullable
- `requested_at`
- `routing_started_at` nullable
- `routed_at` nullable
- `dispatched_at` nullable
- `blocked_at` nullable
- `failed_at` nullable
- `cancelled_at` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may reference a Site
- belongs to a Department
- may reference a Business Process Definition
- may reference a Process Run
- may reference a Process Run Step
- may reference an Assignment Template
- may reference an SOP
- may reference a required Capability
- may create one Assignment
- has many Routing Decisions
- may have Reassignment Events through the Assignment
- may create Activity and Audit Logs

Statuses:

- `pending`
- `routing`
- `waiting_for_manual_selection`
- `routed`
- `assignment_created`
- `blocked`
- `escalated`
- `failed`
- `cancelled`

Indexes and constraints:

- unique `organization_id`, `work_request_key` when `work_request_key` is present
- index `organization_id`, `status`
- index `organization_id`, `department_id`, `status`
- index `organization_id`, `site_id`, `status`
- index `business_process_run_id`, `status`
- index `business_process_run_step_id`
- index `assignment_template_id`
- index `assignment_id`
- index `requested_at`
- index `due_at`

Notes:

- A Work Request is the Router input and should remain visible even after Assignment creation.
- `source_type` may be `business_process`, `human`, `api`, or future `integration`.
- `assignment_id` is nullable until dispatch succeeds.
- Work Requests should preserve the data used to create the Assignment for audit and debugging.

### `routing_decisions`

Represents one Router evaluation for a Work Request.

Fields:

- `id`
- `organization_id`
- `work_request_id`
- `department_id`
- `site_id` nullable
- `assignment_id` nullable
- `selected_employee_id` nullable
- `strategy`
- `status`
- `candidate_count`
- `eligible_count`
- `candidate_snapshot` json nullable
- `eligibility_results` json nullable
- `decision_reason` nullable
- `failure_reason` nullable
- `manager_override`
- `override_reason` nullable
- `decided_by_type` nullable
- `decided_by_id` nullable
- `decided_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Work Request
- belongs to a Department
- may reference a Site
- may reference the selected Employee
- may reference the created Assignment
- may create Activity and Audit Logs

Statuses:

- `pending`
- `evaluating`
- `selected`
- `manual_required`
- `no_eligible_employee`
- `failed`
- `cancelled`
- `superseded`

Indexes and constraints:

- index `organization_id`, `status`
- index `work_request_id`, `created_at`
- index `organization_id`, `department_id`, `status`
- index `selected_employee_id`
- index `assignment_id`
- index `decided_at`

Notes:

- Routing Decisions should be append-friendly. A Work Request may have multiple decisions over time after retry, override, or reassignment.
- Candidate snapshots should capture enough evidence to explain why Employees were selected or excluded.
- A failed decision should not be overwritten by a later successful decision.

### `department_queues`

Represents routing settings and queue state for a Department.

Fields:

- `id`
- `organization_id`
- `department_id`
- `site_id` nullable
- `queue_key`
- `name`
- `status`
- `default_routing_strategy`
- `max_active_assignments_per_employee` nullable
- `pending_work_request_count`
- `blocked_work_request_count`
- `failed_work_request_count`
- `last_selected_employee_id` nullable
- `routing_paused_reason` nullable
- `routing_paused_until` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Department
- may reference a Site-specific queue context
- may reference the last selected Employee for round-robin behavior
- has many Work Requests through Department and optional Site context

Statuses:

- `active`
- `paused`
- `draining`
- `closed`
- `archived`

Indexes and constraints:

- unique `organization_id`, `queue_key`
- unique `organization_id`, `department_id`, `site_id` when `site_id` is present
- index `organization_id`, `status`
- index `organization_id`, `department_id`
- index `site_id`
- index `last_selected_employee_id`

Notes:

- Department Queues are operational routing views, not replacements for Departments.
- A paused Department Queue blocks automatic routing but does not archive the Department.
- Queue count fields may be derived or denormalized. The implementation plan should choose one approach before code is written.

### `employee_workload_snapshots`

Represents workload evidence used during routing.

Fields:

- `id`
- `organization_id`
- `employee_id`
- `department_id`
- `snapshot_source`
- `snapshot_at`
- `active_assignment_count`
- `pending_assignment_count`
- `blocked_assignment_count`
- `needs_review_assignment_count`
- `capacity_limit` nullable
- `utilization_percent` nullable
- `is_overloaded`
- `metadata` json nullable
- `created_at`

Relationships:

- belongs to an Organization
- belongs to an Employee
- belongs to a Department
- may be referenced inside Routing Decision snapshots

Indexes and constraints:

- index `organization_id`, `employee_id`, `snapshot_at`
- index `organization_id`, `department_id`, `snapshot_at`
- index `organization_id`, `is_overloaded`
- index `snapshot_at`

Notes:

- Sprint 002 workload is simple and Assignment-count based.
- Snapshots support auditable routing decisions without requiring real-time optimization.
- A future implementation may replace snapshots with calculated views or event-sourced workload, but the decision evidence must remain inspectable.

### `reassignment_events`

Represents reassignment history after Assignment dispatch.

Fields:

- `id`
- `organization_id`
- `work_request_id` nullable
- `routing_decision_id` nullable
- `assignment_id`
- `department_id`
- `from_employee_id`
- `to_employee_id` nullable
- `status`
- `reason`
- `requested_by_type` nullable
- `requested_by_id` nullable
- `approved_by_type` nullable
- `approved_by_id` nullable
- `manager_override`
- `override_reason` nullable
- `previous_assignment_status` nullable
- `new_assignment_status` nullable
- `occurred_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may reference the original Work Request
- may reference a Routing Decision
- belongs to an Assignment
- belongs to a Department
- references the previous Employee
- may reference the new Employee
- may create Activity and Audit Logs

Statuses:

- `requested`
- `approved`
- `applied`
- `rejected`
- `cancelled`
- `failed`

Indexes and constraints:

- index `organization_id`, `status`
- index `assignment_id`, `created_at`
- index `work_request_id`
- index `routing_decision_id`
- index `from_employee_id`
- index `to_employee_id`
- index `department_id`, `created_at`

Notes:

- Reassignment must not erase the original Routing Decision.
- `to_employee_id` may be nullable while reassignment is requested or when reassignment fails.
- Applied reassignment should create a new Routing Decision or link to the decision that selected the replacement Employee.

## 8. Statuses

### Work Request Statuses

- `pending`: Work Request exists and is waiting for routing.
- `routing`: Router is evaluating candidates.
- `waiting_for_manual_selection`: Work Request requires a human or Manager to choose an Employee.
- `routed`: Routing Decision selected an Employee, but Assignment dispatch is not yet recorded.
- `assignment_created`: Assignment was created from the Work Request.
- `blocked`: Work Request cannot proceed until a blocker is resolved.
- `escalated`: Work Request has been escalated for human or Manager attention.
- `failed`: Router could not route the Work Request and no retry path is active.
- `cancelled`: Work Request was intentionally stopped.

### Routing Decision Statuses

- `pending`: decision record exists but evaluation has not started.
- `evaluating`: Router is checking candidates and eligibility.
- `selected`: Router selected an Employee.
- `manual_required`: strategy or rules require manual Employee selection.
- `no_eligible_employee`: no Candidate Employee passed required eligibility.
- `failed`: routing evaluation failed for a system, data, or Policy reason.
- `cancelled`: decision was stopped because the Work Request was cancelled.
- `superseded`: decision was replaced by a later decision.

### Department Queue Statuses

- `active`: queue accepts and routes Work Requests.
- `paused`: queue keeps Work Requests but does not route automatically.
- `draining`: queue does not accept new automatic routing but existing Work Requests may finish.
- `closed`: queue no longer routes work.
- `archived`: queue is hidden from normal operations while preserving history.

### Reassignment Event Statuses

- `requested`: reassignment has been requested.
- `approved`: reassignment has been approved but not applied.
- `applied`: Assignment has been moved to a replacement Employee.
- `rejected`: reassignment request was denied.
- `cancelled`: reassignment request was intentionally stopped.
- `failed`: reassignment could not be completed.

## 9. Runtime Behavior

Sprint 002 defines deterministic or manual routing behavior. No real AI routing is included.

### Creating A Work Request

1. A source creates a Work Request.
2. The source may be a Process Run Step, human operator, API, or future integration.
3. The Work Request stores Organization, Department, optional Site, required Capability, optional SOP, Assignment Template context, priority, briefing, expected output, input payload, and routing strategy.
4. The Work Request starts as `pending`.
5. The system records Activity and an Audit Log for important Work Request creation.

### Evaluating Eligible Employees

1. The Router loads Candidate Employees from the Work Request Organization.
2. The Router narrows candidates by target Department unless an override or future rule allows cross-Department routing.
3. The Router checks employment status, required Capability, workload, Policy constraints, and optional Site context.
4. The Router records candidate count, eligible count, and exclusion reasons in a Routing Decision.
5. If no Employee is eligible, the Routing Decision becomes `no_eligible_employee` and failure behavior begins.

### Selecting An Employee

1. The Router applies the Work Request routing strategy to eligible Employees.
2. `manual` waits for human or Manager selection.
3. `first_available` chooses the first eligible Employee in stable order.
4. `least_busy` chooses the eligible Employee with lowest workload.
5. `round_robin` chooses the next eligible Employee in the Department Queue rotation.
6. `capability_match` chooses an eligible Employee with the required active Capability using deterministic tie-breaking.
7. The Routing Decision records the selected Employee and decision reason.

### Creating An Assignment

1. After an Employee is selected, the Router creates the Assignment.
2. The Assignment belongs to the same Organization as the Work Request.
3. The Assignment receives Department, selected Employee, optional Site, optional SOP, title, type, priority, briefing, expected output, input payload, required Capability, review flag, review path, and due date when present.
4. The Work Request status becomes `assignment_created`.
5. The Routing Decision status becomes `selected`.
6. The Assignment links back to Work Request and source context in the implementation design.
7. Activity and Audit Logs are created for dispatch.
8. If the Work Request came from a Process Run Step, the Process Run Step can continue waiting on the Assignment lifecycle.

### Handling No Eligible Employee

When no Employee is eligible:

- the Work Request becomes `blocked` or `escalated`;
- the Routing Decision becomes `no_eligible_employee`;
- the Department Queue should show the blocked Work Request;
- the source Process Run Step should become blocked or waiting according to the Business Process Engine rules;
- Activity and Audit Logs must be created;
- an escalation owner should be identified when possible.

### Blocking Or Escalating A Work Request

A Work Request may be blocked or escalated because:

- no eligible Employee exists;
- the Department Queue is paused;
- required Capability is missing;
- all eligible Employees are overloaded;
- a Policy blocks automatic routing;
- Site context cannot be satisfied;
- required Work Request data is missing;
- manual selection is overdue.

Blocked and escalated Work Requests must remain visible in HQ and must not silently disappear.

### Reassigning Work

1. A Manager, human operator, Policy rule, or future system rule requests reassignment.
2. The Reassignment Event records the Assignment, previous Employee, reason, actor, and status.
3. The Router evaluates eligible replacement Employees.
4. If a replacement Employee is selected, the Assignment is reassigned and the event becomes `applied`.
5. The original Routing Decision remains historical evidence.
6. A new Routing Decision or linked decision records the replacement selection.
7. Activity and Audit Logs are created.
8. If no replacement is eligible, the Reassignment Event becomes `failed` and escalation behavior begins.

### Cancelling A Work Request

A Work Request may be cancelled before Assignment dispatch.

Cancellation behavior:

- the Work Request becomes `cancelled`;
- pending or evaluating Routing Decisions become `cancelled`;
- no Assignment is created;
- the cancellation reason is recorded;
- Activity and Audit Logs are created;
- if the Work Request came from a Process Run Step, the Process Run Step should follow Business Process cancellation or blocker rules.

If an Assignment already exists, cancellation should use Assignment lifecycle rules and may also require a Reassignment Event or process cancellation record.

## 10. HQ / Filament Requirements

Sprint 002 HQ should make routing visible and explainable.

### Work Requests

HQ should show:

- Work Request title
- Organization
- optional Site
- source type
- Department
- required Capability
- optional SOP
- priority
- status
- routing strategy
- related Process Run and Run Step when present
- Assignment when created
- requested time
- blocker, failure, or escalation reason when present

### Routing Decisions

HQ should show:

- Work Request
- strategy
- status
- candidate count
- eligible count
- selected Employee when present
- decision reason
- failure reason when present
- manager override flag and reason when present
- actor when present
- decided timestamp

### Department Queues

HQ should show:

- Department
- optional Site context
- queue status
- default routing strategy
- pending Work Requests
- blocked Work Requests
- failed Work Requests
- overloaded Employees
- last selected Employee for round-robin queues when present
- paused reason and paused-until timestamp when present

### Failed Routing

HQ should provide an attention view for:

- Work Requests with `blocked`, `escalated`, or `failed` status
- Routing Decisions with `no_eligible_employee` or `failed` status
- Department Queues that are paused or overloaded
- missing required Capability cases
- manual routing that has waited too long
- latest Activity and Audit Log links when available

### Reassignment History

HQ should show:

- Assignment
- original Employee
- replacement Employee when present
- Work Request when present
- Routing Decision when present
- reason
- status
- manager override flag
- requested actor
- approved actor
- occurred timestamp

## 11. Acceptance Criteria

The Work Router spec is ready for implementation planning when the checks below are true.

### Specification Checks

- The spec clearly separates Business Process orchestration from Employee assignment.
- The target architecture is documented from Business Process to Employee.
- Work Request is defined as separate from Assignment.
- Router, Routing Strategy, Candidate Employee, Eligibility Rule, Routing Decision, Routing Failure, Department Queue, Employee Workload, Assignment Dispatch, Reassignment, and Escalation are defined.
- Relationship to Organization, Site, Department, Employee, Capability, SOP, Policy, Assignment, Activity, Audit Log, Process Definition, Process Run, Process Run Step, and Assignment Template is documented.
- Domain rules state that Business Processes do not choose Employees directly.
- Simple routing strategies are defined.
- Future routing strategies are named but out of scope.
- Employee eligibility rules are explicit.
- Database proposals include fields, relationships, statuses, indexes, and notes for all requested tables.
- Runtime behavior covers Work Request creation, eligibility evaluation, Employee selection, Assignment creation, no eligible Employee, blocking, escalation, reassignment, and cancellation.
- HQ requirements cover Work Requests, Routing Decisions, Department Queues, failed routing, and reassignment history.
- Out of scope exclusions are explicit.

### Implementation Readiness Checks

- The spec can guide a later implementation without introducing real AI routing.
- The spec does not require OpenAI, real AI generation, external notifications, billing, article publishing, or Razbudise integration.
- The spec keeps the Router generic enough for Razbudise, OFFMI, AI Day Trader, OneFiveFour internal operations, and future Organizations.
- The spec does not rename Assignment to Task.
- The spec does not require Business Processes to assign Employees directly.

## 12. Out Of Scope

Sprint 002 Work Router must not include:

- OpenAI
- AI-assisted routing
- real AI generation
- Razbudise integration
- article publishing
- billing
- advanced memory/vector search
- real-time queue optimization
- external notifications
- direct CMS publishing
- autonomous internet browsing
- provider cost tracking
- performance-weighted routing
- cost-aware routing
- deadline-aware routing
- complex visual workflow builder

The Work Router may be deterministic, manual, or mock-driven in Sprint 002, but it must not perform real AI routing or external publishing actions.
