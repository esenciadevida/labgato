LABGOTO/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── InterpretationController.php
│   │   │   │   └── KnowledgeController.php
│   │   │   └── PublicSite/
│   │   │       ├── InterpretationController.php
│   │   │       └── KnowledgeController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreInterpretationRequest.php
│   │           ├── UpdateInterpretationRequest.php
│   │           ├── StoreKnowledgeRequest.php
│   │           └── UpdateKnowledgeRequest.php
│   ├── Models/
│   │   ├── InterpretationResource.php
│   │   └── KnowledgeResource.php
│   ├── Policies/
│   │   ├── InterpretationPolicy.php
│   │   └── KnowledgePolicy.php
│   ├── Services/
│   │   └── ContentImageService.php
│   └── Support/
│       └── RichTextSanitizer.php
├── database/
│   ├── factories/
│   │   ├── InterpretationResourceFactory.php
│   │   └── KnowledgeResourceFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_interpretation_resources_table.php
│   │   ├── xxxx_create_interpretation_resource_category_table.php
│   │   ├── xxxx_create_interpretation_resource_tag_table.php
│   │   ├── xxxx_create_knowledge_resources_table.php
│   │   ├── xxxx_create_knowledge_resource_category_table.php
│   │   └── xxxx_create_knowledge_resource_tag_table.php
│   └── seeders/
│       ├── InterpretationResourceSeeder.php
│       └── KnowledgeResourceSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── interpretation/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── partials/
│       │   │       └── form.blade.php
│       │   └── knowledge/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── partials/
│       │           └── form.blade.php
│       └── public/
│           ├── interpretation/
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           └── knowledge/
│               ├── index.blade.php
│               └── show.blade.php
├── routes/
│   ├── admin.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── Admin/
    │   │   ├── Interpretation/
    │   │   │   ├── InterpretationAdminCrudTest.php
    │   │   │   └── InterpretationAdminValidationTest.php
    │   │   └── Knowledge/
    │   │       ├── KnowledgeAdminCrudTest.php
    │   │       └── KnowledgeAdminValidationTest.php
    │   └── PublicSite/
    │       ├── Interpretation/
    │       │   ├── InterpretationListingTest.php
    │       │   └── InterpretationDetailTest.php
    │       └── Knowledge/
    │           ├── KnowledgeListingTest.php
    │           └── KnowledgeDetailTest.php
    └── Unit/
        ├── Models/
        │   ├── InterpretationResourceTest.php
        │   └── KnowledgeResourceTest.php
        └── Support/
            └── RichTextSanitizerTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-PHASE04-BUILD16

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1, Builds 10-15, and the Theme are complete. Phase 04 Content & Reference Architecture is approved.

We are now implementing Build 16: Interpretation & Knowledge Vertical Slices (Slice 6).

This build covers TWO resource types:
1. Result Interpretation Resources
2. Knowledge & Education Resources

Both share common infrastructure for rich text handling, image uploads, and reading experience.

OBJECTIVE:
Implement the complete Interpretation and Knowledge resource modules, focusing on rich text handling, image uploads, reading experience, and the mandatory educational disclaimer for Interpretation.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Resources use common lifecycle states and RBAC patterns.
- Interpretation and Knowledge resources use separate tables.
- Rich text content must be sanitized.
- Result Interpretation resources require mandatory disclaimers.
- Images must be stored securely with file type validation.
- Knowledge resources support learning objectives and difficulty levels.

FILES TO INSPECT:
- app/Models/SopResource.php
- app/Models/Traits/HasResourceLifecycle.php
- app/Enums/ResourceStatus.php
- app/Http/Controllers/Admin/SopController.php
- app/Http/Controllers/PublicSite/SopController.php
- app/Services/SearchService.php
- config/filesystems.php
- routes/admin.php
- routes/web.php

FILES TO CREATE:

Shared Infrastructure:
- app/Support/RichTextSanitizer.php
- app/Services/ContentImageService.php

