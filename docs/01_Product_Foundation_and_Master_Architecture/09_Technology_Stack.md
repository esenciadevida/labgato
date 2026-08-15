# 09 — Technology Stack

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Current State Assessment.
- The approved Product Scope and Non-Goals document.
- The approved Core Product Areas document.
- The approved Deployment Constraints document.
- The confirmed Hostinger PHP 8.4 hosting constraint.
- The project owner statement that Laravel will work on the hosting environment.

## Defines

This document defines:

- The approved backend technology direction.
- The approved frontend technology direction.
- The approved database direction.
- The approved session, cache, and queue direction.
- The approved build and deployment tooling direction.
- The approved AI integration technology boundaries.
- The approved search technology direction.
- The approved logging and testing direction.
- The excluded technologies.
- The remaining technology verification items.

## Affects

This document affects:

- The backend architecture.
- The frontend architecture.
- The database architecture.
- The API architecture.
- The admin portal architecture.
- The AI architecture.
- The deployment architecture.
- The testing strategy.
- The security architecture.
- The performance strategy.
- All future implementation prompts.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the approved technical foundation for LABGOTO.
- Ensures the architecture remains compatible with Hostinger shared hosting.
- Prevents introduction of incompatible infrastructure such as Redis, Node servers, or long-running workers.
- Guides the selection of packages, drivers, and build tools.
- Provides the technical baseline for the next architecture phases.

---

# 1. Purpose

This document establishes the official technology stack for LABGOTO.

The stack must satisfy the following requirements:

- It must support the approved LABGOTO product scope.
- It must run reliably on Hostinger PHP/HTML hosting.
- It must support PHP 8.4.
- It must avoid technologies that require unavailable server capabilities.
- It must support secure admin management.
- It support pre-built frontend assets.
- It must support controlled AI integration in a later phase.
- It must remain maintainable, testable, and documentation-controlled.

This stack is selected for professional laboratory resource management, not for e-commerce, patient portals, lead generation, or hospital workflows.

---

# 2. Technology Stack Summary

The approved initial LABGOTO technology stack is:

- Language: PHP 8.4
- Backend Framework: Laravel, latest stable version compatible with PHP 8.4
- Database: MySQL or MariaDB
- Frontend Rendering: Laravel Blade
- Styling: Tailwind CSS
- Interactivity: Alpine.js
- Reactive Components: Livewire
- Asset Build Tool: Vite, compiled locally or in CI
- Queue Driver: Database driver, with sync fallback if cron is unavailable
- Session Driver: Database driver
- Cache Driver: Database driver
- File Storage: Local storage on Hostinger
- Mail: Laravel Mail using SMTP or external transactional provider
- Logging: Laravel daily file logs
- Testing: PHPUnit or Laravel-supported test runner
- AI Provider: Groq initially, through a provider-independent adapter
- Search: MySQL-based search initially
- Version Control: Git
- Backend Dependency Manager: Composer
- Frontend Build Dependency Manager: npm, used only in local/CI build process

---

# 3. Backend Stack

## 3.1 PHP

Approved version:

PHP 8.4

Requirements:

- The application must be compatible with PHP 8.4.
- The production environment must be configured to use PHP 8.4.
- Required PHP extensions must be enabled.
- PHP production settings must disable error display.
- Upload limits, memory limits, and execution limits must be verified.

Expected required PHP extensions include:

- PDO
- mbstring
- openssl
- ctype
- json
- tokenizer
- xml
- dom
- fileinfo
- curl
- intl, recommended
- gd or equivalent, only if image processing is approved later

## 3.2 Laravel

Approved backend framework:

Laravel

Selection rules:

- Use the latest stable Laravel version compatible with PHP 8.4.
- Use Laravel features that are compatible with shared hosting.
- Avoid Laravel features that require long-running processes.
- Avoid Laravel packages that require Redis, Node servers, or daemon workers.
- Keep dependencies minimal and maintainable.

Laravel will provide:

- Routing
- Controllers
- Middleware
- Eloquent models
- Migrations
- Validation
- Authentication foundation
- Authorization policies and gates
- Mail
- Logging
- Queue support
- File storage abstraction
- HTTP client for AI provider calls
- Scheduled tasks where cron is available

## 3.3 API Approach

LABGOTO may use Laravel routes and controllers for both server-rendered pages and JSON endpoints.

Rules:

- Public endpoints must be validated and rate-limited where appropriate.
- Admin endpoints must require authentication and authorization.
- API responses must be consistent.
- Sensitive error details must not be exposed publicly.
- APIs must not become the source of truth for business rules.
- API design must follow future API architecture documentation.

