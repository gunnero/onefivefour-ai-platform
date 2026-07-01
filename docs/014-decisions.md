# Architecture Decision Records

## ADR-001: AI Employees Are Persistent Organization Identities

Date: 2026-06-30

Status: Accepted for MVP planning

### Context

The platform is intended to be an AI-native operating system for digital publishers. The product should not feel like a prompt library, task runner, or thin wrapper over model providers.

The first specification already states that an AI Employee is not the same thing as an AI model. That decision needs to be explicit because it affects the product model, database design, UI language, audit trail, and future provider integrations.

### Decision

AI Employees are persistent identities inside an Organization.

An AI Employee may have a name, employee code, department, manager, role title, mission, responsibilities, permissions, capabilities, memory settings, assignments, messages, performance metrics, and audit history.

AI Employees are separate from AI models.

The Brain / Model is the runtime configuration assigned to an employee. It may include provider, model name, settings, context window, and cost profile. Changing the Brain / Model must not create a new AI Employee or erase the employee's history.

The platform models organizations, not simple automations.

Organizations contain Departments, AI Employees, Assignments, Business Processes, SOPs, Company Policies, logs, messages, metrics, capabilities, and integrations. Automations may exist later as implementation machinery, but the product model is an operating organization.

### Consequences

- Database tables must preserve AI Employee identity separately from Brain / Model configuration.
- Assignment history, messages, and performance metrics belong to the AI Employee identity, not to a model provider.
- User-facing language should say Assignment, Business Process, SOP, Capability, and Brain / Model.
- "Task" should not be used as the canonical unit of work.
- "Workflow" should not be the product-level term when Business Process is meant.
- Real provider integration can be delayed while the organization model is built and tested with a mock provider.

### Implementation Guidance

- Store model/provider settings as replaceable configuration on or near the AI Employee.
- Scope core records to Organization.
- Log model changes as auditable events.
- Keep direct publishing out of the MVP.
- Seed initial AI Employees as persistent staff members, not prompt templates.

## ADR-0004: Goals are a future first-class concept

Date: 2026-07-01

Status: Accepted, future implementation

### Context

OneFiveFour AI Platform models Organizations, Departments, Employees, Business Processes, Assignments, SOPs, Policies, Capabilities, Activity, and Audit Logs. Sprint 002 focuses on Business Processes and routing, but the long-term operating model needs a higher layer that explains why work exists.

Organizations do not exist to create Assignments. Organizations exist to achieve Goals.

### Decision

Goals will eventually become the top operational layer above Business Processes.

Target architecture:

```text
Goal
  -> Business Process
  -> Process Run Step
  -> Assignment Template
  -> Work Request
  -> Work Router
  -> Assignment
  -> Employee
  -> Brain
```

Business Processes coordinate work toward Goals. Assignments are execution units. Employees execute Assignments. Brains provide replaceable runtime intelligence for Employees, but Brains do not own identity or business purpose.

### Consequences

- Sprint 002 should implement Business Process and Work Router behavior without pretending Assignments are the top-level purpose of the Organization.
- Business Process records should leave room for future Goal references without requiring Goal Engine tables now.
- HQ language should continue to present Business Processes as operating company work, not as isolated automation runs.
- Future implementation may add Goals as first-class records that own outcomes, targets, KPIs, and Business Process alignment.

### Implementation Guidance

- Do not implement Goal Engine in Sprint 002.
- Do not create Goal tables, Goal services, Goal Filament resources, or Goal routing behavior in Sprint 002.
- Keep Sprint 002 focused on Business Process Definitions, Process Runs, Run Steps, Assignment Templates, Work Requests, deterministic routing, Assignments, Activity, and Audit Logs.
