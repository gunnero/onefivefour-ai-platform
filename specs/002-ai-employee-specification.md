# AI Employee Specification v1.0

## Purpose

AI Employees are persistent digital workers inside OneFiveFour AI Media Platform.

They are not simple AI agents and they are not tied to one model provider.

An AI Employee represents a role inside an organization.

The employee has identity, department, responsibilities, permissions, skills, memory, tasks, performance metrics, and audit history.

## Core Principle

AI Employee ≠ AI Model

The employee is persistent.
The AI model is only the current brain assigned to that employee.

Example:

Mila Andonova may use OpenAI today, Anthropic later, and a local model in the future.

Mila remains the same employee.

## Required Entities

### Organization

Represents a company or customer.

Examples:

- OneFiveFour
- Razbudise
- Future customer

### Department

Represents a business department.

Examples:

- Editorial
- Research
- Writing
- Localization
- SEO
- Trust & Safety
- Creative
- Analytics
- Operations

### AI Employee

Represents a persistent AI worker.

Required fields:

- id
- organization_id
- department_id
- manager_id nullable
- employee_code
- full_name
- slug
- role_title
- employment_status
- avatar_url nullable
- bio
- job_description
- mission
- responsibilities json
- skills json
- languages json
- personality_profile json
- communication_style
- default_model_provider nullable
- default_model_name nullable
- memory_enabled boolean
- approval_authority_level
- permissions json
- kpi_config json
- metadata json
- hired_at
- paused_at nullable
- retired_at nullable
- timestamps

## Employment Statuses

- draft
- active
- paused
- training
- retired
- archived

## Lifecycle

### Draft

Employee profile is being prepared.

### Active

Employee can receive assignments.

### Paused

Employee cannot receive new work but history remains visible.

### Training

Employee is being tested or refined.

### Retired

Employee is no longer active but remains part of history.

### Archived

Employee is hidden from normal operations but still stored for audit.

## Employee Profile

Every AI Employee should have a personnel profile.

Profile sections:

- Identity
- Department
- Role
- Manager
- Job Description
- Mission
- Responsibilities
- Skills
- Languages
- Personality
- Communication Style
- Permissions
- Tools / Capabilities
- Assigned Brain / Model
- Current Assignments
- Performance
- Activity Log
- Memory
- Audit History

## Initial Employees

### Elena Markova

Role: Editor-in-Chief AI  
Department: Editorial  
Mission: Ensure editorial quality and prepare content for human approval.

### Martin Nikolovski

Role: Researcher AI  
Department: Research  
Mission: Find reliable sources, extract facts, and prepare research packages.

### Mila Andonova

Role: Macedonian Writer AI  
Department: Writing  
Mission: Write original Macedonian articles based on approved research.

### Sara Ilieva

Role: Translator / Localization AI  
Department: Localization  
Mission: Convert foreign-language information into natural Macedonian context.

### Viktor Petrov

Role: SEO AI  
Department: SEO  
Mission: Optimize content for search, discovery, CTR, and structured metadata.

### David Kostovski

Role: Fact Checker AI  
Department: Trust & Safety  
Mission: Verify claims, detect unsupported statements, and flag risk.

## Assignments

Assignments are work items given to employees.

An employee does not execute random prompts.

An employee receives assignments from workflows, departments, or humans.

Assignment fields:

- id
- organization_id
- department_id
- ai_employee_id
- workflow_id nullable
- title
- type
- priority
- status
- briefing json
- input json
- output json
- confidence_score nullable
- quality_score nullable
- escalation_required boolean
- due_at nullable
- started_at nullable
- completed_at nullable
- timestamps

## Assignment Statuses

- pending
- accepted
- in_progress
- blocked
- needs_review
- completed
- failed
- cancelled

## Communication

AI Employees may communicate through structured messages.

Messages should be stored for audit.

Message fields:

- sender_employee_id
- receiver_employee_id nullable
- assignment_id nullable
- workflow_id nullable
- message_type
- subject
- body
- metadata json

## Escalation

Employees must escalate when:

- source conflict is detected
- confidence is low
- claim cannot be verified
- task requires human approval
- sensitive topic is detected
- tool access is missing
- task fails repeatedly

Escalation target can be:

- department manager
- another AI Employee
- human supervisor

## Performance Metrics

Every AI Employee should have KPIs.

Examples:

Researcher AI:

- source credibility score
- research completeness
- average completion time
- conflict detection rate

Writer AI:

- approval rate
- rewrite rate
- readability score
- originality score

SEO AI:

- CTR improvement
- ranking improvement
- metadata quality
- internal link relevance

Fact Checker AI:

- false positive rate
- false negative rate
- unsupported claim detection
- verification accuracy

## Memory

Employee memory should be separated into:

- personal memory
- department memory
- organization knowledge
- customer/site knowledge
- workflow memory

Memory must be auditable and editable.

## Permissions

Employees can only use tools and data they are allowed to access.

Permission examples:

- read_sources
- write_drafts
- edit_seo_metadata
- generate_images
- request_human_review
- publish_content
- access_analytics
- access_costs

Initial version should not allow direct publishing.

## Model Assignment

AI Employees may have default models.

Model assignment must be replaceable without changing the employee identity.

Fields:

- provider
- model_name
- temperature
- max_tokens
- context_window
- cost_profile

## Auditability

Every important action must be logged.

Audit log examples:

- employee created
- employee updated
- status changed
- assignment accepted
- assignment completed
- escalation created
- model changed
- permission changed
- SOP changed
- memory updated

## First Implementation Scope

Build only the foundation.

Do not connect OpenAI API yet.

Use mock AI providers.

The first UI should allow:

- create organization
- create departments
- create AI employees
- view employee profiles
- create assignments
- view assignment logs
- change employee status

## Out of Scope for v1

- real OpenAI API calls
- automatic publishing
- autonomous internet browsing
- billing
- marketplace
- multi-model optimization
- advanced memory/vector search
