# Sprint 001: Organizational Core v0.1

## Status

Draft implementation specification.

This document defines the first buildable sprint for OneFiveFour AI Platform. It uses the canonical language from `docs/018-domain-language.md`.

No application code, Laravel code, Next.js code, Docker code, or provider integration is part of this document.

## 1. Sprint Goal

Can OneFiveFour represent a real company?

Sprint 001 proves that the platform can model the organizational structure of a publishing company before it attempts real AI generation, publishing, billing, or external integrations.

At the end of the sprint, a human operator should be able to open HQ and understand:

- which Organization is being managed
- which Sites it operates
- which Departments exist
- which Employees belong to those Departments
- what Capabilities Employees have
- which Policies and SOPs govern work
- which Assignments are current
- what important Activity happened recently
- which important actions were recorded in Audit Logs

The sprint succeeds when the platform feels like the beginning of an operating company, not a prompt runner.

## 2. Scope

Sprint 001 includes the organizational model and the first HQ view.

### Organizations

An Organization is the top-level business entity that owns platform work and context.

Sprint 001 must support at least one Organization with status, identity, summary information, and governance context.

### Sites

A Site is a publication, website, brand, channel, or content property operated by an Organization.

Sprint 001 must support Sites as Organization-owned records. Sites provide publishing context, but no publishing integration is built in this sprint.

### Departments

A Department is a functional team inside an Organization.

Sprint 001 must support Departments such as Editorial, Research, Writing, Localization, SEO, Trust & Safety, Creative, Analytics, and Operations.

### Employees

An Employee is the canonical product name for a persistent AI worker identity inside an Organization.

Sprint 001 must support Employees with Organization, Department, role, mission, status, manager relationship, and Capabilities. Employees do not call real AI providers in this sprint.

### Capabilities

A Capability describes what an Employee is able to do.

Sprint 001 must support a small Capability catalog and assignment of Capabilities to Employees. Capabilities may also be required by SOPs.

### Company Policies

Use the canonical term Policy in product language. "Company Policies" means Organization-level Policies that govern Employees, Departments, SOPs, and Assignments.

Sprint 001 must support versioned Policies with status, category, body, and scope.

### Standard Operating Procedures

Use SOP as the short product term.

Sprint 001 must support versioned SOPs owned by Departments and governed by the Organization. SOPs define repeatable procedures, required Capabilities, success criteria, review rules, and escalation conditions.

### Assignments

An Assignment is concrete work given to an Employee.

Sprint 001 must support manually seeded or manually created Assignments with status, briefing, expected output, assigned Employee, Department, optional Site, optional SOP, and review path.

### Activity Feed

Activity is the readable operational timeline.

Sprint 001 must support an Organization-level Activity Feed for important events such as Employee creation, Assignment status changes, SOP changes, Policy changes, and escalation flags.

### Audit Logs

An Audit Log is the formal record of important actions and changes.

Sprint 001 must support Audit Logs for significant state changes, permission or Capability changes, Policy changes, SOP changes, and Assignment changes.

## 3. Out of Scope

Sprint 001 must not include:

- OpenAI API
- real AI generation
- Razbudise integration
- article publishing
- billing
- advanced memory/vector search
- Brain / Model provider abstraction beyond optional placeholder text
- CMS webhooks
- public customer onboarding
- complex Business Process engine
- autonomous internet browsing
- provider cost tracking
- production analytics dashboards

Razbudise may be used as sample seed data only if helpful. The core model must stay Organization-independent.

## 4. Domain Relationships

- Organizations own Sites, Departments, Employees, Policies, SOPs, Assignments, Activity, and Audit Logs.
- Sites belong to Organizations.
- Sites may provide context for Assignments, SOPs, Policies, and Activity.
- Departments belong to Organizations.
- Departments may have a manager.
- Departments own SOPs.
- Departments group Employees by business function.
- Employees belong to exactly one Organization.
- Employees belong to exactly one Department.
- Employees may have a manager.
- Employees have Capabilities.
- Capabilities describe what Employees can perform.
- SOPs may require Capabilities.
- Policies belong to Organizations.
- Policies may govern the whole Organization, specific Sites, specific Departments, or specific SOPs.
- SOPs implement Policies as repeatable procedures.
- Assignments belong to Organizations.
- Assignments are performed by Employees.
- Assignments may belong to Departments.
- Assignments may reference Sites when the work is site-specific.
- Assignments may follow SOPs.
- Assignments may require human review.
- Activity records a readable timeline of important events.
- Audit Logs record formal evidence of important actions, decisions, and state changes.
- Activity is less formal than Audit Logs.
- Every important action should create an Audit Log.

