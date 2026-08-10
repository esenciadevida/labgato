labgato/
├── app/
│   ├── Contracts/
│   │   └── CalculatorInterface.php
│   ├── Enums/
│   │   └── ResourceStatus.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── CalculatorController.php
│   │   │   └── PublicSite/
│   │   │       └── CalculatorController.php
│   │   ├── Livewire/
│   │   │   └── PublicSite/
│   │   │       └── Calculators/
│   │   │           └── CalculatorRunner.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreCalculatorRequest.php
│   │           └── UpdateCalculatorRequest.php
│   ├── Models/
│   │   ├── Calculator.php
│   │   └── Traits/
│   │       └── HasResourceLifecycle.php
│   ├── Policies/
│   │   └── CalculatorPolicy.php
│   ├── Rules/
│   │   └── ValidResourceSchema.php
│   ├── Services/
│   │   └── Calculators/
│   │       ├── CalculatorService.php
│   │       └── Engines/
│   │           └── AnionGapCalculator.php
│   └── Support/
│       └── NumberFormatter.php
├── database/
│   ├── factories/
│   │   └── CalculatorFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_calculators_table.php
│   │   ├── xxxx_create_calculator_category_table.php
│   │   └── xxxx_create_calculator_tag_table.php
│   └── seeders/
│       └── CalculatorSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── calculators/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── partials/
│       │           └── form.blade.php
│       ├── livewire/
│       │   └── public/
│       │       └── calculators/
│       │           └── calculator-runner.blade.php
│       └── public/
│           └── calculators/
│               ├── index.blade.php
│               └── show.blade.php
├── routes/
│   ├── admin.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── Admin/
    │   │   └── Calculators/
    │   │       ├── CalculatorAdminCrudTest.php
    │   │       └── CalculatorAdminValidationTest.php
    │   └── PublicSite/
    │       └── Calculators/
    │           ├── CalculatorListingTest.php
    │           ├── CalculatorDetailTest.php
    │           └── CalculatorExecutionTest.php
    └── Unit/
        └── Services/
            └── Calculators/
                ├── AnionGapCalculatorTest.php
                └── CalculatorServiceTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE03-BUILD12

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1 Application Foundation is complete. Build 10 Taxonomy Admin Module is complete. Build 11 Search Implementation is complete. Phase 03 Resource Architecture is approved.

We are now implementing Build 12: Calculators Vertical Slice.

This is the first complete resource vertical slice. It must include database schema, model, admin CRUD, public pages, interactive calculation engine, search integration, tests, SEO, and accessibility support.

OBJECTIVE:
Implement the complete Calculators resource module.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Resources use common lifecycle states.
- Calculators use a Code-Based Calculation Engine.
- Arbitrary code execution is prohibited.
- Calculator logic lives in dedicated PHP classes.
- Input and output schemas are stored as JSON.
- Calculators use explicit pivot tables for categories and tags.
- Public URLs use slugs.
- Admin URLs use IDs.
- Published calculators are publicly visible.
- Draft, archived, and obsolete calculators are not publicly visible.
- Search providers register into SearchService.
- RBAC permissions for calculators already exist.
- Audit logging must record calculator changes.
- No fake data.
- No dead UI.

FILES TO INSPECT:
- app/Models/Category.php
- app/Models/Tag.php
- app/Models/User.php
- app/Services/SearchService.php
- app/Support/SearchResult.php
- app/Services/AuditLogService.php
- app/Enums/ directory
- app/Policies/ directory
- routes/admin.php
- routes/web.php
- resources/views/components/admin/
- resources/views/components/public/
- database/migrations/
- database/seeders/RolePermissionSeeder.php

