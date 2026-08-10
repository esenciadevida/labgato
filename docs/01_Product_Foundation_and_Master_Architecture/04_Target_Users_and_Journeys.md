# 04 — Target Users and Journeys

## Depends On

This document depends on:

- The approved LabGato Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Current State Assessment.
- The decision that LabGato is a professional laboratory toolkit.
- The decision that LabGato is not a patient, hospital, or LIS system.

## Defines

This document defines:

- The approved primary user groups.
- The excluded user groups.
- The expected needs of each approved user group.
- The main user journeys.
- The administrative user journeys.
- The anonymous user journeys.
- The journey constraints.
- The relationship between user journeys and future feature documentation.

## Affects

This document affects:

- Information architecture.
- Navigation design.
- Page hierarchy.
- Search architecture.
- Admin portal design.
- Authentication architecture.
- Authorization architecture.
- Accessibility requirements.
- Analytics event planning.
- Future vertical-slice selection.
- Future UI/UX documentation.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Defines who the product is for.
- Defines who the product is not for.
- Guides page structure and navigation.
- Guides feature prioritization.
- Guides admin workflow design.
- Prevents unauthorized user categories from being introduced.

---

# 1. Purpose

This document defines the target users of LabGato and the primary journeys those users are expected to take.

The purpose is to ensure that future pages, tools, calculators, references, SOP resources, knowledge resources, search, admin features, and AI features are designed for the correct audience.

LabGato is designed for laboratory professionals and related learners.

It is not designed for patients, caregivers, doctors seeking patient result access, or public clinical decision-making.

---

# 2. Primary User Groups

The following user groups are approved as primary target users.

## 2.1 Medical Laboratory Scientists

Medical Laboratory Scientists are a primary user group.

Their expected needs include:

- Quick access to laboratory calculators.
- Quick access to reference ranges.
- Access to SOP resources.
- Access to result interpretation references.
- Access to laboratory knowledge resources.
- Search across laboratory resources.
- Reliable and professional reference material.
- Tools that reduce manual calculation effort.
- Organized resources for daily laboratory work.

## 2.2 Medical Laboratory Science Students

Medical Laboratory Science students are a primary user group.

Their expected needs include:

- Educational laboratory content.
- Calculation practice and explanation.
- Reference range learning.
- SOP understanding.
- Result interpretation learning.
- Search across laboratory topics.
- Clear explanations.
- Accessible learning resources.
- Organized study material.

## 2.3 Laboratory Technicians

Laboratory Technicians are a primary user group.

Their expected needs include:

- Practical laboratory tools.
- Quick reference lookup.
- Calculation utilities.
- Procedure references.
- Simple and fast navigation.
- Mobile-friendly access.
- Reliable resource organization.
- Clear instructions and reference details.

## 2.4 Other Relevant Laboratory Professionals

Other relevant laboratory professionals may use LabGato where appropriate.

This category may include, subject to future approval and content relevance:

- Laboratory supervisors
- Laboratory quality officers
- Laboratory educators
- Laboratory researchers
- Laboratory trainees
- Other approved laboratory-related professionals

This category must not be expanded into non-laboratory clinical users unless explicitly approved.

---

# 3. Administrative User Groups

LabGato requires administrative users to manage the platform.

Administrative users are not the public audience of the resource content, but they are essential system users.

## 3.1 Administrators

Administrators are expected to manage the platform.

Their expected needs include:

- Secure login.
- Dashboard overview.
- Management of approved resources.
- User management where approved.
- Administrator management where approved.
- Role and permission management where approved.
- System settings management where approved.
- Audit log review where approved.
- Analytics review where approved.
- Content workflow management where approved.

## 3.2 Content Managers

Content Managers may be introduced later if approved.

Possible responsibilities may include:

- Creating resources.
- Editing resources.
- Organizing categories.
- Preparing content for review.
- Publishing approved content.
- Managing metadata.

This role is not fully confirmed until documented in the User Roles and RBAC document.

## 3.3 Technical Administrators

Technical Administrators may be introduced later if approved.

Possible responsibilities may include:

- System settings.
- Security settings.
- AI configuration where approved.
- SEO settings.
- Analytics settings.
- Audit log review.
- Deployment-related configuration.

This role is not fully confirmed until documented in the User Roles and RBAC document.

---

# 4. Excluded User Groups

