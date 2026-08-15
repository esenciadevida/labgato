LABGOTO/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── CategoryController.php
│   │   │       └── TagController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreCategoryRequest.php
│   │           ├── UpdateCategoryRequest.php
│   │           ├── StoreTagRequest.php
│   │           └── UpdateTagRequest.php
│   └── Services/
│       └── CategoryService.php
├── database/
│   └── seeders/
│       └── LaboratoryCategorySeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── categories/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   └── tags/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       └── edit.blade.php
│       └── components/
│           └── admin/
│               └── resources/
│                   ├── category-selector.blade.php
│                   └── tag-input.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        └── Admin/
            └── Taxonomy/
                ├── CategoryCrudTest.php
                └── TagCrudTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-PHASE03-BUILD10

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1 Application Foundation is complete. Phase 03 Resource Architecture is approved.

We are now implementing Build 10: Taxonomy Admin Module.

This build creates the admin interface for Categories and Tags, plus reusable components that will later be used inside Calculator and Tool forms.

This build does NOT implement Calculators or Tools yet.

OBJECTIVE:
Implement Category and Tag admin CRUD, hierarchical category listing, tag normalization, reusable category selector and tag input components, initial laboratory category seeders, and taxonomy feature tests.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Categories support hierarchy using parent_id.
- Tags are flat and normalized to lowercase.
- Taxonomy management requires taxonomy.view or taxonomy.manage permission.
- Explicit pivot tables will be used later for resource relationships.
- Admin CRUD must follow the approved Resource Admin CRUD Patterns.
- RBAC must be enforced server-side.
- Audit logging must record taxonomy changes.
- No dead UI.
- No fake data.

FILES TO INSPECT:
- app/Models/Category.php
- app/Models/Tag.php
- app/Policies/CategoryPolicy.php
- app/Policies/TagPolicy.php
- app/Http/Middleware/EnsureUserHasPermission.php
- routes/admin.php
- resources/views/layouts/admin.blade.php
- resources/views/components/admin/
- resources/views/components/ui/
- database/migrations/
- database/seeders/RolePermissionSeeder.php

FILES TO CREATE:
- app/Http/Controllers/Admin/CategoryController.php
- app/Http/Controllers/Admin/TagController.php
- app/Http/Requests/Admin/StoreCategoryRequest.php
- app/Http/Requests/Admin/UpdateCategoryRequest.php
- app/Http/Requests/Admin/StoreTagRequest.php
- app/Http/Requests/Admin/UpdateTagRequest.php
- app/Services/CategoryService.php
- database/seeders/LaboratoryCategorySeeder.php
- resources/views/admin/categories/index.blade.php
- resources/views/admin/categories/create.blade.php
- resources/views/admin/categories/edit.blade.php
- resources/views/admin/tags/index.blade.php
- resources/views/admin/tags/create.blade.php
- resources/views/admin/tags/edit.blade.php
- resources/views/components/admin/resources/category-selector.blade.php
- resources/views/components/admin/resources/tag-input.blade.php
- tests/Feature/Admin/Taxonomy/CategoryCrudTest.php
- tests/Feature/Admin/Taxonomy/TagCrudTest.php

FILES TO MODIFY:
- routes/admin.php
- database/seeders/DatabaseSeeder.php if category seeding should be optional
- resources/views/components/admin/navigation.blade.php if Taxonomy navigation links were placeholders
- resources/views/components/admin/sidebar.blade.php if Taxonomy navigation links were placeholders
- app/Models/Category.php if helper methods are required
- app/Models/Tag.php if normalization methods are required

ROUTE REQUIREMENTS:

Modify routes/admin.php.

Create category routes:

GET /admin/categories
- Name: admin.categories.index
- Middleware: auth, active, permission:taxonomy.view

GET /admin/categories/create
- Name: admin.categories.create
- Middleware: auth, active, permission:taxonomy.manage

POST /admin/categories
- Name: admin.categories.store
- Middleware: auth, active, permission:taxonomy.manage

GET /admin/categories/{category}/edit
- Name: admin.categories.edit
- Middleware: auth, active, permission:taxonomy.manage

PUT /admin/categories/{category}
- Name: admin.categories.update
- Middleware: auth, active, permission:taxonomy.manage

DELETE /admin/categories/{category}
- Name: admin.categories.destroy
- Middleware: auth, active, permission:taxonomy.manage

Create tag routes:

GET /admin/tags
- Name: admin.tags.index
- Middleware: auth, active, permission:taxonomy.view

GET /admin/tags/create
- Name: admin.tags.create
- Middleware: auth, active, permission:taxonomy.manage

POST /admin/tags
- Name: admin.tags.store
- Middleware: auth, active, permission:taxonomy.manage