FILES TO CREATE:
- app/Contracts/CalculatorInterface.php
- app/Enums/ResourceStatus.php if not already implemented
- app/Models/Traits/HasResourceLifecycle.php if not already implemented
- app/Models/Calculator.php
- app/Policies/CalculatorPolicy.php
- app/Rules/ValidResourceSchema.php
- app/Services/Calculators/CalculatorService.php
- app/Services/Calculators/Engines/AnionGapCalculator.php
- app/Support/NumberFormatter.php
- app/Http/Controllers/Admin/CalculatorController.php
- app/Http/Controllers/PublicSite/CalculatorController.php
- app/Http/Livewire/PublicSite/Calculators/CalculatorRunner.php
- app/Http/Requests/Admin/StoreCalculatorRequest.php
- app/Http/Requests/Admin/UpdateCalculatorRequest.php
- database/migrations/xxxx_create_calculators_table.php
- database/migrations/xxxx_create_calculator_category_table.php
- database/migrations/xxxx_create_calculator_tag_table.php
- database/factories/CalculatorFactory.php
- database/seeders/CalculatorSeeder.php
- resources/views/admin/calculators/index.blade.php
- resources/views/admin/calculators/create.blade.php
- resources/views/admin/calculators/edit.blade.php
- resources/views/admin/calculators/partials/form.blade.php
- resources/views/public/calculators/index.blade.php
- resources/views/public/calculators/show.blade.php
- resources/views/livewire/public/calculators/calculator-runner.blade.php
- tests/Unit/Services/Calculators/AnionGapCalculatorTest.php
- tests/Unit/Services/Calculators/CalculatorServiceTest.php
- tests/Feature/Admin/Calculators/CalculatorAdminCrudTest.php
- tests/Feature/Admin/Calculators/CalculatorAdminValidationTest.php
- tests/Feature/PublicSite/Calculators/CalculatorListingTest.php
- tests/Feature/PublicSite/Calculators/CalculatorDetailTest.php
- tests/Feature/PublicSite/Calculators/CalculatorExecutionTest.php

FILES TO MODIFY:
- routes/admin.php
- routes/web.php
- app/Providers/AppServiceProvider.php
- database/seeders/DatabaseSeeder.php if optional calculator seeding is needed

DATABASE REQUIREMENTS:

Create calculators table.

Required columns:

id
name
slug
short_description
formula_display
input_schema
output_schema
engine
engine_reference
interpretation_notes
references
status
published_at
created_by
updated_by
seo_title
seo_description
created_at
updated_at

Column rules:

- name: string, required
- slug: string, required, unique
- short_description: text, nullable
- formula_display: string, nullable
- input_schema: json, required
- output_schema: json, required
- engine: string, default php_class
- engine_reference: string, required
- interpretation_notes: text, nullable
- references: text, nullable
- status: string, default draft
- published_at: timestamp, nullable
- created_by: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- updated_by: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- seo_title: string, nullable
- seo_description: text, nullable

Indexes:

- unique index on slug
- index on status
- index on published_at
- fulltext index on name and short_description if supported by MySQL/MariaDB

Create calculator_category pivot table.

Required columns:

id
calculator_id
category_id
created_at
updated_at

Rules:

- calculator_id foreign key to calculators.id, cascade on delete
- category_id foreign key to categories.id, cascade on delete
- unique composite index on calculator_id and category_id

Create calculator_tag pivot table.

Required columns:

id
calculator_id
tag_id
created_at
updated_at

Rules:

- calculator_id foreign key to calculators.id, cascade on delete
- tag_id foreign key to tags.id, cascade on delete
- unique composite index on calculator_id and tag_id

MODEL REQUIREMENTS:

Create app/Models/Calculator.php.

Calculator model requirements:

- Use HasFactory.
- Use HasResourceLifecycle trait if implemented.
- Fillable fields must include all editable columns.
- Cast input_schema to array.
- Cast output_schema to array.
- Cast status to ResourceStatus enum.
- Cast published_at to datetime.
- Define categories() belongsToMany relationship.
- Define tags() belongsToMany relationship.
- Define createdBy() belongsTo User.
- Define updatedBy() belongsTo User.
- Define scopePublished.
- Define scopeSearch.

Do not put calculation logic inside the model.

Create app/Models/Traits/HasResourceLifecycle.php if not already implemented.

Trait requirements:

- scopePublished
- scopeDraft
- scopeArchived
- isPublished()
- isDraft()
- isArchived()
- publish()
- archive()
- markObsolete()

Create app/Enums/ResourceStatus.php if not already implemented.

Enum cases:

- Draft
- UnderReview
- Published
- Archived
- Obsolete

Enum methods:

- label()
- color()
- isPublic()
- canTransitionTo(ResourceStatus $target)

CALCULATOR ENGINE REQUIREMENTS:

Create app/Contracts/CalculatorInterface.php.

Interface method:

public function calculate(array $inputs): array;

Rules:

- Input array keys must match input schema keys.
- Returned array keys must match output schema keys.
- Engine classes must not access the database directly.
- Engine classes must not access request data directly.
- Engine classes must not execute arbitrary code.
- Engine classes must be pure calculation services.

Create app/Services/Calculators/Engines/AnionGapCalculator.php.

Anion Gap formula:

Anion Gap = Sodium - (Chloride + Bicarbonate)

Expected input keys:

- sodium
- chloride
- bicarbonate

Expected output key:

- anion_gap

Rules:

- Validate that inputs are numeric before calculation.
- Throw InvalidArgumentException or return safe failure if required inputs are missing.
- Do not apply final rounding inside the engine unless necessary. Rounding will be applied by CalculatorService using output_schema.

Create app/Services/Calculators/CalculatorService.php.

CalculatorService responsibilities:

1. Provide available calculator engines.
2. Resolve a calculator engine from Calculator model.
3. Validate inputs against calculator input_schema.
4. Execute engine calculate method.
5. Apply output_schema rounding rules.
6. Return formatted outputs.
7. Reject unknown engines.
8. Reject engines that do not implement CalculatorInterface.

Engine resolution rules:

- engine must be php_class for MVP.
- engine_reference must be one of the registered allowed engines.
- Do not instantiate arbitrary class names from user input.
- Do not use eval.
- Do not use dynamic code execution.

Available engines registry:

Create a protected method or config-driven registry that returns allowed engine classes.

For Build 12, the registry must include:

- App\Services\Calculators\Engines\AnionGapCalculator

Admin engine selection must use this registry.

INPUT VALIDATION REQUIREMENTS:

CalculatorService must validate inputs before calculation.

Validation rules must be derived from input_schema.

For each input field:

- If required is true, field is required.
- If type is number, field must be numeric.
- If min exists, field must be greater than or equal to min.
- If max exists, field must be less than or equal to max.
- If type is select, field must be in options.
- If step exists, use it for frontend hints but do not over-enforce unless required.

Validation errors must be returned in a safe format suitable for Livewire or API display.

OUTPUT FORMATTING REQUIREMENTS:

Create app/Support/NumberFormatter.php.

NumberFormatter must apply output schema rules.

Supported rounding values:

- none
- round_up
- round_down
- round_half_up

Supported precision:

- integer number of decimal places

Rules:

- If rounding is none, return value without rounding.
- If rounding is round_up, use ceil with precision.
- If rounding is round_down, use floor with precision.
- If rounding is round_half_up, use round with precision.
- Return numeric value or formatted numeric string.
- Do not append units. Units are rendered by the view.

ADMIN CRUD REQUIREMENTS:

Create Admin CalculatorController.

Controller methods:

- index
- create
- store
- edit
- update
- destroy

Admin routes:

GET /admin/calculators
- Name: admin.calculators.index
- Permission: calculators.view

GET /admin/calculators/create
- Name: admin.calculators.create
- Permission: calculators.create

POST /admin/calculators
- Name: admin.calculators.store
- Permission: calculators.create

GET /admin/calculators/{calculator}/edit
- Name: admin.calculators.edit
- Permission: calculators.edit

PUT /admin/calculators/{calculator}
- Name: admin.calculators.update
- Permission: calculators.edit

DELETE /admin/calculators/{calculator}
- Name: admin.calculators.destroy
- Permission: calculators.delete

Admin index requirements:

- Display calculator list.
- Show name, status, categories, created date, updated date, actions.
- Include search by name.
- Include status filter.
- Include category filter.
- Include pagination.
- Show empty state if no calculators exist.
- Show create button only if user has calculators.create.

Admin create and edit requirements:

- Use shared form partial.
- Include Basic Information section.
- Include Calculator Configuration section.
- Include Taxonomy section.
- Include SEO section.
- Display validation errors inline.
- Use accessible labels.

Form fields:

Basic Information:
- name
- slug
- short_description
- status
- formula_display

Calculator Configuration:
- engine
- engine_reference
- input_schema
- output_schema
- interpretation_notes
- references

