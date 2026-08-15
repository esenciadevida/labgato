LABGOTO/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Admin/
│   │           └── DashboardController.php
│   └── View/
│       └── Components/
│           └── Admin/
│               └── NavigationItem.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── admin.blade.php
│       ├── components/
│       │   ├── admin/
│       │   │   ├── sidebar.blade.php
│       │   │   ├── header.blade.php
│       │   │   ├── footer.blade.php
│       │   │   ├── navigation.blade.php
│       │   │   └── navigation-item.blade.php
│       │   ├── ui/
│       │   │   ├── button.blade.php
│       │   │   ├── input.blade.php
│       │   │   ├── select.blade.php
│       │   │   ├── textarea.blade.php
│       │   │   ├── alert.blade.php
│       │   │   ├── badge.blade.php
│       │   │   ├── card.blade.php
│       │   │   ├── table.blade.php
│       │   │   └── empty-state.blade.php
│       │   └── form/
│       │       ├── label.blade.php
│       │       ├── error.blade.php
│       │       └── section.blade.php
│       └── admin/
│           └── dashboard.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        └── Admin/
            ├── AdminLayoutTest.php
            ├── AdminNavigationTest.php
            └── AdminDashboardTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-SLICE1-BUILD5

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 through Build 4 are complete. The Laravel foundation, database schema, authentication, session security, and RBAC system are implemented.

We are now implementing Build 5: Admin Portal Foundation.

This build creates the Admin Portal layout, permission-based navigation, dashboard shell, and reusable UI components.

This build does NOT implement resource CRUD pages. Resource CRUD will be built in later vertical slices.

OBJECTIVE:
Create the Admin Portal layout, sidebar navigation, header, footer, dashboard shell, and reusable UI/form components.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Admin Portal uses a dedicated layout distinct from the public site.
- Admin navigation is permission-based.
- Fake statistics are prohibited.
- Loading, empty, error, success, and validation states are mandatory.
- Admin UI must be accessible and responsive.
- RBAC is enforced server-side.
- Navigation visibility is controlled by permissions.
- No dead links or dead UI.
- No resource-specific CRUD yet.

FILES TO INSPECT:
- app/Models/User.php
- app/Models/Role.php
- app/Models/Permission.php
- app/Http/Middleware/EnsureUserHasPermission.php
- app/Http/Middleware/EnsureUserIsActive.php
- app/Services/PermissionCacheService.php
- routes/admin.php
- resources/views/layouts/
- resources/views/admin/dashboard.blade.php
- database/seeders/RolePermissionSeeder.php
- config/LABGOTO.php

FILES TO CREATE:
- resources/views/layouts/admin.blade.php
- resources/views/components/admin/sidebar.blade.php
- resources/views/components/admin/header.blade.php
- resources/views/components/admin/footer.blade.php
- resources/views/components/admin/navigation.blade.php
- resources/views/components/admin/navigation-item.blade.php
- resources/views/components/ui/button.blade.php
- resources/views/components/ui/input.blade.php
- resources/views/components/ui/select.blade.php
- resources/views/components/ui/textarea.blade.php
- resources/views/components/ui/alert.blade.php
- resources/views/components/ui/badge.blade.php
- resources/views/components/ui/card.blade.php
- resources/views/components/ui/table.blade.php
- resources/views/components/ui/empty-state.blade.php
- resources/views/components/form/label.blade.php
- resources/views/components/form/error.blade.php
- resources/views/components/form/section.blade.php
- tests/Feature/Admin/AdminLayoutTest.php
- tests/Feature/Admin/AdminNavigationTest.php
- tests/Feature/Admin/AdminDashboardTest.php

FILES TO MODIFY:
- app/Http/Controllers/Admin/DashboardController.php
- resources/views/admin/dashboard.blade.php
- routes/admin.php
- resources/css/app.css if admin-specific styles are needed
- resources/js/app.js if Alpine components are needed for sidebar toggle