GET /admin/tags/{tag}/edit
- Name: admin.tags.edit
- Middleware: auth, active, permission:taxonomy.manage

PUT /admin/tags/{tag}
- Name: admin.tags.update
- Middleware: auth, active, permission:taxonomy.manage

DELETE /admin/tags/{tag}
- Name: admin.tags.destroy
- Middleware: auth, active, permission:taxonomy.manage

CATEGORY REQUIREMENTS:

CategoryController must support:
- index
- create
- store
- edit
- update
- destroy

Index page requirements:
- Display categories in a hierarchical or indented list.
- Show name, slug, parent, resource count if practical, active status, and actions.
- Include search by name.
- Include pagination if list is large.
- Show empty state if no categories exist.
- Show create button only if user has taxonomy.manage permission.

Create and edit form requirements:
- Name input.
- Slug input.
- Slug should auto-generate from name using JavaScript if left blank, but server-side slug generation must remain authoritative.
- Parent category select.
- Description textarea.
- Sort order number input.
- Active checkbox.
- Validation errors displayed inline.

Category validation rules:
- name: required, string, max 255.
- slug: required, string, max 255, alpha_dash, unique in categories table except current record on update.
- parent_id: nullable, exists in categories table, cannot be the same category being edited, cannot create circular hierarchy.
- description: nullable, string, max 1000.
- sort_order: integer, min 0.
- is_active: boolean.

CategoryService responsibilities:
- Build hierarchical category tree for index page.
- Build flattened indented category list for select inputs.
- Prevent circular parent relationships.
- Generate slug if not provided.
- Normalize slug to lowercase hyphenated format.

Category deletion rules:
- Prevent deletion if category has child categories.
- Prevent deletion if category is assigned to resources once resource pivot tables exist.
- For Build 10, prevent deletion if category has children.
- If deletion is blocked, display a safe warning message.

TAG REQUIREMENTS:

TagController must support:
- index
- create
- store
- edit
- update
- destroy

Index page requirements:
- Display tags in a paginated table.
- Show name, slug, resource count if practical, and actions.
- Include search by name.
- Show empty state if no tags exist.
- Show create button only if user has taxonomy.manage permission.

Create and edit form requirements:
- Name input.
- Slug input.
- Slug should auto-generate from name if left blank.
- Validation errors displayed inline.

Tag validation rules:
- name: required, string, max 255, unique case-insensitive.
- slug: required, string, max 255, alpha_dash, unique in tags table except current record on update.

Tag normalization rules:
- Normalize tag name to lowercase before uniqueness check and storage.
- Generate slug from normalized name if slug is not provided.
- Prevent duplicate tags that differ only by case.

Tag deletion rules:
- Tags may be deleted.
- If resource pivot tables exist later, deleting a tag must detach it from resources.
- For Build 10, deletion may simply delete the tag because resource pivots are not implemented yet.

REUSABLE COMPONENT REQUIREMENTS:

Create resources/views/components/admin/resources/category-selector.blade.php.

Purpose:
- Allow administrators to assign categories to a resource form.

Component props:
- name: form input name, default categories[]
- selected: array of selected category IDs
- categories: optional collection, defaults to all active categories

Component behavior:
- Render active categories in an indented hierarchical checkbox list or multi-select.
- Show inactive categories only if they are already selected.
- Support keyboard navigation.
- Use accessible labels.

For Build 10, the component may be implemented as a multi-select or checkbox tree. It will be embedded later in Calculator and Tool forms.

Create resources/views/components/admin/resources/tag-input.blade.php.

Purpose:
- Allow administrators to assign tags to a resource form.

Component props:
- name: form input name, default tags[]
- selected: array of selected tag IDs
- tags: optional collection, defaults to all tags

Component behavior:
- Render a multi-select or Alpine.js tag selector.
- Allow selecting existing tags.
- For MVP, do not require creating new tags inline unless user has taxonomy.manage permission.
- Support keyboard navigation.
- Use accessible labels.
- Normalize selected tag display to lowercase.

If external libraries such as Tom Select or Choices.js are not installed, implement a lightweight Alpine.js multi-select or use a native multi-select for MVP.

Do not add heavy external JavaScript dependencies without approval.

SEEDER REQUIREMENTS:

Create database/seeders/LaboratoryCategorySeeder.php.

Seeder behavior:
- Idempotent.
- Use firstOrCreate.
- Create common laboratory categories.

Initial categories:
- Hematology
- Clinical Chemistry
- Microbiology
- Immunology
- Blood Bank / Transfusion Medicine
- Urinalysis and Body Fluids
- Laboratory Quality Assurance
- Phlebotomy / Specimen Collection

Rules:
- Top-level categories only for Build 10.
- Slugs generated from names.
- Active by default.
- Sort order logical.

