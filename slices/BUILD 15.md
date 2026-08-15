LABGOTO/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── SopController.php
│   │   │   └── PublicSite/
│   │   │       └── SopController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreSopRequest.php
│   │           └── UpdateSopRequest.php
│   ├── Models/
│   │   └── SopResource.php
│   ├── Policies/
│   │   └── SopPolicy.php
│   └── Services/
│       └── SopAttachmentService.php
├── database/
│   ├── factories/
│   │   └── SopResourceFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_sop_resources_table.php
│   │   ├── xxxx_create_sop_resource_category_table.php
│   │   └── xxxx_create_sop_resource_tag_table.php
│   └── seeders/
│       └── SopResourceSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── sops/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── partials/
│       │           └── form.blade.php
│       └── public/
│           └── sops/
│               ├── index.blade.php
│               └── show.blade.php
├── routes/
│   ├── admin.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── Admin/
    │   │   └── Sops/
    │   │       ├── SopAdminCrudTest.php
    │   │       ├── SopAdminValidationTest.php
    │   │       └── SopAttachmentUploadTest.php
    │   └── PublicSite/
    │       └── Sops/
    │           ├── SopListingTest.php
    │           └── SopDetailTest.php
    └── Unit/
        └── Models/
            └── SopResourceTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-PHASE04-BUILD15

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1, Builds 10-14, and the Theme are complete. Phase 04 Content & Reference Architecture is approved.

We are now implementing Build 15: SOP Resources Vertical Slice (Slice 5).

OBJECTIVE:
Implement the complete SOP Resources feature, focusing on document control metadata, structured content sections, secure attachments, and the mandatory educational disclaimer.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Resources use common lifecycle states and RBAC patterns.
- SOPs use structured content columns for MVP.
- Document control metadata is stored on the main table.
- Versioning is handled via new records and status lifecycle for MVP.
- Attachments are stored locally with strict file type and size limits.
- A mandatory educational disclaimer is required on all public SOP pages.
- Rich text content must be sanitized.

FILES TO INSPECT:
- app/Models/ReferenceRange.php
- app/Models/Traits/HasResourceLifecycle.php
- app/Enums/ResourceStatus.php
- app/Http/Controllers/Admin/ReferenceRangeController.php
- app/Http/Controllers/PublicSite/ReferenceRangeController.php
- app/Services/SearchService.php
- config/filesystems.php
- routes/admin.php
- routes/web.php

FILES TO CREATE:
- app/Models/SopResource.php
- app/Policies/SopPolicy.php
- app/Services/SopAttachmentService.php
- app/Http/Controllers/Admin/SopController.php
- app/Http/Controllers/PublicSite/SopController.php
- app/Http/Requests/Admin/StoreSopRequest.php
- app/Http/Requests/Admin/UpdateSopRequest.php
- database/migrations/xxxx_create_sop_resources_table.php
- database/migrations/xxxx_create_sop_resource_category_table.php
- database/migrations/xxxx_create_sop_resource_tag_table.php
- database/factories/SopResourceFactory.php
- database/seeders/SopResourceSeeder.php
- resources/views/admin/sops/index.blade.php
- resources/views/admin/sops/create.blade.php
- resources/views/admin/sops/edit.blade.php
- resources/views/admin/sops/partials/form.blade.php
- resources/views/public/sops/index.blade.php
- resources/views/public/sops/show.blade.php
- tests/Unit/Models/SopResourceTest.php
- tests/Feature/Admin/Sops/SopAdminCrudTest.php
- tests/Feature/Admin/Sops/SopAdminValidationTest.php
- tests/Feature/Admin/Sops/SopAttachmentUploadTest.php
- tests/Feature/PublicSite/Sops/SopListingTest.php
- tests/Feature/PublicSite/Sops/SopDetailTest.php

FILES TO MODIFY:
- routes/admin.php
- routes/web.php
- app/Providers/AppServiceProvider.php (to register search provider)
- database/seeders/DatabaseSeeder.php (if optional seeding is needed)

DATABASE REQUIREMENTS:

Create sop_resources table.

Required columns:
id
name (e.g., "Phlebotomy Standard Operating Procedure")
slug (unique)
short_description
document_code (nullable, e.g., "SOP-PHL-001")
version (nullable, e.g., "1.0")
author_name (nullable)
approved_by (nullable)
effective_date (date, nullable)
next_review_date (date, nullable)
purpose (text, nullable)
scope (text, nullable)
procedure_content (longtext, nullable)
quality_control (text, nullable)
safety_information (text, nullable)
references (text, nullable)
attachments (json, nullable)
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
- index on document_code
- index on status
- index on published_at
- index on next_review_date
- fulltext on name, document_code, short_description

Create sop_resource_category and sop_resource_tag pivot tables (standard explicit pivot structure).

MODEL REQUIREMENTS:

Create app/Models/SopResource.php.
- Use HasFactory, HasResourceLifecycle.
- Cast attachments to array.
- Cast effective_date, next_review_date, published_at to datetime.
- Cast status to ResourceStatus.
- Define categories(), tags(), createdBy(), updatedBy().
- Define scopePublished, scopeSearch.
- Define helper method isReviewOverdue(): bool.
- Define helper method isReviewDueSoon(): bool (within 30 days).

