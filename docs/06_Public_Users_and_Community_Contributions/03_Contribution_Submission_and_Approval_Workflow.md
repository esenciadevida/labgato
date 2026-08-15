# 03 — Contribution Submission and Approval Workflow

## Depends On

This document depends on:

- The approved Phase 03 Resource Architecture (specifically Resource Foundation, SOP, Knowledge, Interpretation architectures).
- The approved Phase 04 Content & Reference Architecture.
- The approved Phase 06 Public User Accounts and Authentication document.
- The approved Phase 06 Member Layout and Dashboard document.
- The completed Build 24 Admin System Features.

## Defines

This document defines:

- The contribution types and scope.
- The submission data model changes (contributed_by, rejection_reason).
- The submission status lifecycle.
- The member submission forms for SOP, Knowledge, and Interpretation.
- The member contribution management (list, view, edit, withdraw).
- The admin review queue.
- The admin approve/reject workflow.
- The validation rules for member submissions.
- The notification strategy.

## Affects

This document affects:

- The Contributor Credit and Profile document.
- The SOP, Knowledge, and Interpretation resource tables.
- The ResourceStatus enum.
- The admin navigation (new Contributions review queue).
- The member area controllers and views.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Enables members to contribute content that requires admin approval.
- Prevents unapproved content from appearing publicly.
- Establishes a clear review workflow for administrators.
- Defines the data model changes needed for contribution tracking.

---

# 1. Purpose

This document establishes the Contribution Submission and Approval Workflow for LabGato.

Laboratory professionals want to share their expertise by contributing SOPs, Knowledge articles, and Interpretation guides. However, because LabGato is a professional reference platform, all contributed content must be reviewed and approved by administrators before publication to ensure accuracy and quality.

This document defines how members submit content, how administrators review it, and how the approval/rejection workflow operates.

---

# 2. Contribution Types and Scope

## 2.1 Allowed Contribution Types

Members can contribute to three resource types:

| Type | Rationale |
|---|---|
| SOP Resources | Content-based, procedural documents |
| Knowledge Articles | Content-based, educational articles |
| Interpretation Resources | Content-based, clinical context guides |

## 2.2 Restricted Resource Types

Members CANNOT contribute to:

| Type | Rationale |
|---|---|
| Calculators | Require validated calculation logic (PHP code) |
| Laboratory Tools | Require validated tool logic (PHP code) |
| Reference Ranges | Require verified, structured medical data |

These restricted types are admin-only because they require technical validation or verified medical data that cannot be safely crowd-sourced.

## 2.3 Member Submission Capabilities

Members can:
- Create draft contributions.
- Edit their own contributions while in draft.
- Submit contributions for review.
- Withdraw submitted contributions (return to draft).
- Revise and resubmit rejected contributions.
- Delete their own draft or rejected contributions.

Members cannot:
- Publish contributions directly.
- Edit contributions after submission (during review).
- Assign categories or tags.
- Upload file attachments (deferred for security; admin can add after approval).
- Set SEO fields.
- Modify contributed_by or status fields.

---

# 3. Submission Data Model Changes

## 3.1 Schema Additions

Add the following columns to three tables: `sop_resources`, `knowledge_resources`, `interpretation_resources`.

contributed_by
- Type: unsignedBigInteger
- Nullable: true
- Foreign key to users.id, null on delete
- Description: The member who contributed this resource. Null for admin-created resources.
- Indexed

rejection_reason
- Type: text
- Nullable: true
- Description: The reason provided by admin when rejecting a contribution.

## 3.2 ResourceStatus Enum Addition

Add a new status to the ResourceStatus enum:

Rejected
- Label: "Rejected"
- Color: danger
- isPublic: false
- Description: Contribution was reviewed and rejected by an administrator.

Updated enum cases:
- Draft
- UnderReview
- Published
- Archived
- Obsolete
- Rejected (new)

## 3.3 Status Transition Rules

Add the following transitions to the ResourceStatus enum:

- Draft → UnderReview (member submits)
- UnderReview → Published (admin approves)
- UnderReview → Rejected (admin rejects)
- UnderReview → Draft (member withdraws)
- Rejected → Draft (member revises)
- Rejected → UnderReview (member resubmits after revision)

Rules:
- Members can only transition: Draft ↔ UnderReview, UnderReview → Draft, Rejected → Draft, Rejected → UnderReview.
- Admins can transition: UnderReview → Published, UnderReview → Rejected.
- Members cannot transition to Published, Archived, or Obsolete.

