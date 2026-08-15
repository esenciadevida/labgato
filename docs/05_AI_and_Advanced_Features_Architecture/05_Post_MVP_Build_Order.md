# 05 — Post-MVP Build Order (AI & Analytics)

## Depends On

This document depends on:

- All preceding Phase 05 documents (01 through 04).
- The approved Phase 01, 02, 03, and 04 documentation.
- The completed MVP Hardening Sprints (Sprints 1-4).
- The completed Accessibility Hotfix.

## Defines

This document defines:

- The exact step-by-step implementation order for the AI and Analytics features.
- The dependencies between the AI adapter, AI UI, and Analytics builds.
- The Definition of Done for the AI and Analytics vertical slices.
- The criteria for transitioning to the v1.1 Release or Phase 06.

## Affects

This document affects:

- The immediate development workflow for the post-MVP engineering cycle.
- The sequence of pull requests and commits for AI and Analytics features.
- The readiness assessment for the v1.1 platform release.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents out-of-order development of complex, asynchronous AI features.
- Ensures that the analytics database foundation is laid before the dashboard UI is built.
- Provides a clear checklist for completing the platform's advanced administrative features.

---

# 1. Purpose

This document establishes the Post-MVP Build Order for LabGato.

With the core MVP complete, hardened, and ready for release, the next phase of development introduces external API integrations (AI) and heavy data aggregation (Analytics). Building these features out of order (e.g., building the AI UI before the rate limiter and adapter exist) will result in blocked work, security vulnerabilities, and runaway API costs.

This build order ensures that the AI foundation is secure, the AI UI is rate-limited, and the Analytics engine is performant before the final dashboard is rendered.

---

# 2. Build Order Philosophy

We will continue to use the **Vertical Slice** and **Dependency-Driven** methodology.

Because AI features span the backend (HTTP client, exceptions), database (usage tracking), and frontend (Livewire, async states), they must be built in two distinct slices: Core Adapter, then Feature/UI Integration.

Analytics must follow AI, as the AI usage metrics are a key component of the Advanced Dashboard.

---

# 3. Step-by-Step Build Order

## Build 17: AI Foundation & Security (Slice 7)

**Objective**: Build the provider-agnostic AI adapter layer, exception handling, configuration, and security boundaries. No UI or database tracking is built in this step.

**Tasks**:
1. Create the `AiProviderInterface` and `AiResponse` support class.
2. Implement the `GroqAdapter` using Laravel's HTTP client with strict timeouts.
3. Create the `AiProviderService` to resolve the active adapter.
4. Create all custom AI exceptions (`AiTimeoutException`, `AiRateLimitException`, etc.).
5. Update `config/labgato.php` and `.env.example` with AI configuration and feature flags.
6. Register the `ai.use` and `ai.manage` permissions in the `RolePermissionSeeder`.
7. Write Unit tests for the Groq adapter (mocking HTTP responses) and exception mapping.

**Dependencies**: MVP Hardening Complete.

## Build 18: AI Content Generation, UI & Rate Limiting (Slice 8)

**Objective**: Build the AI use cases, the `ai_usage` tracking table, the rate limiting middleware, and the Livewire UI components. Integrate AI buttons into the Knowledge, Interpretation, and SOP admin forms.

**Tasks**:
1. Create the `ai_usage` database migration.
2. Implement the internal Rate Limiter (5s cooldown, 50/day user quota, 1000/day global quota) using the database cache driver.
3. Create the AI Use Case service classes (`DraftKnowledgeArticle`, `SummarizeSopProcedure`, etc.) with strict prompt templates and input sanitization.
4. Create the reusable `<x-ai.generate-button>` Blade/Livewire component with loading states, confirmation modals, and hallucination warnings.
5. Integrate the AI buttons into the Knowledge, Interpretation, and SOP admin CRUD forms.
6. Update the global exception handler to map AI exceptions to safe UI error messages.
7. Write Feature tests for rate limiting, prompt sanitization, and UI state transitions.

**Dependencies**: Build 17.

## Build 19: Advanced Analytics Foundation (Slice 9)

**Objective**: Build the database schema and aggregation engine for platform analytics. No dashboard UI is built in this step.

**Tasks**:
1. Create the `analytics_daily_metrics` database migration.
2. Create the `AggregateAnalytics` Artisan command to roll up daily metrics from `search_queries`, `ai_usage`, `audit_logs`, and resource tables.
3. Implement the 90-day raw log purge logic within the aggregation command.
4. Register the command in the Laravel Scheduler to run nightly via Cron.
5. Write Unit and Feature tests to verify the aggregation math and log purging.

