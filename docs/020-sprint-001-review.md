# Sprint 001 Review: Organizational Core

Project Atlas Sprint 001 built the first working Organizational Core for OneFiveFour AI Platform. The sprint proves that the product can represent an operating AI-powered publishing Organization before adding real AI generation, publishing integrations, billing, or memory.

## Implemented Scope

### Organizations

- Added the Organization model, migration, factory, policy, Filament resource, and seed data.
- Seeded the first Organization, `OneFiveFour`, with status, domain, timezone, locale, and summary context.
- Organization owns Sites, Departments, Employees, Policies, SOPs, Assignments, Activity, and Audit Logs.

### Sites

- Added the Site model, migration, factory, policy, Filament resource, and seed data.
- Seeded `Razbudise.mk` as the first publication context without adding any live Razbudise integration.
- Sites can be referenced by SOPs, Assignments, and Activity.

### Departments

- Added Department storage, relationships, factories, policies, Filament resource, and seed data.
- Seeded the initial operating departments: Editorial, Research, Writing, Localization, SEO, Trust & Safety, Creative, Analytics, and Operations.
- Departments show employee, active assignment, and active SOP counts on the HQ Dashboard.

### Employees

- Added persistent Employee identities with employee code, full name, role title, department, manager relationship, status, avatar URL, bio, mission, job description, responsibilities, language, communication style, approval authority, and lifecycle timestamps.
- Seeded the initial Employees: Elena Markova, Martin Nikolovski, Mila Andonova, Sara Ilieva, Viktor Petrov, and David Kostovski.
- Added Filament CRUD resource support and dedicated Employee profile pages.

### Capabilities

- Added the Capability catalog and Employee Capability pivot model.
- Seeded the initial capabilities: Research, Writing, Localization, SEO, Fact Checking, and Editing.
- Seeded one active Capability per initial Employee with level and granted timestamp.
- Employee Capability grants and revocations create Audit Logs.

### Policies

- Added Policy and Policy Scope storage, relationships, factories, policies, Filament resources, and seed data.
- Seeded the first active Policy requiring human approval before publishing.
- Policy create, update, and status-change events create Audit Logs.
- Policy activation creates Activity.

### SOPs

- Added Standard Operating Procedure storage plus SOP Policy and SOP Capability relationships.
- Seeded the first active Editorial Review SOP connected to Department, Site, Policy, and Capability data.
- SOP create, update, and status-change events create Audit Logs.
- SOP activation creates Activity.

### Assignments

- Added Assignment storage, relationships, factories, policy, Filament resource, and seed data.
- Seeded the first current Assignment for Elena Markova: `Review first Razbudise editorial package`.
- Assignments support Department, Employee, optional Site, optional SOP, priority, status, briefing, expected output, payloads, required capabilities, confidence and quality scores, escalation flag, review flag, review path, due date, started timestamp, and completed timestamp.
- Assignment create, update, and status-change events create Audit Logs.
- Assignment create and status-change events create Activity.

### Activity

- Added Activity model, migration, factory, policy, Filament resource, and seed data.
- Activity is a readable operational timeline for HQ and Employee profile views.
- HQ Activity Feed updates from real model changes through observers and services.

### Audit Logs

- Added Audit Log model, migration, factory, policy, Filament resource, and seed data.
- Audit Logs store organization, actor, auditable record, event type, action, summary, before state, after state, and occurred timestamp.
- Important Employee, Capability, Policy, SOP, and Assignment changes are automatically audited.

### HQ Dashboard

- Added the `/admin` HQ Dashboard as the Filament landing page after login.
- The dashboard shows Organization summary, Departments overview, Employees overview, Current Assignments, and Activity Feed.
- Dashboard terminology follows the canonical Sprint 001 language: Organization, Site, Department, Employee, Capability, Policy, SOP, Assignment, Activity, and Audit Log.

### Employee Profiles

- Added a dedicated Employee profile view route in Filament.
- Employee profiles show identity, stats, active Capabilities, current Assignments, blocked Assignments, needs-review Assignments, completed Assignments, linked Activity, and Audit History.
- Seeded Employees can be opened from Filament and tested from seeded data.

### Assignment Lifecycle

- Added `AssignmentLifecycleService`.
- Supported transitions:
  - `pending` to `accepted`
  - `accepted` to `in_progress`
  - `in_progress` to `blocked`
  - `blocked` to `in_progress`
  - `in_progress` to `needs_review`
  - `needs_review` to `completed`
  - `in_progress` to `completed`
  - `pending`, `accepted`, `in_progress`, `blocked`, and `needs_review` to `cancelled`
  - `in_progress` to `failed`
