# Architecture

OneFiveFour AI Media Platform is separate from any CMS or public website.

It provides an AI workforce layer for publishing organizations. The CMS owns published content; OneFiveFour owns AI Employee identity, assignments, SOPs, logs, performance data, policies, capabilities, model configuration, and integration events.

## First Integration

Razbudise.mk will use OneFiveFour AI Media Platform as its first AI workforce.

The architecture must still be customer-independent. Razbudise-specific defaults may be seeded, but core concepts must work for any future Organization.

## Architecture Principles

- Model Organizations, not simple automations.
- Treat AI Employees as persistent identities, not prompts or model aliases.
- Keep Brain / Model configuration replaceable and separate from AI Employee identity.
- Represent work as Assignments connected to Departments, SOPs, Business Processes, and audit logs.
- Keep all important decisions auditable.
- Use mock Brain / Model providers before connecting real model providers.
- Keep publishing approval under human control for the MVP.

## Domain Context

Core domain objects:

- Organization
- Department
- AI Employee
- Assignment
- Assignment Log
- Employee Message
- Employee Performance Metric
- Company Policy
- Standard Operating Procedure
- Capability
- Brain / Model configuration

Business Processes are the product-level description of company operations. SOPs define repeatable instructions inside those processes. Assignments are the executable work items given to AI Employees.

## Communication

Publishing systems communicate with the AI Platform through:

- REST API
- Webhooks
- Later: queues or event bus

The MVP may expose only internal/admin flows first. External publishing integrations should be designed as boundaries, not built before the core organization model is stable.

## Ownership Rule

The AI Platform creates recommendations, drafts, research packages, SEO metadata, verification reports, localization drafts, media assets, logs, and performance records.

The CMS owns published content and public presentation.

## Runtime Rule

An AI Employee may use a Brain / Model at runtime, but the model is not the employee. Runtime model changes must not erase employee history, assignments, performance metrics, messages, permissions, SOP access, or audit records.
