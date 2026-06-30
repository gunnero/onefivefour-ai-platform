# AI Employee Specification v1.1

## Purpose

AI Employees are persistent digital workers inside OneFiveFour AI Media Platform.

They are not simple AI agents, prompts, API keys, provider accounts, or model names. They represent roles inside an Organization and operate within Departments, Business Processes, SOPs, Company Policies, Capabilities, Assignments, logs, messages, metrics, and human review.

An AI Employee has identity, department, responsibilities, permissions, capabilities, memory settings, current assignments, performance metrics, and audit history.

## Core Principle

AI Employee is not the same thing as AI Model.

The AI Employee is persistent.

The Brain / Model is the replaceable runtime configuration currently assigned to that employee.

Example:

Mila Andonova may use a mock provider during MVP development, OpenAI later, Anthropic later, and a local model in the future.

Mila remains the same AI Employee.

## Product Principles

- Build Organizations, not simple automations.
- Represent operational work as Assignments, not generic tasks.
- Represent end-to-end company work as Business Processes, not isolated prompt chains.
- Use SOPs to describe repeatable company procedures.
- Use Capabilities to describe what an AI Employee is allowed and able to do.
- Keep Brain / Model configuration separate from AI Employee identity.
- Keep direct publishing and sensitive approvals under human control in the MVP.
- Log every important action for auditability.

## Canonical Terminology

| Term | Definition |
| --- | --- |
| Organization | A company, customer, or internal operating entity that owns departments, policies, SOPs, AI Employees, assignments, and data. |
| Department | A functional team inside an Organization. |
| AI Employee | A persistent digital worker with identity, role, department, permissions, capabilities, memory settings, assignments, metrics, and audit history. |
| Assignment | A concrete work item given to an AI Employee. |
| Business Process | An end-to-end company process such as article production, translation, fact-checking, SEO review, or content refresh. |
| SOP | A versioned Standard Operating Procedure that tells AI Employees how repeatable work should be performed. |
| Capability | An allowed action or tool class an AI Employee may use. |
| Brain / Model | The provider, model, and runtime settings currently used by an AI Employee. |
| Company Policy | A rule or guideline that constrains work for an Organization. |

## Required Entities

### Organization

Represents a company, customer, or internal operating entity.

Examples:

- OneFiveFour
- Razbudise
- Future customer

An Organization owns Departments, AI Employees, Assignments, Company Policies, SOPs, logs, messages, metrics, and integration settings.

### Department

Represents a business department inside an Organization.

Initial department examples:

- Editorial
- Research
- Writing
- Localization
- SEO
- Trust & Safety
- Creative
- Analytics
- Operations

Departments group AI Employees, policies, SOPs, and assignments around a business function.

### AI Employee

Represents a persistent AI worker identity.

The identity must survive:

- Brain / Model changes
- Assignment completion
- Department moves
- Permission changes
- Status changes
- SOP updates
- Policy updates

Required profile groups:

- Identity: id, organization, department, manager, employee code, full name, slug, role title, status.
- Profile: avatar, bio, job description, mission, responsibilities, languages, personality, communication style.
- Authority: permissions, approval level, allowed Capabilities, escalation rules.
- Runtime: default Brain / Model configuration, memory setting, provider constraints.
- Work history: Assignments, Assignment Logs, Employee Messages, Performance Metrics, audit events.
- Lifecycle: hired, paused, retired, archived timestamps.

### Assignment

Represents a concrete work item given to an AI Employee.

Assignments may be created by:

- Human operators
- Departments
- Business Processes
- SOP-driven flows
- Integrations
- Scheduled operations in a later phase

An Assignment should always have a clear briefing, expected output, status, owner, audit log, and review path.

### Business Process

Represents an end-to-end company process.

Examples:

- Article production
- Research package creation
- Translation and localization
- Fact-checking
- SEO review
- Content refresh
- Analytics review

Business Processes may later be backed by a workflow engine. The product concept should remain Business Process.

### SOP

Represents a versioned Standard Operating Procedure.

An SOP defines:

- Purpose
- Trigger
- Required inputs
- Steps
- Required Capabilities
- Applicable Company Policies
- Success criteria
- Quality checks
- Escalation rules
- Expected output
- Version and status

### Capability

Represents an allowed action or tool class.

Capability examples:

- source_research
- extract_facts
- draft_article
- localize_content
- suggest_seo_metadata
- verify_claims
- generate_media_brief
- read_analytics
- request_human_review

Permissions decide whether a Capability is allowed in a specific scope. Capabilities describe what the AI Employee can do.

### Brain / Model

Represents runtime AI configuration.

Fields may include:

- provider
- model_name
- temperature
- max_tokens
- context_window
- cost_profile
- safety_profile
- fallback_model nullable

The MVP should use mock Brain / Model configuration only. Real provider integration is out of scope for the first implementation.

### Company Policy

Represents an Organization-level rule or guideline.

Examples:

- Do not publish without human approval.
- Escalate health, legal, financial, or political claims.
- Prefer Macedonian context and language standards for Razbudise.
- Require source citations for factual claims.

Policies should be versioned and auditable.

## Employment Statuses

- draft
- training
- active
- paused
- retired
- archived

## Lifecycle

### Draft

Employee profile is being prepared and cannot receive production Assignments.

### Training

Employee is being tested, calibrated, or refined.

### Active

Employee can receive Assignments.

### Paused

Employee cannot receive new work, but history remains visible.

### Retired

Employee is no longer active but remains part of historical records.

### Archived

Employee is hidden from normal operations but still stored for audit.

## Employee Profile

Every AI Employee should have a personnel profile.

Profile sections:

- Identity
- Organization
- Department
- Role
- Manager
- Job Description
- Mission
- Responsibilities
- Languages
- Personality
- Communication Style
- Permissions
- Capabilities
- Assigned Brain / Model
- Current Assignments
- Performance Metrics
- Employee Messages
- Activity Log
- Memory
- Audit History

## Initial Employees

### Elena Markova

Role: Editor-in-Chief AI

Department: Editorial

Mission: Ensure editorial quality and prepare content for human approval.

Initial Capabilities:

- review_draft
- enforce_editorial_policy
- request_rewrite
- request_human_review

### Martin Nikolovski

Role: Researcher AI

Department: Research

Mission: Find reliable sources, extract facts, and prepare research packages.

Initial Capabilities:

- source_research
- extract_facts
- summarize_sources
- flag_source_conflict

### Mila Andonova

Role: Macedonian Writer AI

Department: Writing

Mission: Write original Macedonian articles based on approved research.

Initial Capabilities:

- draft_article
- adapt_tone
- structure_article
- request_editorial_review

### Sara Ilieva

Role: Translator / Localization AI

Department: Localization

Mission: Convert foreign-language information into natural Macedonian context.

Initial Capabilities:

- translate_content
- localize_content
- preserve_meaning
- flag_cultural_context

### Viktor Petrov

Role: SEO AI

Department: SEO

Mission: Optimize content for search, discovery, CTR, and structured metadata.

Initial Capabilities:

- suggest_seo_metadata
- analyze_keywords
- suggest_internal_links
- evaluate_title_ctr

### David Kostovski

Role: Fact Checker AI

Department: Trust & Safety

Mission: Verify claims, detect unsupported statements, and flag risk.

Initial Capabilities:

- verify_claims
- detect_unsupported_claims
- flag_sensitive_topic
- request_human_review

## Assignments

Assignments are work items given to AI Employees.

An AI Employee does not execute random prompts. An AI Employee receives Assignments from humans, Departments, Business Processes, SOPs, integrations, or scheduled operations.

Assignment fields:

- id
- organization_id
- department_id
- ai_employee_id
- standard_operating_procedure_id nullable
- business_process_key nullable
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

## Assignment Logs

Every important Assignment event should create an Assignment Log.

Log examples:

- assignment created
- assignment accepted
- assignment started
- assignment blocked
- output generated
- confidence scored
- quality scored
- escalation requested
- human review requested
- assignment completed
- assignment failed
- assignment cancelled

## Communication

AI Employees may communicate through structured Employee Messages.

Messages should be stored for audit and should be linkable to Assignments when applicable.

Message fields:

- organization_id
- sender_employee_id nullable
- receiver_employee_id nullable
- assignment_id nullable
- sender_type
- receiver_type
- message_type
- subject nullable
- body
- metadata json
- created_at

## Escalation

AI Employees must escalate when:

- source conflict is detected
- confidence is low
- claim cannot be verified
- Assignment requires human approval
- sensitive topic is detected
- applicable Company Policy requires escalation
- required Capability is missing
- tool access is missing
- Assignment fails repeatedly

Escalation target can be:

- Department manager
- another AI Employee
- human supervisor

## Performance Metrics

Every AI Employee should have performance metrics.

Metric examples:

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

Cross-employee MVP metrics:

- assignment count
- completion rate
- escalation rate
- average completion time
- average quality score

## Memory and Knowledge

Employee memory should be separated into:

- personal memory
- department memory
- organization knowledge
- customer/site knowledge
- Business Process memory
- SOP history

Memory must be auditable and editable.

Advanced memory and vector search are out of scope for the MVP.

## Permissions and Capabilities

AI Employees can only use Capabilities, tools, data, and integrations they are allowed to access.

Permission examples:

- read_sources
- write_drafts
- edit_seo_metadata
- generate_media_brief
- request_human_review
- access_analytics
- access_costs

Initial version should not allow direct publishing.

## Brain / Model Assignment

AI Employees may have default Brain / Model profiles.

Brain / Model assignment must be replaceable without changing the employee identity.

Fields:

- provider
- model_name
- temperature
- max_tokens
- context_window
- cost_profile
- safety_profile
- fallback_model nullable

Model changes should be logged as audit events.

## Company Policies and SOPs

Company Policies constrain how AI Employees work.

SOPs translate policies and business standards into repeatable procedures.

Examples:

- A fact-checking SOP may require David to verify every factual claim and escalate unsupported claims.
- An editorial SOP may require Elena to send sensitive topics to a human supervisor.
- An SEO SOP may allow Viktor to suggest metadata but not publish it directly.

Policies and SOPs should be versioned so historical Assignments can be understood later.

## Auditability

Every important action must be logged.

Audit log examples:

- employee created
- employee updated
- status changed
- assignment accepted
- assignment completed
- escalation created
- Brain / Model changed
- permission changed
- Capability changed
- SOP changed
- Company Policy changed
- memory updated

## First Implementation Scope

Build only the foundation.

Do not connect OpenAI API yet.

Use mock Brain / Model providers.

The first UI should allow:

- create or view Organization
- create Departments
- create AI Employees
- view employee profiles
- change employee status
- set mock Brain / Model configuration
- define Capabilities and permissions in simple form
- create Assignments
- view Assignment Logs
- store Employee Messages
- store Employee Performance Metrics
- create Company Policies
- create SOPs

## Out of Scope for v1

- real OpenAI API calls
- automatic publishing
- autonomous internet browsing
- billing
- marketplace
- multi-model optimization
- advanced memory/vector search
- complex Business Process engine
- public customer self-service onboarding

## Implementation References

- Terminology: `docs/015-terminology.md`
- Database model: `docs/006-database.md`
- MVP plan: `docs/009-roadmap.md`
- ADR: `docs/014-decisions.md`