ADMIN LAYOUT REQUIREMENTS:

Create resources/views/layouts/admin.blade.php.

Layout structure:
- HTML5 document.
- Meta charset.
- Meta viewport.
- Meta robots noindex, nofollow.
- CSRF token.
- Page title slot.
- Tailwind CSS compiled assets.
- Alpine.js.
- Livewire styles and scripts where applicable.
- Skip to main content link for accessibility.
- Sidebar navigation.
- Top header.
- Main content area.
- Footer.

Layout behavior:
- Desktop: sidebar visible by default.
- Mobile: off-canvas sidebar with hamburger toggle.
- Sidebar toggle must be keyboard accessible.
- Focus states must be visible.
- Main content area must have appropriate padding and max-width.

Layout slots:
- title
- content
- Optional header actions
- Optional scripts

The layout must not include public site navigation.

ADMIN SIDEBAR REQUIREMENTS:

Create resources/views/components/admin/sidebar.blade.php.

Sidebar must include:
- LABGOTO logo or site name.
- Primary navigation groups.
- Active state highlighting.
- Mobile close button.

Sidebar navigation groups:

1. Dashboard
   - Link to admin.dashboard
   - Permission: dashboard.view

2. Content
   - Tools
   - Calculators
   - Reference Ranges
   - SOPs
   - Interpretation
   - Knowledge

   Content links should be placeholders for now.
   Do not create resource CRUD pages yet.
   If a resource module is not implemented, the link may point to a coming-soon state or be hidden.

3. Taxonomy
   - Categories
   - Tags

   Taxonomy links should be placeholders for now.

4. Users & Roles
   - Users
   - Roles

   User and role management links should be placeholders for now.

5. System
   - Settings
   - Audit Logs
   - Analytics

   System links should be placeholders for now.

Permission visibility rules:
- Dashboard item requires dashboard.view.
- Content group requires at least one resource view permission.
- Taxonomy requires taxonomy.view.
- Users & Roles requires users.view or roles.view.
- System requires settings.view, audit-logs.view, or analytics.view.
- If a user has no visible items in a group, hide the group.
- If a user has no visible navigation items at all, show a safe empty navigation message.

ADMIN HEADER REQUIREMENTS:

Create resources/views/components/admin/header.blade.php.

Header must include:
- Mobile sidebar toggle button.
- Current page title or section label where appropriate.
- Authenticated administrator name.
- Dropdown menu with:
  - View public site link.
  - Logout button.

Logout must:
- Use POST method.
- Include CSRF token.
- Point to auth.logout.

Header must not include fake notifications or fake messages.

ADMIN FOOTER REQUIREMENTS:

Create resources/views/components/admin/footer.blade.php.

Footer must include:
- Application name.
- Environment indicator where appropriate, such as Local or Production.
- Version placeholder if practical.
- Copyright notice.

Footer must not include fake links or fake statistics.

ADMIN NAVIGATION COMPONENT REQUIREMENTS:

Create resources/views/components/admin/navigation.blade.php.

This component should render navigation groups and items.

Create resources/views/components/admin/navigation-item.blade.php.

Navigation item component props:
- label: string
- route: string
- icon: optional string
- active: boolean or route match logic
- permission: optional permission slug

Behavior:
- If permission is provided and user lacks permission, do not render item.
- If route is active, apply active styling and aria-current="page".
- Use semantic anchor elements.
- Use visible focus styles.

UI COMPONENT REQUIREMENTS:

Create reusable Blade components under resources/views/components/ui/.

1. button.blade.php
   Props:
   - type: button, submit, link
   - variant: primary, secondary, danger, ghost
   - size: sm, md, lg
   - disabled: boolean
   - href: optional for link buttons

   Requirements:
   - Use native button element for actions.
   - Use anchor element only for true navigation.
   - Support disabled state.
   - Support visible focus.
   - Do not use divs as buttons.

