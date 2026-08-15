labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       └── ReferenceRangeImportController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           └── ImportReferenceRangesRequest.php
│   └── Services/
│       └── ReferenceRangeImportService.php
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── reference-ranges/
│       │       ├── import.blade.php
│       │       └── import-preview.blade.php
│       └── components/
│           └── admin/
│               └── sidebar.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        └── Admin/
            └── ReferenceRangeImportTest.php
IMPLEMENTATION PROMPT ID: LABGATO-BUILD39-CSV-IMPORT

CONTEXT:
LabGato admins need to import many reference ranges at once via CSV instead of entering them individually. This build adds a bulk CSV import feature with validation, preview, and safe import-as-draft behavior.

OBJECTIVE:
Implement a CSV import system for reference ranges: a downloadable template, upload + parsing, row-by-row validation with a preview step, and a confirmed import that creates the records (defaulting to draft status).

EXISTING ARCHITECTURE:
- reference_ranges table with analyte, demographic, limit, unit, and conversion fields.
- Bulk actions (Build 33) for publishing imported drafts.
- Admin RBAC with per-resource permissions.
- Hostinger shared hosting (use native PHP CSV parsing, no heavy packages).

FILES TO INSPECT:
- app/Models/ReferenceRange.php
- database/migrations/ (reference_ranges table structure)
- database/seeders/RolePermissionSeeder.php
- resources/views/components/admin/sidebar.blade.php
- routes/admin.php

FILES TO CREATE:
- app/Http/Controllers/Admin/ReferenceRangeImportController.php
- app/Http/Requests/Admin/ImportReferenceRangesRequest.php
- app/Services/ReferenceRangeImportService.php
- resources/views/admin/reference-ranges/import.blade.php
- resources/views/admin/reference-ranges/import-preview.blade.php
- tests/Feature/Admin/ReferenceRangeImportTest.php

FILES TO MODIFY:
- database/seeders/RolePermissionSeeder.php
- resources/views/components/admin/sidebar.blade.php
- routes/admin.php

TASK 1: PERMISSION

Modify RolePermissionSeeder.php.

Add permission: reference-ranges.import

Assign to:
- Super Administrator
- Content Manager

TASK 2: CSV TEMPLATE DOWNLOAD

Add a route and method to generate a CSV template dynamically (so it always matches the schema).

Route: GET /admin/reference-ranges/import/template → ReferenceRangeImportController@template

The template CSV should have these header columns:
name, analyte_name, specimen_type, methodology, population_group, age_min, age_max, sex, unit, si_unit, lower_limit, upper_limit, critical_low, critical_high, conversion_factor, source_citation

Include one example row:
"Hemoglobin - Adult Male", "Hemoglobin", "Whole Blood", "", "Adult", "", "", "male", "g/dL", "g/L", "13.8", "17.2", "7.0", "20.0", "10", "Tietz Textbook of Clinical Chemistry"

Return as a downloadable .csv file.

TASK 3: IMPORT FORM

Create resources/views/admin/reference-ranges/import.blade.php.

Layout:
- Title: "Import Reference Ranges".
- Description explaining the process.
- "Download CSV Template" button (links to template route).
- File upload input (accept=".csv").
- Options:
  - Import as: [Draft (default) | Published] radio buttons.
  - Duplicate handling: [Skip duplicates (default) | Update existing] radio buttons.
- "Upload & Preview" submit button.

Validation:
- File must be .csv.
- File size max 2MB.

TASK 4: IMPORT SERVICE

Create app/Services/ReferenceRangeImportService.php.

Methods:
- parseAndValidate(UploadedFile $file): array
  - Read the CSV using native PHP (fgetcsv).
  - Parse the header row to map columns.
  - For each data row, validate and build a candidate record.
  - Return: ['valid' => [...], 'errors' => [...], 'total' => N].

- import(array $validRows, string $status, string $duplicateHandling): array
  - For each valid row, create or update a ReferenceRange.
  - Generate a unique slug from the name.
  - Handle duplicates based on the duplicateHandling option.
  - Return: ['imported' => N, 'skipped' => N, 'updated' => N].

