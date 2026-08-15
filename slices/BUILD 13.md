LABGOTO/
├── app/
│   ├── Contracts/
│   │   └── ToolInterface.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── ToolController.php
│   │   │   └── PublicSite/
│   │   │       └── ToolController.php
│   │   ├── Livewire/
│   │   │   └── PublicSite/
│   │   │       └── Tools/
│   │   │           └── ToolRunner.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreToolRequest.php
│   │           └── UpdateToolRequest.php
│   ├── Models/
│   │   └── LaboratoryTool.php
│   ├── Policies/
│   │   └── ToolPolicy.php
│   ├── Services/
│   │   └── Tools/
│   │       ├── ToolService.php
│   │       └── Engines/
│   │           └── DilutionCalculatorTool.php
│   └── Support/
│       └── NumberFormatter.php
├── database/
│   ├── factories/
│   │   └── LaboratoryToolFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_laboratory_tools_table.php
│   │   ├── xxxx_create_laboratory_tool_category_table.php
│   │   └── xxxx_create_laboratory_tool_tag_table.php
│   └── seeders/
│       └── LaboratoryToolSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── tools/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── partials/
│       │           └── form.blade.php
│       ├── livewire/
│       │   └── public/
│       │       └── tools/
│       │           └── tool-runner.blade.php
│       └── public/
│           └── tools/
│               ├── index.blade.php
│               └── show.blade.php
├── routes/
│   ├── admin.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── Admin/
    │   │   └── Tools/
    │   │       ├── ToolAdminCrudTest.php
    │   │       └── ToolAdminValidationTest.php
    │   └── PublicSite/
    │       └── Tools/
    │           ├── ToolListingTest.php
│           ├── ToolDetailTest.php
│           └── ToolExecutionTest.php
    └── Unit/
        └── Services/
            └── Tools/
                ├── DilutionCalculatorToolTest.php
                └── ToolServiceTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-PHASE03-BUILD13

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1 Application Foundation is complete. Build 10 Taxonomy, Build 11 Search, and Build 12 Calculators are complete. The theme has been applied. Phase 03 Resource Architecture is approved.

We are now implementing Build 13: Laboratory Tools Vertical Slice.

This is the second complete resource vertical slice. It must follow the same patterns as Build 12 Calculators, adapted for the Laboratory Tools Architecture document.

OBJECTIVE:
Implement the complete Laboratory Tools resource module.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Resources use common lifecycle states.
- Laboratory Tools use a Code-Based Tool Engine.
- Arbitrary code execution is prohibited.
- Tool logic lives in dedicated PHP classes.
- Input and output schemas are stored as JSON.
- Tools use explicit pivot tables for categories and tags.
- Public URLs use slugs.
- Admin URLs use IDs.
- Published tools are publicly visible.
- Draft, archived, and obsolete tools are not publicly visible.
- Search providers register into SearchService.
- RBAC permissions for tools already exist.
- Audit logging must record tool changes.
- Output schema supports a format field for standard or scientific notation.
- No fake data.
- No dead UI.

FILES TO INSPECT:
- app/Contracts/CalculatorInterface.php
- app/Models/Calculator.php
- app/Models/Traits/HasResourceLifecycle.php
- app/Enums/ResourceStatus.php
- app/Services/Calculators/CalculatorService.php
- app/Services/Calculators/Engines/AnionGapCalculator.php
- app/Support/NumberFormatter.php
- app/Rules/ValidResourceSchema.php
- app/Policies/CalculatorPolicy.php
- app/Http/Controllers/Admin/CalculatorController.php
- app/Http/Controllers/PublicSite/CalculatorController.php
- app/Http/Livewire/PublicSite/Calculators/CalculatorRunner.php
- app/Http/Requests/Admin/StoreCalculatorRequest.php
- app/Http/Requests/Admin/UpdateCalculatorRequest.php
- database/migrations/ (calculators migrations)
- database/factories/CalculatorFactory.php
- resources/views/admin/calculators/
- resources/views/public/calculators/
- resources/views/livewire/public/calculators/
- routes/admin.php
- routes/web.php