2. input.blade.php
   Props:
   - name
   - type: text, email, password, number, search
   - value
   - placeholder
   - required
   - disabled
   - autocomplete
   - aria-describedby

   Requirements:
   - Support associated label via form.label component.
   - Support validation error styling.
   - Support aria-invalid when invalid.

3. select.blade.php
   Props:
   - name
   - options
   - value
   - required
   - disabled

4. textarea.blade.php
   Props:
   - name
   - value
   - rows
   - placeholder
   - required
   - disabled

5. alert.blade.php
   Props:
   - type: success, error, warning, info
   - title: optional
   - message or slot

   Requirements:
   - Use appropriate color and icon.
   - Use role="alert" for errors.
   - Use role="status" for success messages.

6. badge.blade.php
   Props:
   - variant: success, warning, danger, info, neutral
   - label or slot

7. card.blade.php
   Props:
   - title: optional
   - slot content

8. table.blade.php
   Requirements:
   - Use semantic table elements.
   - Support thead, tbody, th, td.
   - Support responsive horizontal scrolling where needed.
   - Use scope="col" for column headers.

9. empty-state.blade.php
   Props:
   - title
   - description
   - action: optional button or link

   Requirements:
   - Display honest empty message.
   - Do not display fake data.
   - Provide call-to-action where appropriate.

FORM COMPONENT REQUIREMENTS:

Create reusable Blade components under resources/views/components/form/.

1. label.blade.php
   Props:
   - for
   - value
   - required

   Requirements:
   - Render label element with for attribute.
   - Show required indicator where appropriate.

2. error.blade.php
   Props:
   - for

   Requirements:
   - Display validation error for field.
   - Use appropriate aria-describedby connection.
   - Use accessible error styling.

3. section.blade.php
   Props:
   - title
   - description
   - slot

   Requirements:
   - Group related form fields.
   - Use semantic heading structure.

ADMIN DASHBOARD REQUIREMENTS:

Modify app/Http/Controllers/Admin/DashboardController.php.

The dashboard controller should:
- Require authentication.
- Require active user.
- Require dashboard.view permission.
- Return the admin dashboard view.
- Provide honest summary data where available.

Dashboard data for Build 5:
- Total administrators count.
- Total roles count.
- Total permissions count.
- Recent audit log entries limited to 5, if available.

Do not include fake resource statistics.

Modify resources/views/admin/dashboard.blade.php.

Dashboard view requirements:
- Use admin layout.
- Display page title: Dashboard.
- Display welcome message with authenticated user name.
- Display summary cards for administrators, roles, and permissions.
- Display recent activity section using audit logs.
- Display honest empty states where no data exists.
- Do not display fake charts.
- Do not display fake statistics.
- Do not display fake notifications.

If recent audit logs are empty:
- Show empty state: "No recent activity recorded yet."

If counts are zero:
- Show zero honestly.

ROUTES REQUIREMENTS:

Modify routes/admin.php.

Ensure admin dashboard route uses:
- auth middleware
- active middleware
- permission:dashboard.view middleware

Route:

GET /admin/dashboard
- Name: admin.dashboard
- Middleware: auth, active, permission:dashboard.view

Do not create resource CRUD routes yet.

ACCESSIBILITY REQUIREMENTS:

Admin layout must:
- Include skip to main content link.
- Use semantic HTML landmarks.
- Use visible focus states.
- Support keyboard navigation.
- Use ARIA labels where appropriate.
- Use aria-current="page" for active navigation.
- Trap focus in mobile sidebar when open.
- Return focus to toggle button when mobile sidebar closes.
- Use sufficient color contrast.

Sidebar toggle must:
- Be keyboard accessible.
- Have accessible label.
- Support Escape key to close mobile sidebar.

RESPONSIVE REQUIREMENTS:

Admin layout must work on:
- Desktop
- Tablet
- Mobile

Mobile behavior:
- Sidebar hidden by default.
- Hamburger toggle visible.
- Sidebar slides in or overlays content.
- Close button visible inside sidebar.
- Main content remains usable.