ATTACHMENT SERVICE REQUIREMENTS:

Create app/Services/SopAttachmentService.php.

Responsibilities:
- Validate file type and size.
- Sanitize file name.
- Store file in storage/app/public/sops/.
- Return attachment metadata array.
- Delete attachment when removed.

Allowed file types:
- PDF (.pdf, application/pdf)
- Microsoft Word (.doc, application/msword)
- Microsoft Word (.docx, application/vnd.openxmlformats-officedocument.wordprocessingml.document)
- Plain text (.txt, text/plain)

File limits:
- Maximum file size: 5 MB per attachment.
- Maximum attachments per SOP: 5.

Security rules:
- Validate both file extension and MIME type.
- Do not allow executable files (.exe, .php, .sh, .js, etc.).
- Sanitize file names before storage.
- Do not expose the raw storage path publicly.

Storage:
- Store in storage/app/public/sops/.
- Ensure symlink exists from public/storage/sops to storage/app/public/sops.

ADMIN CRUD REQUIREMENTS:

Create Admin SopController and Form Requests.

Admin routes:
GET /admin/sops (admin.sops.index) - permission: sops.view
GET /admin/sops/create (admin.sops.create) - permission: sops.create
POST /admin/sops (admin.sops.store) - permission: sops.create
GET /admin/sops/{sop}/edit (admin.sops.edit) - permission: sops.edit
PUT /admin/sops/{sop} (admin.sops.update) - permission: sops.edit
DELETE /admin/sops/{sop} (admin.sops.destroy) - permission: sops.delete

Form fields:
- Basic: name, slug, short_description, status.
- Document Control: document_code, version, author_name, approved_by, effective_date, next_review_date.
- Content Sections: purpose, scope, procedure_content, quality_control, safety_information, references.
- Attachments: file upload input, list of existing attachments with delete option.
- Taxonomy & SEO.

Admin index requirements:
- Display SOP list with document code, version, status, review date, actions.
- Show review status indicator (Overdue, Due Soon).
- Include search by name or document code.
- Include status filter.
- Include pagination.

Review status indicator:
- If next_review_date is in the past and status is published: show "Review Overdue" badge (danger).
- If next_review_date is within 30 days: show "Review Due Soon" badge (warning).
- Otherwise: no indicator.

PUBLIC ROUTE & PAGE REQUIREMENTS:

Public Routes:
GET /sops (public.sops.index)
GET /sops/{slug} (public.sops.show)

Public Listing Page:
- Display a searchable list of published SOPs.
- Show document code, version, effective date, short description.
- Filter by category and tag.

Public Detail Page:
- Resolve by slug.
- Display document control header (Document Code, Version, Author, Approved By, Effective Date, Next Review Date, Status).
- Display review status indicator if applicable.
- Display structured content sections in order (Purpose, Scope, Procedure, Quality Control, Safety Information, References).
- Empty sections must not be displayed.
- Display attachments as downloadable links.
- Display the Mandatory Educational Disclaimer prominently at the top.
- Include breadcrumbs, meta tags, JSON-LD.

Mandatory Disclaimer Text:
"Educational Template Only: This Standard Operating Procedure is provided for educational and reference purposes. It must be adapted to your specific laboratory's instruments, reagents, and regulatory requirements before use. Always follow your institution's approved procedures."

SEARCH INTEGRATION:

Register SOP search provider in AppServiceProvider.
- Search name, document_code, short_description.
- Map type to 'sop'.
- Map URL to public.sops.show.

SEEDER & FACTORY:

Create SopResourceFactory and SopResourceSeeder.
- Seed a few example SOPs (e.g., Phlebotomy, Hematology Analyzer, Quality Control).

TESTING REQUIREMENTS:

Unit Tests:
- Test SopResource model scopes and helper methods.
- Test isReviewOverdue and isReviewDueSoon methods.

Feature Tests (Admin):
- Test CRUD operations and permissions.
- Test document control fields are saved correctly.
- Test attachment upload with valid file.
- Test attachment upload rejects disallowed file types.
- Test attachment upload rejects files over 5MB.
- Test maximum 5 attachments limit.

Feature Tests (Public):
- Test listing renders only published SOPs.
- Test detail page renders the mandatory disclaimer.
- Test detail page renders structured sections.
- Test empty sections are not displayed.
- Test attachments are downloadable.
- Test 404 for unpublished/nonexistent slugs.

ACCESSIBILITY & SEO:
- Document control header must be accessible.
- Structured sections must use semantic HTML.
- Attachment links must be keyboard accessible.
- Standard resource SEO rules apply.

SECURITY:
- Enforce RBAC server-side.
- Sanitize rich text content before rendering.
- Validate all file uploads.
- Audit log changes to document control metadata and content.

DEFINITION OF DONE:
- sop_resources table and pivots exist.
- SopResource model and policy exist.
- Admin CRUD works with document control and structured sections.
- Attachment upload works with strict validation.
- Public listing and detail pages work.
- Mandatory disclaimer is visible on all public detail pages.
- Review status indicators work correctly.
- Search provider is registered.
- Unit and feature tests pass.
- No fake data or dead UI.