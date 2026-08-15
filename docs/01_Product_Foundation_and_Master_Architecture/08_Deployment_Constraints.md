# 08 — Deployment Constraints

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Current State Assessment.
- The confirmed Hostinger PHP 8.4 hosting constraint provided by the project owner.
- The approved Phase 01 file tree.

## Defines

This document defines:

- The official deployment environment constraints.
- The limitations of the approved shared hosting environment.
- The required strategies for deploying a Laravel application to this environment.
- The rules for frontend asset building and deployment.
- The strategies for background jobs, queues, and scheduling under shared hosting.
- The explicit deployment technologies and patterns that are excluded from the current scope.

## Affects

This document affects:

- The Technology Stack decisions (Document 09).
- The Backend Architecture (future phases).
- The Frontend Architecture (future phases).
- The Database Architecture (future phases).
- The CI/CD and deployment workflow (future phases).
- The caching, session, and queue driver selections.
- The AI integration strategy (e.g., streaming vs. non-streaming).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Forces the technical architecture to remain compatible with shared PHP hosting.
- Prevents the selection of infrastructure-heavy tools (e.g., Redis, Meilisearch, Node.js servers) that would fail in production.
- Dictates how frontend assets must be compiled and uploaded.
- Defines how Laravel's queue and scheduler must be configured to avoid relying on long-running daemon workers.

---

# 1. Purpose

This document establishes the strict deployment constraints for LABGOTO.

Because LABGOTO will be hosted on a specific shared hosting environment, the software architecture must be designed to thrive within those limitations. Designing a system that requires a VPS, Docker, or long-running background workers will result in a deployment failure.

This document ensures that every technical decision made in subsequent phases respects the reality of the production environment.

---

# 2. Confirmed Hosting Environment

The following hosting parameters are confirmed and approved for LABGOTO production deployment.

## 2.1 Hosting Provider

Hostinger.

## 2.2 Hosting Type

PHP/HTML Shared Hosting (or Cloud Hosting with shared-like constraints).

## 2.3 PHP Version

PHP 8.4.

The application must be fully compatible with PHP 8.4 and utilize its features where appropriate, while maintaining backward compatibility with standard Laravel 11.x requirements.

## 2.4 Database Engine

MySQL / MariaDB.

The specific version will be dictated by the Hostinger environment, but the application must use standard MySQL/MariaDB features and avoid engine-specific extensions that might not be available on shared tiers.

## 2.5 Web Server

Apache / LiteSpeed.

The application must rely on standard `.htaccess` rules for URL rewriting and directory protection. Nginx-specific configurations cannot be relied upon.

---

# 3. Shared Hosting Constraints & Limitations

The following limitations must be assumed unless explicitly verified and approved otherwise.

## 3.1 No Guaranteed Redis or Memcached

Shared hosting environments rarely do not provide Redis or Memcached.
Therefore, LABGOTO must not rely on Redis for caching, sessions, or queues.

## 3.2 No Guaranteed Node.js Runtime

The server environment is primarily PHP-focused.
LABGOTO must not rely on a persistent Node.js server, Server-Side Rendering (SSR) via Node, or WebSockets powered by Node on the production server.

## 3.3 No Guaranteed Supervisor or Daemon Workers

Shared hosting does not support long-running background processes.
LABGOTO cannot rely on `php artisan queue:work --daemon` running continuously via Supervisor.

## 3.4 No Guaranteed SSH Access

Depending on the specific Hostinger plan, SSH access may be restricted or unavailable.
The deployment strategy must account for the possibility of file-upload-based deployment or restricted CLI access.

## 3.5 Public Web Root Constraints

Hostinger typically uses `public_html/` as the public web root.
Laravel expects its public files to be in a `public/` directory.
The deployment architecture must safely map Laravel's `public/` directory to `public_html/` without exposing the `.env` file, `vendor/` directory, or `storage/` directory to the public internet.

## 3.6 Resource Limits

Shared hosting enforces strict CPU, RAM, and execution time limits.
- Heavy image processing must be avoided or offloaded.
- AI API calls must have strict, short timeouts to prevent PHP execution timeouts.
- Database queries must be highly optimized and indexed.
- Large file exports/imports must be avoided or chunked carefully.

---

# 4. Laravel on Shared Hosting Strategy

To successfully deploy Laravel under these constraints, the following strategies are mandated.

## 4.1 Directory Structure Mapping

The core Laravel application (`app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`, `vendor`, `.env`) must reside *outside* the public web root if possible, or in a protected subdirectory.

Only the contents of Laravel's `public/` folder (`index.php`, `.htaccess`, `build/`, `favicon.ico`, `robots.txt`) should be exposed in `public_html/`.

If the hosting plan allows changing the document root to a `public/` folder, that is the preferred approach. Otherwise, a secure symlink or `index.php` path-binding strategy must be documented in the Deployment Architecture phase.

## 4.2 Environment File Security

The `.env` file must never be accessible via the web.
Strict `.htaccess` rules must be implemented to deny access to `.env`, `.git`, `composer.json`, and other sensitive files.

## 4.3 Storage and Symlinks

Laravel's `storage/app/public` directory requires a symlink to `public/storage` for file accessibility.
If the hosting environment restricts symlink creation via the UI, the deployment process must include a secure script or SSH command to create the storage link, or files must be stored in a custom accessible directory structure.

---

# 5. Frontend Build & Deployment Strategy

Because Node.js is not guaranteed on the server, frontend assets cannot be built on the production server.

## 5.1 Local or CI/CD Building

All frontend assets (Tailwind CSS, Alpine.js, Vite-compiled JS/CSS) must be compiled locally or via a CI/CD pipeline (e.g., GitHub Actions) *before* deployment.

