# 10 — Testing Foundation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Definition of Done).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Database Foundation document.
- The approved RBAC Implementation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The testing philosophy and mandatory testing scope for LabGato.
- The approved testing tools and environment setup.
- The test directory structure and naming conventions.
- The database testing strategy (transactions vs. migrations).
- The specific testing rules for laboratory calculators and tools.
- The security and RBAC testing requirements.
- The rules for mocking external services (e.g., Mail, AI).
- The CI and local testing constraints.

## Affects

This document affects:

- All future vertical-slice implementation prompts.
- The Deployment and Release Process document.
- All future services, models, controllers, and Livewire components.
- The Definition of Done for every feature.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the mandatory testing baseline that must be met before any feature is marked "Complete".
- Ensures mathematical and scientific accuracy for laboratory calculators.
- Prevents privilege escalation and security regressions.
- Ensures tests can run reliably in local and CI environments without depending on the Hostinger production server.

---

# 1. Purpose

This document establishes the Testing Foundation for LabGato.

Because LabGato is a professional laboratory toolkit providing calculators, reference ranges, and scientific resources, accuracy and security are paramount. A bug in a calculator or a flaw in RBAC could lead to professional errors or unauthorized access.

Testing is not an afterthought; it is a core architectural requirement. No feature is considered complete until it satisfies the testing requirements defined in this document.

---

# 2. Testing Philosophy and Scope

## 2.1 Mandatory Testing

Every vertical slice must include tests.

- Backend logic must have Unit tests.
- HTTP routes, Livewire components, and workflows must have Feature tests.
- Security boundaries must have explicit negative tests (unauthorized access attempts).
- Calculators and Tools must have exact mathematical validation tests.

## 2.2 No Regressions

Before moving to the next feature or slice, the entire test suite must pass.

If a new feature breaks an existing test, the new feature must not be merged or deployed until the regression is fixed or the old test is intentionally and safely updated.

## 2.3 Test Data vs. Production Data

Tests must never run against production data.

Tests must use factories, seeders, or inline mock data to create the exact state required for the test scenario.

---

# 3. Testing Tools and Environment

## 3.1 Testing Framework

Approved baseline framework:

PHPUnit (via Laravel's built-in testing helpers).

Alternative:

Pest PHP may be used if preferred by the development team, provided it integrates seamlessly with Laravel's testing assertions and CI pipeline.

## 3.2 Browser / E2E Testing

For the MVP, Laravel Dusk or Playwright may be deferred unless complex JavaScript-driven calculator interactions require it.

Standard Livewire and HTTP Feature tests are sufficient for the foundation and initial CRUD slices.

## 3.3 Test Environment

Tests must run in the `testing` environment.

- `APP_ENV` must be `testing`.
- `CACHE_STORE`, `QUEUE_CONNECTION`, and `SESSION_DRIVER` should be set to `array` or `sync` in `phpunit.xml` to ensure speed and isolation.
- `MAIL_MAILER` must be set to `array` or `log`.

---

# 4. Test Directory Structure

Tests must follow the approved Laravel Application Structure.

tests/
├── Feature/
│   ├── Admin/
│   │   ├── Auth/
│   │   ├── Calculators/
│   │   ├── Users/
│   │   └── Settings/
│   ├── Api/
│   └── PublicSite/
│       ├── Calculators/
│       └── Search/
├── Unit/
│   ├── Enums/
│   ├── Models/
│   └── Services/
│       └── Calculators/
└── TestCase.php

## 4.1 Naming Conventions

Test classes must end with `Test`.

Examples:

- `LoginTest`
- `CalculatorCreationTest`
- `AnionGapCalculatorTest`
- `UserPolicyTest`

Test methods must describe the behavior clearly.

Examples:

- `test_admin_can_create_calculator()`
- `test_content_manager_cannot_delete_calculator()`
- `test_anion_gap_calculates_correctly_with_normal_values()`
- `test_anion_gap_handles_missing_albumin_gracefully()`

---

# 5. Database Testing Strategy

## 5.1 RefreshDatabase vs. Transactions

For local and CI testing, the `RefreshDatabase` trait is approved.

- It runs migrations once and wraps each test in a database transaction that rolls back at the end.
- This ensures a clean state for every test without the performance penalty of dropping and recreating tables.

## 5.2 Factories and Seeders

- Eloquent models must have corresponding Factory classes.
- Factories must generate valid, realistic dummy data (e.g., valid email formats, realistic laboratory values for reference ranges).
- The RBAC seeder must be executed in the test setup if testing permission-dependent features.

---

# 6. Unit Testing Rules

Unit tests must isolate the specific class or method being tested.

## 6.1 Services

Service classes (e.g., `AuditLogService`, `CalculatorService`) must be unit tested.

- Mock external dependencies (e.g., models, mailers).
- Assert that the service performs the correct logic and calls the correct dependencies.

## 6.2 Models

Model tests should focus on:

- Custom scopes.
- Accessors and mutators.
- Complex relationships.
- Attribute casting.

Standard CRUD operations do not need explicit unit tests if covered by Feature tests.

## 6.3 Enums and Helpers

Custom Enums and Support helpers must have unit tests verifying their expected outputs and edge cases.

---

# 7. Feature (Integration) Testing Rules

Feature tests must simulate real user interactions or API requests.

## 7.1 HTTP and Routing

- Test that public routes return 200 OK.
- Test that protected admin routes redirect guests to login (302).
- Test that authenticated users can access permitted pages (200).
- Test that authenticated users are blocked from unauthorized pages (403).

## 7.2 Livewire Components

Livewire components must be tested using Livewire's testing utilities.

- Test that the component mounts correctly.
- Test that form submissions validate correctly.
- Test that invalid data shows validation errors.
- Test that valid data updates the database and emits the correct events/redirects.

## 7.3 Form Requests

Test that Form Requests correctly reject invalid data and accept valid data.

---

# 8. Calculator and Tool Testing Rules (LabGato Specific)

Because LabGato provides scientific calculators, mathematical accuracy is a safety requirement.

## 8.1 Expected Result Testing

Every calculator must have a set of known inputs and expected outputs based on established medical/laboratory literature.

Example for Anion Gap:
- Na: 140, Cl: 100, HCO3: 24 -> Expected Gap: 16

## 8.2 Boundary and Edge Case Testing

Calculators must be tested against:

- Zero values.
- Negative values (where biologically impossible, should trigger validation or safe handling).
- Extremely high/low values.
- Missing optional parameters (e.g., Albumin correction in Anion Gap).
- Division by zero scenarios.

## 8.3 Rounding Rules

Tests must explicitly verify that the calculator's rounding rules match the documented specification (e.g., rounding to 1 decimal place vs. nearest whole number).

## 8.4 No Arbitrary Code Execution

Tests must verify that the calculator engine does not execute arbitrary PHP/JS code injected via the admin interface.

---

# 9. Security and RBAC Testing Rules

Security must be explicitly tested, not just assumed.

## 9.1 Privilege Escalation Tests

Tests must verify that:

- A Content Manager cannot assign the Super Administrator role.
- A user cannot modify their own roles via direct API/Livewire manipulation.
- A user cannot access routes protected by permissions they lack.

## 9.2 IDOR (Insecure Direct Object Reference) Tests

Tests must verify that:

- A user cannot edit or delete resources belonging to another tenant (if multi-tenancy is ever introduced).
- A user cannot access audit logs or settings without the correct permissions.

## 9.3 Authentication Tests

Tests must verify:

- Login throttling locks out users after X attempts.
- Deactivated users are immediately logged out and cannot log back in.
- Password reset tokens expire and cannot be reused.

---

# 10. Mocking and External Services

## 10.1 Mail and Notifications

Mail must be mocked using `Mail::fake()`.

Tests should assert that the mail was queued or sent to the correct address, but must never actually send emails during testing.

## 10.2 AI Provider (Future)

When AI features are implemented, the AI Provider Service must be mocked.

Tests must not make real HTTP requests to Groq or other AI providers.

## 10.3 Time and Dates

If a feature depends on time (e.g., password reset expiry, session timeout), tests must use `Travel` or `Carbon::setTestNow()` to control time deterministically.

---

# 11. Shared Hosting and CI Constraints

## 11.1 Test Execution Location

Tests must run locally on the developer's machine or in a CI/CD pipeline (e.g., GitHub Actions).

Tests must NEVER be executed on the Hostinger production or staging server.

## 11.2 Database Dependencies

Tests must use a local MySQL/MariaDB instance or an in-memory SQLite database (if SQLite compatibility is maintained for the specific features being tested).

Because LabGato relies on MySQL-specific features (e.g., specific JSON operations or full-text search), MySQL is the preferred test database to ensure parity with production.

## 11.3 No External Network Calls

Tests must not rely on external network availability. All external APIs must be mocked or faked.

---

# Document Completion Check

## Requirements Covered

- Testing philosophy and scope defined.
- Testing tools and environment defined.
- Test directory structure defined.
- Database testing strategy defined.
- Unit and Feature testing rules defined.
- Calculator and Tool specific testing rules defined.
- Security and RBAC testing rules defined.
- Mocking rules defined.
- Shared hosting and CI constraints defined.

## Decisions Established

- Testing is mandatory for the Definition of Done.
- PHPUnit (or Pest) is the approved framework.
- Calculators require exact mathematical and boundary testing.
- Security requires explicit negative and privilege escalation testing.
- Tests run locally or in CI, never on the production host.
- External services (Mail, AI) must be mocked.

## Dependencies

- Approved Phase 01 Core Product Areas.
- Approved Laravel Application Structure.
- Approved Database Foundation.
- Approved RBAC Implementation.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/10_Testing_Foundation.md

It dictates the testing requirements for all future implementation prompts.

## Open Questions

1. Will the CI pipeline use GitHub Actions, GitLab CI, or another provider?
2. Should SQLite be supported for faster local unit tests, or strictly enforce MySQL for all tests?

## TBD Decisions

- TBD: Final CI/CD provider selection.
- TBD: Final decision on SQLite vs MySQL for local testing.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC10

## Context

LabGato requires a strict testing foundation to ensure mathematical accuracy for laboratory tools and security for the admin portal. This document defines the rules, structure, and mandatory test types for all future development.

This prompt implements the documentation outcome of the Testing Foundation document.

## Objective

Create the official LabGato Testing Foundation document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding testing specification that governs the Definition of Done for all features.

## Existing Architecture

Relevant approved decisions:

- LabGato uses Laravel.
- Calculators must be mathematically validated.
- RBAC must prevent privilege escalation.
- Tests must not run on the production server.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 09.
- Any existing `phpunit.xml` or test directories.

## Files to Create

Create:

02_Technical_Foundation_Architecture/10_Testing_Foundation.md

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

- Do not include real test credentials or secrets.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file explicitly defines calculator math testing rules.
- The file explicitly defines RBAC privilege escalation testing rules.

## Integration Requirements

This document must be referenced in the Definition of Done for every future implementation prompt.

## Definition of Done

This implementation is complete when:

- The Testing Foundation file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.