labgato/
├── app/
│   ├── Enums/
│   │   └── ResourceStatus.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Member/
│   │   │   │   └── ContributionController.php
│   │   │   └── Admin/
│   │   │       └── ContributionReviewController.php
│   │   └── Requests/
│   │       ├── Member/
│   │       │   ├── StoreSopContributionRequest.php
│   │       │   ├── StoreKnowledgeContributionRequest.php
│   │       │   ├── StoreInterpretationContributionRequest.php
│   │       │   └── UpdateContributionRequest.php
│   │       └── Admin/
│   │           └── RejectContributionRequest.php
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
├── resources/
│   └── views/
│       ├── member/
│       │   └── contributions/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── show.blade.php
│       │       ├── edit.blade.php
│       │       └── partials/
│       │           ├── sop-form.blade.php
│       │           ├── knowledge-form.blade.php
│       │           └── interpretation-form.blade.php
│       ├── admin/
│       │   └── contributions/
│       │       ├── index.blade.php
│       │       └── review.blade.php
│       └── components/
│           └── admin/
│               ├── sidebar.blade.php
│               └── contributions/
│                   └── review-banner.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
└── tests/
    └── Feature/
        ├── Member/
        │   ├── ContributionSubmissionTest.php
        │   └── ContributionManagementTest.php
        └── Admin/
            ├── ContributionReviewQueueTest.php
            └── ContributionApprovalTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE06-BUILD28

CONTEXT:
LabGato member layout and dashboard (Build 27) are complete. The contributed_by and rejection_reason columns already exist on the three contributable resource tables. We are now implementing Build 28: Contribution Submission & Approval (Slice 13).

This build implements the member contribution workflow and the admin review queue.

OBJECTIVE:
Implement the ResourceStatus Rejected case, member submission forms, contribution management flows, admin review queue, and approve/reject workflow.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Members can contribute SOPs, Knowledge, and Interpretation only.
- Contributions are stored in the resource tables with contributed_by set.
- Members cannot publish directly; admin approval is required.
- Members cannot edit contributions during review.
- Rejection requires a reason.
- Members cannot upload attachments.

FILES TO INSPECT:
- app/Enums/ResourceStatus.php
- app/Models/SopResource.php
- app/Models/KnowledgeResource.php
- app/Models/InterpretationResource.php
- resources/views/admin/sops/partials/form.blade.php
- resources/views/admin/knowledge/partials/form.blade.php
- resources/views/admin/interpretation/partials/form.blade.php
- database/seeders/RolePermissionSeeder.php
- routes/web.php
- routes/admin.php

FILES TO CREATE:
- app/Http/Controllers/Member/ContributionController.php
- app/Http/Controllers/Admin/ContributionReviewController.php
- app/Http/Requests/Member/StoreSopContributionRequest.php
- app/Http/Requests/Member/StoreKnowledgeContributionRequest.php
- app/Http/Requests/Member/StoreInterpretationContributionRequest.php
- app/Http/Requests/Member/UpdateContributionRequest.php
- app/Http/Requests/Admin/RejectContributionRequest.php
- resources/views/member/contributions/index.blade.php
- resources/views/member/contributions/create.blade.php
- resources/views/member/contributions/show.blade.php
- resources/views/member/contributions/edit.blade.php
- resources/views/member/contributions/partials/sop-form.blade.php
- resources/views/member/contributions/partials/knowledge-form.blade.php
- resources/views/member/contributions/partials/interpretation-form.blade.php
- resources/views/admin/contributions/index.blade.php
- resources/views/admin/contributions/review.blade.php
- resources/views/components/admin/contributions/review-banner.blade.php
- tests/Feature/Member/ContributionSubmissionTest.php
- tests/Feature/Member/ContributionManagementTest.php
- tests/Feature/Admin/ContributionReviewQueueTest.php
- tests/Feature/Admin/ContributionApprovalTest.php

FILES TO MODIFY:
- app/Enums/ResourceStatus.php
- database/seeders/RolePermissionSeeder.php
- routes/web.php
- routes/admin.php
- resources/views/components/admin/sidebar.blade.php

TASK 1: RESOURCE STATUS ENUM

Modify app/Enums/ResourceStatus.php.

Add a new case:
- Rejected
  - Label: "Rejected"
  - Color: danger
  - isPublic: false

Update status transition rules:
- Draft → UnderReview (member submits)
- UnderReview → Published (admin approves)
- UnderReview → Rejected (admin rejects)
- UnderReview → Draft (member withdraws)
- Rejected → Draft (member revises)
- Rejected → UnderReview (member resubmits)

