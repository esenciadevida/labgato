# 01 — Laravel Application Structure

## Depends On

This document depends on:

- The approved Phase 01 documentation.
- The approved Technology Stack document.
- The approved Deployment Constraints document.
- The approved Information Architecture document.
- The approved Core Content Model document.
- The approved User Roles and RBAC document.
- The confirmed Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The approved Laravel application folder structure.
- The approved module boundaries.
- The approved code placement rules.
- The approved naming conventions.
- The approved routing file organization.
- The approved view organization.
- The approved test organization.
- The reserved future directories.
- The prohibited architectural patterns.

## Affects

This document affects:

- All future backend implementation.
- All future admin portal implementation.
- All future public site implementation.
- All future API implementation.
- All future Livewire components.
- All future tests.
- All future deployment procedures.
- All future feature documentation.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the folder structure for the Laravel application.
- Prevents disconnected or poorly placed code.
- Ensures admin, public, API, and service logic remain cleanly separated.
- Prepares the codebase for vertical-slice implementation.
- Keeps the architecture compatible with Hostinger shared hosting.

---

# 1. Purpose

This document defines the official Laravel application structure for LabGato.

The structure must be:

- Simple enough for shared hosting.
- Clear enough for long-term maintenance.
- Modular enough for future laboratory resources.
- Secure enough to separate public and admin concerns.
- Testable enough to support automated regression testing.
- Flexible enough for future AI, search, and analytics features.

LabGato must not become a collection of disconnected pages, controllers, and components.

Every file must have a clear responsibility.

---

# 2. Approved Application Layout

The target LabGato repository structure should follow this structure.

labgato/
├── app/
│   ├── Enums/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   │   ├── Auth/
│   │   ├── Content/
│   │   ├── Search/
│   │   ├── Settings/
│   │   └── Ai/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── PublicSite/
│   │   │   └── Api/
│   │   ├── Livewire/
│   │   │   ├── Admin/
│   │   │   └── PublicSite/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   │   ├── Admin/
│   │   │   ├── PublicSite/
│   │   │   └── Api/
│   │   └── Resources/
│   ├── Providers/
│   └── Support/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
│   ├── 01_Product_Foundation_and_Master_Architecture/
│   └── 02_Technical_Foundation_Architecture/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── auth/
│       ├── admin/
│       ├── components/
│       ├── errors/
│       ├── layouts/
│       ├── legal/
│       └── public/
├── routes/
│   ├── admin.php
│   ├── api.php
│   ├── auth.php
│   └── web.php
├── storage/
├── tests/
│   ├── Feature/
│   │   ├── Admin/
│   │   ├── Api/
│   │   └── PublicSite/
│   └── Unit/
│       ├── Enums/
│       ├── Models/
│       └── Services/
├── .env.example
├── artisan
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js

Notes:

- The final generated Laravel structure may vary slightly depending on the Laravel version used.
- The structure must remain compatible with Hostinger shared hosting.
- The public directory must be deployable as the web root or mapped safely to public_html.
- Application source files outside the public directory must not be publicly accessible.

---

# 3. Module Boundaries

LabGato should be mentally organized into modules, even if the first implementation uses a standard Laravel folder structure.

## 3.1 Access and Security Module

Responsible for:

- Administrator authentication
- Session security
- Password reset
- Login throttling
- Roles
- Permissions
- Policies
- Protected admin route access

Primary locations:

app/Http/Controllers/Auth/
app/Http/Middleware/
app/Models/
app/Policies/
app/Services/Auth/
database/migrations/
database/seeders/

## 3.2 System Module

Responsible for:

- Settings
- Audit logs
- Error handling behavior
- Logging rules
- System status pages
- Environment-safe helpers

Primary locations:

app/Models/
app/Services/Settings/
app/Support/
config/
resources/views/errors/

## 3.3 Taxonomy Module

Responsible for:

- Categories
- Tags
- Resource classification
- Taxonomy admin management

Primary locations:

app/Models/
app/Http/Controllers/Admin/
app/Http/Livewire/Admin/
app/Http/Requests/Admin/
database/migrations/

## 3.4 Resource Content Module

Responsible for future laboratory resources:

- Laboratory Tools
- Calculators
- Reference Ranges
- SOP Resources
- Result Interpretation Resources
- Knowledge Resources

Primary locations:

app/Models/
app/Services/Content/
app/Http/Controllers/Admin/
app/Http/Controllers/PublicSite/
app/Http/Livewire/Admin/
app/Http/Livewire/PublicSite/
resources/views/admin/
resources/views/public/

This module will be expanded in later phases.

## 3.5 Public Site Module

Responsible for:

- Public layouts
- Public navigation
- Public resource pages
- Legal pages
- Search results page
- Error pages
- SEO metadata rendering

Primary locations:

app/Http/Controllers/PublicSite/
app/Http/Livewire/PublicSite/
resources/views/public/
resources/views/layouts/
resources/views/legal/
resources/views/errors/

## 3.6 Admin Portal Module

Responsible for:

- Admin layout
- Admin dashboard
- Admin navigation
- Admin CRUD screens
- Admin forms
- Admin tables
- Admin authorization UI

Primary locations:

app/Http/Controllers/Admin/
app/Http/Livewire/Admin/
resources/views/admin/
resources/views/layouts/
resources/views/components/admin/

## 3.7 API Module

Responsible for:

- JSON endpoints where required
- API validation
- API rate limiting
- API response formatting
- Future integrations

Primary locations:

app/Http/Controllers/Api/
app/Http/Requests/Api/
app/Http/Resources/
routes/api.php

The API module should remain minimal until a specific API requirement is approved.

## 3.8 AI Module

Reserved for future controlled AI functionality.

Primary locations:

app/Services/Ai/
app/Http/Controllers/PublicSite/Ai/
app/Http/Livewire/PublicSite/Ai/
app/Models/Ai/

The AI module must not be implemented until the AI architecture phase is approved.

---

# 4. Code Placement Rules

The following rules must be followed during implementation.

## 4.1 Routes

Routes must be defined only in route files.

- Public website routes belong in routes/web.php.
- Authentication routes belong in routes/auth.php.
- Admin routes belong in routes/admin.php.
- API routes belong in routes/api.php.

Route files must not contain complex business logic.

## 4.2 Controllers

Controllers must coordinate HTTP requests.

Allowed controller responsibilities:

- Receive validated request data.
- Call a service or model query.
- Return a view or JSON response.
- Redirect with success or error messages.

Controllers must not contain:

- Complex calculation logic.
- Long procedural workflows.
- Direct SQL unless simple and safe.
- Authorization decisions that belong in policies.
- HTML generation.

## 4.3 Form Requests

Validation must use Laravel Form Requests where appropriate.

Form requests must define:

- Validation rules.
- Authorization for the request where appropriate.
- Custom error messages where needed.
- Input normalization where needed.

Form requests must not contain complex business logic.

## 4.4 Livewire Components

Livewire components may be used for interactive admin and public features.

Allowed Livewire responsibilities:

- Manage UI state.
- Handle form submission.
- Call services.
- Trigger validation.
- Emit events.
- Render dynamic tables, filters, and forms.

Livewire components must not:

- Bypass server-side validation.
- Bypass authorization.
- Contain complex calculation logic directly.
- Expose unauthorized data.
- Trust browser state as the source of truth.

## 4.5 Services

Services must contain business logic.

Examples of service responsibilities:

- Calculator execution.
- Reference range filtering.
- Search querying.
- Settings retrieval.
- Audit log creation.
- Role and permission assignment.
- Content publication workflow.
- AI provider communication in the future.

Services must be testable independently of controllers and Livewire components.

## 4.6 Models

Eloquent models must represent persistent entities.

Allowed model responsibilities:

- Define relationships.
- Define casts.
- Define scopes.
- Define fillable or guarded attributes.
- Define simple accessors and mutators.
- Define basic model events where appropriate.

Models must not:

- Contain complex calculation engines.
- Call external APIs directly.
- Render views.
- Contain authorization rules that belong in policies.
- Become bloated with unrelated business logic.

## 4.7 Policies

Authorization must be defined in policies.

Policies must answer questions such as:

- Can the user view this resource?
- Can the user create this resource?
- Can the user edit this resource?
- Can the user publish this resource?
- Can the user delete this resource?
- Can the user manage administrators?