- Invalid transitions are rejected with `DomainException`.
- Valid transitions update lifecycle fields and create Audit Logs and Activity through the Assignment observer.
- Filament Assignment row actions expose Accept, Start, Block, Resume, Request Review, Complete, Fail, and Cancel when valid for the current status.

## Local Runbook

### PostgreSQL Setup

The project uses PostgreSQL through Docker Compose.

```bash
docker compose up -d postgres
```

Default local database settings are already in `.env.example`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=onefivefour_ai_platform
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

The Compose setup also initializes `onefivefour_ai_platform_testing` for PHPUnit.

### Application Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Migrations and Seeders

```bash
php artisan migrate:fresh --seed
```

The seeders create:

- Admin user
- Organization
- Site
- Departments
- Capabilities
- Employees
- Employee Capabilities
- Policy and Policy Scope
- SOP plus Policy and Capability links
- Assignment
- Audit Log examples
- Activity examples

### Tests

```bash
php artisan test
```

The test suite is configured for PostgreSQL and covers core persistence, seeded data, Filament resource access, HQ dashboard loading, audit/activity automation, assignment lifecycle behavior, Filament lifecycle action visibility, and Employee profile seeded-data loading.

### Admin Login

- Email: `admin@onefivefour.ai`
- Password: `password`

### Local Admin URL

Start the local server:

```bash
php artisan serve --host=127.0.0.1 --port=8002
```

Open:

```text
http://127.0.0.1:8002/admin
```

If port `8002` is unavailable, choose another local port and open `/admin` on that port.

## Verification

Latest passing results captured on July 1, 2026:

| Command | Result |
| --- | --- |
| `php artisan test` | Passed: 18 tests, 300 assertions |
| `./vendor/bin/pint` | Passed |
| `npm run build` | Passed: Vite production build completed |
| `composer validate` | Passed: `./composer.json is valid` |

## Architecture Debt

### Known Limitations

- Authorization is intentionally permissive in generated policies for Sprint 001 admin use. Organization-aware user access rules need to be designed before multi-user or customer-facing use.
- Status values are currently string values. Shared enums or value objects should be considered before statuses spread further.
- Audit and Activity automation is synchronous model-observer behavior. A later event pipeline may be needed for queues, notifications, retries, and external integrations.
- Filament resources are mostly generated admin CRUD screens. The HQ Dashboard and Employee profile are tailored, but many resource forms and tables still expose raw fields.
- Activity and Audit Log views show latest records but do not yet provide advanced filtering, export, retention policy, or tamper-evidence.
- Seed data is intentionally small and proves the model, not production breadth.
- Employee profile stats are assignment and capability counts only. Performance metrics, messages, reviews, and memory are outside this sprint.

### Intentionally Deferred

- OpenAI integration
- Real AI generation
- Razbudise integration
- Article publishing
- Billing
- Advanced memory or vector search
- Complex Business Process engine
- External notifications
- Provider cost tracking
- Public customer onboarding
- Human approval workflow beyond status and review flags

### Revisit Before Sprint 002

- Decide the Sprint 002 boundary: deepen Organization administration, add Business Process concepts, or begin mock runtime behavior.
- Define Organization-scoped authorization and user membership assumptions before expanding admin access.
- Consider central status definitions for Organization, Site, Department, Employee, Capability, Policy, SOP, and Assignment.
- Decide whether Activity and Audit Log creation should remain observer-based or move toward explicit domain events.
- Improve generated Filament forms and tables where operators need cleaner workflows.
- Decide how Employee Messages, Performance Metrics, Reviews, Brain / Model configuration, and mock runtime records fit into the next milestone.
- Add CI or documented release gates before architectural review moves toward deployment review.

## Definition of Done

- [x] Core Sprint 001 database tables exist and migrate on PostgreSQL.
- [x] Core models, factories, policies, and relationships exist.
- [x] Filament resources exist for Organization, Site, Department, Employee, Capability, Policy, SOP, Assignment, Activity, and Audit Log.
- [x] Seed data creates the first Organization structure.
- [x] `/admin` opens the HQ Dashboard after login.
- [x] HQ Dashboard shows Organization summary, Departments, Employees, Current Assignments, and Activity.
- [x] Employee profiles show identity, Capabilities, Assignments, Activity, Audit History, and stats.
- [x] Audit Logs are created automatically for important Organizational Core actions.
- [x] Activity items are created automatically for important readable events.
- [x] Assignment lifecycle transitions are controlled by a service layer.
- [x] Invalid Assignment transitions are rejected.
- [x] Filament Assignment lifecycle actions are available only for valid statuses.
- [x] OpenAI, real AI generation, Razbudise integration, article publishing, billing, and memory remain out of scope.
- [x] Tests pass.
- [x] Pint passes.
- [x] npm build passes.
- [x] composer validate passes.
