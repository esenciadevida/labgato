labgato/
├── tailwind.config.js
├── resources/
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── components/
│       │   ├── theme-script.blade.php
│       │   ├── theme-toggle.blade.php
│       │   ├── ui/
│       │   │   ├── button.blade.php
│       │   │   ├── card.blade.php
│       │   │   ├── alert.blade.php
│       │   │   ├── badge.blade.php
│       │   │   ├── input.blade.php
│       │   │   ├── select.blade.php
│       │   │   ├── textarea.blade.php
│       │   │   ├── table.blade.php
│       │   │   └── empty-state.blade.php
│       │   ├── public/
│       │   │   └── header.blade.php
│       │   ├── admin/
│       │   │   ├── sidebar.blade.php
│       │   │   └── header.blade.php
│       │   └── member/
│       │       └── navigation.blade.php
│       └── layouts/
│           ├── public.blade.php
│           ├── admin.blade.php
│           ├── member.blade.php
│           ├── auth.blade.php
│           └── error.blade.php
└── tests/
    └── Feature/
        └── ThemeTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD35-THEME-DARKMODE

CONTEXT:
LabGato is adopting the "Abyss Blue" palette (Palette A) and adding full dark mode support. This is a foundation build that updates the entire UI. The theme must be professional, clinical, and accessible in both light and dark modes.

OBJECTIVE:
Implement the Abyss Blue color palette, enable class-based dark mode, create a theme toggle with persistence, and apply dark variants across all layouts and components.

EXISTING ARCHITECTURE:
- Tailwind CSS with custom tokens in tailwind.config.js.
- Blade components for UI (button, card, alert, etc.).
- Multiple layouts (public, admin, member, auth, error).
- Alpine.js for interactivity.
- WCAG 2.2 AA required.

FILES TO INSPECT:
- tailwind.config.js
- resources/css/app.css
- resources/views/layouts/ (all layouts)
- resources/views/components/ui/ (all UI components)
- resources/views/components/public/header.blade.php
- resources/views/components/admin/sidebar.blade.php
- resources/views/components/admin/header.blade.php

FILES TO CREATE:
- resources/views/components/theme-script.blade.php
- resources/views/components/theme-toggle.blade.php
- tests/Feature/ThemeTest.php

FILES TO MODIFY:
- tailwind.config.js
- resources/css/app.css
- All layouts (public, admin, member, auth, error)
- All UI components (button, card, alert, badge, input, select, textarea, table, empty-state)
- Public header, admin sidebar, admin header, member navigation

TASK 1: COLOR PALETTE (ABYSS BLUE)

Modify tailwind.config.js.

Set darkMode: 'class'.

Define the color tokens:

primary (Blue / Indigo):
  50:  '#eff6ff'
  100: '#dbeafe'
  200: '#bfdbfe'
  300: '#93c5fd'
  400: '#60a5fa'
  500: '#3b82f6'
  600: '#2563eb'   (main)
  700: '#1d4ed8'
  800: '#1e40af'
  900: '#1e3a8a'
  950: '#172554'

success (Emerald):
  50:'#ecfdf5' 100:'#d1fae5' 500:'#10b981' 600:'#059669' 700:'#047857'

warning (Amber):
  50:'#fffbeb' 100:'#fef3c7' 500:'#f59e0b' 600:'#d97706' 700:'#b45309'

danger (Red):
  50:'#fef2f2' 100:'#fee2e2' 500:'#ef4444' 600:'#dc2626' 700:'#b91c1c'

info (Sky):
  50:'#f0f9ff' 100:'#e0f2fe' 500:'#0ea5e9' 600:'#0284c7' 700:'#0369a1'

Keep 'slate' for neutrals (Tailwind default).
Replace any existing teal/clinical primary references with the new primary.

TASK 2: THEME INITIALIZATION SCRIPT (NO FLASH)

Create resources/views/components/theme-script.blade.php.

An inline script placed in the <head> of every layout, BEFORE any content renders, to apply the saved theme and avoid a flash of the wrong theme:

<script>
  (function() {
    var theme = localStorage.getItem('labgato-theme');
    var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (theme === 'dark' || (theme !== 'light' && prefersDark)) {
      document.documentElement.classList.add('dark');
    }
  })();
</script>

Rules:
- If the user explicitly chose dark, use dark.
- If the user explicitly chose light, use light.
- If no choice is saved, follow the system preference.

TASK 3: THEME TOGGLE COMPONENT

Create resources/views/components/theme-toggle.blade.php.

An Alpine.js component:
- Button with sun/moon icon.
- Shows moon icon when in light mode (click to go dark).
- Shows sun icon when in dark mode (click to go light).
- Accessible: aria-label ("Switch to dark mode" / "Switch to light mode"), aria-pressed.
- On click: toggle the 'dark' class on <html> and save to localStorage.

