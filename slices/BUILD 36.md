labgato/
├── app/
│   └── Http/
│       └── Controllers/
│           └── NotificationController.php
├── resources/
│   └── views/
│       ├── components/
│       │   ├── public/
│       │   │   ├── header.blade.php
│       │   │   └── user-dropdown.blade.php
│       │   ├── admin/
│       │   │   └── header.blade.php
│       │   └── notifications/
│       │       ├── panel.blade.php
│       │       └── item.blade.php
│       └── layouts/
│           └── (public, admin, member layouts if header is inline)
└── tests/
    └── Feature/
        └── HeaderUxTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD36-HEADER-UX

CONTEXT:
LabGato now uses the "Abyss Blue" theme with full dark mode support (Build 35). The header currently uses basic text links for the user menu and a simple dropdown for notifications. We are upgrading this to a modern, professional UX with an avatar-based user dropdown and a rich notification panel.

OBJECTIVE:
Implement a polished user icon dropdown (with avatar, profile links, theme toggle, and logout) and a rich notification panel (with unread indicators, icons, and "mark as read" actions). Ensure both components look excellent in both light and dark modes.

EXISTING ARCHITECTURE:
- Alpine.js for interactive components.
- "Abyss Blue" Tailwind theme with dark: variants.
- Laravel Notifications system (database channel) already in place (Build 31).
- Three main areas: Public, Member (/account), Admin (/admin).

FILES TO INSPECT:
- resources/views/components/public/header.blade.php
- resources/views/components/admin/header.blade.php
- resources/views/components/public/user-menu.blade.php (existing, to be replaced)
- resources/views/components/notifications/bell.blade.php (existing, to be replaced)
- app/Http/Controllers/NotificationController.php

FILES TO CREATE:
- resources/views/components/public/user-dropdown.blade.php
- resources/views/components/notifications/panel.blade.php
- resources/views/components/notifications/item.blade.php
- tests/Feature/HeaderUxTest.php

FILES TO MODIFY:
- resources/views/components/public/header.blade.php
- resources/views/components/admin/header.blade.php
- app/Http/Controllers/NotificationController.php (add unread count endpoint if needed)

TASK 1: USER DROPDOWN COMPONENT

Create resources/views/components/public/user-dropdown.blade.php.

Replace the old user-menu component. This component renders:
1. A circular avatar button (initials-based from Build 29, or a generic user icon if no name).
2. A dropdown menu (positioned bottom-right, `w-56`, `z-50`).

Dropdown Menu Items:
- Header: User's Display Name and Email (non-clickable).
- Divider.
- For Members: "Dashboard" (link to /account/dashboard).
- For Admins: "Admin Portal" (link to /admin/dashboard).
- "My Profile" (link to profile).
- "Messages" (link to /account/messages or /admin/contact-messages).
- Divider.
- Theme Toggle: A button inside the dropdown that toggles dark/light mode (synced with the Alpine state from Build 35). Icon changes between sun/moon.
- Divider.
- "Log out": A form POST to /logout.

Alpine.js Logic:
- `x-data="{ open: false }"`
- Toggle on avatar click.
- Close on outside click (`@click.outside="open = false"`).
- Close on Escape key.
- `aria-expanded`, `aria-haspopup="true"`, `role="menu"`, `role="menuitem"`.

Styling (Abyss Blue + Dark Mode):
- Button: `rounded-full ring-2 ring-transparent hover:ring-primary-500 transition`
- Dropdown: `bg-white dark:bg-slate-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10 rounded-lg`
- Items: `text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700`
- Logout: `text-danger-600 dark:text-danger-400`

TASK 2: NOTIFICATION PANEL COMPONENT

Create resources/views/components/notifications/panel.blade.php.

Replace the old basic bell dropdown. This is a "rich panel" (wide dropdown, e.g., `w-96` or `max-w-sm`) that acts like a modal for quick interactions.