Policies must be enforced server-side.

## 4.8 Enums

PHP enums must be used for controlled status values.

Examples:

- ResourceStatus
- UserStatus
- AuditActionType
- SettingType

Magic strings must not be used for statuses where an enum is practical.

## 4.9 Support Classes

Support classes must contain reusable non-business helpers.

Examples:

- Value objects
- Formatting helpers
- Safe string helpers
- Pagination helpers
- Response helpers

Support classes must not become a dumping ground for unrelated logic.

## 4.10 Views

Views must only present data.

Views must not:

- Query the database directly.
- Contain business logic.
- Access secrets.
- Perform authorization decisions.
- Include admin-only partials in public pages.
- Render sensitive debugging information in production.

---

# 5. Naming Conventions

The following naming conventions must be used.

## 5.1 PHP Classes

Use PascalCase.

Examples:

- UserController
- CalculatorController
- StoreCalculatorRequest
- CalculatorPolicy
- ResourceStatus

## 5.2 Methods and Variables

Use camelCase.

Examples:

- getPublishedCalculators
- storeReferenceRange
- isActive

## 5.3 Models

Use singular PascalCase.

Examples:

- User
- Role
- Permission
- Category
- Tag
- AuditLog
- Setting

## 5.4 Database Tables

Use plural snake_case.

Examples:

- users
- roles
- permissions
- categories
- tags
- audit_logs
- settings

## 5.5 Controllers

Use controller names that describe the resource.

Public examples:

- PublicSite\HomeController
- PublicSite\CalculatorController
- PublicSite\SearchController

Admin examples:

- Admin\DashboardController
- Admin\CalculatorController
- Admin\UserController
- Admin\AuditLogController

API examples:

- Api\V1\CalculatorController
- Api\V1\SearchController

## 5.6 Form Requests

Use verb-based names.

Examples:

- StoreCalculatorRequest
- UpdateCalculatorRequest
- LoginFormRequest
- StoreCategoryRequest

## 5.7 Livewire Components

Use descriptive nested names.

Admin examples:

- Admin\Dashboard\RecentActivity
- Admin\Calculators\CalculatorForm
- Admin\Calculators\CalculatorTable
- Admin\Users\UserRoleManager

Public examples:

- PublicSite\Calculators\CalculatorRunner
- PublicSite\Search\SearchForm

## 5.8 Policies

Use resource-based names.

Examples:

- CalculatorPolicy
- CategoryPolicy
- UserPolicy
- SettingPolicy
- AuditLogPolicy

## 5.9 Services

Use clear action or domain names.

Examples:

- AuthService
- AuditLogService
- SettingService
- CalculatorService
- ReferenceRangeService
- SearchService
- AiProviderService

## 5.10 Views

Use lowercase kebab-case file names.

Examples:

- resources/views/admin/calculators/index.blade.php
- resources/views/admin/calculators/create.blade.php
- resources/views/public/calculators/show.blade.php
- resources/views/layouts/admin.blade.php
- resources/views/layouts/public.blade.php

## 5.11 Route Names

Use dot notation.

Public route names should use the public prefix where useful.

Examples:

- public.home
- public.calculators.index
- public.calculators.show
- public.search
- public.legal.privacy

Authentication route names:

- auth.login
- auth.logout
- auth.password.request
- auth.password.reset

Admin route names:

- admin.dashboard
- admin.calculators.index
- admin.calculators.create
- admin.calculators.edit
- admin.users.index
- admin.audit-logs.index

API route names:

- api.v1.calculators.index
- api.v1.calculators.show
- api.v1.search

---

# 6. Routing Structure

## 6.1 routes/web.php

routes/web.php must contain public website routes.

Expected public route groups include:

- Home
- Resource listing pages
- Resource detail pages
- Search
- Legal pages
- Error fallbacks

Public routes must not require authentication unless explicitly approved.

## 6.2 routes/auth.php

routes/auth.php must contain authentication routes.

Expected authentication routes include:

- Admin login
- Admin logout
- Password request
- Password reset

Authentication routes must not be indexable.

## 6.3 routes/admin.php

routes/admin.php must contain admin portal routes.

All admin routes must be:

- Prefixed with /admin
- Named with admin.
- Protected by authentication middleware
- Protected by authorization rules
- Excluded from search indexing

## 6.4 routes/api.php

routes/api.php must contain API routes.

API routes must be:

- Versioned where appropriate
- Validated
- Rate-limited where appropriate
- Protected where required
- Safe in error responses

The API surface should remain minimal until required.

---

# 7. View Structure

The following view structure is approved.

## 7.1 Layouts

resources/views/layouts/

Expected layouts:

- public.blade.php
- admin.blade.php
- auth.blade.php
- error.blade.php

## 7.2 Components

resources/views/components/

Expected component groups:

- ui/
- admin/
- public/
- layout/

Examples:

- components/ui/button.blade.php
- components/ui/input.blade.php
- components/ui/alert.blade.php
- components/ui/badge.blade.php
- components/admin/sidebar.blade.php
- components/admin/page-header.blade.php
- components/public/meta.blade.php
- components/public/resource-card.blade.php

## 7.3 Admin Views

resources/views/admin/

Expected folders:

- dashboard/
- users/
- roles/
- categories/
- tags/
- settings/
- audit-logs/
- calculators/
- tools/
- reference-ranges/
- sops/
- interpretation/
- knowledge/

Resource-specific folders should be created only when the corresponding vertical slice is approved.

## 7.4 Public Views

resources/views/public/

Expected folders:

- home/
- calculators/
- tools/
- reference-ranges/
- sops/
- interpretation/
- knowledge/
- search/

Resource-specific folders should be created only when the corresponding vertical slice is approved.

## 7.5 Auth Views

resources/views/auth/

Expected pages:

- login.blade.php
- forgot-password.blade.php
- reset-password.blade.php

## 7.6 Legal Views

resources/views/legal/

Expected pages:

- privacy-policy.blade.php
- terms-of-service.blade.php
- disclaimer.blade.php
- accessibility-statement.blade.php
- cookie-policy.blade.php

## 7.7 Error Views

resources/views/errors/

Expected pages:

- 403.blade.php
- 404.blade.php
- 419.blade.php
- 429.blade.php
- 500.blade.php
- 503.blade.php

Error pages must be safe, branded, and free of technical stack traces in production.

---

# 8. Configuration Rules

## 8.1 Static Configuration

Static configuration must live in config files and be driven by environment variables where appropriate.

Examples:

- Database credentials
- Mail credentials
- AI provider keys
- App URL
- Debug mode
- Session driver
- Cache driver
- Queue driver

## 8.2 Dynamic Configuration

Administrator-managed settings must live in the database settings table.

Examples:

- Site name
- Default meta title
- Default meta description
- Logo
- Favicon
- Analytics settings where approved
- AI feature flags where approved

## 8.3 Environment Access

The env() helper must not be used directly outside configuration files.

Application code must use config() values.

## 8.4 Secrets

Secrets must never be committed.

Secrets must never be rendered in views.

Secrets must never be exposed in logs.

Secrets must never be sent to the browser.

---

# 9. Shared Hosting Compatibility Rules

The application structure must remain compatible with Hostinger shared hosting.

## 9.1 Public Directory

The deployment plan must support either:

- Pointing the domain document root to Laravel's public directory, or
- Safely mapping public assets into public_html while keeping the application core protected.

## 9.2 Storage Permissions

The following directories must be writable by the application:

- storage/
- bootstrap/cache/

## 9.3 No Daemon Processes

The application structure must not depend on:

- Supervisor
- Long-running queue workers
- Node servers
- WebSocket daemons

## 9.4 No Server-Side Node Builds

Frontend assets must be built locally or in CI.

The production server must receive compiled assets only.

---

# 10. Reserved Future Directories

The following directories are reserved for approved future work.

## 10.1 app/Services/Ai

Reserved for AI provider adapters, prompt builders, knowledge base services, and safety filters.

This must remain unused until the AI architecture phase is approved.

## 10.2 app/Services/Search

Reserved for search query services, result formatting, and search analytics integration.

## 10.3 app/Services/Content

Reserved for resource publication, versioning, taxonomy assignment, and content workflow services.

## 10.4 app/Http/Livewire/PublicSite

Reserved for public interactive components such as calculator runners and search forms.

