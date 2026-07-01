# Domain Language

This document defines the canonical business vocabulary of OneFiveFour AI Platform.

It is not a database document. It is not an implementation document. It is the official language used across product thinking, documentation, UI copy, planning, onboarding, and future implementation work.

Use this document when naming product concepts, explaining the platform, designing screens, writing specs, or deciding whether a new feature belongs in the organizational model.

## Language Principles

- The platform models AI-powered organizations, not prompt automations.
- Employees own identity. AI Models never own identity.
- Work happens through Assignments, not generic tasks.
- Business Processes describe company operations. Workflows may support them later.
- SOPs and Policies guide repeatable work.
- Brains are replaceable. Employees remain persistent.
- Important work must be observable through Activity and auditable through Audit Logs.

## Organization

**Purpose:** Represent the company or operating entity that owns the work, people, rules, knowledge, and outcomes in the platform.

**Definition:** An Organization is a business entity using OneFiveFour AI Platform. It may be OneFiveFour itself, Razbudise, or any future customer.

**Responsibilities:**

- Own Departments, Employees, Sites, Policies, SOPs, Business Processes, Assignments, Knowledge, Memory, Integrations, Goals, KPIs, Activity, and Audit Logs.
- Provide the business context for all work.
- Define the boundaries for access, data, reporting, and governance.

**Ownership:** An Organization is the top-level business owner of platform data and operations.

**Relationships:**

- Has many Sites.
- Has many Departments.
- Has many Employees.
- Has many Customers only when the Organization is OneFiveFour operating the platform business.
- Connects to external systems through Integrations.
- Uses Providers through Brains, Integrations, or platform services.

**Lifecycle:** Planned, active, paused, archived.

**Future Notes:** Organizations may later support billing, contracts, multiple human users, customer tiers, regional settings, and compliance profiles.

## Site

**Purpose:** Represent a publication, website, brand, magazine, channel, or content property operated by an Organization.

**Definition:** A Site is a publishing destination or brand context inside an Organization.

**Responsibilities:**

- Provide editorial, language, audience, SEO, publishing, and integration context.
- Scope content-specific Assignments and Artifacts.
- Hold site-specific standards that extend Organization-level Policies.

**Ownership:** A Site belongs to exactly one Organization.

**Relationships:**

- Belongs to an Organization.
- May be served by many Departments and Employees.
- May have site-specific Knowledge, SOPs, Goals, KPIs, Integrations, and Artifacts.
- May connect to a CMS or publishing platform through an Integration.

**Lifecycle:** Planned, active, paused, archived.

**Future Notes:** Sites may later include domains, CMS mappings, editorial calendars, content taxonomies, audience profiles, and monetization settings.

## Department

**Purpose:** Represent a functional team inside an Organization.

**Definition:** A Department is a business unit such as Editorial, Research, Writing, Localization, SEO, Trust & Safety, Creative, Analytics, or Operations.

**Responsibilities:**

- Group Employees by business function.
- Own SOPs for its function.
- Define department-level Goals and KPIs.
- Coordinate Assignments within its area of responsibility.

**Ownership:** A Department belongs to exactly one Organization.

**Relationships:**

- Has many Employees.
- May have a Manager.
- Owns SOPs.
- Participates in Business Processes.
- Receives and creates Assignments.

**Lifecycle:** Planned, active, paused, archived.

**Future Notes:** Departments may later support hierarchy, budgets, staffing plans, capacity, and department-level permissions.

## Employee

**Purpose:** Represent a persistent digital worker inside an Organization.

**Definition:** An Employee is the canonical product name for an AI Employee: a durable identity with a role, department, responsibilities, capabilities, permissions, memory, performance history, and audit trail.

**Responsibilities:**

- Execute Assignments.
- Follow SOPs and Policies.
- Use approved Capabilities and Brains.
- Produce Artifacts.
- Escalate work that requires review.
- Generate Activity and Audit Logs through important actions.