Interpretation Resources:
- app/Models/InterpretationResource.php
- app/Policies/InterpretationPolicy.php
- app/Http/Controllers/Admin/InterpretationController.php
- app/Http/Controllers/PublicSite/InterpretationController.php
- app/Http/Requests/Admin/StoreInterpretationRequest.php
- app/Http/Requests/Admin/UpdateInterpretationRequest.php
- database/migrations/xxxx_create_interpretation_resources_table.php
- database/migrations/xxxx_create_interpretation_resource_category_table.php
- database/migrations/xxxx_create_interpretation_resource_tag_table.php
- database/factories/InterpretationResourceFactory.php
- database/seeders/InterpretationResourceSeeder.php
- resources/views/admin/interpretation/index.blade.php
- resources/views/admin/interpretation/create.blade.php
- resources/views/admin/interpretation/edit.blade.php
- resources/views/admin/interpretation/partials/form.blade.php
- resources/views/public/interpretation/index.blade.php
- resources/views/public/interpretation/show.blade.php

Knowledge Resources:
- app/Models/KnowledgeResource.php
- app/Policies/KnowledgePolicy.php
- app/Http/Controllers/Admin/KnowledgeController.php
- app/Http/Controllers/PublicSite/KnowledgeController.php
- app/Http/Requests/Admin/StoreKnowledgeRequest.php
- app/Http/Requests/Admin/UpdateKnowledgeRequest.php
- database/migrations/xxxx_create_knowledge_resources_table.php
- database/migrations/xxxx_create_knowledge_resource_category_table.php
- database/migrations/xxxx_create_knowledge_resource_tag_table.php
- database/factories/KnowledgeResourceFactory.php
- database/seeders/KnowledgeResourceSeeder.php
- resources/views/admin/knowledge/index.blade.php
- resources/views/admin/knowledge/create.blade.php
- resources/views/admin/knowledge/edit.blade.php
- resources/views/admin/knowledge/partials/form.blade.php
- resources/views/public/knowledge/index.blade.php
- resources/views/public/knowledge/show.blade.php

Tests:
- tests/Unit/Models/InterpretationResourceTest.php
- tests/Unit/Models/KnowledgeResourceTest.php
- tests/Unit/Support/RichTextSanitizerTest.php
- tests/Feature/Admin/Interpretation/InterpretationAdminCrudTest.php
- tests/Feature/Admin/Interpretation/InterpretationAdminValidationTest.php
- tests/Feature/Admin/Knowledge/KnowledgeAdminCrudTest.php
- tests/Feature/Admin/Knowledge/KnowledgeAdminValidationTest.php
- tests/Feature/PublicSite/Interpretation/InterpretationListingTest.php
- tests/Feature/PublicSite/Interpretation/InterpretationDetailTest.php
- tests/Feature/PublicSite/Knowledge/KnowledgeListingTest.php
- tests/Feature/PublicSite/Knowledge/KnowledgeDetailTest.php

FILES TO MODIFY:
- routes/admin.php
- routes/web.php
- app/Providers/AppServiceProvider.php (to register search providers)
- database/seeders/DatabaseSeeder.php (if optional seeding is needed)

DATABASE REQUIREMENTS:

Create interpretation_resources table.

Required columns:
id
name (e.g., "Interpreting Elevated Liver Enzymes")
slug (unique)
short_description
content (longtext, required)
clinical_context (text, nullable)
related_analytes (json, nullable)
mandatory_disclaimer (text, required)
author_name (nullable)
featured_image (nullable)
reading_time_minutes (integer, nullable)
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
- index on status
- index on published_at
- fulltext on name, short_description, clinical_context

Create knowledge_resources table.

Required columns:
id
name (e.g., "Introduction to Hematology")
slug (unique)
short_description
content (longtext, required)
topic_category (string, nullable)
difficulty_level (string, nullable, e.g., "Beginner", "Intermediate", "Advanced")
learning_objectives (text, nullable)
author_name (nullable)
featured_image (nullable)
reading_time_minutes (integer, nullable)
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
- index on status
- index on published_at
- index on difficulty_level
- fulltext on name, short_description, content

Create pivot tables:
- interpretation_resource_category
- interpretation_resource_tag
- knowledge_resource_category
- knowledge_resource_tag

MODEL REQUIREMENTS:

Create app/Models/InterpretationResource.php.
- Use HasFactory, HasResourceLifecycle.
- Cast content to string, related_analytes to array.
- Cast published_at to datetime, status to ResourceStatus.
- Define categories(), tags(), createdBy(), updatedBy().
- Define scopePublished, scopeSearch.