The following user groups are excluded from the current LabGato scope unless explicitly approved later.

## 4.1 Patients

Patients are not target users.

LabGato must not be designed as a patient portal.

## 4.2 Caregivers

Caregivers are not target users.

LabGato must not provide caregiver-facing patient tools.

## 4.3 Doctors Seeking Patient Results

Doctors are not current target users unless explicitly approved later.

LabGato must not provide doctor result access, referral tools, or patient clinical workflows.

## 4.4 General Public Seeking Personal Medical Advice

The general public may view public educational resources where applicable, but LabGato must not be designed as a personal medical advice platform.

## 4.5 Hospital Administrators

Hospital administrators are not target users.

LabGato is not a hospital management system.

---

# 5. User Needs Summary

The following user needs should guide LabGato design.

## 5.1 Speed

Users should be able to find resources quickly.

Important resources should not require unnecessary steps.

## 5.2 Accuracy

Laboratory resources should be structured, clear, and carefully managed.

Calculators should be validated.

Reference ranges should include appropriate context.

SOP resources should include version and status information where appropriate.

## 5.3 Organization

Resources should be organized by clear categories and relationships.

Users should understand where they are and what type of resource they are viewing.

## 5.4 Accessibility

Users should be able to use LabGato with keyboard, mouse, touch, and screen reader support where appropriate.

Forms, calculators, tables, and navigation must be accessible.

## 5.5 Mobile Use

Laboratory users may access LabGato on mobile devices.

Important journeys must work well on small screens.

## 5.6 Trust

The interface must feel professional and scientific.

It must avoid fake statistics, decorative filler, and generic marketing language.

## 5.7 Discoverability

Users should be able to discover resources through:

- Navigation
- Categories
- Search
- Related resources
- Clear page titles
- Structured content

## 5.8 Safe Administration

Administrators must be able to manage resources securely.

Administrative actions must be authorized, validated, and logged where appropriate.

---

# 6. Anonymous Public User Journey

An anonymous public user is a visitor who has not logged in.

This user may access public resources if those resources are approved for public access.

The final access model must be documented later.

## Journey 6.1 Discover LabGato

Steps:

1. The user arrives at LabGato through search, a shared link, or direct access.
2. The user sees a clear explanation of what LabGato provides.
3. The user understands that LabGato is a laboratory toolkit.
4. The user sees primary resource areas.
5. The user chooses a resource area.

Success outcome:

The user understands the platform purpose and can navigate to relevant resources.

Failure states:

- The purpose is unclear.
- Navigation is confusing.
- The page does not load.
- The resource areas are hidden.
- The page is not accessible.

## Journey 6.2 Find a Calculator

Steps:

1. The user navigates to calculators or tools.
2. The user browses categories or uses search.
3. The user selects a calculator.
4. The user reads the calculator description and instructions.
5. The user enters required values.
6. The user receives the calculated result.
7. The user reviews references or notes where available.

Success outcome:

The user completes a valid calculation and understands the result context.

Failure states:

- Calculator not found.
- Inputs unclear.
- Validation unclear.
- Calculation fails.
- Result is not explained.
- Reference information is missing.
- Accessibility barriers prevent completion.

## Journey 6.3 Find a Reference Range

Steps:

1. The user navigates to reference ranges or uses search.
2. The user searches or filters by test, analyte, category, or related metadata.
3. The user selects the appropriate reference range.
4. The user reviews the range details.
5. The user reviews population, specimen, unit, age, sex, source, and status where available.

Success outcome:

The user finds the correct reference range and understands its context.

Failure states:

- Reference range not found.
- Ambiguous search results.
- Missing context.
- Unclear units.
- Outdated or unversioned information.
- Accessibility barriers.

## Journey 6.4 Find an SOP Resource

Steps:

1. The user navigates to SOP resources or uses search.
2. The user browses categories or enters a search query.
3. The user selects the SOP resource.
4. The user reviews the SOP content.
5. The user checks version, status, and metadata where available.

Success outcome:

The user finds the relevant SOP reference and understands its status.

Failure states:

- SOP not found.
- Version unclear.
- Status unclear.
- Content inaccessible.
- Access restriction not explained.

## Journey 6.5 Find Educational Content

Steps:

1. The user navigates to knowledge or education resources.
2. The user selects a topic or searches.
3. The user opens the resource.
4. The user reads or studies the content.
5. The user navigates to related resources if needed.