Taxonomy:
- categories using category-selector component
- tags using tag-input component

SEO:
- seo_title
- seo_description

Engine field rules:

- engine should be hidden or read-only with value php_class for MVP.
- engine_reference should be a select dropdown generated from CalculatorService available engines.
- Do not allow free-text arbitrary class entry unless Super Administrator is explicitly approved for that behavior. For MVP, use dropdown only.

Schema fields:

- input_schema must be a JSON textarea.
- output_schema must be a JSON textarea.
- Validate JSON before saving.
- Provide helper text explaining schema format.
- Do not execute schema content.

SCHEMA VALIDATION REQUIREMENTS:

Create app/Rules/ValidResourceSchema.php.

This rule must validate resource JSON schemas.

Input schema requirements:

- Must be valid JSON.
- Must decode to an array.
- Must contain at least one field.
- Each field must have key, label, and type.
- key must be string, snake_case or lowercase alphanumeric with underscores.
- label must be string.
- type must be one of number, select, text.
- If type is number, min and max must be numeric if present.
- If type is select, options must be present and non-empty.

Output schema requirements:

- Must be valid JSON.
- Must decode to an array.
- Must contain at least one field.
- Each field must have key and label.
- key must be string.
- label must be string.
- rounding must be one of none, round_up, round_down, round_half_up if present.
- precision must be integer between 0 and 10 if present.

AUDIT LOGGING REQUIREMENTS:

Use AuditLogService for calculator actions:

- calculators.created
- calculators.updated
- calculators.deleted
- calculators.published
- calculators.archived

Capture:

- Acting user ID.
- Calculator resource type and ID.
- Sanitized changes.
- IP address.
- User agent.

Do not store secrets.

POLICY REQUIREMENTS:

Create app/Policies/CalculatorPolicy.php.

Policy methods:

- viewAny
- view
- create
- update
- publish
- delete

Rules:

- viewAny requires calculators.view.
- view requires calculators.view.
- create requires calculators.create.
- update requires calculators.edit.
- publish requires calculators.publish.
- delete requires calculators.delete.

Super Administrator bypass may already be handled by Gate::before.

PUBLIC ROUTE REQUIREMENTS:

Modify routes/web.php.

Create public calculator routes:

GET /calculators
- Name: public.calculators.index
- Controller: PublicSite\CalculatorController@index

GET /calculators/{slug}
- Name: public.calculators.show
- Controller: PublicSite\CalculatorController@show

Public controller rules:

- Index displays only published calculators.
- Show resolves by slug.
- Show returns 404 if calculator is not published.
- Show returns 404 if calculator does not exist.
- Do not expose draft, archived, or obsolete calculators.

PUBLIC LISTING PAGE REQUIREMENTS:

Create resources/views/public/calculators/index.blade.php.

Listing page requirements:

- Use public layout.
- Display page title: Calculators.
- Display short module description.
- Display search/filter controls.
- Display calculator cards using resource card pattern.
- Display pagination.
- Display empty state if no published calculators exist.
- Include meta tags.
- Use canonical URL pointing to /calculators when filters are present, or mark filtered pages noindex.
- Do not display fake calculators.

Filters:

- Category filter using active categories.
- Tag filter using existing tags.
- Optional search query.

Query parameter examples:

- /calculators?category=clinical-chemistry
- /calculators?tag=anion-gap
- /calculators?q=anion

PUBLIC DETAIL PAGE REQUIREMENTS:

Create resources/views/public/calculators/show.blade.php.

Detail page requirements:

- Use public layout.
- Display breadcrumbs: Home > Calculators > Calculator Name.
- Display calculator name in h1.
- Display calculator type badge.
- Display short description.
- Display formula display if available.
- Display interactive calculator component.
- Display interpretation notes if available.
- Display references if available.
- Display categories and tags.
- Display related calculators if available.
- Include meta tags and JSON-LD structured data.
- Do not expose admin links.
- Do not expose unpublished calculators.

LIVEWIRE CALCULATOR RUNNER REQUIREMENTS:

Create app/Http/Livewire/PublicSite/Calculators/CalculatorRunner.php.

Component responsibilities:

- Receive Calculator model.
- Build dynamic input fields from input_schema.
- Accept user input.
- Validate input using CalculatorService.
- Execute calculation using CalculatorService.
- Store result outputs.
- Display validation errors.
- Display calculation errors safely.
- Display loading state.
- Display result with units and labels.

Component view:

resources/views/livewire/public/calculators/calculator-runner.blade.php

View requirements:

- Render inputs dynamically.
- Use labels associated with inputs.
- Use unit labels.
- Use wire:loading and wire:submit for loading states.
- Use aria-live or role=status for result updates.
- Display validation errors near fields.
- Disable submit button during calculation.
- Do not expose stack traces.
- Do not expose engine class name publicly.

SEARCH INTEGRATION REQUIREMENTS:

Register calculator search provider with SearchService.

Registration location:

AppServiceProvider boot method or dedicated search registration file.

Provider requirements:

- Search only published calculators.
- Search name and short_description.
- Support category filter by category slug.
- Support tag filter by tag slug.
- Return SearchResult objects.
- Map result type as calculator.
- Map URL to public.calculators.show using slug.
- Include short description.
- Include category name if available.
- Include tags if practical.
- Do not expose draft, archived, or obsolete calculators.

SEEDER AND FACTORY REQUIREMENTS:

Create database/factories/CalculatorFactory.php.

Factory requirements:

- Generate valid name.
- Generate unique slug.
- Generate short_description.
- Generate valid input_schema array.
- Generate valid output_schema array.
- Set engine to php_class.
- Set engine_reference to AnionGapCalculator class.
- Set status to published by default or allow state overrides.
- Set published_at when published.

Create database/seeders/CalculatorSeeder.php.

Seeder requirements:

- Optional seeder for development.
- Must not run in production unless explicitly enabled.
- Create an Anion Gap calculator if it does not exist.
- Use firstOrCreate by slug.
- Assign Clinical Chemistry category if category exists.
- Use valid input and output schemas.

Example Anion Gap input schema:

[
  {
    "key": "sodium",
    "label": "Serum Sodium",
    "type": "number",
    "unit": "mEq/L",
    "required": true,
    "min": 100,
    "max": 200,
    "step": 0.1
  },
  {
    "key": "chloride",
    "label": "Serum Chloride",
    "type": "number",
    "unit": "mEq/L",
    "required": true,
    "min": 80,
    "max": 150,
    "step": 0.1
  },
  {
    "key": "bicarbonate",
    "label": "Serum Bicarbonate",
    "type": "number",
    "unit": "mEq/L",
    "required": true,
    "min": 5,
    "max": 50,
    "step": 0.1
  }
]

Example Anion Gap output schema:

[
  {
    "key": "anion_gap",
    "label": "Anion Gap",
    "unit": "mEq/L",
    "rounding": "round_half_up",
    "precision": 1
  }
]

TESTING REQUIREMENTS:

Create tests/Unit/Services/Calculators/AnionGapCalculatorTest.php.

Required tests:

1. test_anion_gap_calculates_correctly_with_normal_values
   - Sodium 140, Chloride 100, Bicarbonate 24.
   - Expected result 16.

2. test_anion_gap_calculates_correctly_with_decimal_values
   - Use decimal inputs.
   - Verify raw result.

3. test_anion_gap_handles_low_values
   - Use low but valid values.
   - Verify calculation.

4. test_anion_gap_requires_all_inputs
   - Missing bicarbonate.
   - Expect exception or safe failure.

5. test_anion_gap_rejects_non_numeric_inputs
   - Input string.
   - Expect exception or safe failure.

Create tests/Unit/Services/Calculators/CalculatorServiceTest.php.

Required tests:

1. test_service_resolves_registered_engine
   - Create calculator with AnionGapCalculator engine reference.
   - Assert service resolves engine.

2. test_service_rejects_unregistered_engine
   - Create calculator with unknown engine reference.
   - Assert service rejects execution.

3. test_service_validates_required_inputs
   - Execute with missing required field.
   - Assert validation errors.

4. test_service_validates_numeric_inputs
   - Execute with non-numeric input.
   - Assert validation errors.

5. test_service_applies_rounding_rules
   - Use output schema with round_half_up and precision 1.
   - Assert formatted result.

6. test_service_does_not_execute_arbitrary_class
   - Set engine_reference to a non-approved class.
   - Assert execution is rejected.