## 3.4 Authentication Approach

Approved initial direction:

Laravel session-based authentication for administrators.

Rules:

- Administrator login must be protected by secure session handling.
- Login throttling must be implemented.
- Passwords must be hashed using Laravel's default secure hashing.
- Session cookies must be secure, HttpOnly, and SameSite-appropriate.
- Public user authentication is not approved unless explicitly added later.

## 3.5 Authorization Approach

Approved initial direction:

Custom role-based access control enforced through Laravel policies, gates, and database-stored permissions.

Rules:

- Authorization must be enforced server-side.
- Frontend hiding is not sufficient.
- Roles must follow least privilege.
- Privilege escalation must be prevented.
- Sensitive administrative actions must be audited.

A third-party permission package may be considered later only if approved through a change request.

---

# 4. Frontend Stack

## 4.1 Rendering Approach

Approved rendering approach:

Laravel Blade

Reason:

- Compatible with shared hosting.
- SEO-friendly.
- No Node server required in production.
- Simple deployment.
- Works well with Laravel authentication and admin pages.
- Avoids unnecessary frontend/backend separation for the MVP.

## 4.2 Styling

Approved styling system:

Tailwind CSS

Rules:

- Tailwind must be compiled during local or CI build.
- Production must receive only compiled CSS.
- No Tailwind CDN should be used as the primary production strategy.
- The design system must avoid generic AI-style visuals.
- The UI must remain clean, scientific, accessible, and professional.

## 4.3 JavaScript Interactivity

Approved lightweight interactivity tool:

Alpine.js

Use cases may include:

- Dropdowns
- Accordions
- Modal open/close behavior
- Simple form toggles
- Mobile navigation behavior
- Lightweight UI state changes

Rules:

- Alpine.js must not replace server-side validation.
- Alpine.js must not store sensitive state.
- Alpine.js must not expose admin logic publicly.

## 4.4 Reactive Components

Approved reactive component tool:

Livewire

Use cases may include:

- Admin CRUD interfaces
- Search interfaces
- Filterable tables
- Dynamic form validation
- Calculator input interaction where appropriate
- Dashboard widgets where appropriate

Rules:

- Livewire components must validate input server-side.
- Livewire components must enforce authorization.
- Livewire components must not expose unauthorized data.
- Livewire components must support loading, empty, error, success, and validation states.

## 4.5 Asset Build Tool

Approved asset build tool:

Vite

Rules:

- Vite must run only in local development or CI.
- Production assets must be pre-built.
- The compiled build output must be uploaded to the server.
- Node.js must not be required on the production host.

## 4.6 Frontend Technologies Excluded

The following frontend approaches are excluded for the current MVP unless a change request is approved:

- Separate Next.js frontend
- Server-side Node rendering
- Inertia SSR requiring Node
- Complex SPA build requiring production Node
- WebSocket-driven real-time UI
- Heavy JavaScript admin frameworks requiring unsupported infrastructure

---

# 5. Database Stack

## 5.1 Database Engine

Approved database direction:

MySQL or MariaDB

Rules:

- Use standard MySQL/MariaDB features.
- Avoid database-specific features that may not be available on shared hosting.
- Use foreign keys.
- Use indexes.
- Use constraints.
- Use appropriate column types.
- Use timestamps.
- Use soft deletion where appropriate.
- Use migrations for all schema changes.

## 5.2 Money and Numeric Data

If future features require numeric precision, such as calculators or reference ranges:

- Store numeric values with appropriate precision.
- Avoid floating-point errors where exactness is required.
- Define rounding rules explicitly.
- Store units explicitly where relevant.
- Do not rely on browser-side calculation as the source of truth.

## 5.3 Database Access

All database access must use:

- Eloquent models
- Query builder where appropriate
- Validated request data
- Parameterized queries
- Protected mass assignment rules

Raw SQL may be used only when necessary and must remain safe from injection.

---

# 6. Session, Cache, and Queue Drivers

## 6.1 Session Driver

Approved session driver:

Database

Reason:

- Avoids reliance on file permission issues.
- Supports session invalidation.
- Works on shared hosting.
- Supports administrative session control.

## 6.2 Cache Driver

Approved cache driver:

Database

Reason:

- Avoids reliance on Redis.
- Avoids file cache permission issues.
- Works on shared hosting.
- Suitable for small to medium platform needs.

Cache usage should remain intentional and optimized.

Examples of appropriate cache use:

- Published resource listings
- Category lists
- Settings
- Search metadata
- Knowledge base context, later for AI
- Sitemap data where appropriate

## 6.3 Queue Driver

Approved queue driver:

Database

Fallback:

Sync, if cron or queue processing is unavailable or unreliable.

Rules:

- Long-running queue workers are prohibited.
- Queue processing must be compatible with cron-based execution.
- Heavy jobs must be avoided.
- Jobs must be idempotent where possible.
- Failed jobs must be logged and reviewable.

---

# 7. File Storage Stack

## 7.1 Storage Driver

Approved storage driver:

Local file storage on Hostinger

Rules:

- Uploads must be validated.
- File types must be restricted.
- File sizes must be limited.
- Filenames must be sanitized.
- Executable files must be rejected.
- Private files must not be publicly exposed.
- Storage symlinks must be verified during deployment.

## 7.2 Likely Upload Types

Future allowed uploads may include:

- Admin user profile images, if approved
- Resource thumbnails
- Knowledge article images
- SOP attachments, if approved
- Favicon and logo files
- Open Graph images

Each upload type must be documented before implementation.

## 7.3 External Storage

External object storage such as S3 may be considered later, but it is not part of the current MVP stack.

---

# 8. Mail Stack

## 8.1 Mail System

Approved mail abstraction:

Laravel Mail

Possible providers:

- Hostinger SMTP
- External transactional email provider
- Local mail trap for development only

Rules:

- Production mail must use a real, verified provider.
- Mail credentials must remain server-side.
- Mail templates must be safe and validated.
- Mail sending must handle failures gracefully.
- Mail logs must not expose sensitive content unnecessarily.

## 8.2 Mail Use Cases

Likely initial mail use cases include:

- Administrator password reset
- Account security notifications where approved
- System failure notifications where approved
- Content workflow notifications where approved

Public user mail is not approved until public user accounts are approved.

---

# 9. Logging Stack

## 9.1 Logging System

Approved logging system:

Laravel logging with daily file logs

Rules:

- Production must not display errors publicly.
- Logs must be protected.
- Logs must not store passwords.
- Logs must not store API keys.
- Logs must not store session tokens.
- Logs must not store unnecessary sensitive data.
- Security-relevant events should be logged.
- Administrative actions should be audited.

## 9.2 Audit Logging

Audit logging must be implemented for important administrative and security actions.

Audit logs must be stored separately from general application logs where practical.

---

# 10. Search Stack

## 10.1 Initial Search Technology

Approved initial search technology:

MySQL-based search

Possible approaches:

- Scoped LIKE queries
- Full-text indexes where appropriate
- Optimized filtering
- Pagination
- Relevance ordering where practical

## 10.2 Search Constraints

Because of shared hosting constraints:

- Meilisearch is excluded.
- Elasticsearch is excluded.
- External search engines are excluded unless approved later.
- Heavy indexing processes must be avoided.
- Search must remain performant on MySQL.

## 10.3 Future Search

If search requirements become advanced, a change request must be submitted.

Possible future options include:

- Managed external search service
- Optimized MySQL search improvements
- Application-level indexing
- Dedicated search infrastructure if hosting is upgraded

---

# 11. AI Stack

## 11.1 AI Provider Direction

Initial approved AI provider direction:

Groq

Rules:

- Groq must be accessed through a provider-independent adapter.
- The application must not be tightly coupled to Groq.
- API keys must remain server-side.
- AI responses must be controlled by approved LABGOTO knowledge.
- AI must not invent information.
- AI must not act as the authorization layer.
- AI must be rate-limited.
- AI must fail gracefully.
- AI logging must avoid unnecessary sensitive data.

## 11.2 AI Response Mode

Approved MVP AI response mode:

Non-streaming request/response

Reason:

- Shared hosting may not reliably support streaming.
- Timeouts are easier to control.
- Error handling is simpler.
- Security and logging are simpler.

Streaming may be evaluated later if hosting supports it and a change request is approved.

## 11.3 AI Knowledge Source

AI must use approved LABGOTO data only.

Possible approved sources include:

- Published tools
- Published calculators
- Published reference ranges
- Published SOP resources
- Published interpretation resources
- Published knowledge resources
- Approved platform metadata

AI must not use:

- Private admin data
- Audit logs
- User credentials
- Unpublished drafts
- Secrets
- Unauthorized external content

---

# 12. Testing Stack

## 12.1 Testing Framework

Approved testing direction:

PHPUnit, or the Laravel-supported default test runner selected during implementation setup.

Rules:

- Tests must be automated where practical.
- Calculator logic must have expected-result tests.
- Feature tests must cover permissions and validation.
- Security tests must cover unauthorized access.
- Accessibility checks must be included for critical UI flows where practical.
- Tests must not use production data.
- Tests must not expose secrets.

## 12.2 Testing Environments

Testing must be performed in:

- Local development
- Staging, if available
- Production verification, carefully and minimally

Tests must never depend on live AI provider keys unless using controlled test credentials.

---

# 13. Build and Deployment Stack

## 13.1 Version Control

Approved version control:

Git

Rules:

- Secrets must never be committed.
- Environment files must not be committed.
- Lock files must be committed.
- Documentation must be versioned with the project.

## 13.2 Backend Dependency Management

Approved backend dependency manager:

Composer

Rules:

- Production dependencies must be installed with no development dependencies.
- Composer lock file must be maintained.
- Unused packages must be removed.
- Package security must be reviewed before installation.

## 13.3 Frontend Dependency Management

Approved frontend build dependency manager:

npm

Rules:

- npm must be used only in local development or CI.
- npm must not be required on the production server.
- Compiled assets must be uploaded after build.
- Development dependencies must not be deployed.

## 13.4 Deployment Approach

Approved deployment direction:

Pre-built deployment

Deployment process should generally follow:

1. Build frontend assets locally or in CI.
2. Install Composer production dependencies locally or in CI.
3. Create a production-ready artifact.
4. Upload the artifact to Hostinger.
5. Configure environment variables.
6. Run database migrations if CLI access is available.
7. Clear and cache configuration, routes, and views.
8. Verify application health.

If SSH or CLI access is unavailable, a secure alternative deployment and migration workflow must be documented before implementation.

---

# 14. Environment Strategy

## 14.1 Required Environments

The project should support:

- Local development
- Staging, if hosting allows
- Production

## 14.2 Environment Separation

Each environment must use separate:

- Database credentials
- API keys
- Mail configuration
- Debug settings
- App URL
- Storage configuration where practical

## 14.3 Production Settings

Production must use:

- APP_ENV=production
- APP_DEBUG=false
- Secure cookies
- HTTPS
- Protected environment variables
- Optimized configuration
- Error logging without public error display

---

# 15. Excluded Technologies

The following technologies are excluded from the current LABGOTO stack because of hosting constraints and scope decisions:

- Redis
- Memcached
- Node.js production server
- Server-side rendering requiring Node
- Docker production deployment
- Kubernetes
- Supervisor
- WebSockets requiring persistent workers
- Meilisearch
- Elasticsearch
- Laravel Reverb or other real-time WebSocket infrastructure
- Queue daemons requiring long-running processes
- External payment systems
- Patient result delivery systems
- Hospital/LIS integration systems

Any of these may be reconsidered only through an approved change request and hosting upgrade.

---

# 16. Technology Decision Log

## Decision T-001

Decision:

Use PHP 8.4 as the server-side language.

Status:

Approved.

Reason:

Confirmed by hosting constraint and project owner.

## Decision T-002

Decision:

Use Laravel as the backend framework.

Status:

Approved direction.

Reason:

Project owner confirmed Laravel will work on Hostinger PHP 8.4 hosting.

## Decision T-003

Decision:

Use MySQL or MariaDB as the database engine.

Status:

Approved direction, subject to Hostinger database verification.

Reason:

Compatible with Hostinger shared hosting and Laravel.

## Decision T-004

Decision:

Use Blade for rendering.

Status:

Approved direction.

Reason:

SEO-friendly, simple deployment, no Node server required.

## Decision T-005

Decision:

Use Tailwind CSS, Alpine.js, and Livewire for frontend implementation.

Status:

Approved direction.

Reason:

Suitable for shared hosting, professional admin interfaces, and progressive interactivity without requiring a separate frontend server.

## Decision T-006

Decision:

Use database drivers for session, cache, and queue where practical.

Status:

Approved direction.

Reason:

Avoids Redis and file-permission uncertainty on shared hosting.

## Decision T-007

Decision:

Use Groq as the initial AI provider through a provider-independent adapter.

Status:

Approved direction for future AI phase.

Reason:

Aligns with project direction while preserving provider independence.

## Decision T-008

Decision:

Use non-streaming AI responses for MVP.

Status:

Approved direction.

Reason:

More reliable under shared hosting constraints.

---

# 17. Remaining Verification Items

The following items must be verified before or during technical foundation implementation:

1. Exact Laravel version compatible with PHP 8.4 and project dependencies.
2. Exact MySQL/MariaDB version available on Hostinger.
3. SSH availability.
4. Composer availability over SSH, if SSH exists.
5. Cron availability and minimum interval.
6. PHP extension list.
7. PHP upload limits and execution limits.
8. Document root behavior.
9. Symlink support.
10. Mail provider reliability.
11. Staging subdomain availability.
12. Backup and restore capability.

---

# Document Completion Check

## Requirements Covered

- Backend stack defined.
- Frontend stack defined.
- Database stack defined.
- Session, cache, and queue drivers defined.
- File storage stack defined.
- Mail stack defined.
- Logging stack defined.
- Search stack defined.
- AI stack boundaries defined.
- Testing stack defined.
- Build and deployment stack defined.
- Environment strategy defined.
- Excluded technologies defined.
- Technology decision log created.
- Remaining verification items listed.

## Decisions Established

- PHP 8.4 is the approved server-side language.
- Laravel is the approved backend framework direction.
- MySQL/MariaDB is the approved database direction.
- Blade, Tailwind, Alpine.js, and Livewire are the approved frontend direction.
- Database drivers are approved for session, cache, and queue.
- Vite builds must occur locally or in CI.
- Groq is approved as initial AI provider direction through an adapter.
- Non-streaming AI responses are approved for MVP.
- Redis, Node production servers, and long-running workers are excluded.

## Dependencies

- Approved LABGOTO master directive.
- Approved Project Charter.
- Approved Deployment Constraints.
- Approved Product Scope and Non-Goals.
- Hostinger PHP 8.4 constraint.
- Project owner confirmation that Laravel will work.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/09_Technology_Stack.md

It affects all future backend, frontend, database, API, AI, testing, and deployment documentation.

## Open Questions

1. Which exact Laravel version should be locked for the project?
2. Which exact MySQL/MariaDB version is available on the Hostinger plan?
3. Is SSH available?
4. Is Composer available through SSH?
5. Are one-minute cron jobs available?
6. Can the document root be pointed to a public folder?
7. Which mail provider will be used in production?
8. Should Pest be used instead of PHPUnit, or should PHPUnit remain the default?
9. Should a third-party RBAC package be considered later?
10. Should staging be created as a subdomain?

## TBD Decisions

- TBD: Exact Laravel version lock.
- TBD: Exact database version verification.
- TBD: Final mail provider.
- TBD: Final testing runner if Pest is preferred.
- TBD: Final RBAC package decision, if any.
- TBD: Final staging environment strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC09

## Context

LABGOTO requires an approved technology stack that is compatible with Hostinger PHP 8.4 shared hosting and the approved product scope. This document establishes the backend, frontend, database, queue, cache, session, AI, search, logging, testing, and deployment technology direction.

This prompt implements the documentation outcome of the Technology Stack document.

## Objective

Create the official LABGOTO Technology Stack document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository should contain the approved technical foundation for future architecture and implementation work.

## Existing Architecture

Relevant approved decisions:

- LABGOTO follows the new master directive.
- Hostinger PHP 8.4 is the deployment constraint.
- Laravel will work on the hosting environment.
- Shared hosting limitations must be respected.
- Redis, Node production servers, and long-running workers are excluded.
- AI must be provider-independent and server-side.
- Documentation controls implementation.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- 01_Project_Charter.md
- 02_Current_State_Assessment.md
- 08_Deployment_Constraints.md
- Any existing composer.json
- Any existing package.json
- Any existing MLS Toolkit technology decisions

If no files exist, record that the documentation structure is being initialized.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/09_Technology_Stack.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

01_Product_Foundation_and_Master_Architecture/09_Technology_Stack.md

Do not modify application code.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets.
- Do not include API keys.
- Do not include database credentials.
- Do not include hosting credentials.
- Do not include personal data.
- Do not expose private infrastructure details beyond what is necessary.

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- Consistent technology naming.
- No ambiguous labels.

## SEO Requirements

No public SEO impact.

This is an internal/documentation file.

Do not make admin or private documentation indexable if later served by the application.

## Analytics Requirements

No analytics events are required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file defines the approved technology stack.
- The file respects the Hostinger deployment constraints.
- The file excludes Redis, Node production servers, and long-running workers.
- The file records Groq as a provider-independent future AI direction.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future backend, frontend, database, API, AI, testing, and deployment documents.

No future technology selection may conflict with this document without an approved change request.

## Definition of Done

This implementation is complete when:

- The Technology Stack file exists.
- The file matches the approved Phase 01 file tree.
- The file defines the approved stack.
- The file records excluded technologies.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.