# Codex Starting Point

## Project

OneFiveFour AI Media Platform

## Mission

Build an AI-native operating system for digital publishing companies.

This platform is not a CMS. It provides AI Employees, assignments, research, writing, translation, SEO, fact-checking, analytics, cost tracking, and integrations for publishing platforms.

Razbudise.mk is the first customer, but the platform must stay independent from Razbudise.

## Core Principle

Build organizations, not automations.

The platform should feel like managing a real AI-powered company, not running prompts.

## First Milestone

The first milestone is not article generation.

The first milestone is:

A user logs into the platform and sees the initial AI company structure:

- Organization
- Departments
- AI Employees
- Assignments
- Assignment Logs
- Employee Messages
- Business Process statuses
- SOPs
- Company Policies
- Mock Brain / Model configuration

No real OpenAI API integration yet.

Use mock Brain / Model providers first.

## Initial AI Employees

- Elena Markova: Editor-in-Chief AI
- Martin Nikolovski: Researcher AI
- Mila Andonova: Macedonian Writer AI
- Sara Ilieva: Translator / Localization AI
- Viktor Petrov: SEO AI
- David Kostovski: Fact Checker AI

## Initial Departments

- Editorial
- Research
- Writing
- Localization
- SEO
- Trust & Safety
- Creative
- Analytics
- Operations

## Technical Direction

Preferred stack:

- Backend: Laravel
- Admin/Dashboard: Filament or Laravel + Inertia/React
- Frontend: optional later
- Database: PostgreSQL
- Queue: Redis
- Search: Meilisearch later
- Brain / Model Providers: OpenAI later, mock provider first

## Important Rule

Do not connect OpenAI API in the first implementation.

Create interfaces and mock services first.

## First Build Scope

Create the foundation:

- Organizations
- Departments
- AI Employees
- Employee statuses
- Assignments
- Assignment Logs
- Employee Messages
- Employee Performance Metrics
- Business Processes
- Standard Operating Procedures
- Company Policies
- Capabilities
- Brain / Model provider abstraction
- Mock Brain / Model provider
- Basic dashboard/admin

## Documentation First

Before writing implementation code, read:

- README.md
- docs/*
- specs/*

Then propose the exact backend/frontend structure and implementation plan.