**Ownership:** An Employee belongs to exactly one Organization and exactly one Department.

**Relationships:**

- May have a Manager.
- Has one or more Roles over time.
- Has Capabilities and Permissions.
- Receives Assignments.
- Produces Artifacts.
- Uses a Brain.
- Contributes to Memory and Performance Metrics.

**Lifecycle:** Draft, training, active, paused, promoted, retired, archived.

**Future Notes:** Human staff may later exist in the same Organization model. Until then, Employee means AI Employee unless explicitly described as a human user or human supervisor.

## Manager

**Purpose:** Represent supervisory responsibility for Employees, Assignments, quality, and escalation.

**Definition:** A Manager is a human or Employee responsible for overseeing another Employee, Department, or Assignment path.

**Responsibilities:**

- Review Employee output when required.
- Resolve escalations.
- Approve changes in scope, Role, Capability, or status.
- Monitor performance, quality, and workload.
- Help enforce SOPs and Policies.

**Ownership:** A Manager relationship belongs to an Organization and is defined within its governance rules.

**Relationships:**

- May manage Employees.
- May manage a Department.
- May receive Notifications and Reviews.
- May own or approve Promotions and Retirements.

**Lifecycle:** Assigned, active, reassigned, ended.

**Future Notes:** Manager may later split into human manager, employee manager, department lead, and review owner, but the business meaning remains supervisory accountability.

## Capability

**Purpose:** Describe what an Employee is able to do.

**Definition:** A Capability is an executable ability or tool class, such as source research, draft writing, localization, SEO metadata, claim verification, analytics reading, or human review request.

**Responsibilities:**

- Define the work an Employee can perform.
- Describe required abilities for Assignments and SOPs.
- Help determine whether an Employee is eligible for specific work.

**Ownership:** Capabilities are defined by the platform and applied within Organizations.

**Relationships:**

- Employees have Capabilities.
- SOPs require Capabilities.
- Assignments may require Capabilities.
- Permissions govern whether a Capability may be used in a specific context.
- Providers and Integrations may supply technical support for some Capabilities.

**Lifecycle:** Proposed, active, deprecated, retired.

**Future Notes:** Capabilities may later become a formal catalog with levels, certifications, training status, cost profiles, and provider requirements.

## Policy

**Purpose:** Define rules, standards, and constraints that govern work.

**Definition:** A Policy is an Organization-level rule that Employees, Departments, SOPs, Business Processes, and Assignments must follow.

**Responsibilities:**

- Set safety, editorial, legal, brand, privacy, compliance, and approval boundaries.
- Define when work must be escalated or reviewed.
- Provide governance for repeatable decisions.

**Ownership:** A Policy belongs to an Organization. Some Policies may apply to specific Sites, Departments, Employees, or Business Processes.

**Relationships:**

- SOPs implement Policies.
- Employees follow Policies.
- Assignments may reference applicable Policies.
- Reviews and Audit Logs may record Policy compliance.

**Lifecycle:** Draft, active, superseded, archived.

**Future Notes:** Policies may later support versioning, approval workflows, jurisdiction, customer-specific compliance, and policy conflict detection.

## Standard Operating Procedure (SOP)

**Purpose:** Define how repeatable work should be performed.

**Definition:** A Standard Operating Procedure is a versioned procedure that translates Policies and business standards into practical steps.

**Responsibilities:**

- Describe inputs, steps, outputs, quality checks, required Capabilities, review rules, and escalation conditions.
- Standardize repeatable work across Employees and Departments.
- Make Business Processes predictable and auditable.

**Ownership:** Departments own SOPs. An Organization governs them.

**Relationships:**

- Belongs to an Organization.
- Usually belongs to a Department.
- May reference Policies.
- May be used by Business Processes.
- May guide Assignments.
- May require Capabilities and Reviews.

**Lifecycle:** Draft, active, superseded, archived.

**Future Notes:** SOPs may later support version comparison, approvals, testing, simulation, performance scoring, and automatic assignment generation.