## 5.2 Pre-built Asset Upload

Only the compiled, production-ready assets (e.g., the `public/build/` directory) should be uploaded to the server.
The `node_modules/` directory and raw frontend source files must never be uploaded to production.

## 5.3 No Server-Side Rendering (SSR)

Because Node.js is unavailable, Laravel Inertia SSR or Next.js SSR cannot be used.
The frontend must rely on standard server-side rendering via Laravel Blade, or client-side rendering via standard SPA/API patterns if approved later.

---

# 6. Background Jobs & Scheduling Strategy

Laravel's queue and scheduler must be adapted for shared hosting.

## 6.1 Queue Driver Selection

- **Sync Driver:** Acceptable for MVP and low-volume tasks, but blocks the user request.
- **Database Driver:** The preferred approach for shared hosting. Jobs are stored in a `jobs` table.
- **Redis Driver:** Excluded due to hosting constraints.

## 6.2 Queue Worker Execution

Since Supervisor is unavailable, the queue worker must be triggered via Cron.
The deployment must configure a Cron job to run:
`* * * * * php /path/to/artisan queue:work --stop-when-empty --max-time=60`

If Cron is restricted to minimum 1-hour intervals on the specific Hostinger plan, the application must fall back to the `sync` driver or use a poor-man's cron (triggering queue processing at the end of web requests, with strict rate limiting to prevent performance degradation).

## 6.3 Task Scheduler

The Laravel Scheduler (`php artisan schedule:run`) must also be triggered via the hosting Cron job, running every minute if possible.

---

# 7. AI Integration Constraints

The AI architecture must respect shared hosting limitations.

## 7.1 Server-Side Execution Only

All AI API calls (e.g., to Groq) must be made from the Laravel backend. API keys must never be exposed to the browser.

## 7.2 Timeout Management

Shared hosting PHP execution limits (often 30-60 seconds) mean AI requests must have strict timeouts (e.g., 10-15 seconds).
If the AI provider is slow, the application must fail gracefully and return a fallback message rather than crashing the PHP process.

## 7.3 Streaming Responses

Server-Sent Events (SSE) or streaming AI responses can be problematic on shared hosting due to output buffering and proxy timeouts.
**MVP Constraint:** AI responses should be non-streaming (standard request/response) for the initial release. Streaming may be evaluated later if the hosting environment supports it reliably.

---

# 8. Excluded Deployment Technologies

To maintain compatibility with the approved hosting environment, the following technologies are explicitly excluded from the deployment architecture unless a Change Request is approved to upgrade the hosting environment:

- Docker / Docker Compose
- Kubernetes
- Redis / Memcached
- Meilisearch / Elasticsearch (unless using a managed external API)
- Node.js persistent servers
- WebSockets (e.g., Laravel Reverb, Pusher) requiring open persistent ports
- Supervisor
- Nginx-specific configurations

---

# Document Completion Check

## Requirements Covered

- Confirmed hosting environment defined.
- Shared hosting constraints and limitations defined.
- Laravel on shared hosting strategy defined.
- Frontend build and deployment strategy defined.
- Background jobs and scheduling strategy defined.
- AI integration constraints defined.
- Excluded deployment technologies defined.

## Decisions Established

- LABGOTO will be deployed to Hostinger PHP 8.4 shared hosting.
- Redis, Node.js, and Supervisor are excluded from the deployment architecture.
- Frontend assets must be pre-built locally or via CI/CD.
- Queues must use the database driver or sync driver, triggered via Cron.
- AI streaming is deferred; non-streaming is required for MVP.
- The `.env` file and core application files must be protected from the public web root.

## Dependencies

- Approved LABGOTO Master Directive.
- Approved Project Charter.
- Confirmed Hostinger constraint from the project owner.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/08_Deployment_Constraints.md

It directly restricts the choices available in Document 09 (Technology Stack) and future backend/frontend architecture documents.

## Open Questions

1. Does the specific Hostinger plan include SSH access?
2. Does the specific Hostinger plan allow 1-minute Cron jobs?
3. Can the document root be changed to a `public/` folder, or must we use `public_html/` mapping?
4. What is the exact PHP `max_execution_time` on the server?

## TBD Decisions

- TBD: Final verification of Hostinger plan capabilities (SSH, Cron, Document Root).
- TBD: Exact `.htaccess` security rules for the production environment.
- TBD: Fallback strategy if 1-minute Cron is not available.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC08

## Context

LABGOTO must be deployed to a specific shared hosting environment (Hostinger PHP 8.4). This document establishes the strict constraints and strategies required to ensure the application architecture remains compatible with this environment, preventing the selection of infrastructure-heavy tools that would fail in production.

This prompt implements the documentation outcome of the Deployment Constraints document.

## Objective

Create the official LABGOTO Deployment Constraints document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository will have a binding set of infrastructure rules that govern all future technology stack and architecture decisions.

## Existing Architecture

Relevant approved decisions:

- LABGOTO follows the new master directive.
- Hostinger PHP 8.4 is the confirmed deployment target.
- Shared hosting limitations must be respected.
- Documentation controls implementation.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Documents 01 through 07.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/08_Deployment_Constraints.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include actual hosting credentials, SSH keys, or server IP addresses.
- Focus only on the architectural constraints and strategies.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact. This is an internal infrastructure document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the shared hosting limitations.
- The file explicitly excludes Redis, Node.js, and Supervisor.
- The file defines the frontend pre-build strategy.

## Integration Requirements

This document must be treated as binding rules by the AI assistant when proposing the Technology Stack (Document 09) and future backend architecture.

## Definition of Done

This implementation is complete when:

- The Deployment Constraints file exists.
- The file matches the approved Phase 01 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.