Modify DatabaseSeeder.php if appropriate:
- Call LaboratoryCategorySeeder only in local or staging environments, or make it optional.
- Do not force production seeding without approval.

AUDIT LOGGING REQUIREMENTS:

Use AuditLogService to record taxonomy actions:

- taxonomy.category.created
- taxonomy.category.updated
- taxonomy.category.deleted
- taxonomy.tag.created
- taxonomy.tag.updated
- taxonomy.tag.deleted

Capture:
- Acting user ID.
- Action.
- Resource type Category or Tag.
- Resource ID.
- Sanitized changes.
- IP address.
- User agent.

Do not log secrets.

NAVIGATION REQUIREMENTS:

Update admin navigation if Taxonomy links were placeholders.

Taxonomy group should include:
- Categories
- Tags

Visibility rules:
- Show Taxonomy group if user has taxonomy.view or taxonomy.manage.
- Show create/edit/delete controls only if user has taxonomy.manage.

ACCESSIBILITY REQUIREMENTS:

Admin taxonomy pages must:
- Use semantic table elements for listing pages.
- Use visible labels for form fields.
- Support keyboard navigation.
- Display validation errors accessibly.
- Use visible focus states.
- Use confirmation for delete actions.

Delete confirmation may use:
- Accessible modal, or
- Native browser confirm for MVP.

TESTING REQUIREMENTS:

Create tests/Feature/Admin/Taxonomy/CategoryCrudTest.php.

Required tests:

1. test_guest_cannot_access_categories
   - Visit admin.categories.index as guest.
   - Assert redirected to login.

2. test_user_without_taxonomy_permission_cannot_access_categories
   - Authenticate user without taxonomy.view.
   - Visit admin.categories.index.
   - Assert 403.

3. test_user_with_taxonomy_view_can_view_category_index
   - Authenticate user with taxonomy.view.
   - Visit admin.categories.index.
   - Assert successful response.

4. test_user_with_taxonomy_manage_can_create_category
   - Authenticate user with taxonomy.manage.
   - POST category data.
   - Assert category exists in database.
   - Assert redirect and success message.

5. test_category_requires_name
   - POST without name.
   - Assert validation error.

6. test_category_slug_must_be_unique
   - Create category with slug hematology.
   - Attempt another category with same slug.
   - Assert validation error.

7. test_category_parent_cannot_be_itself
   - Create category.
   - Attempt update with parent_id equal to category id.
   - Assert validation error.

8. test_category_with_children_cannot_be_deleted
   - Create parent and child category.
   - Attempt delete parent.
   - Assert parent still exists.
   - Assert warning message displayed.

Create tests/Feature/Admin/Taxonomy/TagCrudTest.php.

Required tests:

1. test_guest_cannot_access_tags
   - Visit admin.tags.index as guest.
   - Assert redirected to login.

2. test_user_without_taxonomy_permission_cannot_access_tags
   - Authenticate user without taxonomy.view.
   - Visit admin.tags.index.
   - Assert 403.

3. test_user_with_taxonomy_view_can_view_tag_index
   - Authenticate user with taxonomy.view.
   - Visit admin.tags.index.
   - Assert successful response.

4. test_user_with_taxonomy_manage_can_create_tag
   - Authenticate user with taxonomy.manage.
   - POST tag data.
   - Assert tag exists in database.
   - Assert redirect and success message.

5. test_tag_name_is_normalized_to_lowercase
   - Create tag with name Hematology.
   - Assert stored name is hematology.

6. test_duplicate_tag_names_are_rejected_case_insensitively
   - Create tag hematology.
   - Attempt create tag HEMATOLOGY.
   - Assert validation error.

7. test_tag_requires_name
   - POST without name.
   - Assert validation error.

SECURITY REQUIREMENTS:

- RBAC must be enforced server-side.
- Do not rely on hiding buttons for security.
- Validate all inputs.
- Prevent circular category hierarchy.
- Prevent slug injection.
- Do not expose stack traces.
- Audit log must not store sensitive fields.

SEO REQUIREMENTS:

Admin taxonomy pages must be noindex.

No public taxonomy pages are required for Build 10.

DEFINITION OF DONE:

- Category admin CRUD works.
- Tag admin CRUD works.
- Category hierarchy is displayed correctly.
- Category parent cannot be itself.
- Category with children cannot be deleted.
- Tags are normalized to lowercase.
- Duplicate tags are rejected case-insensitively.
- Category selector component exists.
- Tag input component exists.
- Initial laboratory categories seeder exists.
- Taxonomy navigation appears for authorized users.
- Audit logs record taxonomy changes.
- Feature tests pass.
- No dead UI exists.
- No fake data exists.
- No secrets are committed.