FILES TO CREATE:
- app/Contracts/ToolInterface.php
- app/Models/LaboratoryTool.php
- app/Policies/ToolPolicy.php
- app/Services/Tools/ToolService.php
- app/Services/Tools/Engines/DilutionCalculatorTool.php
- app/Http/Controllers/Admin/ToolController.php
- app/Http/Controllers/PublicSite/ToolController.php
- app/Http/Livewire/PublicSite/Tools/ToolRunner.php
- app/Http/Requests/Admin/StoreToolRequest.php
- app/Http/Requests/Admin/UpdateToolRequest.php
- database/migrations/xxxx_create_laboratory_tools_table.php
- database/migrations/xxxx_create_laboratory_tool_category_table.php
- database/migrations/xxxx_create_laboratory_tool_tag_table.php
- database/factories/LaboratoryToolFactory.php
- database/seeders/LaboratoryToolSeeder.php
- resources/views/admin/tools/index.blade.php
- resources/views/admin/tools/create.blade.php
- resources/views/admin/tools/edit.blade.php
- resources/views/admin/tools/partials/form.blade.php
- resources/views/public/tools/index.blade.php
- resources/views/public/tools/show.blade.php
- resources/views/livewire/public/tools/tool-runner.blade.php
- tests/Unit/Services/Tools/DilutionCalculatorToolTest.php
- tests/Unit/Services/Tools/ToolServiceTest.php
- tests/Feature/Admin/Tools/ToolAdminCrudTest.php
- tests/Feature/Admin/Tools/ToolAdminValidationTest.php
- tests/Feature/PublicSite/Tools/ToolListingTest.php
- tests/Feature/PublicSite/Tools/ToolDetailTest.php
- tests/Feature/PublicSite/Tools/ToolExecutionTest.php

FILES TO MODIFY:
- routes/admin.php
- routes/web.php
- app/Providers/AppServiceProvider.php
- database/seeders/DatabaseSeeder.php if optional tool seeding is needed
- app/Support/NumberFormatter.php if scientific notation formatting is not yet supported

DATABASE REQUIREMENTS:

Create laboratory_tools table.

Required columns:

id
name
slug
short_description
tool_type
input_schema
output_schema
engine
engine_reference
instructions
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
- tool_type: string, nullable, example values converter, preparation, general
- input_schema: json, required
- output_schema: json, required
- engine: string, default php_class
- engine_reference: string, required
- instructions: text, nullable
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

Create laboratory_tool_category pivot table.

Required columns:

id
laboratory_tool_id
category_id
created_at
updated_at

Rules:

- laboratory_tool_id foreign key to laboratory_tools.id, cascade on delete
- category_id foreign key to categories.id, cascade on delete
- unique composite index on laboratory_tool_id and category_id

Create laboratory_tool_tag pivot table.

Required columns:

id
laboratory_tool_id
tag_id
created_at
updated_at

Rules:

- laboratory_tool_id foreign key to laboratory_tools.id, cascade on delete
- tag_id foreign key to tags.id, cascade on delete
- unique composite index on laboratory_tool_id and tag_id

MODEL REQUIREMENTS:

Create app/Models/LaboratoryTool.php.

LaboratoryTool model requirements:

- Use HasFactory.
- Use HasResourceLifecycle trait.
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

Do not put tool logic inside the model.

TOOL ENGINE REQUIREMENTS:

Create app/Contracts/ToolInterface.php.

Interface method:

public function execute(array $inputs): array;

Rules:

- Input array keys must match input schema keys.
- Returned array keys must match output schema keys.
- Engine classes must not access the database directly.
- Engine classes must not access request data directly.
- Engine classes must not execute arbitrary code.
- Engine classes must be pure calculation services.

Create app/Services/Tools/Engines/DilutionCalculatorTool.php.

Dilution formula:

C1 * V1 = C2 * V2

Where:
- C1 = stock concentration
- V1 = volume of stock needed (the result)
- C2 = desired final concentration
- V2 = desired final volume

Solving for V1:

V1 = (C2 * V2) / C1

Expected input keys:

- stock_concentration
- final_concentration
- final_volume

Expected output key:

- stock_volume

Rules:

- Validate that inputs are numeric before calculation.
- Validate that stock_concentration is greater than zero to prevent division by zero.
- Validate that final_concentration is less than or equal to stock_concentration.
- Throw InvalidArgumentException or return safe failure for invalid inputs.
- Do not apply final rounding inside the engine unless necessary. Rounding will be applied by ToolService using output_schema.

Create app/Services/Tools/ToolService.php.

ToolService responsibilities:

1. Provide available tool engines.
2. Resolve a tool engine from LaboratoryTool model.
3. Validate inputs against tool input_schema.
4. Execute engine execute method.
5. Apply output_schema rounding and formatting rules.
6. Return formatted outputs.
7. Reject unknown engines.
8. Reject engines that do not implement ToolInterface.

Engine resolution rules:

- engine must be php_class for MVP.
- engine_reference must be one of the registered allowed engines.
- Do not instantiate arbitrary class names from user input.
- Do not use eval.
- Do not use dynamic code execution.

