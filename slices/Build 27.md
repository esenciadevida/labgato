labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Member/
│   │   │       ├── DashboardController.php
│   │   │       ├── ProfileController.php
│   │   │       └── SettingsController.php
│   │   └── Requests/
│   │       └── Member/
│   │           ├── UpdateProfileRequest.php
│   │           └── UpdatePasswordRequest.php
├── database/
│   └── migrations/
│       └── xxxx_add_contribution_fields_to_resources.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── member.blade.php
│       ├── components/
│       │   ├── member/
│       │   │   ├── navigation.blade.php
│       │   │   └── stat-card.blade.php
│       │   └── public/
│       │       ├── header.blade.php
│       │       └── user-menu.blade.php
│       └── member/
│           ├── dashboard.blade.php
│           ├── profile.blade.php
│           └── settings.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Member/
            ├── DashboardTest.php
            ├── ProfileTest.php
            ├── SettingsTest.php
            └── HeaderAdaptationTest.php


IMPLEMENTATION PROMPT ID: LABGATO-PHASE06-BUILD27

CONTEXT:
LabGato public user accounts and authentication (Build 26) are complete. We are now implementing Build 27: Member Layout & Dashboard (Slice 12).

This build creates the member area experience: layout, navigation, dashboard, profile, settings, and the adaptive public header.

OBJECTIVE:
Implement the member layout, navigation, dashboard, profile, settings, public header adaptation, and user menu dropdown.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Member area uses horizontal tab navigation (not sidebar).
- Member layout extends the public design system.
- Avatars are initials-based for MVP.
- Account deactivation is soft (is_active=false).
- Header adapts to guest/member/admin states.
- Admins see an "Admin" shortcut link in the header.

FILES TO INSPECT:
- app/Models/User.php
- app/Models/SopResource.php
- app/Models/KnowledgeResource.php
- app/Models/InterpretationResource.php
- resources/views/layouts/public.blade.php
- resources/views/components/public/header.blade.php
- routes/web.php
- app/Enums/ResourceStatus.php

FILES TO CREATE:
- database/migrations/xxxx_add_contribution_fields_to_resources.php
- app/Http/Controllers/Member/DashboardController.php
- app/Http/Controllers/Member/ProfileController.php
- app/Http/Controllers/Member/SettingsController.php
- app/Http/Requests/Member/UpdateProfileRequest.php
- app/Http/Requests/Member/UpdatePasswordRequest.php
- resources/views/layouts/member.blade.php
- resources/views/components/member/navigation.blade.php
- resources/views/components/member/stat-card.blade.php
- resources/views/components/public/user-menu.blade.php
- resources/views/member/dashboard.blade.php
- resources/views/member/profile.blade.php
- resources/views/member/settings.blade.php
- tests/Feature/Member/DashboardTest.php
- tests/Feature/Member/ProfileTest.php
- tests/Feature/Member/SettingsTest.php
- tests/Feature/Member/HeaderAdaptationTest.php

FILES TO MODIFY:
- resources/views/components/public/header.blade.php
- routes/web.php

TASK 1: SCHEMA ADDITIONS

Create migration: add_contribution_fields_to_resources.

Add to sop_resources, knowledge_resources, interpretation_resources:

contributed_by
- Type: unsignedBigInteger
- Nullable: true
- Foreign key to users.id, null on delete
- Indexed

rejection_reason
- Type: text
- Nullable: true

Update the three resource models to include contributed_by and rejection_reason in $fillable.

TASK 2: MEMBER LAYOUT

Create resources/views/layouts/member.blade.php.

Structure:
- Extends the public design system (same theme, fonts, colors).
- Includes the public header (which adapts to member state).
- Includes the member navigation component below the header.
- Includes a main content area with max-w-5xl container.
- Includes the public footer.
- Includes skip link to main content.

The member layout should feel like part of the public site, not the admin portal.

TASK 3: MEMBER NAVIGATION

Create resources/views/components/member/navigation.blade.php.

A horizontal tab navigation with items:
- Dashboard → account.dashboard
- My Contributions → account.contributions.index
- Submit → account.contributions.create
- Profile → account.profile
- Settings → account.settings

Rules:
- Active item highlighted with primary color and indicator.
- Use aria-current="page" on active item.
- Keyboard navigable.
- On mobile: horizontal scroll.

Create resources/views/components/member/stat-card.blade.php.

Props: label, value, icon, color.
A reusable card for dashboard stats.

TASK 4: MEMBER DASHBOARD

Create Member\DashboardController with index method.

Route: GET /account/dashboard → account.dashboard
Middleware: auth, active.user, member

Logic:
- Fetch the authenticated member.
- Query contribution stats across sop_resources, knowledge_resources, interpretation_resources where contributed_by = user id.
- Calculate: total, approved (published), pending (under_review), rejected.
- Fetch last 5 contributions (merged from three tables, ordered by updated_at desc).
- Pass to view.

