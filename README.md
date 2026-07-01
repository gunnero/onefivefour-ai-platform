# OneFiveFour AI Media Platform

OneFiveFour AI Media Platform is an AI-native operating system for digital publishers.

It models organizations, departments, AI Employees, business processes, SOPs, assignments, audit logs, policies, capabilities, analytics, cost tracking, and publishing integrations.

The platform does not replace a CMS. It connects to publishing platforms and acts as the AI workforce behind them.

## First Customer

- Razbudise.mk

## Core Idea

A human supervises important decisions. AI Employees handle operational work inside an organization.

The platform should feel like managing an AI-powered media company, not running prompt automations.

## Canonical Terms

- Organization: a company, customer, or internal operating entity that owns departments, policies, SOPs, AI Employees, and assignments.
- Department: a functional team inside an organization, such as Editorial, Research, Writing, Localization, SEO, Trust & Safety, Analytics, or Operations.
- AI Employee: a persistent digital worker with identity, role, department, permissions, capabilities, memory, performance history, and audit trail.
- Assignment: a work item given to an AI Employee by a human, department, SOP, or business process.
- Business Process: an end-to-end company process such as article production, translation, fact-checking, SEO review, or content refresh.
- SOP: a versioned standard operating procedure that tells AI Employees how to perform repeatable work.
- Capability: an allowed action or tool class an AI Employee may use, such as research, draft writing, translation, SEO metadata, or claim verification.
- Brain / Model: the provider, model, and runtime settings currently used by an AI Employee. It is replaceable and does not define employee identity.

## Planned Modules

- Organizations
- Departments
- AI Employees
- Assignments
- Assignment Logs
- Employee Messages
- Employee Performance Metrics
- Business Processes
- Standard Operating Procedures
- Company Policies
- Capabilities
- Brain / Model Provider Abstraction
- Knowledge Base
- Publishing Integrations
- Analytics
- Cost Tracking

## Documentation Map

- [Vision](docs/001-vision.md)
- [Architecture](docs/002-architecture.md)
- [AI Employees](docs/003-ai-employees.md)
- [Business Processes, SOPs, and Assignments](docs/004-workflows.md)
- [Database Model](docs/006-database.md)
- [Roadmap and MVP Plan](docs/009-roadmap.md)
- [MVP Product Requirements](docs/010-prd.md)
- [Architecture Decisions](docs/014-decisions.md)
- [Terminology](docs/015-terminology.md)
- [Domain Language](docs/018-domain-language.md)

## Backend Foundation

Sprint 001 uses Laravel 13, PostgreSQL configuration, and Filament for the admin/HQ surface.

Local setup:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Run tests:

```bash
php artisan test
```

Filament opens at `/admin`.

Seeded admin user:

- Email: `admin@onefivefour.ai`
- Password: `password`
