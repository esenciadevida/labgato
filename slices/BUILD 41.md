labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── PopupController.php
│   │   │       └── AnnouncementController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StorePopupRequest.php
│   │           ├── UpdatePopupRequest.php
│   │           ├── StoreAnnouncementRequest.php
│   │           └── UpdateAnnouncementRequest.php
│   ├── Models/
│   │   ├── Popup.php
│   │   └── Announcement.php
│   └── View/
│       └── Components/
│           └── Public/
│               ├── PopupRenderer.php
│               └── AnnouncementRenderer.php
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_popups_table.php
│   │   └── xxxx_create_announcements_table.php
│   └── seeders/
│       └── RolePermissionSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── popups/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   └── announcements/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       └── edit.blade.php
│       ├── components/
│       │   ├── public/
│       │   │   ├── popup.blade.php
│       │   │   └── announcement-banner.blade.php
│       │   └── admin/
│       │       └── sidebar.blade.php
│       └── layouts/
│           └── public.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        ├── Admin/
        │   ├── PopupManagementTest.php
        │   └── AnnouncementManagementTest.php
        └── PublicSite/
            ├── PopupDisplayTest.php
            └── AnnouncementDisplayTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD41-ADMIN-COMMUNICATION

CONTEXT:
LabGato super admins need to communicate with public visitors via pop-ups and announcement banners. This build adds both features plus polishes the existing contact reply system.

OBJECTIVE:
Implement a pop-up manager, an announcement banner system, and verify/enhance the contact reply feature. All three are admin-controlled and displayed on public pages.

EXISTING ARCHITECTURE:
- Contact Us system with reply (Build 32).
- Admin system with RBAC (Build 24).
- Abyss Blue theme with dark mode (Build 35).
- Public layout (layouts/public.blade.php).

FILES TO INSPECT:
- resources/views/layouts/public.blade.php
- resources/views/components/admin/sidebar.blade.php
- app/Http/Controllers/Admin/ContactMessageController.php
- database/seeders/RolePermissionSeeder.php
- routes/admin.php

FILES TO CREATE:
- database/migrations/xxxx_create_popups_table.php
- database/migrations/xxxx_create_announcements_table.php
- app/Models/Popup.php
- app/Models/Announcement.php
- app/Http/Controllers/Admin/PopupController.php
- app/Http/Controllers/Admin/AnnouncementController.php
- app/Http/Requests/Admin/StorePopupRequest.php
- app/Http/Requests/Admin/UpdatePopupRequest.php
- app/Http/Requests/Admin/StoreAnnouncementRequest.php
- app/Http/Requests/Admin/UpdateAnnouncementRequest.php
- resources/views/admin/popups/index.blade.php
- resources/views/admin/popups/create.blade.php
- resources/views/admin/popups/edit.blade.php
- resources/views/admin/announcements/index.blade.php
- resources/views/admin/announcements/create.blade.php
- resources/views/admin/announcements/edit.blade.php
- resources/views/components/public/popup.blade.php
- resources/views/components/public/announcement-banner.blade.php
- tests/Feature/Admin/PopupManagementTest.php
- tests/Feature/Admin/AnnouncementManagementTest.php
- tests/Feature/PublicSite/PopupDisplayTest.php
- tests/Feature/PublicSite/AnnouncementDisplayTest.php

FILES TO MODIFY:
- database/seeders/RolePermissionSeeder.php
- resources/views/components/admin/sidebar.blade.php
- resources/views/layouts/public.blade.php
- routes/admin.php
- app/Http/Controllers/Admin/ContactMessageController.php (polish reply UX)

TASK 1: PERMISSIONS

Modify RolePermissionSeeder.php.

Add permissions:
- popups.manage
- announcements.manage

Assign to: Super Administrator ONLY.

TASK 2: POP-UP SCHEMA

Create migration: create_popups_table.

Columns:
- id: primary key
- title: string, required
- content: text, required
- cta_text: string, nullable (call-to-action button label)
- cta_url: string, nullable (call-to-action button link)
- trigger_type: string, default 'on_load' (values: on_load, after_delay)
- delay_seconds: integer, default 0 (used if trigger_type is after_delay)
- is_active: boolean, default false
- is_dismissible: boolean, default true
- starts_at: timestamp, nullable
- ends_at: timestamp, nullable
- created_by: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- created_at, updated_at

TASK 3: POP-UP MODEL + ADMIN CRUD

Create app/Models/Popup.php:
- Fillable fields.
- Casts: is_active and is_dismissible to boolean, starts_at/ends_at to datetime.
- Scope: scopeActive (is_active=true, within starts_at/ends_at window).

Create PopupController with standard CRUD: index, create, store, edit, update, destroy.
- Permission: popups.manage.
- Audit log all popup changes.

Create the admin views (index, create, edit):
- Index: list popups with title, status (active/inactive), trigger, schedule, actions.
- Create/Edit form:
  - Title (text input).
  - Content (textarea).
  - CTA text + CTA URL (optional).
  - Trigger type (on_load or after_delay) + delay seconds.
  - Active toggle.
  - Dismissible toggle.
  - Schedule (starts_at, ends_at) optional.

TASK 4: ANNOUNCEMENT SCHEMA

Create migration: create_announcements_table.

Columns:
- id: primary key
- message: string, required (short banner text)
- type: string, default 'info' (values: info, success, warning, danger)
- link_text: string, nullable
- link_url: string, nullable
- is_active: boolean, default false
- is_dismissible: boolean, default true
- starts_at: timestamp, nullable
- ends_at: timestamp, nullable
- created_by: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- created_at, updated_at