Add helper methods:
- canMemberEdit(): returns true for Draft and Rejected.
- canMemberWithdraw(): returns true for UnderReview.
- canMemberDelete(): returns true for Draft and Rejected.

TASK 2: CONTRIBUTIONS REVIEW PERMISSION

Modify database/seeders/RolePermissionSeeder.php.

Add permission: contributions.review

Assign to:
- Super Administrator
- Content Manager

TASK 3: MEMBER CONTRIBUTION CONTROLLER

Create app/Http/Controllers/Member/ContributionController.php.

Methods:
- index: List member's contributions (merged from three tables).
- create: Show submission form (type via query param).
- store: Create a new contribution (draft or submit).
- show: Display contribution detail.
- edit: Show edit form (only for draft/rejected).
- update: Update contribution.
- destroy: Delete contribution (only for draft/rejected).
- submit: Submit a draft for review.
- withdraw: Withdraw an under_review contribution.
- resubmit: Resubmit a rejected contribution.

Access control:
- Members can only access their own contributions (contributed_by = user id).
- Return 403 if accessing another member's contribution.

Type resolution:
- Use a type parameter ('sop', 'knowledge', 'interpretation') to determine the model.
- Create a helper method to resolve the model class from type.

TASK 4: MEMBER SUBMISSION FORMS

Create the three form request classes with validation:

StoreSopContributionRequest:
- name: required, string, 3-150
- short_description: required, string, 10-300
- purpose: required, string, max 5000
- scope: required, string, max 5000
- procedure_content: required, string, max 50000
- quality_control: nullable, string, max 5000
- safety_information: nullable, string, max 5000
- references: nullable, string, max 5000

StoreKnowledgeContributionRequest:
- name: required, string, 3-150
- short_description: required, string, 10-300
- content: required, string, max 50000
- learning_objectives: nullable, string, max 2000
- topic_category: nullable, string
- difficulty_level: nullable, string, in [Beginner, Intermediate, Advanced]

StoreInterpretationContributionRequest:
- name: required, string, 3-150
- short_description: required, string, 10-300
- content: required, string, max 50000
- clinical_context: nullable, string, max 5000

Sanitize all rich text input using RichTextSanitizer.

TASK 5: MEMBER CONTRIBUTION VIEWS

Create resources/views/member/contributions/index.blade.php:
- List member's contributions with title, type badge, status badge, date.
- Actions: View, Edit (if draft/rejected), Withdraw (if under_review), Delete (if draft/rejected).
- Filters by type and status.
- Pagination.
- Empty state if no contributions.

Create resources/views/member/contributions/create.blade.php:
- Render the appropriate form partial based on type query param.
- Two submit buttons: "Save Draft" and "Submit for Review".

Create resources/views/member/contributions/show.blade.php:
- Display full rendered content.
- Status badge.
- If rejected: display rejection_reason prominently.
- If published: link to public page.
- Action buttons based on status.

Create resources/views/member/contributions/edit.blade.php:
- Render the appropriate form partial.
- Only accessible for draft/rejected status.
- Two submit buttons: "Save Draft" and "Submit for Review".

Create the three form partials (sop-form, knowledge-form, interpretation-form).

TASK 6: ADMIN REVIEW QUEUE

Create app/Http/Controllers/Admin/ContributionReviewController.php.

Methods:
- index: List pending contributions.
- show: Display review page.
- approve: Approve a contribution.
- reject: Reject a contribution.

Permission: contributions.review

Create resources/views/admin/contributions/index.blade.php:
- List pending contributions (status='under_review', contributed_by not null).
- Columns: Title, Type, Contributor, Submitted date, Status, Actions.
- Filters by type.
- Default sort: submitted date ascending.
- Pagination.
- Empty state if no pending contributions.

Create resources/views/admin/contributions/review.blade.php:
- Contributor banner at top (name, avatar, submission date, type, status).
- The resource content displayed (or editable form).
- Admin can edit content, assign categories/tags, set SEO.
- Approve button (primary).
- Reject button (danger) → opens rejection reason dialog.

Create resources/views/components/admin/contributions/review-banner.blade.php.

TASK 7: APPROVE/REJECT ACTIONS

Approve logic:
1. Verify resource is 'under_review' and contributed_by is set.
2. Save any admin edits.
3. Set status to 'published'.
4. Set published_at to now.
5. Preserve contributed_by.
6. Audit log: contributions.approved.
7. Redirect to review queue with success.

