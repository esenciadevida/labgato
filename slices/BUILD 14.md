LABGOTO/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── ReferenceRangeController.php
│   │   │   └── PublicSite/
│   │   │       └── ReferenceRangeController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreReferenceRangeRequest.php
│   │           └── UpdateReferenceRangeRequest.php
│   ├── Models/
│   │   └── ReferenceRange.php
│   ├── Policies/
│   │   └── ReferenceRangePolicy.php
│   └── Rules/
│       └── ValidReferenceLimits.php
├── database/
│   ├── factories/
│   │   └── ReferenceRangeFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_reference_ranges_table.php
│   │   ├── xxxx_create_reference_range_category_table.php
│   │   └── xxxx_create_reference_range_tag_table.php
│   └── seeders/
│       └── ReferenceRangeSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── reference-ranges/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── partials/
│       │           └── form.blade.php
│       └── public/
│           └── reference-ranges/
│               ├── index.blade.php
│               └── show.blade.php
├── routes/
│   ├── admin.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── Admin/
    │   │   └── ReferenceRanges/
    │   │       ├── ReferenceRangeAdminCrudTest.php
    │   │       └── ReferenceRangeAdminValidationTest.php
    │   └── PublicSite/
    │       └── ReferenceRanges/
    │           ├── ReferenceRangeListingTest.php
    │           └── ReferenceRangeDetailTest.php
    └── Unit/
        └── Models/
            └── ReferenceRangeTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-PHASE04-BUILD14

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1, Builds 10-13, and the Theme are complete. Phase 04 Content & Reference Architecture is approved.

We are now implementing Build 14: Reference Ranges Vertical Slice (Slice 4).

OBJECTIVE:
Implement the complete Reference Ranges resource module, focusing on demographic variations, data integrity validation, and the mandatory educational disclaimer.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Resources use common lifecycle states and RBAC patterns.
- Reference ranges use a flat table with demographic columns.
- Grouping on the public page is handled by matching `analyte_name`.
- Unit conversions are handled via a `conversion_factor` column.
- A mandatory educational disclaimer is required on all public Reference Range pages.
- Bulk CSV import is deferred.

FILES TO INSPECT:
- app/Models/Calculator.php
- app/Models/Traits/HasResourceLifecycle.php
- app/Enums/ResourceStatus.php
- app/Http/Controllers/Admin/CalculatorController.php
- app/Http/Controllers/PublicSite/CalculatorController.php
- app/Services/SearchService.php
- routes/admin.php
- routes/web.php

FILES TO CREATE:
- app/Models/ReferenceRange.php
- app/Policies/ReferenceRangePolicy.php
- app/Rules/ValidReferenceLimits.php
- app/Http/Controllers/Admin/ReferenceRangeController.php
- app/Http/Controllers/PublicSite/ReferenceRangeController.php
- app/Http/Requests/Admin/StoreReferenceRangeRequest.php
- app/Http/Requests/Admin/UpdateReferenceRangeRequest.php
- database/migrations/xxxx_create_reference_ranges_table.php
- database/migrations/xxxx_create_reference_range_category_table.php
- database/migrations/xxxx_create_reference_range_tag_table.php
- database/factories/ReferenceRangeFactory.php
- database/seeders/ReferenceRangeSeeder.php
- resources/views/admin/reference-ranges/index.blade.php
- resources/views/admin/reference-ranges/create.blade.php
- resources/views/admin/reference-ranges/edit.blade.php
- resources/views/admin/reference-ranges/partials/form.blade.php
- resources/views/public/reference-ranges/index.blade.php
- resources/views/public/reference-ranges/show.blade.php
- tests/Unit/Models/ReferenceRangeTest.php
- tests/Feature/Admin/ReferenceRanges/ReferenceRangeAdminCrudTest.php
- tests/Feature/Admin/ReferenceRanges/ReferenceRangeAdminValidationTest.php
- tests/Feature/PublicSite/ReferenceRanges/ReferenceRangeListingTest.php
- tests/Feature/PublicSite/ReferenceRanges/ReferenceRangeDetailTest.php

FILES TO MODIFY:
- routes/admin.php
- routes/web.php
- app/Providers/AppServiceProvider.php (to register search provider)
- database/seeders/DatabaseSeeder.php (if optional seeding is needed)

DATABASE REQUIREMENTS:

Create reference_ranges table.