Create app/Models/KnowledgeResource.php.
- Use HasFactory, HasResourceLifecycle.
- Cast content to string, learning_objectives to string.
- Cast published_at to datetime, status to ResourceStatus.
- Define categories(), tags(), createdBy(), updatedBy().
- Define scopePublished, scopeSearch.
- Define scopeByDifficulty($difficulty).

RICH TEXT SANITIZATION REQUIREMENTS:

Create app/Support/RichTextSanitizer.php.

Responsibilities:
- Sanitize HTML content to prevent XSS.
- Remove script tags and inline event handlers.
- Allow safe HTML tags: h1-h6, p, ul, ol, li, strong, em, a, img, blockquote, code, pre, table, thead, tbody, tr, th, td.
- Sanitize image src attributes to only allow local storage paths or approved domains.
- Remove javascript: URLs.

For MVP, content may be stored as Markdown and rendered to HTML using a Markdown parser, then sanitized.

Rules:
- Do not allow <script> tags.
- Do not allow inline event handlers (onclick, onload, etc.).
- Do not allow javascript: URLs.
- Sanitize all user-provided HTML before rendering.

CONTENT IMAGE SERVICE REQUIREMENTS:

Create app/Services/ContentImageService.php.

Responsibilities:
- Validate image type and size.
- Sanitize file name.
- Store image in storage/app/public/content/.
- Return image path.
- Delete image when removed.

Allowed image types:
- JPEG (.jpg, .jpeg, image/jpeg)
- PNG (.png, image/png)
- WebP (.webp, image/webp)
- SVG (.svg, image/svg+xml) - must be sanitized

Image limits:
- Maximum file size: 2 MB per image.
- Maximum images per resource: 10.

Storage:
- Store in storage/app/public/content/.
- Ensure symlink exists from public/storage/content to storage/app/public/content/.

Alt text requirements:
- Provide an alt text input field in the admin form.
- Use empty alt text (alt="") for decorative images.
- Use descriptive alt text for informative images.

ADMIN CRUD REQUIREMENTS:

Create Admin InterpretationController and Form Requests.

Admin routes for Interpretation:
GET /admin/interpretation (admin.interpretation.index) - permission: interpretation.view
GET /admin/interpretation/create (admin.interpretation.create) - permission: interpretation.create
POST /admin/interpretation (admin.interpretation.store) - permission: interpretation.create
GET /admin/interpretation/{interpretation}/edit (admin.interpretation.edit) - permission: interpretation.edit
PUT /admin/interpretation/{interpretation} (admin.interpretation.update) - permission: interpretation.edit
DELETE /admin/interpretation/{interpretation} (admin.interpretation.destroy) - permission: interpretation.delete

Form fields for Interpretation:
- Basic: name, slug, short_description, status.
- Content: content (rich text or Markdown editor), clinical_context.
- Related: related_analytes (JSON or multi-select).
- Disclaimer: mandatory_disclaimer (pre-filled with default text, editable).
- Media: featured_image upload.
- Metadata: author_name, reading_time_minutes.
- Taxonomy & SEO.

Create Admin KnowledgeController and Form Requests.

Admin routes for Knowledge:
GET /admin/knowledge (admin.knowledge.index) - permission: knowledge.view
GET /admin/knowledge/create (admin.knowledge.create) - permission: knowledge.create
POST /admin/knowledge (admin.knowledge.store) - permission: knowledge.create
GET /admin/knowledge/{knowledge}/edit (admin.knowledge.edit) - permission: knowledge.edit
PUT /admin/knowledge/{knowledge} (admin.knowledge.update) - permission: knowledge.edit
DELETE /admin/knowledge/{knowledge} (admin.knowledge.destroy) - permission: knowledge.delete

Form fields for Knowledge:
- Basic: name, slug, short_description, status.
- Content: content (rich text or Markdown editor).
- Classification: topic_category, difficulty_level.
- Learning: learning_objectives.
- Media: featured_image upload.
- Metadata: author_name, reading_time_minutes.
- Taxonomy & SEO.

PUBLIC ROUTE & PAGE REQUIREMENTS:

Public Routes for Interpretation:
GET /interpretation (public.interpretation.index)
GET /interpretation/{slug} (public.interpretation.show)

