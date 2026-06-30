# Business Processes, SOPs, and Assignments

This project uses organization language instead of generic automation language.

Use Business Process for an end-to-end company process, SOP for a repeatable standard operating procedure, and Assignment for a concrete work item given to an AI Employee.

## Canonical Flow

1. A human, department, integration, or scheduled trigger starts a Business Process.
2. The Business Process selects the relevant SOPs.
3. SOPs define the steps, inputs, success criteria, required capabilities, escalation rules, and quality checks.
4. The system creates Assignments for AI Employees.
5. AI Employees perform the assignments using their permitted Capabilities and assigned Brain / Model.
6. Assignment Logs, Employee Messages, outputs, quality scores, and performance metrics are stored.
7. Human review handles approvals, publishing, sensitive topics, and exceptions.

## Business Process

A Business Process represents a company-level process such as:

- Article production
- Translation and localization
- Fact-checking
- SEO review
- Content refresh
- Social media preparation
- Analytics review

Business Processes may later be implemented with a workflow engine. Product documentation should still call the company concept a Business Process.

## SOP

An SOP is a versioned Standard Operating Procedure. It defines how repeatable work should be done.

An SOP should include:

- Purpose
- Trigger
- Required inputs
- Step sequence
- Required Capabilities
- Company Policies that apply
- Success criteria
- Quality checks
- Escalation rules
- Output expectations
- Version and status

## Assignment

An Assignment is the work item an AI Employee receives.

Assignments should include:

- Organization
- Department
- AI Employee
- Business Process reference when applicable
- SOP reference when applicable
- Title
- Type
- Priority
- Status
- Briefing
- Input payload
- Output payload
- Confidence score
- Quality score
- Escalation flag
- Due, started, and completed timestamps

## Example: Article Production

1. A human creates an article production Assignment or starts the article production Business Process.
2. Martin Nikolovski receives a research Assignment and follows the research SOP.
3. David Kostovski receives a fact-check Assignment for the research package.
4. Mila Andonova receives a writing Assignment after research is approved.
5. Elena Markova receives an editorial review Assignment.
6. Viktor Petrov receives an SEO metadata Assignment.
7. A human reviews the final package before publication in the CMS.