---

# 11. Prohibited Patterns

The following patterns are prohibited.

## 11.1 Business Logic in Views

Blade views must not perform calculations, query models, or make authorization decisions.

## 11.2 Fat Controllers

Controllers must not contain large workflows that belong in services.

## 11.3 Fat Models

Models must not become containers for complex calculation engines or external integrations.

## 11.4 Unprotected Admin Routes

Admin routes must never be accessible without authentication and authorization.

## 11.5 Public Exposure of Admin Components

Admin-only components must not be rendered on public pages.

## 11.6 Hardcoded Statuses

Status values must not be hardcoded as arbitrary strings when enums are appropriate.

## 11.7 Direct Environment Access

Application classes must not call env() directly.

## 11.8 Dead UI

Buttons, links, forms, tabs, filters, and modals must not be rendered unless they are connected to working behavior.

## 11.9 Fake Data in Production UI

The admin dashboard and public pages must not display fake production statistics.

## 11.10 Silent Architecture Changes

Folder names, module boundaries, and route groups must not be changed without a documented change request.

---

# Document Completion Check

## Requirements Covered

- Laravel application structure defined.
- Module boundaries defined.
- Code placement rules defined.
- Naming conventions defined.
- Routing file organization defined.
- View organization defined.
- Configuration rules defined.
- Shared hosting compatibility rules defined.
- Reserved future directories defined.
- Prohibited patterns defined.

## Decisions Established

- LabGato will use a structured Laravel application layout.
- Admin, public, auth, and API routes will be separated.
- Business logic will live in services.
- Authorization will live in policies.
- Views will remain presentation-only.
- Livewire will be used for interactive components where appropriate.
- AI directories are reserved but not implemented yet.
- The structure must remain Hostinger-compatible.

## Dependencies

- Approved Technology Stack.
- Approved Deployment Constraints.
- Approved Information Architecture.
- Approved Core Content Model.
- Approved User Roles and RBAC.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/01_Laravel_Application_Structure.md

It affects all future backend, frontend, admin, API, and test implementation files.

## Open Questions

1. Should the project use a dedicated modular package structure later if the codebase grows?
2. Should route model binding be used for all public resources?
3. Should admin resource routes use IDs or slugs?
4. Should API versioning begin immediately as /api/v1 or remain unversioned until needed?

## TBD Decisions

- TBD: Final decision on modular package structure if needed.
- TBD: Final decision on route model binding strategy.
- TBD: Final decision on admin route parameter type.
- TBD: Final decision on API versioning start point.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC01

## Context

LabGato requires an approved Laravel application structure before foundation implementation begins. This document defines the folder structure, module boundaries, code placement rules, naming conventions, and routing organization required to keep the codebase maintainable and Hostinger-compatible.

This prompt implements the documentation outcome of the Laravel Application Structure document.

## Objective

Create the official LabGato Laravel Application Structure document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository should contain the approved structural foundation for future Laravel implementation.

## Existing Architecture

Relevant approved decisions:

- LabGato uses Laravel.
- LabGato uses Blade, Tailwind, Alpine.js, and Livewire.
- LabGato is deployed to Hostinger shared hosting.
- Admin, public, auth, and API areas must be separated.
- Business logic must not live in views.
- AI implementation is deferred.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Phase 01 documents
- Any existing Laravel application files
- Any existing route files
- Any existing view folders

If no files exist, record that the technical documentation structure is being initialized.

## Files to Create

Create:

02_Technical_Foundation_Architecture/01_Laravel_Application_Structure.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

02_Technical_Foundation_Architecture/01_Laravel_Application_Structure.md

Do not modify application code during this documentation step.

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

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- Consistent naming examples.

## SEO Requirements

No public SEO impact.

This is an internal/documentation file.

## Analytics Requirements

No analytics events are required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file defines admin/public separation.
- The file defines service and policy placement.
- The file does not introduce AI implementation before approval.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by all future Phase 02 technical documents.

No future code structure may conflict with this document without an approved change request.

## Definition of Done

This implementation is complete when:

- The Laravel Application Structure file exists.
- The file matches the approved Phase 02 file tree.
- The file defines the approved folder structure.
- The file defines code placement rules.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.