## 5. Proposed Database Tables

The table names below follow the canonical language in `docs/018-domain-language.md`. Older documents may still mention `ai_employees` or `company_policies`; Sprint 001 should use `employees` and `policies` unless implementation constraints require a documented ADR.

Use explicit foreign keys for core relationships. Use JSON only for flexible text or structured content that is likely to change before the model stabilizes.

### `organizations`

Represents a business entity using OneFiveFour AI Platform.

Fields:

- `id`
- `name`
- `slug`
- `legal_name` nullable
- `status`
- `timezone`
- `locale`
- `primary_domain` nullable
- `summary` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- has many Sites
- has many Departments
- has many Employees
- has many Policies
- has many SOPs
- has many Assignments
- has many Activity records
- has many Audit Logs

Statuses:

- `planned`
- `active`
- `paused`
- `archived`

Indexes and constraints:

- unique `slug`
- index `status`
- index `created_at`

Notes:

- Organization is the top-level owner of Sprint 001 data.
- Customer is a commercial view of Organization and does not need a separate Sprint 001 table.

### `sites`

Represents a publication, website, brand, channel, or content property operated by an Organization.

Fields:

- `id`
- `organization_id`
- `name`
- `slug`
- `status`
- `site_type` nullable
- `primary_domain` nullable
- `default_locale` nullable
- `timezone` nullable
- `audience_notes` nullable
- `editorial_context` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may be referenced by Policies
- may be referenced by SOPs
- may be referenced by Assignments
- may appear in Activity and Audit Logs

Statuses:

- `planned`
- `active`
- `paused`
- `archived`

Indexes and constraints:

- unique `organization_id`, `slug`
- index `organization_id`, `status`
- index `primary_domain`

Notes:

- Sites provide context only in Sprint 001.
- No CMS integration or publishing behavior is included.

### `departments`

Represents a functional team inside an Organization.

Fields:

- `id`
- `organization_id`
- `parent_department_id` nullable
- `manager_employee_id` nullable
- `name`
- `slug`
- `status`
- `purpose` nullable
- `sort_order`
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may belong to a parent Department
- may have a manager Employee
- has many Employees
- owns SOPs
- may receive Assignments
- may be governed by Policies

Statuses:

- `planned`
- `active`
- `paused`
- `archived`

Indexes and constraints:

- unique `organization_id`, `slug`
- index `organization_id`, `status`
- index `organization_id`, `parent_department_id`
- index `manager_employee_id`

Notes:

- `manager_employee_id` can be nullable to avoid circular seed requirements.
- Department hierarchy is allowed but not required in Sprint 001 UI.

### `employees`

Represents a persistent Employee identity inside an Organization.

Fields:

- `id`
- `organization_id`
- `department_id`
- `manager_employee_id` nullable
- `employee_code`
- `full_name`
- `slug`
- `role_title`
- `employment_status`
- `avatar_url` nullable
- `bio` nullable
- `job_description` nullable
- `mission` nullable
- `responsibilities` json nullable
- `languages` json nullable
- `communication_style` nullable
- `personality_profile` json nullable
- `approval_authority_level` nullable
- `metadata` json nullable
- `hired_at` nullable
- `paused_at` nullable
- `retired_at` nullable
- `archived_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Department
- may have a manager Employee
- may manage Employees
- has Capabilities through `employee_capabilities`
- performs Assignments
- may appear in Activity and Audit Logs

Statuses:

- `draft`
- `training`
- `active`
- `paused`
- `retired`
- `archived`

Indexes and constraints:

- unique `organization_id`, `employee_code`
- unique `organization_id`, `slug`
- index `organization_id`, `department_id`
- index `employment_status`
- index `manager_employee_id`

Notes:

- Employee identity must survive Assignment completion, status changes, Department moves, Policy changes, SOP changes, and future Brain changes.
- Sprint 001 does not execute real AI work.
- Promotion is not an Employee status. Promotion is a career event and should later be modeled through employee career history, reviews, or promotion events.

### `capabilities`

Represents an executable ability or tool class.

Fields:

- `id`
- `capability_key`
- `name`
- `description` nullable
- `category` nullable
- `status`
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- assigned to Employees through `employee_capabilities`
- required by SOPs through `sop_capabilities`
- may be referenced by Assignments through `required_capability_keys` or a future join table

Statuses:

- `proposed`
- `active`
- `deprecated`
- `retired`

Indexes and constraints:

- unique `capability_key`
- index `status`
- index `category`

Notes:

- Capabilities are defined by the platform and applied within Organizations.
- Permissions decide whether a Capability is allowed in a specific context. Detailed Permission modeling is not part of Sprint 001.

### `employee_capabilities`

Represents which Capabilities an Employee has.

Fields:

- `id`
- `organization_id`
- `employee_id`
- `capability_id`
- `status`
- `level` nullable
- `notes` nullable
- `granted_at` nullable
- `revoked_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to an Employee
- belongs to a Capability

Statuses:

- `active`
- `paused`
- `retired`

Indexes and constraints:

- unique `employee_id`, `capability_id`
- index `organization_id`, `employee_id`
- index `organization_id`, `capability_id`
- index `status`

Notes:

- The implementation must enforce that the Employee belongs to the same Organization as `organization_id`.
- Changes should create Audit Logs.

### `policies`

Represents Organization-level rules and constraints. This is the canonical Sprint 001 table for Company Policies.

Fields:

- `id`
- `organization_id`
- `policy_key`
- `title`
- `category`
- `status`
- `body`
- `version`
- `effective_from` nullable
- `effective_to` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may govern an Organization, Site, Department, Employee, SOP, or Assignment through `policy_scopes`
- may be referenced by SOPs through `sop_policies`
- may appear in Activity and Audit Logs

Statuses:

- `draft`
- `active`
- `superseded`
- `archived`

Indexes and constraints:

- unique `organization_id`, `policy_key`, `version`
- index `organization_id`, `status`
- index `category`
- index `effective_from`, `effective_to`

Notes:

- Policies should be versioned so historical Assignments can be understood later.
- Policy changes should create Audit Logs.

### `policy_scopes`

Represents where a Policy applies.

Fields:

- `id`
- `organization_id`
- `policy_id`
- `scope_type`
- `scope_id` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Policy
- points to an Organization, Site, Department, Employee, SOP, or Assignment scope

Statuses:

- no lifecycle status required in Sprint 001

Indexes and constraints:

- unique `policy_id`, `scope_type`, `scope_id`
- index `organization_id`, `scope_type`
- index `policy_id`

Notes:

- `scope_type = organization` should use `scope_id = null`.
- The implementation must validate that scoped records belong to the same Organization.

### `standard_operating_procedures`

Represents versioned procedures for repeatable work.

Fields:

- `id`
- `organization_id`
- `department_id`
- `site_id` nullable
- `sop_key`
- `title`
- `status`
- `purpose`
- `trigger_description` nullable
- `inputs_schema` json nullable
- `steps` json
- `success_criteria` json nullable
- `quality_checks` json nullable
- `escalation_rules` json nullable
- `output_expectations` json nullable
- `version`
- `effective_from` nullable
- `effective_to` nullable
- `metadata` json nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to a Department
- may belong to a Site context
- may reference Policies through `sop_policies`
- may require Capabilities through `sop_capabilities`
- may guide Assignments
- may appear in Activity and Audit Logs

Statuses:

- `draft`
- `active`
- `superseded`
- `archived`

Indexes and constraints:

- unique `organization_id`, `sop_key`, `version`
- index `organization_id`, `department_id`
- index `organization_id`, `status`
- index `site_id`

Notes:

- Departments own SOPs. Organization governs them.
- SOP changes should create Audit Logs.

### `sop_policies`

Represents Policies referenced by SOPs.

Fields:

- `id`
- `organization_id`
- `standard_operating_procedure_id`
- `policy_id`
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to an SOP
- belongs to a Policy

Statuses:

- no lifecycle status required in Sprint 001

Indexes and constraints:

- unique `standard_operating_procedure_id`, `policy_id`
- index `organization_id`, `standard_operating_procedure_id`
- index `organization_id`, `policy_id`

Notes:

- The implementation must validate that the SOP and Policy belong to the same Organization.

### `sop_capabilities`

Represents Capabilities required by SOPs.

Fields:

- `id`
- `organization_id`
- `standard_operating_procedure_id`
- `capability_id`
- `required_level` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- belongs to an SOP
- belongs to a Capability

Statuses:

- no lifecycle status required in Sprint 001

Indexes and constraints:

- unique `standard_operating_procedure_id`, `capability_id`
- index `organization_id`, `standard_operating_procedure_id`
- index `organization_id`, `capability_id`

Notes:

- SOP Capability requirements help show whether an Employee can perform an Assignment that follows that SOP.

### `assignments`

Represents concrete work given to an Employee.

Fields:

- `id`
- `organization_id`
- `site_id` nullable
- `department_id`
- `employee_id`
- `standard_operating_procedure_id` nullable
- `title`
- `assignment_type`
- `priority`
- `status`
- `briefing` json
- `expected_output` nullable
- `input_payload` json nullable
- `output_payload` json nullable
- `required_capability_keys` json nullable
- `confidence_score` nullable
- `quality_score` nullable
- `escalation_required`
- `review_required`
- `review_path` nullable
- `due_at` nullable
- `started_at` nullable
- `completed_at` nullable
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may belong to a Site context
- belongs to a Department
- assigned to an Employee
- may follow an SOP
- may generate Activity
- must create Audit Logs for important state changes

Statuses:

- `pending`
- `accepted`
- `in_progress`
- `blocked`
- `needs_review`
- `completed`
- `failed`
- `cancelled`

Priority values:

- `low`
- `normal`
- `high`
- `urgent`

Indexes and constraints:

- index `organization_id`, `status`
- index `organization_id`, `employee_id`, `status`
- index `organization_id`, `department_id`, `status`
- index `organization_id`, `site_id`, `status`
- index `standard_operating_procedure_id`
- index `due_at`

Notes:

- Sprint 001 Assignments may be manually created or seeded.
- Assignment execution can be simulated through status and output fields. No real AI generation is allowed.
- Assignment status changes should create Activity and Audit Logs.

### `activities`

Represents the readable Activity Feed.

Fields:

- `id`
- `organization_id`
- `site_id` nullable
- `department_id` nullable
- `employee_id` nullable
- `assignment_id` nullable
- `audit_log_id` nullable
- `activity_type`
- `status`
- `title`
- `body` nullable
- `metadata` json nullable
- `occurred_at`
- `created_at`
- `updated_at`

Relationships:

- belongs to an Organization
- may reference a Site
- may reference a Department
- may reference an Employee
- may reference an Assignment
- may reference an Audit Log

Statuses:

- `created`
- `visible`
- `filtered`
- `archived`

Indexes and constraints:

- index `organization_id`, `occurred_at`
- index `organization_id`, `activity_type`
- index `organization_id`, `status`
- index `site_id`, `occurred_at`
- index `department_id`, `occurred_at`
- index `employee_id`, `occurred_at`
- index `assignment_id`, `occurred_at`

Notes:

- Activity is for HQ readability.
- Activity may summarize events that also have formal Audit Logs.

### `audit_logs`

Represents formal records of important actions and changes.

Fields:

- `id`
- `organization_id`
- `actor_type` nullable
- `actor_id` nullable
- `auditable_type`
- `auditable_id`
- `event_type`
- `action`
- `summary` nullable
- `before_state` json nullable
- `after_state` json nullable
- `reason` nullable
- `metadata` json nullable
- `occurred_at`
- `created_at`

Relationships:

- belongs to an Organization
- may reference an actor, such as a human user, Employee, system process, or future Integration
- references the changed object through `auditable_type` and `auditable_id`
- may be linked from Activity

Statuses:

- no editable lifecycle status required in Sprint 001
- retention states may later be `created`, `retained`, `archived`

Indexes and constraints:

- index `organization_id`, `occurred_at`
- index `organization_id`, `event_type`
- index `auditable_type`, `auditable_id`
- index `actor_type`, `actor_id`

Notes:

- Audit Logs should not be casually edited.
- Important actions must create Audit Logs even if no Activity item is displayed.
- Audit Logs are more formal than Activity.

## 6. UI/HQ Requirements

The first HQ screen is the main proof of Sprint 001.

HQ should be an operational overview, not a marketing page.

### Required first-screen content

The first HQ screen must show:

- Organization summary
- Departments
- Employees
- Current Assignments
- Activity Feed

### Organization summary

Show:

- Organization name
- status
- primary domain if present
- timezone and locale
- number of Sites
- number of Departments
- number of Employees
- number of active Assignments
- number of active Policies
- number of active SOPs

### Departments

Show:

- Department name
- status
- purpose
- manager if assigned
- Employee count
- active Assignment count
- active SOP count

### Employees

Show:

- full name
- role title
- Department
- employment status
- manager if assigned
- Capability summary
- current Assignment count

Use Employee as the primary UI term. Do not present Employees as AI Models, prompts, bots, or generic agents.

### Current Assignments

Show:

- title
- status
- priority
- Department
- assigned Employee
- optional Site
- optional SOP
- due date if present
- escalation flag
- review flag

Use Assignment as the canonical unit of work. Do not use Task in UI copy for this concept.

### Activity Feed

Show recent Activity items with:

- time
- Activity type
- readable title
- linked Organization, Site, Department, Employee, Assignment, Policy, or SOP when available
- optional connection to Audit Log

### Navigation expectations

Sprint 001 HQ should make these records reachable:

- Organizations
- Sites
- Departments
- Employees
- Capabilities
- Policies
- SOPs
- Assignments
- Activity
- Audit Logs

The UI may be admin-first and simple. It must still use canonical terms consistently.

## 7. Acceptance Criteria

Sprint 001 is complete when all checks below pass.

### Domain model checks

- The system can store at least one Organization.
- The Organization can own multiple Sites.
- The Organization can own multiple Departments.
- Each Department belongs to exactly one Organization.
- The Organization can own multiple Employees.
- Each Employee belongs to exactly one Organization and exactly one Department.
- Employees can have Capabilities.
- Departments can own SOPs.
- Policies can govern an Organization and can be scoped to Sites or Departments.
- Assignments can be assigned to Employees.
- Assignments can reference Departments, optional Sites, and optional SOPs.
- Activity can record readable important events.
- Audit Logs can record formal important actions and state changes.

### Status checks

- Organizations, Sites, and Departments support `planned`, `active`, `paused`, and `archived`.
- Employees support `draft`, `training`, `active`, `paused`, `retired`, and `archived`.
- Capabilities support `proposed`, `active`, `deprecated`, and `retired`.
- Policies and SOPs support `draft`, `active`, `superseded`, and `archived`.
- Assignments support `pending`, `accepted`, `in_progress`, `blocked`, `needs_review`, `completed`, `failed`, and `cancelled`.

### HQ checks

- The first HQ screen shows Organization summary.
- The first HQ screen shows Departments.
- The first HQ screen shows Employees.
- The first HQ screen shows current Assignments.
- The first HQ screen shows Activity Feed.
- HQ uses Organization, Site, Department, Employee, Capability, Policy, SOP, Assignment, Activity, and Audit Log consistently.
- HQ does not use Task as the product term for Assignment.
- HQ does not present Employees as AI Models, prompts, bots, or generic agents.

### Seed data checks

- Seed data includes one Organization.
- Seed data includes at least one Site.
- Seed data includes the initial Departments from existing docs.
- Seed data includes the initial Employees from existing docs.
- Seed data assigns Capabilities to Employees.
- Seed data includes at least one active Policy.
- Seed data includes at least one active SOP owned by a Department.
- Seed data includes at least one current Assignment assigned to an Employee.
- Seed data includes Activity and Audit Log examples.

### Auditability checks

