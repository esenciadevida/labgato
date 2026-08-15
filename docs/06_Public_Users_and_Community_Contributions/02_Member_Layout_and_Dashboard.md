# 02 — Member Layout and Dashboard

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically Public Site Foundation, Admin Portal Foundation).
- The approved Phase 03 documentation (specifically Design System and UI Theme).
- The approved Phase 06 Public User Accounts and Authentication document.
- The completed Theme Application.

## Defines

This document defines:

- The member area layout architecture.
- The member dashboard content and structure.
- The member profile management pages.
- The member account settings pages.
- The public header behavior for guests, members, and admins.
- The member area navigation structure.
- The responsive design rules for the member area.
- The accessibility requirements for the member area.

## Affects

This document affects:

- The Contribution Submission and Approval Workflow document.
- The Contributor Credit and Profile document.
- The public header component.
- The Blade layout files.
- The member-facing routes and controllers.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the visual and structural foundation for the member area.
- Ensures the member experience is distinct from both the public site and the admin portal.
- Provides the layout into which contribution features will be integrated.
- Defines how the header adapts to different user types.

---

# 1. Purpose

This document establishes the Member Layout and Dashboard architecture for LabGato.

With public user accounts introduced, members need a dedicated area to manage their profile, view their contributions, and submit new content. This area must feel welcoming and simple — distinctly different from the admin portal's density — while maintaining the same professional design system.

This document defines the layout, navigation, dashboard, and profile pages that form the member experience.

---

# 2. Member Area Structure

## 2.1 Routes

The member area lives under `/account`:

| Route | Name | Purpose |
|---|---|---|
| `/account/dashboard` | `account.dashboard` | Member dashboard |
| `/account/profile` | `account.profile` | View/edit profile |
| `/account/contributions` | `account.contributions.index` | List own contributions |
| `/account/contributions/create` | `account.contributions.create` | Start new contribution |
| `/account/settings` | `account.settings` | Account settings |

Middleware for all `/account/*` routes:
- `auth`
- `EnsureUserIsActive`
- `EnsureUserIsMember` (allows both members and admins)

## 2.2 Layout File

Create a dedicated member layout:

Location: `resources/views/layouts/member.blade.php`

Structure:
- Extends the public design system (same header, footer, theme).
- Includes a member-specific sub-navigation.
- Uses the same skip links, meta component, and accessibility patterns as the public site.

Rationale: The member area should feel like part of the public site (not the admin portal), because members are laboratory professionals, not platform staff.

## 2.3 Page Container

Member pages use a max-width container:

- Max width: `max-w-5xl`
- Horizontal padding: `px-4 sm:px-6 lg:px-8`
- Vertical padding: `py-8`

---

# 3. Member Navigation

## 3.1 Navigation Pattern

Use a horizontal tab navigation below the public header (not a sidebar like the admin portal).

Rationale: The member area has fewer sections than the admin portal. Horizontal tabs are simpler and more appropriate for a lightweight member experience.

## 3.2 Navigation Items

| Item | Route | Icon |
|---|---|---|
| Dashboard | `account.dashboard` | Home |
| My Contributions | `account.contributions.index` | Document |
| Submit | `account.contributions.create` | Plus |
| Profile | `account.profile` | User |
| Settings | `account.settings` | Cog |

## 3.3 Navigation Behavior

- Active item: highlighted with primary color and underline/indicator.
- Use `aria-current="page"` on the active item.
- Keyboard navigable.
- On mobile: horizontal scroll or collapse into a menu.

## 3.4 Component

Create: `resources/views/components/member/navigation.blade.php`

Props: none (uses `auth()->user()` internally).

---

# 4. Member Dashboard

## 4.1 Route

`GET /account/dashboard` → `Member\DashboardController@index`

## 4.2 Dashboard Layout

The dashboard displays four sections in a single-column or two-column layout:

1. Welcome header
2. Quick stats row
3. Quick actions
4. Recent contributions list

## 4.3 Welcome Header

Display:
- "Welcome back, {display_name}"
- Member since date
- Link to edit profile

## 4.4 Quick Stats Row

Four stat cards:

| Stat | Description |
|---|---|
| Total Contributions | Count of all member's contributions |
| Approved | Count of published contributions |
| Pending Review | Count of contributions awaiting admin review |
| Rejected | Count of rejected contributions |