Alpine logic:
x-data="{
  isDark: document.documentElement.classList.contains('dark'),
  toggle() {
    this.isDark = !this.isDark;
    document.documentElement.classList.toggle('dark', this.isDark);
    localStorage.setItem('labgato-theme', this.isDark ? 'dark' : 'light');
  }
}"

TASK 4: UPDATE LAYOUTS

Modify all 5 layouts (public, admin, member, auth, error):

1. Add <x-theme-script /> in the <head> before content.
2. Apply dark variants to the base background and text:
   - Light: bg-white text-slate-900
   - Dark: dark:bg-slate-950 dark:text-slate-100
3. Add the theme toggle to the header area.
4. Ensure the main content area has appropriate dark background.

Base body pattern:
<body class="bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased">

TASK 5: UPDATE UI COMPONENTS (DARK VARIANTS)

Apply dark: variants to all UI components. Use this pattern:

Surfaces (cards, panels):
- Light: bg-white border-slate-200
- Dark: dark:bg-slate-900 dark:border-slate-800

Text:
- Primary: text-slate-900 dark:text-slate-100
- Secondary: text-slate-600 dark:text-slate-400
- Muted: text-slate-500 dark:text-slate-500

Borders:
- border-slate-200 dark:border-slate-800

Inputs:
- Light: bg-white border-slate-300 text-slate-900
- Dark: dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100

Buttons:
- Primary: bg-primary-600 hover:bg-primary-700 text-white (works in both modes)
- Secondary: bg-white border-slate-300 text-slate-700 → dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200
- Ghost: text-slate-700 hover:bg-slate-100 → dark:text-slate-200 dark:hover:bg-slate-800

Alerts (semantic):
- Success: bg-success-50 text-success-700 → dark:bg-success-700/20 dark:text-success-400
- Warning: bg-warning-50 text-warning-700 → dark:bg-warning-700/20 dark:text-warning-400
- Danger: bg-danger-50 text-danger-700 → dark:bg-danger-700/20 dark:text-danger-400
- Info: bg-info-50 text-info-700 → dark:bg-info-700/20 dark:text-info-400

Tables:
- Header: bg-slate-50 → dark:bg-slate-800/50
- Rows: bg-white → dark:bg-slate-900
- Row hover: hover:bg-slate-50 → dark:hover:bg-slate-800/50
- Borders: border-slate-200 → dark:border-slate-800

Badges:
- Use translucent backgrounds in dark mode (e.g., dark:bg-primary-500/20 dark:text-primary-300).

TASK 6: UPDATE NAVIGATION

Public header:
- Light: bg-white border-slate-200
- Dark: dark:bg-slate-900 dark:border-slate-800
- Links: text-slate-700 hover:text-primary-600 → dark:text-slate-200 dark:hover:text-primary-400
- Add theme toggle to the header right side.

Admin sidebar:
- Light: bg-white or bg-slate-50 border-slate-200
- Dark: dark:bg-slate-900 dark:border-slate-800
- Active item: bg-primary-50 text-primary-700 → dark:bg-primary-500/10 dark:text-primary-400
- Add theme toggle to admin header.

Member navigation:
- Apply same dark variants as public header.

TASK 7: ACCESSIBILITY CONTRAST

Verify WCAG 2.2 AA contrast in BOTH modes:
- Body text on background: ≥ 4.5:1.
- Large text: ≥ 3:1.
- UI components: ≥ 3:1.

Check specifically:
- slate-400 on slate-950 (dark secondary text) — ensure readable.
- primary-400 on slate-950 (dark links) — ensure readable.
- Buttons in dark mode maintain contrast.

Adjust any failing combinations.

TASK 8: FEATURE TESTS

Create tests/Feature/ThemeTest.php:
- Test public pages render without errors.
- Test admin pages render without errors.
- Test the theme toggle component is present in headers.
- Test the theme initialization script is in the head.
- Test dark: classes are present in the compiled CSS (spot check).

BUILD VERIFICATION:
After implementation, manually verify:
- Light mode looks clean and professional.
- Dark mode uses deep slate/navy backgrounds.
- Theme toggle works and persists across page loads.
- System preference is respected on first visit.
- No flash of wrong theme on load.
- All pages are readable in both modes.
- No default Tailwind blue/teal remnants from the old theme.

DEFINITION OF DONE:
- Abyss Blue palette is defined in tailwind.config.js.
- darkMode: 'class' is enabled.
- Theme toggle works with localStorage persistence.
- System preference is respected (no flash).
- All layouts and UI components have dark variants.
- Theme toggle is present in public header and admin header.
- WCAG 2.2 AA contrast is met in both modes.
- All feature tests pass.
- No old teal theme remnants.