- Creating or updating an Employee creates an Audit Log.
- Changing an Employee status creates an Audit Log.
- Changing Employee Capabilities creates an Audit Log.
- Creating or updating a Policy creates an Audit Log.
- Creating or updating an SOP creates an Audit Log.
- Creating an Assignment creates an Audit Log and Activity.
- Changing Assignment status creates an Audit Log and Activity.
- Audit Logs retain enough before and after state to explain what changed.

### Out-of-scope checks

- No OpenAI API call exists in Sprint 001 behavior.
- No real AI generation exists in Sprint 001 behavior.
- No Razbudise integration exists beyond optional seed data.
- No article publishing exists.
- No billing exists.
- No advanced memory/vector search exists.
- No CMS webhook or external publishing action exists.

### Test checks

- Automated tests cover Organization, Site, Department, Employee, Capability, Policy, SOP, Assignment, Activity, and Audit Log persistence.
- Automated tests cover Organization scoping for core records.
- Automated tests cover required relationships.
- Automated tests cover key status transitions.
- Automated tests cover Audit Log creation for important changes.
- Automated tests cover HQ data loading from seed data or fixtures.

## 8. Implementation Phases

These phases are intentionally small so Codex can implement, test, and verify one layer at a time after this spec is approved.

### Phase 1: Backend foundation

- Confirm the backend application structure before writing code.
- Confirm authentication/admin assumptions.
- Define Organization scoping conventions.
- Define shared status handling conventions.
- Define Audit Log creation conventions.
- Define Activity creation conventions.
- Document any ADR needed for table names, especially `employees` and `policies`.

### Phase 2: Database migrations

- Create migrations for Organizations.
- Create migrations for Sites.
- Create migrations for Departments.
- Create migrations for Employees.
- Create migrations for Capabilities and Employee Capabilities.
- Create migrations for Policies and Policy Scopes.
- Create migrations for SOPs, SOP Policies, and SOP Capabilities.
- Create migrations for Assignments.
- Create migrations for Activity.
- Create migrations for Audit Logs.
- Add indexes, foreign keys, nullable fields, and constraints from this spec.

### Phase 3: Seed data

- Seed the first Organization.
- Seed at least one Site.
- Seed initial Departments.
- Seed initial Employees.
- Seed initial Capabilities.
- Assign Capabilities to Employees.
- Seed at least one active Policy.
- Seed at least one active SOP per key Department where useful.
- Seed at least one current Assignment.
- Seed representative Activity.
- Seed representative Audit Logs.

### Phase 4: Admin/HQ screens

- Build the first HQ overview screen.
- Show Organization summary.
- Show Departments.
- Show Employees.
- Show current Assignments.
- Show Activity Feed.
- Add simple navigation to core records.
- Add basic detail screens or admin resource views for Organizations, Sites, Departments, Employees, Capabilities, Policies, SOPs, Assignments, Activity, and Audit Logs.
- Keep the UI admin-first and terminology-correct.

### Phase 5: Tests

- Add migration tests or schema checks where appropriate.
- Add model relationship tests.
- Add Organization scoping tests.
- Add status transition tests.
- Add seed data tests.
- Add HQ data loading tests.
- Add Audit Log creation tests.
- Add Activity Feed creation tests.
- Add terminology regression checks if a lightweight approach exists.

### Phase 6: Documentation updates

- Update README if the Sprint 001 implementation changes project setup or terminology.
- Update `docs/006-database.md` if table names differ from older proposed names.
- Update `docs/009-roadmap.md` with Sprint 001 completion status.
- Update `docs/014-decisions.md` with any ADR created during implementation.
- Add implementation notes for how to run tests and load seed data.

## Implementation Guardrails

- Do not connect OpenAI API in Sprint 001.
- Do not add real AI generation in Sprint 001.
- Do not add Razbudise integration in Sprint 001.
- Do not add article publishing in Sprint 001.
- Do not add billing in Sprint 001.
- Do not add advanced memory/vector search in Sprint 001.
- Do not rename Assignment to Task.
- Do not present Employee identity as an AI Model.
- Do not use Workflow as the user-facing term when Business Process is meant.
- Keep publishing decisions under human approval when publishing work appears in later sprints.