Create tests/Feature/Admin/Calculators/CalculatorAdminCrudTest.php.

Required tests:

1. test_guest_cannot_access_calculator_admin
2. test_user_without_calculator_permission_cannot_access_calculator_admin
3. test_user_with_calculator_view_can_view_index
4. test_user_with_calculator_create_can_create_calculator
5. test_user_with_calculator_edit_can_update_calculator
6. test_user_with_calculator_delete_can_delete_calculator
7. test_calculator_creation_is_audited
8. test_calculator_update_is_audited

Create tests/Feature/Admin/Calculators/CalculatorAdminValidationTest.php.

Required tests:

1. test_name_is_required
2. test_slug_must_be_unique
3. test_input_schema_must_be_valid_json
4. test_output_schema_must_be_valid_json
5. test_input_schema_requires_fields
6. test_output_schema_requires_fields
7. test_engine_reference_must_be_registered
8. test_status_must_be_valid

Create tests/Feature/PublicSite/Calculators/CalculatorListingTest.php.

Required tests:

1. test_calculator_listing_renders
2. test_calculator_listing_displays_only_published_calculators
3. test_calculator_listing_displays_empty_state_when_no_published_calculators_exist
4. test_calculator_listing_can_filter_by_category
5. test_calculator_listing_can_filter_by_tag

Create tests/Feature/PublicSite/Calculators/CalculatorDetailTest.php.

Required tests:

1. test_published_calculator_detail_renders
2. test_draft_calculator_returns_404
3. test_archived_calculator_returns_404
4. test_nonexistent_calculator_returns_404
5. test_calculator_detail_displays_breadcrumbs
6. test_calculator_detail_includes_meta_tags

Create tests/Feature/PublicSite/Calculators/CalculatorExecutionTest.php.

Required tests:

1. test_calculator_runner_renders_inputs_from_schema
2. test_valid_inputs_return_calculation_result
3. test_invalid_inputs_return_validation_errors
4. test_missing_required_inputs_return_validation_errors
5. test_calculation_result_displays_units
6. test_calculation_failure_displays_safe_error_message

ACCESSIBILITY REQUIREMENTS:

Calculator form must:
- Use visible labels.
- Associate labels with inputs.
- Support keyboard navigation.
- Display validation errors near fields.
- Use role=status or aria-live for result updates.
- Disable submit button during calculation.
- Maintain visible focus states.

Admin calculator forms must:
- Use accessible labels.
- Display validation errors clearly.
- Support keyboard navigation.

SEO REQUIREMENTS:

Calculator listing page must:
- Have title: Calculators | LabGato
- Have meta description.
- Use canonical URL.
- Use noindex for filtered pages or canonical to base listing.

Calculator detail page must:
- Have unique title.
- Have meta description.
- Have canonical URL.
- Include Open Graph tags.
- Include JSON-LD structured data using SoftwareApplication or WebApplication.
- Be indexable only when published.

SECURITY REQUIREMENTS:

- Do not execute arbitrary code.
- Do not instantiate unapproved classes.
- Do not expose engine class names publicly.
- Do not expose draft, archived, or obsolete calculators.
- Validate all user inputs.
- Enforce RBAC server-side.
- Log calculator changes.
- Do not store secrets in schemas.
- Do not expose stack traces.

DEFINITION OF DONE:

- Calculators table exists with correct schema.
- Calculator pivot tables exist.
- Calculator model uses lifecycle trait and relationships.
- CalculatorPolicy enforces permissions.
- Admin calculator CRUD works.
- Admin schema validation works.
- Engine selection is restricted to registered engines.
- Anion Gap calculator engine works correctly.
- CalculatorService validates inputs and applies rounding.
- Public calculator listing displays published calculators only.
- Public calculator detail displays published calculator only.
- CalculatorRunner executes valid calculations safely.
- CalculatorRunner displays validation errors safely.
- Calculator search provider is registered.
- Calculator factory exists.
- Optional calculator seeder exists and is safe.
- Unit tests pass.
- Feature tests pass.
- SEO requirements are satisfied.
- Accessibility requirements are satisfied.
- No fake calculators are displayed.
- No dead UI exists.
- No secrets are committed.