TASK 5: ANNOUNCEMENT MODEL + ADMIN CRUD

Create app/Models/Announcement.php:
- Fillable fields.
- Casts: is_active and is_dismissible to boolean, starts_at/ends_at to datetime.
- Scope: scopeActive.

Create AnnouncementController with standard CRUD.
- Permission: announcements.manage.
- Audit log all announcement changes.

Create the admin views (index, create, edit):
- Index: list announcements with message preview, type badge, status, actions.
- Create/Edit form:
  - Message (text input).
  - Type (select: info, success, warning, danger).
  - Link text + URL (optional).
  - Active toggle.
  - Dismissible toggle.
  - Schedule optional.

TASK 6: PUBLIC POP-UP COMPONENT

Create resources/views/components/public/popup.blade.php.

An Alpine.js modal component:
- Fetches the active popup (passed from the layout/controller).
- If no active popup, render nothing.
- If popup exists and not dismissed (check localStorage), show it.
- Respects trigger_type:
  - on_load: show immediately.
  - after_delay: show after delay_seconds.
- Centered modal with overlay.
- Displays title, content, and optional CTA button.
- Close button (X) if dismissible.
- On dismiss: store popup ID in localStorage ('labgato-dismissed-popup-{id}').
- Accessible: role="dialog", aria-modal, focus trap, close on Escape.
- Dark mode support.

TASK 7: PUBLIC ANNOUNCEMENT BANNER COMPONENT

Create resources/views/components/public/announcement-banner.blade.php.

An Alpine.js banner component:
- Fetches active announcements (passed from layout/controller).
- Renders at the very top of the page (above the header).
- Color-coded by type (info=blue, success=green, warning=amber, danger=red).
- Displays message + optional link.
- Dismiss button (X) if dismissible.
- On dismiss: store announcement ID in localStorage.
- Stacks multiple active announcements.
- Dark mode support.

TASK 8: INTEGRATE INTO PUBLIC LAYOUT

Modify resources/views/layouts/public.blade.php:
- Add <x-public.announcement-banner /> at the very top (above header).
- Add <x-public.popup /> at the bottom of the body (before closing </body>).
- Pass active popups and announcements from the controller/view composer.

Create a view composer or service to fetch active popups/announcements for the public layout:
- Query active popups and announcements.
- Pass to the layout.

TASK 9: CONTACT REPLY VERIFICATION & POLISH

Inspect app/Http/Controllers/Admin/ContactMessageController.php and the reply flow.

Verify:
- Super admin can view the contact inbox.
- Super admin can reply to a message.
- Reply updates the message status to 'replied'.
- Member sender gets an in-app notification.
- Guest sender gets an email (logged via mail driver).

Polish the reply UX:
- Ensure the reply form is clear and accessible.
- Show the full message thread (original + replies) on the detail page.
- Add a visual indicator for unread/new messages in the inbox.
- Ensure the reply success message is clear.

TASK 10: ADMIN NAVIGATION

Modify resources/views/components/admin/sidebar.blade.php.

Add under a "Communication" group (or under "System"):
- "Pop-ups" → admin.popups.index (icon: Window/Modal)
- "Announcements" → admin.announcements.index (icon: Megaphone/Bell)
- Only visible to users with the respective permissions.

TASK 11: ROUTES

Add to routes/admin.php:

Route::middleware(['permission:popups.manage'])->prefix('popups')->name('popups.')->group(function () {
    Route::get('/', [PopupController::class, 'index'])->name('index');
    Route::get('/create', [PopupController::class, 'create'])->name('create');
    Route::post('/', [PopupController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [PopupController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PopupController::class, 'update'])->name('update');
    Route::delete('/{id}', [PopupController::class, 'destroy'])->name('destroy');
});

Route::middleware(['permission:announcements.manage'])->prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('/{id}', [AnnouncementController::class, 'destroy'])->name('destroy');
});

TASK 12: FEATURE TESTS

Create tests/Feature/Admin/PopupManagementTest.php:
- Test super admin can create/edit/delete popups.
- Test non-super-admin cannot access popup management.
- Test popup validation (title and content required).

Create tests/Feature/Admin/AnnouncementManagementTest.php:
- Test super admin can create/edit/delete announcements.
- Test non-super-admin cannot access announcement management.
- Test announcement type validation.

Create tests/Feature/PublicSite/PopupDisplayTest.php:
- Test active popup is displayed on public pages.
- Test inactive popup is not displayed.
- Test dismissed popup is not shown again (localStorage logic).
- Test popup respects schedule (starts_at/ends_at).
- Test after_delay trigger.

Create tests/Feature/PublicSite/AnnouncementDisplayTest.php:
- Test active announcement banner is displayed.
- Test inactive announcement is not displayed.
- Test announcement type affects color.
- Test dismissed announcement is hidden.
- Test multiple active announcements stack.

SECURITY REQUIREMENTS:
- Only super admins can manage popups and announcements.
- Popup/announcement content is sanitized before rendering.
- No JavaScript injection via popup content.
- Audit log all popup/announcement changes.
- Contact reply remains super-admin only.

DEFINITION OF DONE:
- Pop-ups can be created, scheduled, and displayed on public pages.
- Announcements can be created and shown as banners.
- Both respect dismiss state via localStorage.
- Both respect schedule windows.
- Contact reply is verified working and polished.
- Admin navigation includes Pop-ups and Announcements.
- All feature tests pass.
- Dark mode renders correctly for popups and banners.