Success outcome:

The user finds useful educational content and can continue learning.

Failure states:

- Content not found.
- Content unclear.
- Navigation ends unexpectedly.
- Related resources missing.
- Accessibility barriers.

## Journey 6.6 Search for a Resource

Steps:

1. The user uses the search feature.
2. The user enters a query.
3. The system returns relevant results.
4. The user selects a result.
5. The user reaches the correct resource page.

Success outcome:

The user finds the intended resource efficiently.

Failure states:

- No results.
- Irrelevant results.
- Slow search.
- Unclear result labels.
- Broken result links.
- Search not accessible.

---

# 7. Registered User Journey

Public registered user accounts are not confirmed.

If public user accounts are approved later, the following journeys may apply.

## Journey 7.1 Create Account

Possible steps:

1. The user chooses to register.
2. The user completes a registration form.
3. The system validates the information.
4. The system creates a deactivated or pending account where required.
5. The user verifies the account if verification is required.
6. The user logs in.

Status:

TBD — Public user accounts are not currently approved.

## Journey 7.2 Log In

Possible steps:

1. The user opens the login page.
2. The user enters credentials.
3. The system validates credentials securely.
4. The system creates a secure session.
5. The user reaches the appropriate authenticated area.

Status:

TBD for public users.

Required for administrators.

## Journey 7.3 Deactivate Account

Possible steps:

1. The user requests account deletion or deactivation.
2. The system asks for confirmation.
3. The system deactivates the account.
4. The system invalidates sessions.
5. The system preserves required records according to retention policy.
6. The system applies approved privacy rules.

Status:

TBD for public users.

Required where account functionality exists.

---

# 8. Administrator Journeys

Administrators use the Admin Portal to manage LabGato.

## Journey 8.1 Administrator Login

Steps:

1. The administrator opens the admin login page.
2. The administrator enters credentials.
3. The system validates credentials.
4. The system applies login throttling where required.
5. The system creates a secure session.
6. The administrator reaches the Admin Portal dashboard.

Success outcome:

The administrator securely accesses the Admin Portal.

Failure states:

- Invalid credentials.
- Locked or deactivated account.
- Login rate limit reached.
- Session failure.
- Unclear error message.

## Journey 8.2 View Dashboard

Steps:

1. The administrator logs in.
2. The administrator views the dashboard.
3. The dashboard displays real data where available.
4. The dashboard shows honest empty states when no data exists.
5. The administrator navigates to relevant modules.

Success outcome:

The administrator understands current platform activity and can navigate to management areas.

Failure states:

- Fake statistics.
- Broken widgets.
- Missing permissions.
- Slow dashboard.
- Inaccessible components.

## Journey 8.3 Create a Resource

Steps:

1. The administrator opens the relevant resource module.
2. The administrator selects create.
3. The administrator enters required information.
4. The system validates the input.
5. The system saves the resource.
6. The system displays a success message.
7. The resource appears in the appropriate list.

Success outcome:

The administrator creates a valid resource.

Failure states:

- Validation errors.
- Permission denied.
- Save failure.
- Duplicate slug or identifier.
- Missing required fields.
- Unclear error message.

## Journey 8.4 Edit a Resource

Steps:

1. The administrator opens the resource list.
2. The administrator searches or filters for the resource.
3. The administrator opens the resource.
4. The administrator edits permitted fields.
5. The system validates changes.
6. The system saves changes.
7. The system records the change where audit logging applies.

Success outcome:

The resource is updated securely and correctly.

Failure states:

- Unauthorized edit attempt.
- Validation failure.
- Lost changes.
- Broken relationship.
- Audit logging failure.

## Journey 8.5 Publish or Archive a Resource

Steps:

1. The administrator opens the resource.
2. The administrator changes the status.
3. The system validates permission and workflow rules.
4. The system saves the new status.
5. The public visibility updates according to the access model.
6. The system records the action where audit logging applies.

Success outcome:

The resource status changes correctly.

Failure states:

- Unauthorized status change.
- Incorrect public visibility.
- Broken workflow.
- Missing audit record.

## Journey 8.6 Manage Administrators

Steps:

1. An authorized administrator opens administrator management.
2. The administrator views existing administrators.
3. The administrator creates, edits, activates, deactivates, or assigns roles where permitted.
4. The system validates permissions.
5. The system prevents privilege escalation.
6. The system records the action.

