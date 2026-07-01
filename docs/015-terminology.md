# Terminology

This document is the short terminology guide for README, docs, specs, UI copy, API naming, and database naming.

The canonical business dictionary is [Domain Language](018-domain-language.md).

## Canonical Terms

| Term | Meaning | Use Instead Of |
| --- | --- | --- |
| Organization | A company, customer, or internal operating entity that owns departments, policies, SOPs, AI Employees, assignments, and data. | Tenant, client, workspace when the business entity is meant |
| Department | A functional team inside an Organization. | Category, group, folder |
| AI Employee | A persistent digital worker with identity, role, permissions, capabilities, memory settings, assignments, metrics, and audit history. | Bot, prompt, model, agent when identity is meant |
| Assignment | A concrete work item given to an AI Employee. | Task |
| Business Process | An end-to-end company process such as article production or SEO review. | Workflow when speaking to users |
| SOP | A versioned Standard Operating Procedure that tells AI Employees how repeatable work should be performed. | Prompt template, checklist when official procedure is meant |
| Capability | An allowed action or tool class an AI Employee may use. | Skill, tool, permission when execution ability is meant |
| Brain / Model | The provider, model, and runtime settings currently used by an AI Employee. | Employee identity, bot identity |
| Company Policy | A rule or guideline that constrains work for an Organization. | Generic policy when organization scope matters |

## Usage Rules

- Use AI Employee as the product term. Do not reduce employees to models, prompts, or bots.
- Use Assignment as the canonical unit of work.
- Use Business Process for the company process. Use workflow only when referring to a future technical workflow engine.
- Use SOP for repeatable standard operating instructions.
- Use Capability for executable ability. Permissions define whether a Capability is allowed in a given scope.
- Use Brain / Model when discussing provider/model/runtime configuration.
- Use Organization for the business owner of data and operations.

## Database Naming

- `organizations`
- `departments`
- `ai_employees`
- `assignments`
- `assignment_logs`
- `employee_messages`
- `employee_performance_metrics`
- `company_policies`
- `standard_operating_procedures`

Future tables may be added for capabilities, brain profiles, business process definitions, provider usage, knowledge, and integrations.