Validation rules per row:
- name: required.
- analyte_name: required.
- unit: required.
- lower_limit OR upper_limit: at least one must be present.
- sex: must be empty, "any", "male", or "female".
- Numeric fields (age_min, age_max, lower_limit, upper_limit, critical_low, critical_high, conversion_factor): must be numeric if present.
- Skip completely empty rows.

Duplicate detection:
- A duplicate is a reference range with the same analyte_name + sex + population_group + age range.
- If "skip": do not import the duplicate.
- If "update": update the existing record with the new values.

TASK 5: PREVIEW STEP

Add controller method: ReferenceRangeImportController@preview (POST from import form).

Logic:
1. Validate the uploaded file (ImportReferenceRangesRequest).
2. Call parseAndValidate.
3. Store the parsed valid rows and options in the session (for the confirm step).
4. Render the preview view.

Create resources/views/admin/reference-ranges/import-preview.blade.php.

Layout:
- Summary: "X rows valid, Y rows with errors, Z total".
- Options summary (import as draft/published, duplicate handling).
- Valid rows table: show parsed values for each valid row.
- Errors table: show each error row with the row number and error message.
- Buttons:
  - "Confirm Import" (submits to the import route).
  - "Cancel" (back to import form).

TASK 6: CONFIRM IMPORT

Add controller method: ReferenceRangeImportController@import (POST).

Logic:
1. Read the parsed valid rows and options from the session.
2. Call the import method.
3. Clear the session data.
4. Redirect to reference-ranges index with a success flash message summarizing results (e.g., "Imported 45 reference ranges as drafts. 2 skipped.").
5. Audit log the import action.

TASK 7: ADMIN NAVIGATION

Modify resources/views/components/admin/sidebar.blade.php.

Add an "Import CSV" item under the Reference Ranges section (or as a button on the reference-ranges index page):
- Label: "Import CSV"
- Icon: Upload
- Route: admin.reference-ranges.import
- Only visible to users with reference-ranges.import permission.

Also add an "Import CSV" button to the reference-ranges index page header.

TASK 8: ROUTES

Add to routes/admin.php:

Route::middleware(['permission:reference-ranges.import'])->prefix('reference-ranges')->group(function () {
    Route::get('/import', [ReferenceRangeImportController::class, 'showImportForm'])->name('reference-ranges.import');
    Route::get('/import/template', [ReferenceRangeImportController::class, 'template'])->name('reference-ranges.import.template');
    Route::post('/import/preview', [ReferenceRangeImportController::class, 'preview'])->name('reference-ranges.import.preview');
    Route::post('/import', [ReferenceRangeImportController::class, 'import'])->name('reference-ranges.import.confirm');
});

Note: Place these BEFORE the /reference-ranges/{slug} route so "import" isn't captured as a slug.

TASK 9: FEATURE TESTS

Create tests/Feature/Admin/ReferenceRangeImportTest.php:
- Test import form is accessible with permission.
- Test import form is blocked without permission.
- Test template download returns a CSV.
- Test uploading a valid CSV shows preview with correct counts.
- Test invalid rows are flagged with error messages.
- Test confirming import creates reference ranges.
- Test imported ranges default to draft status.
- Test "skip duplicates" does not create duplicates.
- Test "update existing" updates existing records.
- Test non-CSV files are rejected.
- Test import is audit logged.

SECURITY REQUIREMENTS:
- Only users with reference-ranges.import can access.
- Validate file type and size.
- Sanitize all CSV values before inserting.
- Use parameterized queries / Eloquent (no raw SQL injection).
- Store parsed data in session temporarily; clear after import.
- Do not allow the CSV to set status directly (use the import-as option).

DEFINITION OF DONE:
- reference-ranges.import permission exists and is assigned.
- CSV template can be downloaded.
- Admins can upload a CSV and see a preview with validation results.
- Confirming import creates reference ranges (as draft by default).
- Duplicate handling (skip/update) works.
- Errors are clearly reported per row.
- Imported drafts can be bulk-published via Build 33.
- All feature tests pass.