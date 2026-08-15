LABGOTO/
├── database/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── CalculatorSeeder.php
│       ├── LaboratoryToolSeeder.php
│       ├── ReferenceRangeSeeder.php
│       ├── SopResourceSeeder.php
│       ├── InterpretationResourceSeeder.php
│       └── KnowledgeResourceSeeder.php
├── routes/
│   ├── web.php
│   ├── admin.php
│   └── api.php
├── RELEASE_NOTES.md
├── CHANGELOG.md
├── .env.example
└── tests/
    └── Feature/
        └── SmokeTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-HARDENING-SPRINT4

CONTEXT:
LABGOTO MVP feature development and all hardening sprints (Security, Performance/SEO, Accessibility) are complete. The application is now stable, secure, and accessible.

We are now in Sprint 4: Production Readiness & Real-World Seed Data. This is the final step before the application is considered ready for its first production deployment on Hostinger.

OBJECTIVE:
Populate the database with high-quality, accurate, real-world laboratory seed data. Verify that all production caching mechanisms work without errors. Clean up the environment configuration. Execute a final end-to-end smoke test. Prepare the v1.0.0 release documentation.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- LABGOTO targets professional laboratory scientists and students.
- Seed data must be mathematically and scientifically accurate.
- Production environment requires config, route, and view caching for performance on shared hosting.
- Fake data and placeholder text must be removed before release.

FILES TO INSPECT:
- database/seeders/ (all resource seeders)
- routes/web.php
- routes/admin.php
- routes/api.php
- config/ (all config files)
- .env.example
- DEPLOYMENT.md

FILES TO CREATE:
- RELEASE_NOTES.md
- CHANGELOG.md
- tests/Feature/SmokeTest.php

FILES TO MODIFY:
- database/seeders/ (all resource seeders) - update with real data
- database/seeders/DatabaseSeeder.php - ensure proper execution order
- routes/*.php - remove any closures to allow route:cache
- .env.example - clean up and finalize

TASK 1: REAL-WORLD SEED DATA POPULATION

Update all resource seeders to include accurate, professional laboratory data. Do not use "Lorem Ipsum" or fake medical data.

1. CalculatorSeeder:
   - Anion Gap (Na - (Cl + HCO3))
   - Corrected Calcium (Ca + 0.8 * (4.0 - Albumin))
   - Absolute Neutrophil Count (ANC) (WBC * %Neutrophils)
   - MCV, MCH, MCHC (Red Blood Cell Indices)
   *Ensure input/output schemas and calculation engines are perfectly aligned with these formulas.*

2. ReferenceRangeSeeder:
   - Complete Blood Count (CBC): WBC, RBC, Hemoglobin, Hematocrit, Platelets (include Male/Female/Adult/Pediatric variations).
   - Comprehensive Metabolic Panel (CMP): Glucose, BUN, Creatinine, Sodium, Potassium, Calcium.
   *Ensure demographic variations and units are accurate.*

3. LaboratoryToolSeeder:
   - Dilution Calculator (C1V1 = C2V2)
   - Centrifugation RPM to RCF (Relative Centrifugal Force) converter.

4. SopResourceSeeder:
   - Standard Venipuncture (Phlebotomy) Procedure.
   - Daily Hematology Analyzer Quality Control.

5. InterpretationResourceSeeder:
   - Interpreting Elevated Liver Enzymes (AST/ALT).
   - Interpreting Anemia Indices (Microcytic vs. Macrocytic).

6. KnowledgeResourceSeeder:
   - Introduction to Hematology.
   - Principles of Clinical Chemistry.

TASK 2: PRODUCTION CACHING VERIFICATION

Laravel's production optimization commands will fail if there are architectural violations (e.g., closures in routes).

Requirements:
1. Inspect `routes/web.php`, `routes/admin.php`, and `routes/api.php`.
   - Ensure NO routes use Closure functions (e.g., `Route::get('/test', function() { ... })`). All routes must point to Controller actions.
2. Inspect `config/*.php` files.
   - Ensure NO config files use dynamic variables, `env()` calls outside of `config/app.php`, or closure functions.
3. Run the following commands locally to verify they execute without exceptions:
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
4. If any command fails, fix the underlying architectural issue immediately.
5. Run `php artisan optimize:clear` after testing to return to local dev state.

TASK 3: ENVIRONMENT & CONFIGURATION CLEANUP

Review `.env.example`.

Requirements:
1. Ensure all required variables are present and documented with comments.
2. Ensure NO real secrets, API keys, or passwords are present.
3. Ensure `APP_DEBUG` is set to `false` by default in the production example.
4. Ensure `APP_ENV` is set to `production` in the production example.
5. Remove any unused or deprecated environment variables.

TASK 4: END-TO-END SMOKE TEST

Create `tests/Feature/SmokeTest.php`.

This test simulates a complete user journey to ensure no critical regressions exist after all hardening sprints.

Required test scenarios:
1. test_public_home_and_search_journey
   - Visit home page (assert 200).
   - Visit search page, search for "Anion Gap" (assert 200, assert results found).
   - Click through to the Anion Gap calculator public page (assert 200).
2. test_admin_login_and_dashboard_journey
   - Visit login page.
   - Login as Super Admin.
   - Assert redirected to admin dashboard.
   - Visit admin calculators index (assert 200).
3. test_public_resource_rendering
   - Visit a published Reference Range detail page (assert 200, assert disclaimer is visible).
   - Visit a published SOP detail page (assert 200, assert disclaimer is visible).
   - Visit a published Knowledge detail page (assert 200).

TASK 5: RELEASE DOCUMENTATION

Create `RELEASE_NOTES.md` and `CHANGELOG.md`.

`RELEASE_NOTES.md` should include:
- Version: v1.0.0 (MVP Release)
- Release Date: [Current Date]
- Overview: Launch of LABGOTO MVP, a professional laboratory toolkit.
- Key Features:
  - 6 Core Resource Modules (Calculators, Tools, Reference Ranges, SOPs, Interpretation, Knowledge).
  - Role-Based Access Control (Super Admin, Content Manager, Viewer).
  - Global Search with Analytics.
  - Secure Authentication & Session Management.
  - WCAG 2.2 AA Accessible Public and Admin Interfaces.
  - SEO-optimized public pages.
- Known Limitations:
  - AI Assistance deferred to v1.1.
  - Public user accounts deferred.
  - Dark mode deferred.

`CHANGELOG.md` should follow the Keep a Changelog format (Added, Changed, Fixed, etc.).

CLEANUP REQUIREMENTS:
- Delete any temporary test files created during development that are not part of the permanent test suite.
- Clear local `storage/logs/laravel.log`.
- Ensure `.gitignore` is correctly ignoring all sensitive and generated files.

DEFINITION OF DONE:
- Seeders populate the database with accurate, real-world laboratory data.
- `php artisan config:cache`, `route:cache`, and `view:cache` run successfully without errors.
- `.env.example` is clean and production-ready.
- Smoke tests pass, verifying the critical user journeys.
- `RELEASE_NOTES.md` and `CHANGELOG.md` are created.
- The codebase is clean, committed, and ready for the `DEPLOYMENT.md` procedure to be executed on Hostinger.