# Proposed Database Model

This is a proposed domain model, not an implementation migration. It is intended to guide the first Laravel/PostgreSQL implementation later.

All tables should be scoped by `organization_id` where practical. The MVP should prefer explicit foreign keys for core relationships and JSON fields only for flexible profile/configuration data that is still expected to change.

## Naming Rules

- Use `Organization`, `Department`, `AI Employee`, `Assignment`, `Business Process`, `SOP`, `Capability`, and `Brain / Model` in user-facing documentation.
- Use snake_case plural table names.
- Use `assignment` instead of `task`.
- Use `business_process` instead of user-facing `workflow` language.
- Treat Brain / Model data as runtime configuration, not employee identity.

## organizations

Represents a customer company, internal operating company, or future tenant.

Proposed fields:

- id
- name
- slug
- legal_name nullable
- status
- timezone
- locale
- primary_domain nullable
- metadata json
- created_at
- updated_at

Indexes and constraints:

- unique slug
- index status

## departments

Represents a functional team inside an Organization.

Proposed fields:

- id
- organization_id
- parent_department_id nullable
- manager_employee_id nullable
- name
- slug
- purpose nullable
- sort_order
- metadata json
- created_at
- updated_at

Indexes and constraints:

- unique organization_id + slug
- index organization_id + parent_department_id

## ai_employees

Represents a persistent AI Employee identity.

Proposed fields:

- id
- organization_id
- department_id
- manager_employee_id nullable
- employee_code
- full_name
- slug
- role_title
- employment_status
- avatar_url nullable
- bio nullable
- job_description nullable
- mission nullable
- responsibilities json
- languages json
- personality_profile json
- communication_style nullable
- capability_keys json
- default_brain_profile json
- memory_enabled boolean
- approval_authority_level
- permissions json
- kpi_config json
- metadata json
- hired_at nullable
- paused_at nullable
- retired_at nullable
- created_at
- updated_at

Indexes and constraints:

- unique organization_id + employee_code
- unique organization_id + slug
- index organization_id + department_id
- index employment_status

Notes:

- `default_brain_profile` may include provider, model name, temperature, max tokens, context window, and cost profile.
- Changing `default_brain_profile` must not create a new AI Employee identity.
- A future implementation may normalize Brain / Model profiles into a separate table when provider configuration becomes complex.

## assignments

Represents a concrete work item given to an AI Employee.

Proposed fields:

- id
- organization_id
- department_id
- ai_employee_id
- standard_operating_procedure_id nullable
- business_process_key nullable
- requested_by_user_id nullable
- title
- assignment_type
- priority
- status
- briefing json
- input_payload json
- output_payload json nullable
- confidence_score nullable
- quality_score nullable
- escalation_required boolean
- due_at nullable
- started_at nullable
- completed_at nullable
- created_at
- updated_at

Indexes and constraints:

- index organization_id + status
- index organization_id + ai_employee_id + status
- index organization_id + department_id + status
- index due_at

## assignment_logs

Represents the audit trail for Assignment activity.

Proposed fields:

- id
- organization_id
- assignment_id
- ai_employee_id nullable
- event_type
- message nullable
- before_state json nullable
- after_state json nullable
- metadata json
- created_at

Indexes and constraints:

- index assignment_id + created_at
- index organization_id + event_type

## employee_messages

Represents structured communication between AI Employees, humans, and the system.

Proposed fields:

- id
- organization_id
- assignment_id nullable
- sender_employee_id nullable
- receiver_employee_id nullable
- sender_type
- receiver_type
- message_type
- subject nullable
- body
- metadata json
- created_at

Indexes and constraints:

- index organization_id + assignment_id
- index sender_employee_id + created_at
- index receiver_employee_id + created_at

## employee_performance_metrics

Represents measured quality, speed, cost, and outcome metrics for AI Employees.

Proposed fields:

- id
- organization_id
- ai_employee_id
- assignment_id nullable
- metric_key
- metric_name
- metric_value numeric
- metric_unit nullable
- measurement_window_start nullable
- measurement_window_end nullable
- source
- metadata json
- recorded_at
- created_at

Indexes and constraints:

- index organization_id + ai_employee_id + metric_key
- index recorded_at

## company_policies

Represents organization-level rules that SOPs and AI Employees must follow.

Proposed fields:

- id
- organization_id
- title
- policy_key
- category
- status
- body
- applies_to_department_id nullable
- applies_to_employee_id nullable
- effective_from nullable
- effective_to nullable
- version
- metadata json
- created_at
- updated_at

Indexes and constraints:

- unique organization_id + policy_key + version
- index organization_id + status
- index category

## standard_operating_procedures

Represents versioned instructions for repeatable work.

Proposed fields:

- id
- organization_id
- department_id nullable
- company_policy_id nullable
- title
- sop_key
- status
- purpose
- trigger_description nullable
- inputs_schema json
- steps json
- success_criteria json
- escalation_rules json
- required_capabilities json
- version
- effective_from nullable
- effective_to nullable
- metadata json
- created_at
- updated_at

Indexes and constraints:

- unique organization_id + sop_key + version
- index organization_id + status
- index organization_id + department_id

## Suggested Status Values

Organizations:

- active
- paused
- archived

AI Employees:

- draft
- training
- active
- paused
- retired
- archived

Assignments:

- pending
- accepted
- in_progress
- blocked
- needs_review
- completed
- failed
- cancelled

Policies and SOPs:

- draft
- active
- superseded
- archived

## Relationship Summary

- Organization has many Departments.
- Organization has many AI Employees.
- Department has many AI Employees.
- AI Employee may manage other AI Employees.
- AI Employee has many Assignments.
- Assignment has many Assignment Logs.
- Assignment may have many Employee Messages.
- AI Employee has many Employee Performance Metrics.
- Organization has many Company Policies.
- Organization has many SOPs.
- SOPs may reference Company Policies.
- Assignments may reference SOPs.
