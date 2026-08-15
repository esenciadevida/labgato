labgato/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Admin/
│   │           └── SopVersionController.php
│   ├── Models/
│   │   └── SopVersion.php
│   └── Services/
│       └── SopVersionService.php
├── database/
│   └── migrations/
│       └── xxxx_create_sop_versions_table.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── sops/
│       │       ├── edit.blade.php
│       │       ├── partials/
│       │       │   └── form.blade.php
│       │       └── versions/
│       │           ├── index.blade.php
│       │           └── show.blade.php
│       └── public/
│           └── sops/
│               └── show.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        └── Admin/
            └── SopVersionHistoryTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD40-SOP-VERSIONS

CONTEXT:
LabGato SOPs are document-controlled and get revised over time. This build adds version history so every revision is tracked as a snapshot, admins can review the history, and previous versions can be restored safely.

OBJECTIVE:
Implement SOP version history: a versions table storing content snapshots, automatic snapshot on creation, a "save as new version" workflow, a history viewer, and a restore feature.

EXISTING ARCHITECTURE:
- sop_resources table with document control fields (version, document_code, purpose, scope, procedure_content, etc.).
- Admin SOP CRUD (Build 15).
- RBAC with sops.* permissions.

FILES TO INSPECT:
- app/Models/SopResource.php
- app/Http/Controllers/Admin/SopController.php
- resources/views/admin/sops/edit.blade.php
- resources/views/admin/sops/partials/form.blade.php
- database/migrations/ (sop_resources table structure)
- routes/admin.php

FILES TO CREATE:
- database/migrations/xxxx_create_sop_versions_table.php
- app/Models/SopVersion.php
- app/Services/SopVersionService.php
- app/Http/Controllers/Admin/SopVersionController.php
- resources/views/admin/sops/versions/index.blade.php
- resources/views/admin/sops/versions/show.blade.php
- tests/Feature/Admin/SopVersionHistoryTest.php

FILES TO MODIFY:
- app/Http/Controllers/Admin/SopController.php
- resources/views/admin/sops/partials/form.blade.php
- resources/views/admin/sops/edit.blade.php
- resources/views/public/sops/show.blade.php
- routes/admin.php

TASK 1: SCHEMA

Create migration: create_sop_versions_table.

Columns:
- id: primary key
- sop_id: unsignedBigInteger, foreign key to sop_resources.id, cascade on delete, indexed
- version_number: string (e.g., "1.0", "1.1", "2.0")
- document_code: string, nullable
- purpose: text, nullable
- scope: text, nullable
- procedure_content: longtext, nullable
- quality_control: text, nullable
- safety_information: text, nullable
- references: text, nullable
- change_summary: string, nullable (what changed in this version)
- created_by: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- created_at: timestamp

Indexes:
- Index on (sop_id, created_at desc).

TASK 2: MODEL

Create app/Models/SopVersion.php:
- Fillable: sop_id, version_number, document_code, purpose, scope, procedure_content, quality_control, safety_information, references, change_summary, created_by.
- Relationships: sop(), creator().

TASK 3: VERSION SERVICE

Create app/Services/SopVersionService.php.

Methods:
- createSnapshot(SopResource $sop, string $changeSummary = null): SopVersion
  - Creates a snapshot of the SOP's current content fields into sop_versions.
  - Uses the SOP's current version_number.
  - Sets created_by to the authenticated user.

- getHistory(SopResource $sop): Collection
  - Returns all versions for the SOP, ordered by created_at desc.

- suggestNextVersion(string $currentVersion): string
  - Bumps the minor version (e.g., "1.0" → "1.1").

- restore(SopResource $sop, SopVersion $version): SopResource
  - Copies the snapshot's content back to the SOP.
  - Sets a new version_number (bumped from current).
  - Sets change_summary to "Restored from version {version}".
  - Creates a new snapshot for the restored state.
  - Returns the updated SOP.

TASK 4: HOOK INTO SOP CREATE/UPDATE

Modify app/Http/Controllers/Admin/SopController.php.

On store (creation):
- After creating the SOP, call createSnapshot with version "1.0".

On update:
- Read a new form field is_new_version (boolean) and change_summary.
- If is_new_version is true:
  - Determine the new version_number (from form, or use suggestNextVersion).
  - Update the SOP with the new content and version_number.
  - Call createSnapshot with the change_summary.
- If is_new_version is false:
  - Just update the SOP (minor edit, no new snapshot).

TASK 5: EDIT FORM UPDATES

Modify resources/views/admin/sops/partials/form.blade.php.

Add (only on edit, not create):
- Display the current version (read-only).
- A checkbox: "Save as a new version".
- When checked (Alpine.js), show:
  - New version number (text input, pre-filled with suggestNextVersion).
  - Change summary (textarea, placeholder "Describe what changed in this version").

TASK 6: VERSION HISTORY UI

Create SopVersionController with index and show methods.

Routes:
- GET /admin/sops/{sop}/versions → versions.index (permission: sops.view)
- GET /admin/sops/{sop}/versions/{version} → versions.show (permission: sops.view)
- POST /admin/sops/{sop}/versions/{version}/restore → versions.restore (permission: sops.edit)

Create resources/views/admin/sops/versions/index.blade.php:
- List all versions: version_number, change_summary, created_by, created_at.
- Highlight the current version.
- Actions per version: "View", "Restore" (except current).
- Back link to SOP edit.

Create resources/views/admin/sops/versions/show.blade.php:
- Read-only display of the version snapshot (all content fields).
- Show version_number, change_summary, created_by, created_at.
- "Restore this version" button (if not current).
- Back link to version history.

TASK 7: RESTORE

Add restore method to SopVersionController:
- Call SopVersionService::restore.
- Audit log the restore action.
- Redirect to SOP edit with success message.

TASK 8: INTEGRATION

Modify resources/views/admin/sops/edit.blade.php:
- Add a "Version History" link/button (shows version count).

Modify resources/views/public/sops/show.blade.php:
- Display the current version number in the meta bar (e.g., "Version 2.1").

TASK 9: FEATURE TESTS

Create tests/Feature/Admin/SopVersionHistoryTest.php:
- Test creating an SOP creates a v1.0 snapshot.
- Test saving as a new version creates a snapshot.
- Test saving without "new version" does not create a snapshot.
- Test version history lists all versions.
- Test viewing a specific version shows its content.
- Test restoring a version creates a new version with the old content.
- Test restore is audit logged.
- Test users without sops.view cannot see version history.
- Test users without sops.edit cannot restore.

SECURITY REQUIREMENTS:
- Version history requires sops.view permission.
- Restore requires sops.edit permission.
- Snapshots are immutable (no edit/delete of version records).
- All restore actions are audit logged.
- Sanitize content before rendering version snapshots.

DEFINITION OF DONE:
- sop_versions table exists.
- Creating an SOP creates a v1.0 snapshot.
- "Save as new version" creates a snapshot with change summary.
- Version history lists all versions with metadata.
- Any past version can be viewed read-only.
- Restoring a version creates a new version from old content.
- Public SOP page shows the current version number.
- All feature tests pass.