## Assignment

**Purpose:** Represent concrete work given to an Employee.

**Definition:** An Assignment is a specific work item with an owner, briefing, expected output, status, and review path.

**Responsibilities:**

- Tell an Employee what work to perform.
- Capture input, output, owner, status, confidence, quality, and escalation needs.
- Produce Artifacts and Activity.
- Create Audit Logs for important state changes.

**Ownership:** Every Assignment belongs to an Organization and has an owner. The owner may be an Employee, human supervisor, Department, or Business Process depending on context.

**Relationships:**

- Assigned to an Employee.
- May belong to a Department.
- May be created by a Business Process.
- May follow an SOP.
- May produce Artifacts.
- May create Events, Notifications, Activity, Reviews, and Audit Logs.

**Lifecycle:** Pending, accepted, in progress, blocked, needs review, completed, failed, cancelled.

**Future Notes:** Assignments may later support dependencies, due dates, recurring schedules, queues, service-level targets, and multi-employee collaboration.

## Business Process

**Purpose:** Represent an end-to-end company operation.

**Definition:** A Business Process is a business-level process such as article production, translation, fact-checking, SEO review, content refresh, or analytics review.

**Responsibilities:**

- Coordinate Departments, Employees, SOPs, Assignments, Artifacts, Reviews, Goals, and KPIs around a business outcome.
- Define the order of work and decision points at a business level.
- Provide visibility into operational status.

**Ownership:** A Business Process belongs to an Organization and may be owned by a Department or Manager.

**Relationships:**

- Orchestrates Assignments.
- Uses SOPs.
- Involves Departments and Employees.
- Produces Artifacts.
- Emits Events and Activity.
- May be supported by Workflows later.

**Lifecycle:** Designed, active, paused, improved, retired.

**Future Notes:** Business Processes may later be modeled with a technical workflow engine, analytics, templates, and process improvement recommendations.

## Workflow

**Purpose:** Describe the operational sequence or automation mechanics that support a Business Process.

**Definition:** A Workflow is the step-by-step orchestration pattern used to move work through states. In product language, Business Process is preferred when describing the company operation.

**Responsibilities:**

- Sequence Assignment creation, routing, waiting, retrying, escalation, and completion.
- Provide repeatable execution logic for a Business Process.
- Track operational progress.

**Ownership:** A Workflow belongs to an Organization when it is business-configurable. Technical workflow machinery may belong to the platform.

**Relationships:**

- Supports Business Processes.
- Creates or updates Assignments.
- May follow SOPs.
- Emits Events, Activity, Notifications, and Audit Logs.

**Lifecycle:** Draft, active, paused, changed, retired.

**Future Notes:** Workflow should not replace Business Process in user-facing business vocabulary. It may become an implementation concept later.

## Artifact

**Purpose:** Represent a durable output of work.

**Definition:** An Artifact is a created or collected work product such as a research package, draft article, translation, SEO brief, fact-check report, image brief, analytics summary, or review note.

**Responsibilities:**

- Preserve the result of an Assignment or Business Process.
- Carry enough context for review, reuse, publication, or audit.
- Link work output back to its owner, source, and process.

**Ownership:** An Artifact belongs to an Organization and usually to the Assignment or Business Process that produced it.

**Relationships:**

- Produced by Employees.
- Produced from Assignments.
- May be attached to Reviews.
- May use Knowledge and Memory.
- May later be sent to a Site or external Integration.

**Lifecycle:** Draft, submitted, under review, approved, rejected, published externally, archived.

**Future Notes:** Artifacts may later support versions, citations, provenance, media assets, CMS mappings, and approval packages.

## Knowledge

**Purpose:** Represent Organization-owned facts, references, standards, and context.

**Definition:** Knowledge is durable information the Organization wants Employees to rely on, such as brand rules, editorial standards, audience context, source lists, product facts, site information, and internal references.

**Responsibilities:**