Public Routes for Knowledge:
GET /knowledge (public.knowledge.index)
GET /knowledge/{slug} (public.knowledge.show)

Public Interpretation Listing Page:
- Display a searchable list of published Interpretation resources.
- Show short description and reading time.
- Filter by category and tag.

Public Interpretation Detail Page:
- Resolve by slug.
- Display the Mandatory Educational Disclaimer prominently at the top.
- Display clinical context.
- Display rich text content.
- Display related analytes and resources.
- Show reading time.
- Include breadcrumbs, meta tags, JSON-LD.

Mandatory Disclaimer Text for Interpretation:
"Educational Interpretation Only: This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient's clinical condition and consult with a qualified healthcare professional."

Public Knowledge Listing Page:
- Display a searchable list of published Knowledge resources.
- Show short description, reading time, and difficulty level.
- Filter by category, tag, and difficulty level.

Public Knowledge Detail Page:
- Resolve by slug.
- Display learning objectives (if available) at the top.
- Display featured image (if available).
- Display rich text content.
- Display related resources.
- Show reading time and difficulty level.
- Include breadcrumbs, meta tags, JSON-LD.

Reading Experience Rules:
- Use a readable line height (at least 1.5).
- Use a max width for reading content (e.g., max-w-3xl).
- Use semantic HTML for headings.
- Ensure images are responsive.

SEARCH INTEGRATION:

Register Interpretation search provider in AppServiceProvider.
- Search name, short_description, clinical_context.
- Map type to 'interpretation'.
- Map URL to public.interpretation.show.

Register Knowledge search provider in AppServiceProvider.
- Search name, short_description.
- Map type to 'knowledge'.
- Map URL to public.knowledge.show.

SEEDER & FACTORY:

Create InterpretationResourceFactory, InterpretationResourceSeeder.
Create KnowledgeResourceFactory, KnowledgeResourceSeeder.
- Seed a few example Interpretation resources (e.g., Elevated Liver Enzymes, Anemia Indices).
- Seed a few example Knowledge resources (e.g., Hematology Basics, Clinical Chemistry Overview).

TESTING REQUIREMENTS:

Unit Tests:
- Test RichTextSanitizer removes script tags.
- Test RichTextSanitizer removes inline event handlers.
- Test RichTextSanitizer allows safe HTML tags.
- Test InterpretationResource model scopes.
- Test KnowledgeResource model scopes and difficulty filter.

Feature Tests (Admin Interpretation):
- Test CRUD operations and permissions.
- Test mandatory disclaimer is required.
- Test rich text content is saved correctly.
- Test featured image upload with valid image.
- Test featured image upload rejects disallowed types.

Feature Tests (Admin Knowledge):
- Test CRUD operations and permissions.
- Test learning objectives are saved correctly.
- Test difficulty level is saved correctly.
- Test featured image upload with valid image.

Feature Tests (Public Interpretation):
- Test listing renders only published resources.
- Test detail page renders the mandatory disclaimer.
- Test detail page renders clinical context.
- Test 404 for unpublished/nonexistent slugs.

Feature Tests (Public Knowledge):
- Test listing renders only published resources.
- Test listing can filter by difficulty level.
- Test detail page renders learning objectives.
- Test detail page renders featured image.
- Test 404 for unpublished/nonexistent slugs.

ACCESSIBILITY & SEO:
- Rich text content must use semantic HTML.
- Images must have alt text.
- Reading content must be keyboard accessible.
- Standard resource SEO rules apply.

SECURITY:
- Enforce RBAC server-side.
- Sanitize all rich text content before rendering.
- Validate all image uploads.
- Audit log changes.

DEFINITION OF DONE:
- interpretation_resources and knowledge_resources tables and pivots exist.
- InterpretationResource and KnowledgeResource models and policies exist.
- RichTextSanitizer works correctly.
- ContentImageService works correctly.
- Admin CRUD works for both resource types.
- Mandatory disclaimer is visible on all public Interpretation pages.
- Learning objectives are displayed on Knowledge detail pages.
- Public listing and detail pages work for both resource types.
- Reading experience is comfortable (max-width, line height).
- Search providers are registered for both resource types.
- Unit and feature tests pass.
- No fake data or dead UI.