Success outcome:

Administrator accounts are managed securely.

Failure states:

- Privilege escalation.
- Unauthorized role assignment.
- Missing audit record.
- Insecure session handling.

## Journey 8.7 Review Audit Logs

Steps:

1. An authorized administrator opens audit logs.
2. The administrator filters by event, user, resource, or date where available.
3. The administrator reviews relevant activity.
4. The administrator investigates suspicious activity if needed.

Success outcome:

Important administrative and security actions are reviewable.

Failure states:

- Missing logs.
- Incomplete logs.
- Unauthorized access to logs.
- Logs exposing sensitive data.

## Journey 8.8 Review Analytics

Steps:

1. An authorized administrator opens analytics.
2. The administrator views approved metrics.
3. The administrator reviews resource usage, search usage, or tool usage where available.
4. The administrator uses the data to improve content or prioritization.

Success outcome:

The administrator can review real, privacy-conscious analytics.

Failure states:

- Fake data.
- Missing data.
- Sensitive data exposure.
- Inaccurate metrics.

---

# 9. Cross-Cutting Journey Requirements

All LabGato journeys must support the following requirements.

## 9.1 Loading States

Where data loading occurs, the user must see a clear loading state.

The interface must not appear frozen or broken.

## 9.2 Empty States

Where no data exists, the user must see a clear empty state.

Empty states must be honest and useful.

They must not display fake data.

## 9.3 Error States

Where an operation fails, the user must see a clear error state.

Error messages must be safe and user-appropriate.

They must not expose sensitive technical details.

## 9.4 Success States

Where an operation succeeds, the user must see clear confirmation.

Success feedback should be visible and accessible.

## 9.5 Validation States

Where input is invalid, the user must see clear validation feedback.

Validation messages must identify the problem without exposing sensitive data.

## 9.6 Keyboard Accessibility

Important journeys must be completable by keyboard.

Focus order must be logical.

Focus must be visible.

## 9.7 Screen Reader Support

Important labels, errors, results, and status changes must be accessible to screen readers where appropriate.

## 9.8 Mobile Responsiveness

Important journeys must work on mobile devices.

Forms, tables, calculators, navigation, and search must remain usable on small screens.

## 9.9 Clear Navigation

Users must know:

- Where they are.
- What resource they are viewing.
- How to go back.
- How to reach related resources.
- How to reach main sections.

## 9.10 No Dead Ends

Journeys must not end in dead pages.

Every action should lead to a clear result or explain why it cannot be completed.

---

# 10. Journey Constraints

The following constraints apply to all journeys.

## 10.1 No Patient Journeys

LabGato must not create patient-facing journeys unless explicitly approved later.

## 10.2 No Diagnostic Journeys

LabGato must not create journeys that claim to diagnose conditions or replace clinical judgment.

## 10.3 No Unapproved AI Journeys

AI journeys must not be implemented before the AI architecture is approved.

## 10.4 No Fake Functionality

No journey may include buttons, links, forms, or actions that do not work.

## 10.5 No Unauthorized Admin Access

Administrative journeys must be protected by authentication and authorization.

## 10.6 No Sensitive Data Exposure

Journeys must not expose:

- Secrets
- API keys
- Database credentials
- Internal stack traces
- Other users’ private data
- Sensitive audit information
- Unauthorized administrative links

---

# 11. Journey-to-Feature Mapping

The following mapping shows how journeys relate to future feature documentation.

| Journey Area | Related Future Documentation |
|---|---|
| Resource discovery | Information Architecture |
| Calculator usage | Tool/Calculator Specification |
| Reference lookup | Reference Range Specification |
| SOP access | SOP Specification |
| Search | Search Architecture |
| Admin login | Authentication Architecture |
| Admin management | Admin Portal Specification |
| Role control | User Roles and RBAC |
| Audit review | Security Architecture |
| Analytics review | Analytics Specification |
| AI assistance | AI Architecture, later phase |

This mapping ensures that user journeys are connected to future implementation documents.

---

# 12. Priority Journey Recommendations

For the first implementation slices, the following journeys should receive priority once the relevant architecture is approved.

## High Priority

- Administrator login.
- Administrator dashboard access.
- Resource creation.
- Resource editing.
- Resource listing.
- Public resource detail viewing.
- Search or category navigation.
- Basic accessibility support.
- Basic error and empty states.