- Provide trusted context for Employees and Business Processes.
- Reduce repeated explanation.
- Preserve Organization-level truth separate from individual Employee Memory.

**Ownership:** Knowledge belongs to an Organization. Some Knowledge may be scoped to Sites, Departments, Policies, SOPs, or Business Processes.

**Relationships:**

- Used by Employees, Brains, SOPs, Assignments, and Business Processes.
- May be created from Artifacts or human input.
- May inform Prompt Templates and Reviews.

**Lifecycle:** Proposed, active, updated, deprecated, archived.

**Future Notes:** Knowledge may later support citations, source trust, freshness, permissions, embeddings, versioning, and approval workflows.

## Memory

**Purpose:** Preserve useful context learned through work over time.

**Definition:** Memory is retained context from past Assignments, Reviews, Employee behavior, preferences, decisions, and outcomes.

**Responsibilities:**

- Help Employees improve continuity and avoid repeated mistakes.
- Preserve useful historical context while remaining auditable and editable.
- Support better Assignment execution and Review quality.

**Ownership:** Memory belongs to an Organization. It may be scoped to an Employee, Department, Site, Business Process, or Assignment history.

**Relationships:**

- Used by Employees and Brains.
- Informed by Assignments, Reviews, Activity, Artifacts, and Audit Logs.
- Must respect Policies and Permissions.
- Distinct from Knowledge, which is more formal and Organization-approved.

**Lifecycle:** Captured, active, corrected, forgotten, archived.

**Future Notes:** Memory may later support human editing, retention rules, confidence, provenance, privacy controls, and vector search.

## Brain

**Purpose:** Define the replaceable runtime intelligence configuration used by an Employee.

**Definition:** A Brain is the operating profile that connects an Employee to AI Models, Provider settings, Prompt Templates, tool access, safety behavior, and runtime preferences.

**Responsibilities:**

- Select or configure AI Models.
- Apply model behavior, temperature, context, fallback, and safety settings.
- Support Employee work without owning Employee identity.

**Ownership:** A Brain is assigned within an Organization and used by an Employee. The Employee owns identity; the Brain does not.

**Relationships:**

- Used by Employees.
- May reference AI Models, Providers, Prompt Templates, Capabilities, Policies, and Knowledge.
- May be changed without changing Employee identity.

**Lifecycle:** Draft, active, tested, replaced, retired.

**Future Notes:** Brains may later support model routing, provider fallbacks, cost policies, evaluation results, and role-specific tuning.

## AI Model

**Purpose:** Represent a specific model used for AI reasoning or generation.

**Definition:** An AI Model is a provider-supplied or internally hosted model that can generate, classify, extract, translate, summarize, reason, or otherwise assist work.

**Responsibilities:**

- Provide runtime AI capability through a Brain.
- Produce outputs according to Prompt Templates, context, and configuration.
- Report usage, limits, quality, and cost when available.

**Ownership:** AI Models are owned by Providers or by the platform if self-hosted. They never own Employee identity.

**Relationships:**

- Used by Brains.
- Supplied by Providers.
- May support Capabilities.
- May be evaluated through KPIs, Reviews, and performance metrics.

**Lifecycle:** Available, approved, deprecated, unavailable, retired.

**Future Notes:** AI Models may later support comparison, routing, cost optimization, benchmark scores, safety profiles, and customer-specific allow lists.

## Prompt Template

**Purpose:** Provide reusable instruction structure for AI work.

**Definition:** A Prompt Template is a reusable instruction pattern used by a Brain or Assignment. It is not an Employee, SOP, Policy, or Business Process.

**Responsibilities:**

- Shape AI Model behavior for a specific type of work.
- Include variables, context requirements, output expectations, and constraints.
- Support consistency while staying subordinate to Policies and SOPs.

**Ownership:** Prompt Templates belong to an Organization or the platform, depending on whether they are customer-specific or shared.

**Relationships:**