Panel Structure:
1. Bell Icon Button:
   - SVG Bell icon.
   - Unread badge (red/blue dot with count) positioned absolute top-right.
   - `aria-label="Notifications"`.

2. Panel Dropdown (Alpine.js controlled):
   - Header: "Notifications" title + "Mark all as read" button.
   - Tabs (Optional but nice): "All" | "Unread".
   - Scrollable list (`max-h-96 overflow-y-auto`).
   - Empty state: "You're all caught up!" (with an icon) if no notifications.
   - Footer: "View all notifications" link to `/notifications`.

Alpine.js Logic:
- Fetch unread count on load (or pass from backend).
- Toggle panel on bell click.
- Close on outside click.
- Provide a `markAsRead(id)` method that sends an AJAX/Fetch POST to mark a single notification as read and updates the UI instantly.
- Provide a `markAllAsRead()` method.

Styling (Dark Mode):
- Panel BG: `bg-white dark:bg-slate-800`
- Header/Footer: `border-b border-slate-200 dark:border-slate-700`
- Unread Badge: `bg-primary-600 text-white` (or danger-500).

TASK 3: NOTIFICATION ITEM COMPONENT

Create resources/views/components/notifications/item.blade.php.

Props: `$notification` (the database notification object).

Layout:
- Left: Icon based on notification type (e.g., CheckCircle for approved, XCircle for rejected, Envelope for contact, UserPlus for registration). Color-coded.
- Middle:
  - Message text (bold if unread, normal if read).
  - Time ago (e.g., "2 hours ago" in `text-slate-500 dark:text-slate-400 text-xs`).
- Right: Unread indicator (small primary-500 dot) if `read_at` is null.

Interaction:
- Clicking the item marks it as read and navigates to the notification's URL (if applicable) or just marks it as read.

TASK 4: HEADER INTEGRATION

Modify resources/views/components/public/header.blade.php:
- Remove old user menu and old notification bell.
- Add the new `<x-notifications.panel />` (only if authenticated).
- Add the new `<x-public.user-dropdown />` (only if authenticated).
- Ensure they are in a flex container with `gap-4` and `items-center` on the right side.

Modify resources/views/components/admin/header.blade.php:
- Same integration. Ensure the admin header uses the exact same components for consistency.
- Admins will see their admin-specific links inside the user-dropdown.

TASK 5: BACKEND ENHANCEMENTS

Modify app/Http/Controllers/NotificationController.php:
- Ensure the `index` method (or a new `unreadCount` method) efficiently returns the count of unread notifications.
- Ensure `markAsRead` and `markAllAsRead` return JSON responses if requested via AJAX (so the Alpine panel can update without a full page reload).

Example JSON response for markAsRead:
`return response()->json(['success' => true, 'unread_count' => $newCount]);`

TASK 6: FEATURE TESTS

Create tests/Feature/HeaderUxTest.php:
- test_public_header_shows_user_dropdown_for_authenticated_user
- test_public_header_hides_dropdown_for_guest
- test_notification_panel_shows_unread_count
- test_mark_as_read_updates_database
- test_mark_all_as_read_clears_unread_badge
- test_user_dropdown_contains_logout_and_profile_links

ACCESSIBILITY REQUIREMENTS:
- Dropdowns must be keyboard navigable (Tab through items, Enter to select, Escape to close).
- Bell button must have `aria-label="Notifications"`.
- Unread badge must have `aria-label="X unread notifications"`.
- Focus management: when dropdown opens, focus should ideally move to the first item or the container.

DEFINITION OF DONE:
- User avatar icon with dropdown is present in all authenticated headers.
- Dropdown includes Profile, Messages, Theme Toggle, and Logout.
- Notification bell opens a rich panel (not a tiny dropdown).
- Notification panel shows unread indicators, icons, and time ago.
- "Mark as read" works via AJAX without page reload.
- All components look excellent and have correct contrast in Dark Mode.
- Keyboard navigation and ARIA attributes are correct.
- All feature tests pass.