## Medium Priority

- Resource publishing workflow.
- Resource versioning where required.
- Advanced search.
- Analytics review.
- Audit log review.
- Related resources.

## Lower Priority Until Approved

- Public user accounts.
- Saved favorites.
- Personalization.
- AI assistant.
- Advanced analytics.
- Export tools.
- Print-friendly views.

---

# Document Completion Check

## Requirements Covered

- Primary user groups defined.
- Administrative user groups defined.
- Excluded user groups defined.
- User needs defined.
- Anonymous public journeys defined.
- Registered user journeys marked TBD where appropriate.
- Administrator journeys defined.
- Cross-cutting journey requirements defined.
- Journey constraints defined.
- Journey-to-feature mapping defined.
- Priority journey recommendations defined.

## Decisions Established

- LabGato targets laboratory professionals and related learners.
- Patients are excluded from current scope.
- Doctors seeking patient results are excluded from current scope.
- Administrators are required.
- Public user accounts are not confirmed.
- Journeys must support loading, empty, error, success, and validation states.
- Journeys must be accessible and responsive.
- AI journeys are deferred.

## Dependencies

- Approved LabGato master directive.
- Approved Project Charter.
- Approved Product Scope and Non-Goals.
- Approved Current State Assessment.
- Approved Phase 01 file tree.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/04_Target_Users_and_Journeys.md

It affects future information architecture, admin portal, authentication, authorization, accessibility, analytics, and feature documentation.

## Open Questions

1. Which resource type should be prioritized for the first user journey?
2. Should public resources be fully public or partially restricted?
3. Are public user accounts required in a future phase?
4. Which administrator roles are required initially?
5. Which analytics events are approved?
6. Should SOP resources be public or restricted?
7. Should reference ranges be public or restricted?
8. What search behavior is required for MVP?
9. What content is available for the first journeys?
10. What accessibility conformance level is realistically achievable for MVP?

## TBD Decisions

- TBD: Public user account model.
- TBD: Final administrator role model.
- TBD: Final resource access model.
- TBD: Final analytics event list.
- TBD: Final search behavior.
- TBD: Final accessibility testing method.
- TBD: First vertical-slice journey selection.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE01-DOC04

## Context

LabGato has adopted the new master directive. The product requires a clear definition of target users and user journeys before information architecture, page structure, admin design, and feature implementation can proceed.

This prompt implements the documentation outcome of the Target Users and Journeys document.

## Objective

Create the official LabGato Target Users and Journeys document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository should contain an approved user and journey foundation for future architecture documents.

## Existing Architecture

Relevant approved decisions:

- LabGato is a professional laboratory toolkit.
- LabGato is not a patient, hospital, or LIS system.
- Public user accounts are not confirmed.
- Administrators are required.
- AI is deferred until the resource architecture is sufficiently mature.
- All features must support real loading, empty, error, success, and validation states.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- 01_Project_Charter.md
- 02_Current_State_Assessment.md
- 03_Product_Scope_and_Non_Goals.md
- Any existing user persona documents
- Any existing journey maps
- Any existing MLS Toolkit documentation

If no files exist, record that the documentation structure is being initialized.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/04_Target_Users_and_Journeys.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

01_Product_Foundation_and_Master_Architecture/04_Target_Users_and_Journeys.md

Do not modify application code.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets.
- Do not include API keys.
- Do not include database credentials.
- Do not include hosting credentials.
- Do not include personal data.
- Do not expose private infrastructure details beyond what is necessary.

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- No decorative-only content.
- No ambiguous link text.

## SEO Requirements

No public SEO impact.

This is an internal/documentation file.

Do not make admin or private documentation indexable if later served by the application.

## Analytics Requirements

No analytics events are required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file defines approved user groups.
- The file defines excluded user groups.
- The file defines user journeys.
- The file does not introduce patient-facing journeys as approved.
- The file does not introduce AI journeys as currently approved.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future Phase 01 documents where relevant.

It must also be referenced in the Phase 01 implementation map after all Phase 01 documents are completed.

## Definition of Done

This implementation is complete when:

- The Target Users and Journeys file exists.
- The file matches the approved Phase 01 file tree.
- The file defines approved users.
- The file defines excluded users.
- The file defines main journeys.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.