- Used by Brains and Assignments.
- Informed by SOPs, Policies, Capabilities, Knowledge, and Reviews.
- May produce Artifacts.

**Lifecycle:** Draft, active, tested, deprecated, archived.

**Future Notes:** Prompt Templates may later support versioning, experiments, evaluation scores, approvals, and provider-specific variants.

## Event

**Purpose:** Represent something meaningful that happened.

**Definition:** An Event is a discrete occurrence in the platform, such as Assignment created, Employee paused, Review requested, SOP changed, Artifact approved, or Integration failed.

**Responsibilities:**

- Record meaningful changes.
- Trigger Notifications, Activity, Audit Logs, or Workflow steps.
- Provide a common language for operational observability.

**Ownership:** Events belong to the Organization context where they occur. Platform-level Events may belong to the platform operator.

**Relationships:**

- May create Notifications.
- May appear as Activity.
- May be recorded in Audit Logs.
- May affect Assignments, Employees, SOPs, Integrations, or Business Processes.

**Lifecycle:** Occurred, processed, retained, archived.

**Future Notes:** Events may later support subscriptions, webhooks, event streams, replay, and automation triggers.

## Notification

**Purpose:** Bring attention to something that needs awareness or action.

**Definition:** A Notification is a message to a human, Employee, Manager, Department, or system channel about an Event, Assignment, Review, escalation, failure, or deadline.

**Responsibilities:**

- Alert the right owner at the right time.
- Support review, escalation, approval, and operational awareness.
- Reduce missed work.

**Ownership:** Notifications belong to an Organization and are addressed to a recipient.

**Relationships:**

- Usually created from Events.
- May reference Assignments, Reviews, Employees, Integrations, or Business Processes.
- May appear in Activity.

**Lifecycle:** Created, delivered, read, acted on, dismissed, expired.

**Future Notes:** Notifications may later support email, Slack, in-app, SMS, routing rules, priority, and quiet hours.

## Activity

**Purpose:** Provide a readable operational timeline.

**Definition:** Activity is the human-readable feed of important work happening in an Organization, Department, Employee profile, Assignment, Site, or Business Process.

**Responsibilities:**

- Help users understand what happened recently.
- Surface progress, blockers, reviews, status changes, and outputs.
- Connect work history to business context.

**Ownership:** Activity belongs to an Organization and may be scoped to specific objects.

**Relationships:**

- Generated by Events.
- May link to Assignments, Employees, Artifacts, Reviews, Notifications, and Audit Logs.
- Less formal than Audit Logs.

**Lifecycle:** Created, visible, filtered, archived.

**Future Notes:** Activity may later support comments, filtering, summarization, daily briefs, and role-based visibility.

## Audit Log

**Purpose:** Preserve trustworthy records of important actions.

**Definition:** An Audit Log is a durable record of a significant action, decision, state change, permission change, policy change, model change, review, or integration event.

**Responsibilities:**

- Support accountability and governance.
- Explain who or what changed something, when, and why.
- Preserve evidence for operational, editorial, security, and compliance review.

**Ownership:** Audit Logs belong to an Organization. Platform-level Audit Logs belong to the platform operator.

**Relationships:**

- Created by Events and important actions.
- May reference Employees, Assignments, Policies, SOPs, Brains, AI Models, Integrations, Roles, Permissions, Reviews, and Artifacts.
- More formal than Activity.

**Lifecycle:** Created, retained, archived. Audit Logs should not be casually edited.

**Future Notes:** Audit Logs may later support retention policies, export, tamper evidence, legal hold, and compliance views.

## Role

**Purpose:** Describe a job function or authority pattern.

**Definition:** A Role describes what an Employee or human user is expected to do, such as Editor-in-Chief AI, Researcher AI, Writer AI, SEO AI, Fact Checker AI, Manager, or Human Supervisor.

**Responsibilities:**

- Clarify job expectations.
- Help assign Capabilities, Permissions, SOPs, Reviews, and Goals.
- Support consistent onboarding and promotion.