Create resources/views/member/dashboard.blade.php.

Sections:
1. Welcome header: "Welcome back, {display_name}" + member since date.
2. Quick stats row: 4 stat cards (Total, Approved, Pending, Rejected).
3. Quick actions: 3 buttons (Submit SOP, Submit Knowledge, Submit Interpretation).
4. Recent contributions: list of last 5 with title, type badge, status badge, date.
   - If no contributions, show empty state with CTA.

TASK 5: MEMBER PROFILE

Create Member\ProfileController with edit and update methods.

Routes:
- GET /account/profile → account.profile
- PUT /account/profile → account.profile.update
Middleware: auth, active.user, member

UpdateProfileRequest validation:
- display_name: required, string, 2-50 characters
- bio: nullable, string, max 500 characters

Rules:
- email is read-only (displayed but not editable here).
- profile_slug is not editable.
- On update, redirect back with success message.
- Audit log profile changes.

Create resources/views/member/profile.blade.php.

Display:
- Initials-based avatar.
- Display name, bio, member since, contribution count.
- Edit form for display_name and bio.

TASK 6: ACCOUNT SETTINGS

Create Member\SettingsController with edit, updatePassword, and deactivate methods.

Routes:
- GET /account/settings → account.settings
- PUT /account/settings/password → account.settings.password
- DELETE /account/settings → account.settings.deactivate
Middleware: auth, active.user, member

UpdatePasswordRequest validation:
- current_password: required, must match current password
- new_password: required, min 8, letter + number, confirmed

Deactivation logic:
- Require password confirmation.
- Set is_active = false.
- Log out the user.
- Redirect to home with message.
- Audit log the deactivation.

Create resources/views/member/settings.blade.php.

Sections:
1. Change Password form.
2. Email Preferences (placeholder for future).
3. Deactivate Account (with confirmation dialog).

TASK 7: PUBLIC HEADER ADAPTATION

Modify resources/views/components/public/header.blade.php.

Adapt the header right side based on auth state:

Guest (not authenticated):
- "Log in" link → /login
- "Register" button → /register

Member (authenticated, user_type=member):
- User menu dropdown (avatar + display name).

Admin (authenticated, user_type=admin):
- User menu dropdown.
- Additional "Admin" link → /admin/dashboard.

Create resources/views/components/public/user-menu.blade.php.

An Alpine.js dropdown component:
- Trigger: initials-based avatar + display name.
- Dropdown items: My Dashboard, My Contributions, Submit Contribution, Profile, Settings, divider, Log out.
- For admins: additional "Admin Portal" link.
- Close on outside click or Escape.
- Keyboard navigable.
- Use aria-expanded, aria-haspopup, role="menu", role="menuitem".

TASK 8: ROUTES

Add member routes to routes/web.php:

Route::middleware(['auth', 'active.user', 'member'])->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings', [SettingsController::class, 'deactivate'])->name('settings.deactivate');
});

TASK 9: FEATURE TESTS

Create tests/Feature/Member/DashboardTest.php:
- Test member can access dashboard.
- Test guest is redirected to login.
- Test admin can access dashboard.
- Test dashboard displays welcome message with display_name.
- Test dashboard displays stat cards.
- Test dashboard displays empty state when no contributions.

Create tests/Feature/Member/ProfileTest.php:
- Test member can view profile.
- Test member can update display_name.
- Test member can update bio.
- Test display_name validation is enforced.
- Test email is not editable.

Create tests/Feature/Member/SettingsTest.php:
- Test member can change password with correct current password.
- Test password change fails with incorrect current password.
- Test member can deactivate account.
- Test deactivated user is logged out and cannot log in.

Create tests/Feature/Member/HeaderAdaptationTest.php:
- Test header shows Log in/Register for guests.
- Test header shows user menu for members.
- Test header shows Admin link for admins.
- Test user menu dropdown contains expected links.

ACCESSIBILITY REQUIREMENTS:
- Skip link to main content.
- Semantic HTML (main, nav, header).
- One h1 per page.
- aria-current="page" on active nav item.
- User menu uses proper ARIA (aria-expanded, role=menu, role=menuitem).
- Forms have visible labels and accessible errors.
- Stat cards use semantic markup.

DEFINITION OF DONE:
- contributed_by and rejection_reason columns exist on three resource tables.
- Member layout renders with navigation.
- Member dashboard displays stats, quick actions, and recent contributions.
- Member profile can be viewed and edited.
- Account settings allow password change and deactivation.
- Public header adapts to guest/member/admin states.
- User menu dropdown is accessible and functional.
- All feature tests pass.
- No privilege escalation between members and admins.