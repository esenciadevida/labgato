labgato/
├── tailwind.config.js
├── resources/
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── components/
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
│       │   ├── form/
│       │   │   ├── label.blade.php
│       │   │   ├── error.blade.php
│       │   │   └── section.blade.php
│       │   ├── admin/
│       │   │   ├── sidebar.blade.php
│       │   │   ├── header.blade.php
│       │   │   ├── navigation.blade.php
│       │   │   └── navigation-item.blade.php
│       │   ├── public/
│       │   │   ├── header.blade.php
│       │   │   ├── footer.blade.php
│       │   │   ├── navigation.blade.php
│       │   │   ├── breadcrumbs.blade.php
│       │   │   └── search/
│       │   │       ├── form.blade.php
│       │   │       └── result-item.blade.php
│       │   └── admin/
│       │       └── resources/
│       │           ├── category-selector.blade.php
│       │           └── tag-input.blade.php
│       ├── layouts/
│       │   ├── public.blade.php
│       │   ├── admin.blade.php
│       │   └── auth.blade.php

IMPLEMENTATION PROMPT ID: LABGATO-THEME-APPLICATION

CONTEXT:
LabGato uses Tailwind CSS, Alpine.js, and Livewire. UI components were built in Build 5 and Build 6 using default Tailwind colors. The Design System and UI Theme document is now approved.

We are now applying the approved theme to the codebase.

This step does NOT add new features. It updates Tailwind configuration and restyles existing components to use the LabGato design tokens.

OBJECTIVE:
Apply the approved LabGato design tokens to tailwind.config.js and update all existing UI, admin, and public components to use the theme consistently.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Primary color is clinical teal.
- Neutral color is slate.
- Semantic colors are success, warning, danger, info.
- System font stack, no external fonts.
- Minimal shadows, rounded-md/lg, no glassmorphism.
- Light theme only for MVP.
- All components must use design tokens, not arbitrary values.
- Accessibility and focus states must be preserved.

FILES TO INSPECT:
- tailwind.config.js
- resources/css/app.css
- resources/views/components/ui/
- resources/views/components/form/
- resources/views/components/admin/
- resources/views/components/public/
- resources/views/layouts/

FILES TO MODIFY:
- tailwind.config.js
- resources/css/app.css
- All UI components listed in the target file paths.
- All admin layout components.
- All public layout components.

TASK 1: ADD TAILWIND THEME TOKENS

Modify tailwind.config.js.

Add the following under theme.extend.

colors:

primary:
  50:  '#F0FDFA'
  100: '#CCFBF1'
  200: '#99F6E4'
  300: '#5EEAD4'
  400: '#2DD4BF'
  500: '#14B8A6'
  600: '#0D9488'
  700: '#0F766E'
  800: '#115E59'
  900: '#134E4A'
  950: '#042F2E'

success:
  50:  '#F0FDF4'
  600: '#16A34A'

warning:
  50:  '#FFFBEB'
  600: '#D97706'

danger:
  50:  '#FEF2F2'
  600: '#DC2626'

info:
  50:  '#F0F9FF'
  600: '#0284C7'

fontFamily:

sans:
  ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif

mono:
  ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace

boxShadow:

card: '0 1px 2px 0 rgb(0 0 0 / 0.05)'

Rules:
- Do not redefine the neutral palette. Use Tailwind's built-in slate.
- Do not add external font imports.
- Do not add arbitrary colors outside this system.
- Preserve any existing content and plugin configuration.

TASK 2: BASE STYLES

Modify resources/css/app.css.

Add base body styles:

- Background: neutral-50.
- Text color: neutral-900.
- Font family: font-sans.
- Antialiased rendering.
- Respect prefers-reduced-motion for non-essential transitions.

Ensure the Tailwind directives are present:

@tailwind base;
@tailwind components;
@tailwind utilities;

TASK 3: UI COMPONENT UPDATES

Update each component to use theme tokens. Preserve all existing functionality, Alpine bindings, Livewire bindings, ARIA attributes, and accessibility behavior.

button.blade.php:

Primary variant:
- bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md px-4 py-2
- Visible focus ring: focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2

Secondary variant:
- bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 rounded-md px-4 py-2

Danger variant:
- bg-danger-600 hover:bg-danger-700 text-white font-medium rounded-md px-4 py-2

Ghost variant:
- bg-transparent text-primary-700 hover:bg-primary-50 rounded-md px-4 py-2

Disabled state:
- opacity-50 cursor-not-allowed pointer-events-none or disabled attribute handling

input.blade.php, select.blade.php, textarea.blade.php:

- border border-neutral-300 rounded-md px-3 py-2 w-full
- Background white
- Text neutral-900
- Placeholder neutral-400
- Focus: focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20
- Error state: border-danger-500 focus:border-danger-500 focus:ring-danger-500/20
- Disabled: bg-neutral-100 text-neutral-500 cursor-not-allowed

alert.blade.php:

Map alert types to semantic colors:

success:
- bg-success-50 border border-success-600/20 text-success-600

warning:
- bg-warning-50 border border-warning-600/20 text-warning-600

danger:
- bg-danger-50 border border-danger-600/20 text-danger-600

info:
- bg-info-50 border border-info-600/20 text-info-600

Rules:
- Use role="alert" for danger and warning.
- Use role="status" for success and info.
- Include an icon and message.

badge.blade.php:

- rounded-full text-xs font-medium px-2.5 py-0.5 inline-flex items-center
- Map variants to semantic background-50 with text-600/700 and subtle border.
- Map resource status to semantic colors:
  Draft → neutral
  Under Review → warning
  Published → success
  Archived → info
  Obsolete → danger

card.blade.php:

- bg-white border border-neutral-200 rounded-lg shadow-card
- Padding p-4 or p-6
- Optional interactive hover: hover:border-primary-300 transition

table.blade.php:

- Wrapper: overflow-x-auto
- Table: min-w-full divide-y divide-neutral-200
- Header: bg-neutral-50
- Header cells: px-4 py-3 text-left text-xs font-medium text-neutral-600 uppercase tracking-wider
- Body rows: border-b border-neutral-200 or divide-y divide-neutral-200
- Row hover: hover:bg-neutral-50
- Optional striping: even:bg-neutral-50

empty-state.blade.php:

- Centered container: text-center py-12
- Icon: text-neutral-400
- Title: text-lg font-medium text-neutral-900
- Description: text-sm text-neutral-500
- Optional CTA button using primary variant

TASK 4: FORM COMPONENT UPDATES

label.blade.php:
- text-sm font-medium text-neutral-700
- Required indicator: text-danger-600

error.blade.php:
- text-sm text-danger-600
- Display below the field
- Use accessible aria-describedby connection

section.blade.php:
- Heading: text-lg font-medium text-neutral-900
- Description: text-sm text-neutral-500
- Consistent vertical spacing

TASK 5: ADMIN LAYOUT UPDATES

sidebar.blade.php:
- Background white
- Right border: border-r border-neutral-200
- Width around w-64 on desktop
- Off-canvas behavior preserved for mobile

navigation-item.blade.php:
- Default: text-neutral-700 hover:bg-neutral-50 rounded-md px-3 py-2
- Active: bg-primary-50 text-primary-700 font-medium
- Focus visible
- Use aria-current="page" for active

header.blade.php (admin):
- Background white
- Bottom border: border-b border-neutral-200
- User dropdown accessible

footer.blade.php (admin):
- Text neutral-500 text-sm
- Top border: border-t border-neutral-200

TASK 6: PUBLIC LAYOUT UPDATES

header.blade.php (public):
- Background white
- Bottom border: border-b border-neutral-200
- Logo/site name in neutral-900
- Active nav link: text-primary-700 font-medium
- Default nav link: text-neutral-700 hover:text-primary-700

navigation.blade.php (public):
- Mobile menu accessible
- Focus visible
- Escape key closes menu

footer.blade.php (public):
- Background white or neutral-50
- Top border: border-t border-neutral-200
- Links: text-neutral-600 hover:text-primary-700
- Legal links accessible

breadcrumbs.blade.php:
- Text neutral-500 text-sm
- Current item: text-neutral-900
- Separator: text-neutral-400

search/form.blade.php:
- Input uses theme input styles
- Search button uses primary variant

search/result-item.blade.php:
- Title link: text-primary-700 hover:text-primary-800 font-medium
- Type badge uses semantic badge
- Description: text-neutral-600

category-selector.blade.php and tag-input.blade.php:
- Use theme input/select styles
- Selected items use primary or neutral chips
- Accessible labels preserved

TASK 7: FOCUS AND CONTRAST VERIFICATION

Verify:
- All interactive elements have a visible focus ring.
- All text meets WCAG 2.2 AA contrast ratios.
- Links are distinguishable from surrounding text.
- Touch targets are at least 44x44 CSS pixels.
- No gradient, glassmorphism, or heavy shadow styling remains.

TASK 8: REBUILD AND VISUAL VERIFICATION

Run:
- npm run build

Visually verify:
- Public home page
- Public search page
- Admin login page
- Admin dashboard
- Admin categories page
- Admin tags page

Confirm:
- Colors match the theme.
- No default blue or inconsistent colors remain.
- Focus states visible.
- Layout intact.
- No console errors.

DEFINITION OF DONE:

- tailwind.config.js contains primary, success, warning, danger, info, font, and shadow tokens.
- app.css applies base body styles.
- All UI components use theme tokens.
- All admin layout components use theme tokens.
- All public layout components use theme tokens.
- Focus states are visible across components.
- Contrast meets WCAG 2.2 AA.
- No arbitrary color values remain.
- No gradients, glassmorphism, or heavy shadows.
- Assets rebuild successfully.
- Key pages verified visually.
- No functionality broken.
- No accessibility regressions.