**Ownership:** Roles belong to an Organization or are provided as platform templates.

**Relationships:**

- Employees have Roles.
- Roles may imply Capabilities and default Permissions.
- Roles may connect to Department responsibilities, SOPs, Goals, and KPIs.

**Lifecycle:** Draft, active, changed, deprecated, retired.

**Future Notes:** Roles may later support templates, seniority levels, certifications, compensation analogies, and role-based dashboards.

## Permission

**Purpose:** Control what an Employee, human user, or Integration may access or do.

**Definition:** A Permission is an allowed action in a specific scope. It decides whether a Capability, record, Integration, or administrative action can be used.

**Responsibilities:**

- Protect Organization data and operations.
- Enforce boundaries from Policies and Roles.
- Limit risky actions such as publishing, model changes, policy edits, and integration changes.

**Ownership:** Permissions belong to an Organization's governance model and may also include platform-level controls.

**Relationships:**

- Granted to Employees, human users, Roles, or Integrations.
- Govern Capabilities.
- Checked during Assignments, Reviews, Policy changes, SOP changes, Brain changes, and Integration actions.

**Lifecycle:** Requested, granted, changed, suspended, revoked.

**Future Notes:** Permissions may later support approval workflows, temporary access, audit views, inherited permissions, and least-privilege recommendations.

## Goal

**Purpose:** Define a desired business outcome.

**Definition:** A Goal is a target or direction an Organization, Site, Department, Employee, Business Process, or campaign is working toward.

**Responsibilities:**

- Align work with business outcomes.
- Provide context for KPIs, Assignments, Reviews, and performance evaluation.
- Help prioritize work.

**Ownership:** Goals belong to an Organization and may be scoped to Sites, Departments, Employees, or Business Processes.

**Relationships:**

- Measured by KPIs.
- Influences Assignments and Reviews.
- May guide Business Processes, SOP changes, and Promotions.

**Lifecycle:** Proposed, active, measured, achieved, missed, retired.

**Future Notes:** Goals may later support time periods, owners, progress tracking, OKR-style structures, and recommendations.

## KPI

**Purpose:** Measure whether work is achieving the intended outcome.

**Definition:** A KPI is a key performance indicator tied to a Goal, Employee, Department, Site, Business Process, or Organization.

**Responsibilities:**

- Quantify performance, quality, speed, cost, risk, and impact.
- Inform Reviews, Promotions, Retirements, and process improvement.
- Make performance visible and comparable over time.

**Ownership:** KPIs belong to an Organization and may be owned by a Department, Manager, or business owner.

**Relationships:**

- Measures Goals.
- Applies to Employees, Departments, Sites, Assignments, Business Processes, Providers, and Integrations.
- Used in Reviews and performance metrics.

**Lifecycle:** Defined, active, measured, revised, retired.

**Future Notes:** KPIs may later support dashboards, targets, alerts, benchmarks, forecasting, and cost-quality tradeoff analysis.

## Review

**Purpose:** Evaluate work, decisions, quality, or performance before acceptance or improvement.

**Definition:** A Review is a structured evaluation by a human, Manager, Employee, Department, or system rule.

**Responsibilities:**

- Approve, reject, request changes, or escalate work.
- Improve quality and safety.
- Provide feedback for Employees, SOPs, Brains, Prompt Templates, and Business Processes.

**Ownership:** Reviews belong to an Organization and have a reviewer or review owner.

**Relationships:**

- May review Assignments, Artifacts, Employees, SOPs, Policies, Brains, AI Models, Providers, or Integrations.
- May create Notifications, Activity, Audit Logs, and Memory.
- May affect KPIs, Promotions, and Retirements.

**Lifecycle:** Requested, in review, changes requested, approved, rejected, escalated, closed.

**Future Notes:** Reviews may later support multi-step approvals, rubrics, scoring, reviewer assignment, and quality analytics.

## Promotion

**Purpose:** Represent increased trust, scope, authority, or responsibility for an Employee.