Required columns:
id
name (e.g., "Hemoglobin - Adult Male")
slug (unique)
short_description
analyte_name (e.g., "Hemoglobin")
specimen_type (nullable)
methodology (nullable)
population_group (nullable)
age_min (decimal 8,4, nullable)
age_max (decimal 8,4, nullable)
sex (string, default 'any')
unit (string)
lower_limit (decimal 12,4, nullable)
upper_limit (decimal 12,4, nullable)
critical_low (decimal 12,4, nullable)
critical_high (decimal 12,4, nullable)
conversion_factor (decimal 12,6, nullable)
source_citation (text, nullable)
effective_date (date, nullable)
status (string, default draft)
published_at (timestamp, nullable)
created_by (foreign key users.id, nullable)
updated_by (foreign key users.id, nullable)
seo_title (nullable)
seo_description (text, nullable)
created_at
updated_at

Indexes:
- unique on slug
- index on analyte_name
- index on status
- index on published_at
- fulltext on name, analyte_name, short_description

Create reference_range_category and reference_range_tag pivot tables (standard explicit pivot structure).

MODEL REQUIREMENTS:

Create app/Models/ReferenceRange.php.
- Use HasFactory, HasResourceLifecycle.
- Cast age_min, age_max, limits to decimal/float as appropriate.
- Cast effective_date, published_at to datetime.
- Cast status to ResourceStatus.
- Define categories(), tags(), createdBy(), updatedBy().
- Define scopePublished, scopeSearch.
- Define scopeForAnalyte($analyteName).

ADMIN CRUD REQUIREMENTS:

Create Admin ReferenceRangeController and Form Requests.

Form fields:
- Basic: name, slug, short_description, status, analyte_name.
- Demographics: specimen_type, methodology, population_group, age_min, age_max, sex.
- Values: unit, lower_limit, upper_limit, critical_low, critical_high, conversion_factor.
- Metadata: source_citation, effective_date.
- Taxonomy & SEO.

Validation Rules (ValidReferenceLimits):
- lower_limit must be < upper_limit (if both present).
- critical_low must be < lower_limit (if both present).
- critical_high must be > upper_limit (if both present).
- age_min must be <= age_max (if both present).

Admin UX:
- Include a "Save and Create Another" button on the create form to facilitate rapid entry of demographic variations.

PUBLIC ROUTE & PAGE REQUIREMENTS:

Public Routes:
GET /reference-ranges (public.reference-ranges.index)
GET /reference-ranges/{slug} (public.reference-ranges.show)

Public Listing Page:
- Display a searchable list of published reference ranges.
- Group or highlight by `analyte_name` if practical, or just list them with the analyte name as a badge/column.

Public Detail Page:
- Resolve by slug.
- Fetch the specific record.
- Fetch all OTHER published records with the same `analyte_name` to display a "Variations" table.
- Display the Mandatory Educational Disclaimer prominently at the top.
- Display the specific record's details and the variations table.
- Include breadcrumbs, meta tags, JSON-LD.

Mandatory Disclaimer Text:
"Educational Reference Only: Reference ranges vary significantly between laboratories based on the specific assay, instrument, and patient population. Always use the reference range provided by the testing laboratory on the patient's specific lab report. This data is for educational purposes and must not be used for clinical diagnosis or treatment decisions."

SEARCH INTEGRATION:

Register reference range search provider in AppServiceProvider.
- Search name, analyte_name, short_description.
- Map type to 'reference_range'.
- Map URL to public.reference-ranges.show.

SEEDER & FACTORY:

Create ReferenceRangeFactory and ReferenceRangeSeeder.
- Seed a few common analytes with variations (e.g., Hemoglobin Male/Female, Glucose Fasting/Random).

TESTING REQUIREMENTS:

Unit Tests:
- Test ValidReferenceLimits rule (passes valid, fails invalid limits/ages).

Feature Tests (Admin):
- Test CRUD operations and permissions.
- Test validation failures for invalid limits.
- Test "Save and Create Another" flow (optional, or just test standard store).

Feature Tests (Public):
- Test listing renders only published ranges.
- Test detail page renders the mandatory disclaimer.
- Test detail page renders variations for the same analyte.
- Test 404 for unpublished/nonexistent slugs.

ACCESSIBILITY & SEO:
- Tables must use proper <th scope="col"> and <th scope="row">.
- Disclaimer must be readable and high contrast.
- Standard resource SEO rules apply.

SECURITY:
- Enforce RBAC server-side.
- Sanitize outputs.
- Audit log changes.

DEFINITION OF DONE:
- reference_ranges table and pivots exist.
- ReferenceRange model and policy exist.
- Admin CRUD works with limit validation.
- Public listing and detail pages work.
- Mandatory disclaimer is visible on all public detail pages.
- Variations for the same analyte are grouped on the detail page.
- Search provider is registered.
- Unit and feature tests pass.
- No fake data or dead UI.