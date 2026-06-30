# Roadmap and MVP Implementation Plan

This plan is for implementation later. Do not create Laravel, Next.js, or Docker application code until the documentation/spec phase is approved.

## MVP Goal

The MVP should prove that OneFiveFour models an AI-powered publishing organization, not simple automations.

A user should be able to see and manage:

- Organization
- Departments
- AI Employees
- Assignments
- Assignment Logs
- Employee Messages
- Performance Metrics
- Company Policies
- SOPs
- Mock Brain / Model configuration

## Phase 0: Blueprint Normalization

Status: in progress.

Outcomes:

- Normalize terminology across README, docs, and specs.
- Improve the AI Employee specification.
- Add the ADR for persistent AI Employee identities.
- Add the proposed database model.
- Add this MVP implementation plan.

## Phase 1: Project Foundation

Future implementation outcomes:

- Create the Laravel application foundation.
- Configure PostgreSQL and Redis.
- Add environment configuration.
- Add basic authentication/admin access.
- Add migrations for the core organization model.
- Add seed data for the first Organization, Departments, AI Employees, sample SOPs, and policies.

## Phase 2: Organization and Employee Administration

Future implementation outcomes:

- Organization CRUD.
- Department CRUD.
- AI Employee CRUD.
- Employee status transitions.
- Employee profile pages.
- Brain / Model configuration fields using mock provider data.
- Capability and permission editing.

## Phase 3: Assignment Operations

Future implementation outcomes:

- Create Assignments manually.
- Assign work to AI Employees.
- Track assignment statuses.
- Store assignment input and output payloads.
- Record Assignment Logs for every important state change.
- Support human review states.

## Phase 4: Communication and Metrics

Future implementation outcomes:

- Store Employee Messages.
- Display assignment-linked conversation history.
- Record employee performance metrics.
- Show basic metrics per AI Employee: assignment count, completion rate, quality score, escalation rate, and average completion time.

## Phase 5: SOPs and Policies

Future implementation outcomes:

- Create Company Policies.
- Create versioned SOPs.
- Link SOPs to Departments, Assignments, and Policies.
- Store required Capabilities and escalation rules on SOPs.
- Use SOPs to prefill Assignment briefing and success criteria.

## Phase 6: Mock AI Runtime

Future implementation outcomes:

- Define an AI provider interface.
- Implement a mock Brain / Model provider.
- Produce deterministic mock outputs for Assignments.
- Log provider usage and simulated cost.
- Keep the real OpenAI API out of scope until the core operating model is stable.

## MVP Acceptance Criteria

- A user can create or view an Organization.
- A user can create Departments inside the Organization.
- A user can create AI Employees with department, role, status, permissions, capabilities, and mock Brain / Model configuration.
- A user can create Assignments for AI Employees.
- A user can move Assignments through statuses.
- The system records Assignment Logs.
- The system stores Employee Messages.
- The system stores basic Employee Performance Metrics.
- The system stores Company Policies and SOPs.
- The UI and database use "Assignment" instead of "Task" for the unit of work.
- The UI and documentation do not present AI Employees as equivalent to AI models.

## Out of Scope for MVP

- Real OpenAI API calls.
- Automatic publishing to a CMS.
- Autonomous internet browsing.
- Billing.
- Marketplace.
- Multi-model optimization.
- Advanced memory/vector search.
- Complex workflow engine.
- Public customer self-service onboarding.