**Definition:** Promotion is a formal change that gives an Employee a higher Role, broader Capability set, increased approval authority, stronger Brain, or more complex Assignment eligibility.

**Responsibilities:**

- Recognize improved performance.
- Expand what an Employee is trusted to do.
- Record the reason and approval for increased responsibility.

**Ownership:** Promotions belong to an Organization and should be approved by a Manager or human supervisor.

**Relationships:**

- Applies to Employees.
- Informed by KPIs, Reviews, Assignment history, Audit Logs, and Goals.
- May change Role, Permissions, Capabilities, Brain, or Department responsibilities.

**Lifecycle:** Proposed, reviewed, approved, applied, recorded.

**Future Notes:** Promotions may later support levels, probation, compensation metaphors, skill certifications, and automated recommendations.

## Retirement

**Purpose:** Remove an Employee, Role, SOP, Brain, AI Model, Provider, or other concept from active operation while preserving history.

**Definition:** Retirement is the controlled end of active use. It is not deletion of business history.

**Responsibilities:**

- Stop new work from being assigned to retired items.
- Preserve past Assignments, Artifacts, Reviews, KPIs, and Audit Logs.
- Make operational history understandable after active use ends.

**Ownership:** Retirement belongs to the Organization that owns the retired item, or the platform operator for platform-level items.

**Relationships:**

- May apply to Employees, Roles, Capabilities, Policies, SOPs, Brains, AI Models, Providers, Prompt Templates, Integrations, and Business Processes.
- May trigger Notifications, Activity, and Audit Logs.

**Lifecycle:** Proposed, approved, retired, archived.

**Future Notes:** Retirement may later support replacement recommendations, migration plans, sunset windows, and impact analysis.

## Integration

**Purpose:** Connect an Organization to an external system.

**Definition:** An Integration is a configured connection between OneFiveFour AI Platform and another system, such as a CMS, analytics platform, search tool, storage provider, communication channel, or publishing system.

**Responsibilities:**

- Exchange data or actions with external systems.
- Support Sites, Assignments, Artifacts, Notifications, and Business Processes.
- Respect Permissions, Policies, and Audit requirements.

**Ownership:** Integrations belong to Organizations. Platform-level shared connectors may be maintained by OneFiveFour.

**Relationships:**

- Connect Organizations to external systems.
- May serve Sites.
- May be used by Employees through Capabilities and Permissions.
- May create Events, Notifications, Activity, and Audit Logs.
- May depend on Providers.

**Lifecycle:** Proposed, configured, active, degraded, paused, disconnected, retired.

**Future Notes:** Integrations may later support OAuth, webhooks, sync history, error handling, scoped credentials, and customer-specific configuration.

## Customer

**Purpose:** Represent the business using or buying the platform.

**Definition:** A Customer is an Organization from the commercial perspective. Razbudise is the first Customer and is represented as an Organization in the product model.

**Responsibilities:**

- Provide business context, Sites, Policies, SOPs, and operational requirements.
- Own its work outcomes and data.
- Approve publishing and sensitive decisions through its human supervisors.

**Ownership:** A Customer is owned commercially by OneFiveFour but owns its own Organization data and operating context in the product model.

**Relationships:**

- Customers are Organizations.
- Customers may have Sites, Departments, Employees, Integrations, Goals, KPIs, and Policies.
- Customers may use Providers indirectly through Brains and Integrations.

**Lifecycle:** Prospect, onboarding, active, paused, churned, archived.

**Future Notes:** Customer may later include billing, contracts, subscription plans, service tiers, support contacts, and account management.

## Provider

**Purpose:** Represent an external or internal service supplier used by the platform.

**Definition:** A Provider supplies a service such as AI Models, hosting, search, analytics, CMS access, translation, storage, messaging, or media generation.

**Responsibilities:**

- Provide capabilities or infrastructure used by Brains, Integrations, or platform services.
- Expose limits, costs, reliability, and safety constraints.
- Support operational monitoring and provider evaluation.