**Dependencies**: Build 18 (Requires `ai_usage` table to exist for AI metric aggregation).

## Build 20: Advanced Analytics Dashboard UI (Slice 10)

**Objective**: Build the Advanced Analytics Dashboard views, KPI cards, accessible CSS charts, and RBAC routing.

**Tasks**:
1. Update the `Admin\DashboardController` to resolve the correct view based on the `analytics.view` permission.
2. Create the Advanced Dashboard Blade views with KPI cards, Resource Growth charts, Search Insights (Content Gaps), and AI Utilization widgets.
3. Implement lightweight, Tailwind CSS-based bar charts with mandatory `sr-only` HTML table fallbacks for screen readers.
4. Implement 15-minute database caching for "Today's" real-time metrics.
5. Write Feature tests to verify RBAC routing (Basic vs. Advanced dashboard) and widget rendering.
6. Verify dashboard load times remain under 500ms.

**Dependencies**: Build 19.

---

# 4. Definition of Done for AI & Analytics

The AI and Analytics implementation is only considered **COMPLETE** when:

- [ ] The Groq adapter successfully generates content with strict timeouts.
- [ ] Prompt injection and data leakage are prevented via sanitization and system prompt isolation.
- [ ] Internal rate limits successfully block excessive API usage and return safe UI errors.
- [ ] AI usage is accurately tracked in the `ai_usage` table.
- [ ] AI buttons are integrated into Knowledge, Interpretation, and SOP forms with proper loading and confirmation states.
- [ ] The `analytics:aggregate` command runs successfully and purges old logs.
- [ ] The Advanced Dashboard renders KPIs, Search Insights, and AI metrics.
- [ ] All charts include accessible HTML table fallbacks.
- [ ] Users without `analytics.view` see the basic MVP dashboard.
- [ ] All AI and Analytics unit and feature tests pass.
- [ ] No AI feature can auto-publish a resource or bypass the HITL workflow.

---

# 5. Transition to v1.1 Release or Phase 06

Once Build 17 through Build 20 are marked **COMPLETE**, LabGato will have achieved its **v1.1 (AI & Analytics) milestone**.

At that point, the project will undergo a final regression test and be prepared for the v1.1 production deployment.

Future phases (Phase 06+) may include:
- Public User Accounts & Personalization.
- Bulk CSV Import for Reference Ranges.
- Dark Mode implementation.
- Advanced AI features (e.g., multi-draft generation, translation).

---

# Document Completion Check

## Requirements Covered

- Build order philosophy defined.
- Step-by-step build order for AI Foundation (Slice 7) defined.
- Step-by-step build order for AI UI & Rate Limiting (Slice 8) defined.
- Step-by-step build order for Analytics Foundation (Slice 9) defined.
- Step-by-step build order for Analytics UI (Slice 10) defined.
- Definition of Done for AI/Analytics defined.

## Decisions Established

- AI Adapter and Security must be built before AI UI and Use Cases.
- AI Usage tracking must be built before Analytics Aggregation.
- Analytics Aggregation must be built before the Dashboard UI.
- A slice is only complete when backend, database, UI, rate limits, and tests are all verified.

## Dependencies

- All Phase 05 documents.
- Completed MVP Hardening Sprints.

## Files Affected

This document creates:

05_AI_and_Advanced_Features_Architecture/05_Post_MVP_Build_Order.md

It serves as the execution plan for the post-MVP development cycle.

## Open Questions

1. Should the v1.1 release include a dedicated "AI Settings" page for Super Admins to adjust rate limits, or should they remain hardcoded in `.env` for now?

## TBD Decisions

- TBD: Final decision on AI Settings UI for v1.1.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE05-DOC05

## Context

Phase 05 documentation is concluding. This document establishes the exact build order for the AI adapter, AI UI/rate limiting, analytics aggregation, and analytics dashboard.

This prompt implements the documentation outcome of the Post-MVP Build Order document.

## Objective

Create the official LabGato Post-MVP Build Order document inside the approved Phase 05 documentation structure.

## Existing Architecture

Relevant approved decisions:

- AI Adapter precedes AI UI.
- Analytics Aggregation precedes Dashboard UI.
- Vertical slices must include DB, Backend, UI, and Tests.

## Files to Create

Create:

05_AI_and_Advanced_Features_Architecture/05_Post_MVP_Build_Order.md

Use the approved document content from this documentation output.

## Definition of Done

This implementation is complete when:

- The Post-MVP Build Order file exists.
- The file matches the approved Phase 05 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.