Available engines registry:

Create a protected method or config-driven registry that returns allowed engine classes.

For Build 13, the registry must include:

- App\Services\Tools\Engines\DilutionCalculatorTool

Admin engine selection must use this registry.

OUTPUT FORMATTING REQUIREMENTS:

Modify app/Support/NumberFormatter.php if needed.

Add support for the format field in output_schema.

Supported format values:

- standard: Display as a regular number.
- scientific: Display in scientific notation if the value is very large or very small.

Rules:

- If format is standard, display the rounded number normally.
- If format is scientific, use scientific notation for values outside a reasonable range, for example less than 0.001 or greater than 10000.
- If format is not specified, default to standard.

INPUT VALIDATION REQUIREMENTS:

ToolService must validate inputs before execution.

Validation rules must be derived from input_schema.

For each input field:

- If required is true, field is required.
- If type is number, field must be numeric.
- If min exists, field must be greater than or equal to min.
- If max exists, field must be less than or equal to max.
- If type is select, field must be in options.
- If type is text, field must be a string.

Validation errors must be returned in a safe format suitable for Livewire or API display.

ADMIN CRUD REQUIREMENTS:

Create Admin ToolController.

Controller methods:

- index
- create
- store
- edit
- update
- destroy

Admin routes:

GET /admin/tools
- Name: admin.tools.index
- Permission: tools.view

GET /admin/tools/create
- Name: admin.tools.create
- Permission: tools.create

POST /admin/tools
- Name: admin.tools.store
- Permission: tools.create

GET /admin/tools/{tool}/edit
- Name: admin.tools.edit
- Permission: tools.edit

PUT /admin/tools/{tool}
- Name: admin.tools.update
- Permission: tools.edit

DELETE /admin/tools/{tool}
- Name: admin.tools.destroy
- Permission: tools.delete

Admin index requirements:

- Display tool list.
- Show name, tool type, status, categories, created date, updated date, actions.
- Include search by name.
- Include status filter.
- Include tool type filter.
- Include category filter.
- Include pagination.
- Show empty state if no tools exist.
- Show create button only if user has tools.create.

Admin create and edit requirements:

- Use shared form partial.
- Include Basic Information section.
- Include Tool Configuration section.
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
- tool_type

Tool Configuration:
- engine
- engine_reference
- input_schema
- output_schema
- instructions
- references

Taxonomy:
- categories using category-selector component
- tags using tag-input component

SEO:
- seo_title
- seo_description

Engine field rules:

- engine should be hidden or read-only with value php_class for MVP.
- engine_reference should be a select dropdown generated from ToolService available engines.
- Do not allow free-text arbitrary class entry. Use dropdown only.

Schema fields:

- input_schema must be a JSON textarea.
- output_schema must be a JSON textarea.
- Validate JSON before saving.
- Provide helper text explaining schema format.
- Do not execute schema content.

SCHEMA VALIDATION REQUIREMENTS:

Reuse app/Rules/ValidResourceSchema.php.

The rule must validate tool input and output schemas using the same rules as calculators, plus support for the format field in output schema.

Output schema format field:

- format must be one of standard, scientific if present.

POLICY REQUIREMENTS:

Create app/Policies/ToolPolicy.php.

Policy methods:

- viewAny
- view
- create
- update
- publish
- delete

Rules:

- viewAny requires tools.view.
- view requires tools.view.
- create requires tools.create.
- update requires tools.edit.
- publish requires tools.publish.
- delete requires tools.delete.

Super Administrator bypass may already be handled by Gate::before.

PUBLIC ROUTE REQUIREMENTS:

Modify routes/web.php.

Create public tool routes:

GET /tools
- Name: public.tools.index
- Controller: PublicSite\ToolController@index

GET /tools/{slug}
- Name: public.tools.show
- Controller: PublicSite\ToolController@show

Public controller rules:

- Index displays only published tools.
- Show resolves by slug.
- Show returns 404 if tool is not published.
- Show returns 404 if tool does not exist.
- Do not expose draft, archived, or obsolete tools.

PUBLIC LISTING PAGE REQUIREMENTS:

Create resources/views/public/tools/index.blade.php.

Listing page requirements:

- Use public layout.
- Display page title: Laboratory Tools.
- Display short module description.
- Display search/filter controls.
- Display tool cards using resource card pattern.
- Display pagination.
- Display empty state if no published tools exist.
- Include meta tags.
- Use canonical URL pointing to /tools when filters are present, or mark filtered pages noindex.
- Do not display fake tools.

Filters:

- Category filter using active categories.
- Tag filter using existing tags.
- Tool type filter.
- Optional search query.

PUBLIC DETAIL PAGE REQUIREMENTS:

Create resources/views/public/tools/show.blade.php.

Detail page requirements:

- Use public layout.
- Display breadcrumbs: Home > Tools > Tool Name.
- Display tool name in h1.
- Display tool type badge.
- Display short description.
- Display interactive tool component.
- Display instructions if available.
- Display references if available.
- Display categories and tags.
- Display related tools if available.
- Include meta tags and JSON-LD structured data.
- Do not expose admin links.
- Do not expose unpublished tools.

LIVEWIRE TOOL RUNNER REQUIREMENTS:

Create app/Http/Livewire/PublicSite/Tools/ToolRunner.php.

Component responsibilities:

- Receive LaboratoryTool model.
- Build dynamic input fields from input_schema.
- Accept user input.
- Validate input using ToolService.
- Execute tool using ToolService.
- Store result outputs.
- Display validation errors.
- Display execution errors safely.
- Display loading state.
- Display result with units and labels.

Component view:

resources/views/livewire/public/tools/tool-runner.blade.php

View requirements:

- Render inputs dynamically.
- Use labels associated with inputs.
- Use unit labels.
- Use wire:loading and wire:submit for loading states.
- Use aria-live or role=status for result updates.
- Display validation errors near fields.
- Disable submit button during execution.
- Do not expose stack traces.
- Do not expose engine class name publicly.

SEARCH INTEGRATION REQUIREMENTS:

Register tool search provider with SearchService.

Registration location:

AppServiceProvider boot method or dedicated search registration file.

Provider requirements:

- Search only published tools.
- Search name and short_description.
- Support category filter by category slug.
- Support tag filter by tag slug.
- Support tool type filter if practical.
- Return SearchResult objects.
- Map result type as tool.
- Map URL to public.tools.show using slug.
- Include short description.
- Include category name if available.
- Include tags if practical.
- Do not expose draft, archived, or obsolete tools.

SEEDER AND FACTORY REQUIREMENTS:

Create database/factories/LaboratoryToolFactory.php.

Factory requirements:

- Generate valid name.
- Generate unique slug.
- Generate short_description.
- Generate valid input_schema array.
- Generate valid output_schema array.
- Set engine to php_class.
- Set engine_reference to DilutionCalculatorTool class.
- Set status to published by default or allow state overrides.
- Set published_at when published.

Create database/seeders/LaboratoryToolSeeder.php.

Seeder requirements:

- Optional seeder for development.
- Must not run in production unless explicitly enabled.
- Create a Dilution Calculator tool if it does not exist.
- Use firstOrCreate by slug.
- Assign an appropriate category if category exists.
- Use valid input and output schemas.

Example Dilution Calculator input schema:

[
  {
    "key": "stock_concentration",
    "label": "Stock Concentration",
    "type": "number",
    "unit": "mg/mL",
    "required": true,
    "min": 0.001,
    "step": 0.01
  },
  {
    "key": "final_concentration",
    "label": "Desired Final Concentration",
    "type": "number",
    "unit": "mg/mL",
    "required": true,
    "min": 0,
    "step": 0.01
  },
  {
    "key": "final_volume",
    "label": "Desired Final Volume",
    "type": "number",
    "unit": "mL",
    "required": true,
    "min": 0,
    "step": 0.1
  }
]

Example Dilution Calculator output schema:

[
  {
    "key": "stock_volume",
    "label": "Volume of Stock Needed",
    "unit": "mL",
    "rounding": "round_half_up",
    "precision": 3,
    "format": "standard"
  }
]

TESTING REQUIREMENTS:

Create tests/Unit/Services/Tools/DilutionCalculatorToolTest.php.

Required tests:

1. test_dilution_calculates_correctly_with_normal_values
   - Stock concentration 10, final concentration 1, final volume 100.
   - Expected stock volume: 10.

2. test_dilution_calculates_correctly_with_decimal_values
   - Use decimal inputs.
   - Verify raw result.

3. test_dilution_requires_stock_concentration_greater_than_zero
   - Stock concentration 0.
   - Expect exception or safe failure.

4. test_dilution_requires_final_concentration_not_greater_than_stock
   - Final concentration greater than stock concentration.
   - Expect exception or safe failure.

5. test_dilution_requires_all_inputs
   - Missing final volume.
   - Expect exception or safe failure.

6. test_dilution_rejects_non_numeric_inputs
   - Input string.
   - Expect exception or safe failure.

Create tests/Unit/Services/Tools/ToolServiceTest.php.

Required tests:

1. test_service_resolves_registered_engine
   - Create tool with DilutionCalculatorTool engine reference.
   - Assert service resolves engine.

2. test_service_rejects_unregistered_engine
   - Create tool with unknown engine reference.
   - Assert service rejects execution.

3. test_service_validates_required_inputs
   - Execute with missing required field.
   - Assert validation errors.

4. test_service_validates_numeric_inputs
   - Execute with non-numeric input.
   - Assert validation errors.

5. test_service_applies_rounding_rules
   - Use output schema with round_half_up and precision 3.
   - Assert formatted result.

6. test_service_applies_scientific_format_when_specified
   - Use output schema with format scientific and a very small value.
   - Assert result is formatted in scientific notation.

7. test_service_does_not_execute_arbitrary_class
   - Set engine_reference to a non-approved class.
   - Assert execution is rejected.

Create tests/Feature/Admin/Tools/ToolAdminCrudTest.php.

Required tests:

1. test_guest_cannot_access_tool_admin
2. test_user_without_tool_permission_cannot_access_tool_admin
3. test_user_with_tool_view_can_view_index
4. test_user_with_tool_create_can_create_tool
5. test_user_with_tool_edit_can_update_tool
6. test_user_with_tool_delete_can_delete_tool
7. test_tool_creation_is_audited
8. test_tool_update_is_audited

Create tests/Feature/Admin/Tools/ToolAdminValidationTest.php.

Required tests:

1. test_name_is_required
2. test_slug_must_be_unique
3. test_input_schema_must_be_valid_json
4. test_output_schema_must_be_valid_json
5. test_input_schema_requires_fields
6. test_output_schema_requires_fields
7. test_engine_reference_must_be_registered
8. test_status_must_be_valid

Create tests/Feature/PublicSite/Tools/ToolListingTest.php.

Required tests:

1. test_tool_listing_renders
2. test_tool_listing_displays_only_published_tools
3. test_tool_listing_displays_empty_state_when_no_published_tools_exist
4. test_tool_listing_can_filter_by_category
5. test_tool_listing_can_filter_by_tag
6. test_tool_listing_can_filter_by_tool_type

Create tests/Feature/PublicSite/Tools/ToolDetailTest.php.

Required tests:

1. test_published_tool_detail_renders
2. test_draft_tool_returns_404
3. test_archived_tool_returns_404
4. test_nonexistent_tool_returns_404
5. test_tool_detail_displays_breadcrumbs
6. test_tool_detail_includes_meta_tags

Create tests/Feature/PublicSite/Tools/ToolExecutionTest.php.

Required tests:

1. test_tool_runner_renders_inputs_from_schema
2. test_valid_inputs_return_execution_result
3. test_invalid_inputs_return_validation_errors
4. test_missing_required_inputs_return_validation_errors
5. test_execution_result_displays_units
6. test_execution_failure_displays_safe_error_message

ACCESSIBILITY REQUIREMENTS:

Tool form must:
- Use visible labels.
- Associate labels with inputs.
- Support keyboard navigation.
- Display validation errors near fields.
- Use role=status or aria-live for result updates.
- Disable submit button during execution.
- Maintain visible focus states.

Admin tool forms must:
- Use accessible labels.
- Display validation errors clearly.
- Support keyboard navigation.

SEO REQUIREMENTS:

Tool listing page must:
- Have title: Laboratory Tools | LABGOTO
- Have meta description.
- Use canonical URL.
- Use noindex for filtered pages or canonical to base listing.

Tool detail page must:
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
- Do not expose draft, archived, or obsolete tools.
- Validate all user inputs.
- Enforce RBAC server-side.
- Log tool changes.
- Do not store secrets in schemas.
- Do not expose stack traces.

DEFINITION OF DONE:

- Laboratory tools table exists with correct schema.
- Tool pivot tables exist.
- LaboratoryTool model uses lifecycle trait and relationships.
- ToolPolicy enforces permissions.
- Admin tool CRUD works.
- Admin schema validation works.
- Engine selection is restricted to registered engines.
- Dilution Calculator tool engine works correctly.
- ToolService validates inputs and applies rounding and formatting.
- Public tool listing displays published tools only.
- Public tool detail displays published tool only.
- ToolRunner executes valid tools safely.
- ToolRunner displays validation errors safely.
- Tool search provider is registered.
- Tool factory exists.
- Optional tool seeder exists and is safe.
- Unit tests pass.
- Feature tests pass.
- SEO requirements are satisfied.
- Accessibility requirements are satisfied.
- No fake tools are displayed.
- No dead UI exists.
- No secrets are committed.