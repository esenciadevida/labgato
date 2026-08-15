labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── AdministratorController.php
│   │   │       └── UserManagementController.php
│   │   └── Livewire/
│   │       └── Public/
│   │           └── Calculators/
│   │               └── CalculatorRunner.php
│   ├── Models/
│   │   └── User.php
│   └── View/
│       └── Composers/
│           └── PublicContentComposer.php
├── database/
│   └── seeders/
│       └── UserSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── administrators/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   └── users/
│       │       ├── index.blade.php
│       │       └── edit.blade.php
│       ├── components/
│       │   ├── admin/
│       │   │   └── sidebar.blade.php
│       │   └── public/
│       │       ├── popup.blade.php
│       │       └── announcement-banner.blade.php
│       ├── layouts/
│       │   └── public.blade.php
│       └── livewire/
│           └── public/
│               └── calculators/
│                   └── calculator-runner.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        ├── Admin/
        │   └── UserSystemTest.php
        └── PublicSite/
            ├── PopupAnnouncementDisplayTest.php
            └── CalculatorOptionsTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD42-USER-SYSTEM-AND-FIXES

CONTEXT:
LabGato needs a clearer, more professional user management system distinguishing Administrators from Users, plus fixes for pop-ups/announcements not displaying and calculator options rendering empty.

OBJECTIVE:
PART A: Refine the user system with a clear Administrator/User distinction and full super-admin CRUD + active/inactive control.
PART B: Fix pop-ups and announcements so they render on the public frontend.
PART C: Fix calculator input options (select/radio) so they display on the frontend.

EXISTING ARCHITECTURE:
- users table with user_type ('admin' or 'member').
- User Management built in Build 24.
- Pop-ups and Announcements built in Build 41.
- Calculator schema with input options (e.g., eGFR sex: ["female","male"]).
- CalculatorRunner Livewire component.

FILES TO INSPECT:
- app/Models/User.php
- app/Http/Controllers/Admin/UserController.php
- resources/views/components/admin/sidebar.blade.php
- resources/views/layouts/public.blade.php
- resources/views/components/public/popup.blade.php
- resources/views/components/public/announcement-banner.blade.php
- app/Http/Livewire/Public/Calculators/CalculatorRunner.php
- resources/views/livewire/public/calculators/calculator-runner.blade.php
- app/Models/Calculator.php (schema structure)

============================================================
PART A — PROFESSIONAL USER SYSTEM
============================================================

TASK A1: CLARIFY TERMINOLOGY & MODEL

Modify app/Models/User.php:
- Add helper methods: isAdministrator(), isRegularUser().
- Add scopes: scopeAdministrators (user_type='admin'), scopeUsers (user_type='member').
- Ensure isAdmin() and isMember() align with the new terminology.

Terminology mapping:
- user_type='admin'  → "Administrator"
- user_type='member' → "User"

TASK A2: ADMINISTRATOR MANAGEMENT

Create app/Http/Controllers/Admin/AdministratorController.php.

CRUD for administrators (user_type='admin'):
- index: list administrators.
- create: form to add a new administrator.
- store: create administrator (assign role).
- edit: edit administrator.
- update: update administrator.
- toggleActive: activate/deactivate.
- destroy: delete (soft) administrator.

Permission: users.manage (or administrators.manage).
Restriction: Super Admin only can manage administrators.
Safety: Cannot deactivate/delete yourself. Cannot delete the last Super Admin.

Create views:
- resources/views/admin/administrators/index.blade.php
  - Table: name, email, role, status (active/inactive badge), last login, actions.
  - "Add Administrator" button.
  - Active/inactive toggle per row.
  - Edit / Delete actions.
- resources/views/admin/administrators/create.blade.php
- resources/views/admin/administrators/edit.blade.php

TASK A3: USER MANAGEMENT (PUBLIC USERS)

Create app/Http/Controllers/Admin/UserManagementController.php.

Management for public users (user_type='member'):
- index: list users with filters (status, search).
- edit: edit user (view details, change status).
- toggleActive: activate/deactivate.
- destroy: delete (soft) user.

Permission: users.manage.
Note: Super admins and admins can view users. Only Super Admin can delete.

Create views:
- resources/views/admin/users/index.blade.php
  - Table: name, email, status, joined date, contributions count, last login, actions.
  - Search and filter by status.
  - Active/inactive toggle per row.
  - View / Edit / Delete actions.
- resources/views/admin/users/edit.blade.php
  - View user details.
  - Toggle active/inactive.
  - View their contributions.

TASK A4: UPDATE NAVIGATION

Modify resources/views/components/admin/sidebar.blade.php.

Under a "People" or "User Management" group, add two clear items:
- "Administrators" → admin.administrators.index (icon: Shield/UserCog)
- "Users" → admin.users.index (icon: Users)

Replace or reorganize the existing single "Users" link to reflect this split.
Only show "Administrators" to Super Admin.

TASK A5: VERIFY EXISTING USERS

Modify database/seeders/UserSeeder.php (or run a one-time fix):
- Ensure all users who can access the admin portal have user_type='admin'.
- Ensure public members have user_type='member'.
- Ensure the primary Super Admin is active and user_type='admin'.

TASK A6: ROUTES

Add to routes/admin.php:

Route::middleware(['permission:users.manage'])->group(function () {
    // Administrators (Super Admin only for create/delete)
    Route::prefix('administrators')->name('administrators.')->group(function () {
        Route::get('/', [AdministratorController::class, 'index'])->name('index');
        Route::get('/create', [AdministratorController::class, 'create'])->name('create');
        Route::post('/', [AdministratorController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdministratorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdministratorController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-active', [AdministratorController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}', [AdministratorController::class, 'destroy'])->name('destroy');
    });

    // Users (public members)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
});

============================================================
PART B — FIX POP-UPS & ANNOUNCEMENTS DISPLAY
============================================================

TASK B1: DIAGNOSE THE ISSUE

Inspect how pop-ups and announcements are passed to the public layout.

Common causes:
- No view composer or controller passing active pop-ups/announcements to layouts/public.blade.php.
- The components are included but receive no data.
- The active scope returns nothing (is_active=false or schedule mismatch).

TASK B2: CREATE A VIEW COMPOSER

Create app/View/Composers/PublicContentComposer.php.

This composer attaches active pop-ups and announcements to the public layout:

class PublicContentComposer
{
    public function compose(View $view)
    {
        $view->with('activePopups', Popup::active()->get());
        $view->with('activeAnnouncements', Announcement::active()->orderBy('created_at', 'desc')->get());
    }
}

Register the composer in a service provider (e.g., AppServiceProvider boot method):
View::composer('layouts.public', PublicContentComposer::class);

TASK B3: VERIFY ACTIVE SCOPES

Inspect app/Models/Popup.php and app/Models/Announcement.php.

Ensure the scopeActive method correctly filters:
- is_active = true.
- (starts_at is null OR starts_at <= now).
- (ends_at is null OR ends_at >= now).

TASK B4: VERIFY PUBLIC LAYOUT INTEGRATION

Inspect resources/views/layouts/public.blade.php.

Ensure:
- <x-public.announcement-banner :announcements="$activeAnnouncements" /> is at the top (above header).
- <x-public.popup :popups="$activePopups" /> is at the bottom (before </body>).
- The variables are correctly passed.

TASK B5: VERIFY COMPONENTS

Inspect resources/views/components/public/popup.blade.php and announcement-banner.blade.php.

Ensure:
- They accept the passed data.
- They render nothing if the collection is empty.
- They check localStorage for dismissed state.
- They have correct Alpine.js logic.
- Dark mode classes are applied.

TASK B6: TEST WITH A SEED

Create a test or manually:
- Create an active popup (is_active=true, no schedule restrictions).
- Create an active announcement.
- Visit the public home page.
- Verify both render.

============================================================
PART C — FIX CALCULATOR OPTIONS DISPLAY
============================================================

TASK C1: DIAGNOSE THE ISSUE

Inspect the calculator schema structure. For eGFR, the schema has:
"inputs": [ { "name": "sex", "type": "select", "options": ["female", "male"] } ]

Inspect app/Http/Livewire/Public/Calculators/CalculatorRunner.php and resources/views/livewire/public/calculators/calculator-runner.blade.php.

Determine why the options array is not rendering on the frontend.

Common causes:
- The schema JSON isn't being decoded properly.
- The view isn't iterating over the options array for select/radio inputs.
- The input type isn't recognized as needing options.

TASK C2: FIX THE CALCULATOR RUNNER

Modify the CalculatorRunner Livewire component and its view.

Ensure:
- The schema is decoded from JSON to an array/object.
- For each input, the component passes the full input definition (including options) to the view.
- The view correctly renders:
  - type="select" → <select> with <option> elements from options array.
  - type="radio" → radio buttons from options array.
  - type="checkbox" → checkboxes from options array.

Example view logic for a select input:
@if($input['type'] === 'select')
    <select wire:model="inputs.{{ $input['name'] }}" ...>
        <option value="">Select...</option>
        @foreach($input['options'] as $option)
            <option value="{{ $option }}">{{ ucfirst($option) }}</option>
        @endforeach
    </select>
@endif

TASK C3: VERIFY SCHEMA STORAGE

Inspect app/Models/Calculator.php and the admin calculator form.

Ensure:
- The options array is correctly saved in the schema JSON.
- The schema is retrieved correctly on the public page.
- No data is lost between admin save and public render.

TASK C4: TEST CALCULATOR OPTIONS

Create tests/Feature/PublicSite/CalculatorOptionsTest.php:
- Test a calculator with select options renders the options on the frontend.
- Test eGFR specifically shows male/female options.
- Test selecting an option and calculating works.
- Test a calculator without options still renders correctly.

============================================================
TESTING & VERIFICATION
============================================================

TASK D1: USER SYSTEM TESTS

Create tests/Feature/Admin/UserSystemTest.php:
- Test super admin can view administrators list.
- Test super admin can create/edit/deactivate an administrator.
- Test super admin can view users list.
- Test super admin can deactivate a user.
- Test non-super-admin cannot manage administrators.
- Test cannot deactivate yourself.
- Test cannot delete the last super admin.
- Test administrators and users are clearly separated.

TASK D2: POPUP/ANNOUNCEMENT DISPLAY TESTS

Create tests/Feature/PublicSite/PopupAnnouncementDisplayTest.php:
- Test active popup renders on public pages.
- Test active announcement renders on public pages.
- Test inactive popup/announcement does not render.
- Test view composer passes data to the layout.

TASK D3: MANUAL VERIFICATION

After implementation, manually verify:
- Navigate to /admin/administrators and /admin/users — both show clear, professional lists.
- Create an active popup and announcement, visit the public home page, confirm both display.
- Open the eGFR calculator, confirm the male/female dropdown renders with options.
- Select an option and calculate, confirm it works.

DEFINITION OF DONE:
- Administrators and Users are clearly separated in the admin UI.
- Super admin can CRUD both and toggle active/inactive.
- Existing admin-portal users are correctly user_type='admin'.
- Pop-ups render on the public frontend when active.
- Announcements render on the public frontend when active.
- Calculator select/radio options render correctly on the frontend.
- eGFR shows male/female options.
- All feature tests pass.
- Dark mode renders correctly for all new/fixed elements.