**Ownership:** Providers are owned externally or by the platform operator if internal. They do not own Organization identity, Employee identity, or customer work.

**Relationships:**

- Supply AI Models.
- Support Brains.
- May power Integrations.
- May affect KPIs, costs, Events, Notifications, and Audit Logs.

**Lifecycle:** Proposed, approved, active, degraded, deprecated, retired.

**Future Notes:** Providers may later support routing, fallback, cost controls, vendor risk review, contract metadata, and provider-specific performance reports.

# Organizational Design Rules

- Everything belongs to an Organization.
- Every Organization can have multiple Sites.
- Customers are Organizations.
- Integrations connect Organizations to external systems.
- Every Employee belongs to exactly one Organization.
- Every Employee belongs to exactly one Department.
- Departments own SOPs.
- Organizations own Policies.
- Policies define rules.
- SOPs define repeatable procedures.
- Employees execute Assignments.
- Every Assignment has an owner.
- Every Assignment should have a clear briefing, status, expected output, and review path.
- Business Processes orchestrate Assignments.
- Workflows may support Business Processes, but Business Process is the business term.
- Employees produce Artifacts.
- Knowledge belongs to Organizations.
- Memory belongs to Organizations and may be scoped to Employees, Departments, Sites, or Business Processes.
- AI Models never own identity.
- Employees own identity.
- Brains are replaceable.
- Brains may use AI Models, Providers, Prompt Templates, Knowledge, and runtime settings.
- Prompt Templates are reusable instructions, not Employees.
- Capabilities describe what can be done.
- Permissions decide what is allowed.
- Roles describe job function and authority patterns.
- Managers own supervision and escalation responsibility.
- Goals define desired outcomes.
- KPIs measure progress and performance.
- Reviews evaluate work, quality, decisions, or performance.
- Promotions increase trust, authority, or responsibility.
- Retirement ends active use while preserving history.
- Events describe what happened.
- Notifications ask for attention or action.
- Activity explains what happened in a readable timeline.
- Every important action creates an Audit Log.
- Audit Logs are more formal than Activity.
- Publishing decisions require human approval in the MVP.

# Glossary

| Term | Short Meaning |
| --- | --- |
| Activity | Human-readable operational timeline. |
| AI Model | Provider-supplied or internally hosted model used through a Brain. |
| Artifact | Durable output of work. |
| Assignment | Concrete work item given to an Employee. |
| Audit Log | Formal record of important actions and changes. |
| Brain | Replaceable runtime intelligence configuration used by an Employee. |
| Business Process | End-to-end company operation that coordinates work. |
| Capability | Ability or tool class an Employee can perform. |
| Customer | Commercial view of an Organization using the platform. |
| Department | Functional team inside an Organization. |
| Employee | Persistent AI worker identity inside an Organization. |
| Event | Meaningful occurrence in the platform. |
| Goal | Desired business outcome. |
| Integration | Connection between an Organization and an external system. |
| Knowledge | Organization-owned trusted context and facts. |
| KPI | Measurement of progress, quality, performance, or impact. |
| Manager | Supervisor responsible for Employees, Assignments, quality, or escalation. |
| Memory | Retained context learned through work over time. |
| Notification | Message calling attention to something. |
| Organization | Business entity that owns platform work and context. |
| Permission | Scoped allowance to access or perform an action. |
| Policy | Organization-level rule or constraint. |
| Promotion | Increased trust, authority, scope, or responsibility. |
| Prompt Template | Reusable instruction pattern for AI work. |
| Provider | External or internal service supplier. |
| Retirement | Controlled end of active use while preserving history. |
| Review | Structured evaluation of work, quality, decisions, or performance. |
| Role | Job function or authority pattern. |
| Site | Publication, website, brand, channel, or content property. |
| SOP | Versioned procedure for repeatable work. |
| Workflow | Execution sequence that may support a Business Process. |