Reject logic:
1. Verify resource is 'under_review'.
2. Require rejection_reason (10-1000 chars) via RejectContributionRequest.
3. Set status to 'rejected'.
4. Set rejection_reason.
5. Audit log: contributions.rejected.
6. Redirect to review queue with success.

TASK 8: ADMIN NAVIGATION

Modify resources/views/components/admin/sidebar.blade.php.

Add a "Contributions" navigation item:
- Label: "Contributions"
- Icon: Inbox or DocumentCheck
- Route: admin.contributions.index
- Badge: count of pending contributions.
- Only visible to users with contributions.review permission.

Position: Under "Content" group or as a top-level item.

TASK 9: ROUTES

Add member contribution routes to routes/web.php:

Route::middleware(['auth', 'active.user', 'member'])->prefix('account/contributions')->name('account.contributions.')->group(function () {
    Route::get('/', [ContributionController::class, 'index'])->name('index');
    Route::get('/create', [ContributionController::class, 'create'])->name('create');
    Route::post('/{type}', [ContributionController::class, 'store'])->name('store');
    Route::get('/{type}/{id}', [ContributionController::class, 'show'])->name('show');
    Route::get('/{type}/{id}/edit', [ContributionController::class, 'edit'])->name('edit');
    Route::put('/{type}/{id}', [ContributionController::class, 'update'])->name('update');
    Route::delete('/{type}/{id}', [ContributionController::class, 'destroy'])->name('destroy');
    Route::post('/{type}/{id}/submit', [ContributionController::class, 'submit'])->name('submit');
    Route::post('/{type}/{id}/withdraw', [ContributionController::class, 'withdraw'])->name('withdraw');
    Route::post('/{type}/{id}/resubmit', [ContributionController::class, 'resubmit'])->name('resubmit');
});

Add admin contribution routes to routes/admin.php:

Route::middleware(['permission:contributions.review'])->prefix('contributions')->name('contributions.')->group(function () {
    Route::get('/', [ContributionReviewController::class, 'index'])->name('index');
    Route::get('/{type}/{id}/review', [ContributionReviewController::class, 'show'])->name('review');
    Route::post('/{type}/{id}/approve', [ContributionReviewController::class, 'approve'])->name('approve');
    Route::post('/{type}/{id}/reject', [ContributionReviewController::class, 'reject'])->name('reject');
});

TASK 10: FEATURE TESTS

Create tests/Feature/Member/ContributionSubmissionTest.php:
- Test member can create a draft SOP contribution.
- Test member can create a draft Knowledge contribution.
- Test member can create a draft Interpretation contribution.
- Test member can submit a draft for review.
- Test submission sets status to under_review and contributed_by.
- Test member cannot submit directly to published.
- Test member cannot set categories, tags, or SEO fields.
- Test rich text is sanitized.

Create tests/Feature/Member/ContributionManagementTest.php:
- Test member can view their contribution list.
- Test member can view their contribution detail.
- Test member can edit a draft contribution.
- Test member cannot edit an under_review contribution.
- Test member can withdraw an under_review contribution.
- Test member can delete a draft contribution.
- Test member cannot delete a published contribution.
- Test member can revise and resubmit a rejected contribution.
- Test member cannot access another member's contribution.

Create tests/Feature/Admin/ContributionReviewQueueTest.php:
- Test admin with contributions.review can view the review queue.
- Test admin without contributions.review cannot view the review queue.
- Test review queue lists only under_review contributions with contributed_by.
- Test review queue can filter by type.
- Test review queue shows empty state when no pending contributions.

Create tests/Feature/Admin/ContributionApprovalTest.php:
- Test admin can approve a contribution.
- Test approval sets status to published and preserves contributed_by.
- Test admin can reject a contribution with a reason.
- Test rejection requires a reason.
- Test rejection sets status to rejected and stores reason.
- Test admin can edit contribution before approving.
- Test approval and rejection are audit logged.

SECURITY REQUIREMENTS:
- Members can only access their own contributions.
- Members cannot modify status, contributed_by, or slug directly.
- Members cannot set categories, tags, or SEO fields.
- Admin actions require contributions.review permission.
- Status transitions are enforced server-side.
- Rich text is sanitized.
- Audit log all submission, approval, and rejection actions.

DEFINITION OF DONE:
- ResourceStatus enum includes Rejected case.
- contributions.review permission is seeded and assigned.
- Members can create, edit, submit, withdraw, delete, and resubmit contributions.
- Members cannot edit during review or publish directly.
- Admin review queue lists pending contributions.
- Admin can approve or reject with a reason.
- Contributions are not publicly visible until approved.
- All feature tests pass.
- No privilege escalation is possible.