TESTING REQUIREMENTS:

Create tests/Feature/Admin/AdminLayoutTest.php.

Required tests:

1. test_guest_cannot_access_admin_layout
   - Visit admin.dashboard as guest.
   - Assert redirected to auth.login.

2. test_inactive_user_cannot_access_admin_layout
   - Authenticate inactive user.
   - Visit admin.dashboard.
   - Assert redirected to auth.login.

3. test_active_user_with_dashboard_permission_can_access_admin_layout
   - Create active user with dashboard.view permission.
   - Authenticate.
   - Visit admin.dashboard.
   - Assert successful response.
   - Assert admin layout elements are visible.

4. test_admin_layout_includes_skip_link
   - Authenticate authorized user.
   - Visit admin.dashboard.
   - Assert skip to main content link exists.

Create tests/Feature/Admin/AdminNavigationTest.php.

Required tests:

1. test_navigation_shows_dashboard_for_user_with_dashboard_permission
   - Create user with dashboard.view.
   - Authenticate.
   - Visit admin.dashboard.
   - Assert Dashboard navigation item is visible.

2. test_navigation_hides_users_group_from_user_without_users_or_roles_permission
   - Create user with dashboard.view only.
   - Authenticate.
   - Visit admin.dashboard.
   - Assert Users & Roles navigation group is not visible.

3. test_navigation_shows_users_group_for_user_with_users_view_permission
   - Create user with dashboard.view and users.view.
   - Authenticate.
   - Visit admin.dashboard.
   - Assert Users & Roles navigation group is visible.

4. test_navigation_hides_system_group_from_user_without_system_permissions
   - Create user with dashboard.view only.
   - Authenticate.
   - Visit admin.dashboard.
   - Assert System navigation group is not visible.

Create tests/Feature/Admin/AdminDashboardTest.php.

Required tests:

1. test_dashboard_displays_authenticated_user_name
   - Authenticate authorized user.
   - Visit admin.dashboard.
   - Assert user name is visible.

2. test_dashboard_displays_honest_counts
   - Authenticate authorized user.
   - Visit admin.dashboard.
   - Assert administrator count, role count, and permission count are displayed.

3. test_dashboard_displays_empty_recent_activity_when_no_audit_logs_exist
   - Authenticate authorized user.
   - Ensure audit_logs table is empty.
   - Visit admin.dashboard.
   - Assert empty activity message is visible.

4. test_dashboard_does_not_display_fake_statistics
   - Authenticate authorized user.
   - Visit admin.dashboard.
   - Assert no placeholder fake resource statistics are present.

SECURITY REQUIREMENTS:

- Admin layout must not be accessible without authentication.
- Admin layout must not be accessible to inactive users.
- Admin dashboard must require dashboard.view permission.
- Navigation visibility must be enforced server-side.
- Do not expose permission slugs in public error messages.
- Do not expose stack traces in production.
- Logout must use POST and CSRF.

SEO REQUIREMENTS:

Admin layout must include:

<meta name="robots" content="noindex, nofollow">

Admin pages must not be added to sitemap.

API:

No API endpoints are required for Build 5.

DATABASE:

No new tables are required.

Use existing tables:
- users
- roles
- permissions
- audit_logs

DEFINITION OF DONE:

- Admin layout renders correctly for authorized administrators.
- Admin layout redirects unauthorized users.
- Admin sidebar renders navigation groups.
- Navigation visibility respects permissions.
- Admin header displays authenticated user and logout.
- Admin footer displays application information.
- Reusable UI components exist and render correctly.
- Reusable form components exist and render correctly.
- Admin dashboard displays honest counts.
- Admin dashboard displays honest empty states.
- Admin layout includes skip link.
- Admin layout supports keyboard navigation.
- Admin layout is responsive on mobile.
- Admin pages are noindex.
- All Build 5 tests pass.
- No resource CRUD pages are created.
- No fake statistics are displayed.
- No dead links exist.
- No secrets are committed.