Use the `x-ui.card` component with stat styling.

## 4.5 Quick Actions

Three action buttons:

| Action | Route | Description |
|---|---|---|
| Submit SOP | `account.contributions.create?type=sop` | Contribute a Standard Operating Procedure |
| Submit Knowledge Article | `account.contributions.create?type=knowledge` | Contribute an educational article |
| Submit Interpretation | `account.contributions.create?type=interpretation` | Contribute a result interpretation guide |

## 4.6 Recent Contributions List

Display the member's last 5 contributions:
- Title
- Type badge (SOP, Knowledge, Interpretation)
- Status badge (Pending, Approved, Rejected)
- Submitted date
- Link to view details

If no contributions exist, display an empty state with a call-to-action.

---

# 5. Member Profile

## 5.1 Route

`GET /account/profile` → `Member\ProfileController@edit`
`PUT /account/profile` → `Member\ProfileController@update`

## 5.2 Profile Form Fields

| Field | Editable | Validation |
|---|---|---|
| display_name | Yes | Required, 2-50 characters |
| email | Read-only | Displayed but not editable here |
| bio | Yes | Optional, max 500 characters |
| Member since | Read-only | Displayed |
| Total contributions | Read-only | Displayed |

## 5.3 Profile Display

The profile page shows:
- Avatar placeholder (initials-based, no image upload for MVP).
- Display name.
- Bio.
- Member since date.
- Contribution count.
- Edit form.

## 5.4 Avatar Strategy

For the MVP, use initials-based avatars:
- Generate from display_name (e.g., "John Smith" → "JS").
- Use a colored circle with the initials.
- Color derived from a hash of the user ID (for variety).

Image-based avatars are deferred to a future phase.

## 5.5 Public Contributor Profile