---

# 4. Submission Status Lifecycle

## 4.1 Status Flow Diagram

```text
                    ┌─────────────┐
                    │    Draft    │ ← Member creates/edits
                    └──────┬──────┘
                           │ Member submits
                           ▼
                    ┌─────────────┐
                    │ Under Review│ ← Admin reviews
                    └──────┬──────┘
                           │
              ┌────────────┴────────────┐
              │                         │
        Admin approves            Admin rejects
              │                         │
              ▼                         ▼
       ┌─────────────┐          ┌─────────────┐
       │  Published  │          │  Rejected   │
       └─────────────┘          └──────┬──────┘
                                       │ Member revises
                                       ▼
                                ┌─────────────┐
                                │    Draft    │ (cycle repeats)
                                └─────────────┘
4.2 Visibility Rules
Status
Visible to Member (owner)
Visible to Admin
Visible Publicly
Draft
Yes
Yes
No
UnderReview
Yes
Yes
No
Published
Yes
Yes
Yes
Rejected
Yes
Yes
No
Archived
No (unless admin)
Yes
No
Obsolete
No (unless admin)
Yes
No
5. Member Submission Forms
5.1 Contribution Creation Route
GET /account/contributions/create?type={sop|knowledge|interpretation}
The type query parameter determines which form to render.
5.2 Common Form Fields
All contribution types share:
name (required, 3-150 characters)
short_description (required, 10-300 characters)
5.3 SOP Submission Form
Additional fields:
purpose (required, text)
scope (required, text)
procedure_content (required, longtext, rich text or Markdown)
quality_control (optional, text)
safety_information (optional, text)
references (optional, text)
Excluded (admin-only):
document_code, version, author_name, approved_by, effective_date, next_review_date
attachments
categories, tags
SEO fields
5.4 Knowledge Submission Form
Additional fields:
content (required, longtext, rich text or Markdown)
learning_objectives (optional, text)
topic_category (optional, select from predefined list)
difficulty_level (optional, select: Beginner, Intermediate, Advanced)
Excluded (admin-only):
featured_image
categories, tags
SEO fields
5.5 Interpretation Submission Form
Additional fields:
content (required, longtext, rich text or Markdown)
clinical_context (optional, text)
Excluded (admin-only):
related_analytes
mandatory_disclaimer (auto-set by system)
featured_image
categories, tags
SEO fields
5.6 Form Behavior
On submit (save draft): Create resource with status='draft', contributed_by=current_user_id.
On submit for review: Create/update resource with status='under_review'.
Slug is auto-generated from name (not editable by member).
The mandatory_disclaimer for Interpretation is auto-set to the default disclaimer text.
5.7 Rich Text Handling
Member-submitted rich text must be sanitized using the existing RichTextSanitizer.
Rules:
Strip script tags, inline event handlers, and dangerous protocols.
Allow safe formatting: headings, paragraphs, lists, bold, italic, links.
Do not allow image uploads from members (deferred).
6. Member Contribution Management
6.1 Contribution List
Route: GET /account/contributions
Display the member's contributions:
Title
Type badge (SOP, Knowledge, Interpretation)
Status badge (Draft, Under Review, Published, Rejected)
Submitted/updated date
Actions: View, Edit (if draft/rejected), Withdraw (if under_review), Delete (if draft/rejected)
Filters:
By type
By status
Pagination: 15 per page.
6.2 Contribution Detail
Route: GET /account/contributions/{id}
Display:
Full content (rendered).
Status badge.
If rejected: rejection_reason displayed prominently.
If published: link to public page.
Action buttons based on status.
Access control:
Member can only view their own contributions.
Return 403 if accessing another member's contribution.
6.3 Edit Contribution
Route: GET /account/contributions/{id}/edit
Allowed only when status is 'draft' or 'rejected'.
If status is 'under_review', 'published', 'archived', or 'obsolete':
Show read-only view.
Display message: "This contribution is currently under review and cannot be edited."
6.4 Withdraw Contribution
Route: POST /account/contributions/{id}/withdraw
Allowed only when status is 'under_review'.
Action:
Set status to 'draft'.
Log the withdrawal.
Display confirmation message.
6.5 Delete Contribution
Route: DELETE /account/contributions/{id}
Allowed only when status is 'draft' or 'rejected'.
Action:
Hard delete the resource.
Log the deletion.
Redirect to contribution list.
Restriction:
Published contributions cannot be deleted by members.
If a member wants a published contribution removed, they must contact an admin.
6.6 Resubmit Rejected Contribution
Route: POST /account/contributions/{id}/resubmit
Allowed only when status is 'rejected'.
Action:
Member edits the content (goes to edit form).
On save, set status to 'under_review'.
Clear rejection_reason.
Log the resubmission.
7. Admin Review Queue
7.1 Review Queue Route
GET /admin/contributions → Admin\ContributionReviewController@index
Permission: contributions.review
7.2 Navigation Integration
Add a new navigation item to the admin sidebar:
Label: "Contributions"
Icon: Inbox or DocumentCheck
Position: Under "Content" group, or as a top-level item.
Badge: Count of pending contributions (status='under_review', contributed_by not null).
Only visible to users with contributions.review permission.
7.3 Review Queue List
Display pending contributions:
Title
Type badge
Contributor name (with link to contributor profile)
Submitted date
Status
Actions: Review
Filters:
By type (SOP, Knowledge, Interpretation)
By status (default: Under Review, option to show all member contributions)
Default sort: Submitted date ascending (oldest first).
Pagination: 20 per page.
7.4 Empty State
If no pending contributions:
Display empty state: "No contributions awaiting review."
7.5 Permission
Create a new permission: contributions.review
Assign to:
Super Administrator
Content Manager
8. Admin Approve/Reject Workflow
8.1 Review Page
Route: GET /admin/contributions/{id}/review → Admin\ContributionReviewController@show
The review page extends the existing admin edit form for the resource type, with additions:
Contributor banner at the top:
Contributor name and avatar.
Submission date.
Contribution type.
Status badge.
The standard admin edit form for that resource type:
All fields editable by admin.
Admin can modify content, assign categories/tags, set SEO fields.
Approve/Reject action buttons:
"Approve & Publish" button (primary).
"Reject" button (danger).
8.2 Approve Action
Route: POST /admin/contributions/{id}/approve
Permission: contributions.review
Action:
Validate the resource is currently 'under_review'.
Optionally save any admin edits first.
Set status to 'published'.
Set published_at to current timestamp.
Preserve contributed_by (for credit).
Log the approval in audit logs.
Redirect to review queue with success message.
8.3 Reject Action
Route: POST /admin/contributions/{id}/reject
Permission: contributions.review
Action:
Validate the resource is currently 'under_review'.
Require rejection_reason (minimum 10 characters).
Set status to 'rejected'.
Set rejection_reason.
Log the rejection in audit logs.
Redirect to review queue with success message.
Rejection reason input:
Display a modal or inline form requesting the reason.
The reason is visible to the member.
Provide helpful placeholder: "Explain why this contribution was rejected and what the member can improve."
8.4 Admin Editing During Review
Admins can edit the contribution content before approving:
Fix formatting, grammar, or structure.
Add or adjust categories and tags.
Set SEO fields.
Add attachments (for SOPs).
All admin edits are tracked via updated_by and audit logs.
8.5 Audit Logging
Log the following actions:
contributions.submitted (member submits)
contributions.withdrawn (member withdraws)
contributions.resubmitted (member resubmits)
contributions.approved (admin approves)
contributions.rejected (admin rejects)
contributions.deleted (member deletes draft)
Capture:
Acting user ID.
Resource type and ID.
Status transition.
Rejection reason (for rejections).
9. Validation Rules
9.1 Member Submission Validation
Common rules:
name: required, string, 3-150 characters.
short_description: required, string, 10-300 characters.
SOP-specific:
purpose: required, string, max 5000 characters.
scope: required, string, max 5000 characters.
procedure_content: required, string, max 50000 characters.
quality_control: nullable, string, max 5000 characters.
safety_information: nullable, string, max 5000 characters.
references: nullable, string, max 5000 characters.
Knowledge-specific:
content: required, string, max 50000 characters.
learning_objectives: nullable, string, max 2000 characters.
topic_category: nullable, string, in predefined list.
difficulty_level: nullable, string, in [Beginner, Intermediate, Advanced].
Interpretation-specific:
content: required, string, max 50000 characters.
clinical_context: nullable, string, max 5000 characters.
9.2 Admin Review Validation
Approve:
Resource must be 'under_review'.
Resource must have contributed_by set.
Reject:
Resource must be 'under_review'.
rejection_reason: required, string, 10-1000 characters.
9.3 Access Control Validation
Members can only access their own contributions.
Members cannot modify status, contributed_by, or slug directly.
Members cannot set categories, tags, or SEO fields.
Admin actions require contributions.review permission.
10. Notification Strategy
10.1 In-App Notifications (MVP)
For the MVP, notifications are in-app only:
Member sees status changes in their contribution list.
Rejected contributions display the rejection_reason prominently.
Dashboard shows updated counts.
10.2 Email Notifications (Deferred)
Email notifications are deferred until mail configuration is confirmed.
Future email notifications:
Contribution approved.
Contribution rejected (with reason).
Contribution published.
10.3 Admin Notifications
The admin sidebar "Contributions" nav item shows a badge with the count of pending contributions. This serves as the admin notification mechanism.
11. Testing Requirements
11.1 Member Submission Tests
Test member can create a draft SOP contribution.
Test member can create a draft Knowledge contribution.
Test member can create a draft Interpretation contribution.
Test member can submit a draft for review.
Test member cannot submit directly to published.
Test member cannot assign categories or tags.
Test member cannot access another member's contribution.
11.2 Member Management Tests
Test member can edit their draft contribution.
Test member cannot edit an under_review contribution.
Test member can withdraw an under_review contribution.
Test member can delete a draft contribution.
Test member cannot delete a published contribution.
Test member can revise and resubmit a rejected contribution.
11.3 Admin Review Tests
Test admin with contributions.review can view the review queue.
Test admin without contributions.review cannot view the review queue.
Test admin can approve a contribution.
Test approval sets status to published and preserves contributed_by.
Test admin can reject a contribution with a reason.
Test rejection requires a reason.
Test admin can edit contribution before approving.
11.4 Access Control Tests
Test guest cannot access /account/contributions.
Test member cannot access /admin/contributions.
Test member cannot approve their own contribution.
Test status transitions are enforced server-side.
11.5 Validation Tests
Test name is required.
Test content is required for Knowledge and Interpretation.
Test procedure_content is required for SOP.
Test rejection_reason is required for rejection.
Test rich text is sanitized.
Document Completion Check
Requirements Covered
Contribution types and scope defined.
Submission data model changes defined.
Submission status lifecycle defined.
Member submission forms defined.
Member contribution management defined.
Admin review queue defined.
Admin approve/reject workflow defined.
Validation rules defined.
Notification strategy defined.
Testing requirements defined.
Decisions Established
Members can contribute SOPs, Knowledge, and Interpretation only.
Calculators, Tools, and Reference Ranges are admin-only.
Contributions are stored directly in the resource tables with contributed_by set.
A new 'Rejected' status is added to ResourceStatus.
Members cannot edit contributions during review.
Members cannot upload attachments (deferred for security).
Admin assigns taxonomy and SEO fields during review.
Rejection requires a reason.
Notifications are in-app only for MVP.
Dependencies
Approved Phase 03 Resource Architecture.
Approved Phase 04 Content & Reference Architecture.
Approved Phase 06 Public User Accounts and Authentication.
Approved Phase 06 Member Layout and Dashboard.
Files Affected
This document creates:
06_Public_Users_and_Community_Contributions/03_Contribution_Submission_and_Approval_Workflow.md
It dictates schema changes to three resource tables, ResourceStatus enum addition, member submission controllers, and admin review queue.
Open Questions
Should members be allowed to upload a single image for Knowledge articles in the MVP, or defer all file uploads?
Should there be a limit on how many pending contributions a member can have at once?
TBD Decisions
TBD: Final decision on member image uploads.
TBD: Final decision on pending contribution limits.
Implementation Prompt
Implementation Prompt ID
LABGATO-PHASE06-DOC03
Context
LabGato needs a contribution system where members submit content that requires admin approval. This document defines the submission workflow, approval process, and data model changes.
This prompt implements the documentation outcome of the Contribution Submission and Approval Workflow document.
Objective
Create the official LabGato Contribution Submission and Approval Workflow document inside the approved Phase 06 documentation structure.
Files to Create
Create:
06_Public_Users_and_Community_Contributions/03_Contribution_Submission_and_Approval_Workflow.md
Use the approved document content from this documentation output.
Definition of Done
This implementation is complete when:
The Contribution Submission and Approval Workflow file exists.
The file matches the approved Phase 06 file tree.
The file includes the document completion check.
The file includes the implementation prompt.
No unauthorized files were added.