The public contributor profile (what others see when viewing a contributor's credited work) is defined in Document 04. This document covers only the member's own profile management.

---

# 6. Account Settings

## 6.1 Route

`GET /account/settings` → `Member\SettingsController@edit`
`PUT /account/settings/password` → `Member\SettingsController@updatePassword`
`DELETE /account/settings` → `Member\SettingsController@deactivate`

## 6.2 Settings Sections

1. Change Password
2. Email Preferences (future, placeholder)
3. Deactivate Account

## 6.3 Change Password

Form fields:
- current_password (required)
- new_password (required, min 8, letter + number)
- new_password_confirmation (required, must match)

Rules:
- Verify current password before allowing change.
- Log the password change in audit logs.
- Invalidate other sessions after password change.

## 6.4 Deactivate Account

Rules:
- Requires password confirmation.
- Sets `is_active = false`.
- Does not delete the user or their contributions.
- Contributions remain published with credit (using display_name).
- User can contact admin to reactivate.
- Log the deactivation.

Confirmation dialog text:
"Deactivating your account will log you out and prevent you from signing in. Your published contributions will remain on the platform with your name credited. This action can be reversed by contacting an administrator. Are you sure?"

---

# 7. Public Header Behavior

## 7.1 Header Adaptation

The public header (`resources/views/components/public/header.blade.php`) must adapt based on authentication state.

## 7.2 Guest State

Display in the header right side:
- "Log in" link → `/login`
- "Register" button → `/register`

## 7.3 Member State

Display in the header right side:
- Avatar (initials-based).
- Display name.
- Dropdown menu with:
  - My Dashboard → `/account/dashboard`
  - My Contributions → `/account/contributions`
  - Submit Contribution → `/account/contributions/create`
  - Profile → `/account/profile`
  - Settings → `/account/settings`
  - Divider
  - Log out → `POST /logout`

## 7.4 Admin State

Display everything from the Member State, plus:
- "Admin" link → `/admin/dashboard` (visible only to user_type=admin)

This allows admins to quickly switch between the member experience and the admin portal.

## 7.5 User Menu Component

Create: `resources/views/components/public/user-menu.blade.php`

An Alpine.js dropdown component that:
- Shows avatar + name.
- Opens dropdown on click.
- Closes on outside click or Escape.
- Keyboard navigable.
- Uses `aria-expanded`, `aria-haspopup`, `role="menu"`, `role="menuitem"`.

---

# 8. Responsive Design

## 8.1 Breakpoints

Use Tailwind's default breakpoints:
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

## 8.2 Mobile Behavior

- Member navigation: horizontal scroll or hamburger menu.
- Dashboard stats: stack vertically (1 column).
- Quick actions: stack vertically.
- User menu: dropdown still works, but trigger may show only avatar (no name).

## 8.3 Desktop Behavior

- Member navigation: horizontal tabs.
- Dashboard stats: 4 columns.
- Quick actions: 3 columns.
- User menu: avatar + name.

---

# 9. Accessibility Requirements

## 9.1 Layout

- Skip link to main content.
- Semantic HTML (`<main>`, `<nav>`, `<header>`).
- One `<h1>` per page.

## 9.2 Navigation

- `aria-current="page"` on active item.
- Keyboard navigable.
- Visible focus states.

## 9.3 User Menu Dropdown

- `aria-expanded` on trigger.
- `aria-haspopup="true"` on trigger.
- `role="menu"` on dropdown.
- `role="menuitem"` on items.
- Close on Escape.
- Return focus to trigger on close.

## 9.4 Forms

- Visible labels.
- Accessible error messages.
- `aria-invalid` on invalid fields.
- Autocomplete attributes where appropriate.

## 9.5 Stat Cards

- Use semantic markup.
- Do not rely on color alone.

---

# 10. Testing Requirements

## 10.1 Access Control Tests

- Test guest is redirected to login when accessing `/account/*`.
- Test member can access `/account/dashboard`.
- Test admin can access `/account/dashboard`.
- Test inactive user is redirected to login.

## 10.2 Dashboard Tests

- Test dashboard displays welcome message with display_name.
- Test dashboard displays correct stat counts.
- Test dashboard displays recent contributions.
- Test dashboard displays empty state when no contributions.

## 10.3 Profile Tests

- Test member can view their profile.
- Test member can update display_name.
- Test member can update bio.
- Test display_name validation is enforced.

## 10.4 Settings Tests

- Test member can change password with correct current password.
- Test password change fails with incorrect current password.
- Test member can deactivate their account.
- Test deactivated user cannot log in.

## 10.5 Header Tests

- Test header shows Log in/Register for guests.
- Test header shows user menu for members.
- Test header shows Admin link for admins.
- Test user menu dropdown opens and closes.

---

# Document Completion Check

## Requirements Covered

- Member area routes and layout defined.
- Member navigation defined.
- Member dashboard structure defined.
- Member profile management defined.
- Account settings defined.
- Public header behavior for all user types defined.
- Responsive design rules defined.
- Accessibility requirements defined.
- Testing requirements defined.

## Decisions Established

- Member area uses a horizontal tab navigation (not sidebar).
- Member layout extends the public design system.
- Avatars are initials-based for MVP (no image upload).
- Account deactivation is soft (is_active=false).
- Header adapts to guest/member/admin states.
- Admins see an "Admin" shortcut link in the header.

## Dependencies

- Approved Phase 06 Public User Accounts and Authentication.
- Approved Phase 03 Design System and UI Theme.
- Completed Theme Application.

## Files Affected

This document creates:

06_Public_Users_and_Community_Contributions/02_Member_Layout_and_Dashboard.md

It dictates the creation of the member layout, navigation, dashboard, profile, settings, and header adaptations.

## Open Questions

1. Should the member dashboard show a "getting started" guide for new members?
2. Should members receive email notifications when their contribution status changes?

## TBD Decisions

- TBD: Final decision on getting-started guide.
- TBD: Final decision on email notifications (depends on mail configuration).

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE06-DOC02

## Context

LabGato needs a member area for public users to manage their profile and contributions. This document defines the layout, dashboard, profile, settings, and header behavior.

This prompt implements the documentation outcome of the Member Layout and Dashboard document.

## Objective

Create the official LabGato Member Layout and Dashboard document inside the approved Phase 06 documentation structure.

## Files to Create

Create:

06_Public_Users_and_Community_Contributions/02_Member_Layout_and_Dashboard.md

Use the approved document content from this documentation output.

## Definition of Done

This implementation is complete when:

- The Member Layout and Dashboard file exists.
- The file matches the approved Phase 06 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.