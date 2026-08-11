# 10 — Design System and UI Theme

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Project Charter, Technology Stack).
- The approved Phase 02 documentation (specifically Admin Portal Foundation, Public Site Foundation, SEO and Accessibility Foundation).
- The completed Slice 1 Application Foundation implementation.
- The completed Build 5 Admin Portal Foundation UI components.
- The completed Build 6 Public Site Foundation UI components.

## Defines

This document defines:

- The LabGato design principles.
- The official color system (primary, neutral, semantic).
- The typography system.
- The spacing, layout, and container rules.
- The border radius, shadow, and border token rules.
- The visual specifications for core UI components.
- The dark mode policy.
- The Tailwind theme extension tokens.
- The accessibility requirements for the theme.

## Affects

This document affects:

- The tailwind.config.js theme extension.
- The resources/css/app.css base styles.
- All existing and future Blade UI components.
- All existing and future Admin Portal views.
- All existing and future Public Site views.
- All future vertical-slice resource UI.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a locked, professional visual identity for LabGato.
- Replaces ad-hoc default Tailwind colors with defined LabGato tokens.
- Ensures consistent, accessible, brand-aligned UI across all future builds.
- Prevents visual inconsistency as new resource modules are added.

---

# 1. Purpose

This document establishes the Design System and UI Theme for LabGato.

Until now, UI components have used ad-hoc default Tailwind colors. This document locks a professional, scientific, accessible visual identity so that all current and future interfaces are consistent and brand-aligned.

LabGato must look like a trustworthy professional laboratory instrument, not a generic marketing site or a decorative dashboard.

---

# 2. Design Principles

## 2.1 Professional and Scientific

The interface must feel precise, calm, and trustworthy.

## 2.2 Clarity Over Decoration

- No gradients.
- No glassmorphism.
- No excessive shadows.
- No decorative clutter.
- No autoplaying media.

## 2.3 High Contrast and Accessible

- All text must meet WCAG 2.2 AA contrast ratios.
- Interactive elements must have visible focus states.
- Color must never be the only indicator of state.

## 2.4 Honest Data

- No fake statistics.
- No fake charts.
- No fake testimonials.
- Empty states must be honest and useful.

## 2.5 Consistency

- Use design tokens, not arbitrary values.
- Use reusable components, not one-off markup.

---

# 3. Color System

## 3.1 Primary Palette

The primary color is a professional clinical teal. It conveys science, cleanliness, and trust.

Token mapping:

primary-50   #F0FDFA
primary-100  #CCFBF1
primary-200  #99F6E4
primary-300  #5EEAD4
primary-400  #2DD4BF
primary-500  #14B8A6
primary-600  #0D9488
primary-700  #0F766E
primary-800  #115E59
primary-900  #134E4A
primary-950  #042F2E

Usage:
- primary-600 and primary-700 for primary buttons and active states.
- primary-700 for primary links.
- primary-50 and primary-100 for subtle backgrounds and highlights.
- primary-900 for headings on light backgrounds where appropriate.

## 3.2 Neutral Palette

Use a slate-based neutral palette for text, backgrounds, and borders.

Token mapping:

neutral-50   #F8FAFC
neutral-100  #F1F5F9
neutral-200  #E2E8F0
neutral-300  #CBD5E1
neutral-400  #94A3B8
neutral-500  #64748B
neutral-600  #475569
neutral-700  #334155
neutral-800  #1E293B
neutral-900  #0F172A
neutral-950  #020617

Usage:
- neutral-50 for page backgrounds.
- neutral-100 for section backgrounds and table striping.
- neutral-200 for borders.
- neutral-500 for secondary text.
- neutral-700 and neutral-900 for primary text.

## 3.3 Semantic Colors

Semantic colors communicate state. Each has a text/icon value and a subtle background value.

Success:
- success-600  #16A34A
- success-50   #F0FDF4

Warning:
- warning-600  #D97706
- warning-50   #FFFBEB

Danger:
- danger-600   #DC2626
- danger-50    #FEF2F2

Info:
- info-600     #0284C7
- info-50      #F0F9FF

Usage:
- Success for published, completed, and positive states.
- Warning for draft, under review, and caution states.
- Danger for errors, deletion, obsolete, and destructive actions.
- Info for neutral informational messages.

## 3.4 Resource Status Badge Colors

Map resource lifecycle states to semantic colors:

- Draft        → neutral
- Under Review → warning
- Published    → success
- Archived     → info
- Obsolete     → danger

## 3.5 Color Usage Rules

- Never use color alone to convey state. Pair with text or icon.
- Never use pure black (#000000) for body text. Use neutral-900.
- Never use pure white (#FFFFFF) on large areas without a border or subtle background in bright contexts.
- Ensure all text on colored backgrounds meets AA contrast.
- Primary color must not be used for large background fills except in subtle 50/100 tints.

---

# 4. Typography

## 4.1 Font Stack

Use a system font stack for performance and to avoid external font loading on shared hosting.

font-sans:
ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif

font-mono:
ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace

Rules:
- Do not load external web fonts in MVP.
- Use font-sans for all UI text.
- Use font-mono for code, formulas, and technical values.

## 4.2 Type Scale

Use the following semantic sizes:

- Display / Hero:    text-4xl (36px) bold
- Page Title (h1):   text-3xl (30px) semibold
- Section Title (h2):text-2xl (24px) semibold
- Card Title (h3):   text-xl (20px) semibold
- Subtitle (h4):     text-lg (18px) medium
- Body:              text-base (16px) normal
- Small / Caption:   text-sm (14px) normal
- Micro / Label:     text-xs (12px) medium

## 4.3 Typography Rules

- Exactly one h1 per page.
- Logical heading hierarchy, no skipped levels.
- Body text line-height at least 1.5.
- Max line length around 65–75 characters for readability.
- Use semibold for headings, not bold, to keep weight balanced.

---

# 5. Spacing and Layout

## 5.1 Container Widths

- Public content max width: max-w-7xl with horizontal padding.
- Reading content (legal, knowledge) max width: max-w-3xl.
- Admin content max width: max-w-7xl.

## 5.2 Spacing Scale

Use Tailwind spacing tokens. Do not invent arbitrary pixel values.

Common patterns:
- Section vertical padding: py-8 (mobile), py-12 (desktop).
- Card padding: p-4 (mobile), p-6 (desktop).
- Grid gap: gap-4 or gap-6.
- Form field spacing: space-y-4 or space-y-6.

## 5.3 Grid

- Public resource grid: 1 column mobile, 2 columns tablet, 3 columns desktop.
- Admin tables: full width with horizontal scroll on small screens.

---

# 6. Borders, Radius, and Shadows

## 6.1 Border Radius

- Default radius: rounded-md (6px) for inputs and small buttons.
- Cards and containers: rounded-lg (8px).
- Badges: rounded-full.
- Avoid large rounded-2xl or rounded-3xl for a professional look.

## 6.2 Borders

- Default border: border border-neutral-200.
- Input focus: border-primary-500 with ring.
- Dividers: border-neutral-200.

## 6.3 Shadows

Use minimal shadows.

- Cards: shadow-sm.
- Dropdowns/modals: shadow-lg.
- Avoid heavy shadows on normal surfaces.

---

# 7. Component Visual Specifications

## 7.1 Buttons

Primary button:
- Background primary-600.
- Hover primary-700.
- Text white.
- Font medium.
- Rounded-md.
- Padding px-4 py-2.
- Visible focus ring.

Secondary button:
- Background white.
- Border neutral-300.
- Text neutral-700.
- Hover background neutral-50.

Danger button:
- Background danger-600.
- Hover danger-700.
- Text white.

Ghost button:
- Transparent background.
- Text primary-700.
- Hover background primary-50.

Disabled state:
- Reduced opacity.
- cursor-not-allowed.

## 7.2 Inputs

- Border neutral-300.
- Rounded-md.
- Padding px-3 py-2.
- Focus: border-primary-500 with ring-primary-500/20.
- Error: border-danger-500 with error message below.
- Disabled: background neutral-100.

## 7.3 Cards

- Background white.
- Border neutral-200.
- Rounded-lg.
- Shadow-sm.
- Padding p-4 or p-6.
- Hover: subtle border-primary-300 for interactive cards.

## 7.4 Tables

- Header: background neutral-50, text neutral-600, uppercase text-xs.
- Rows: border-b border-neutral-200.
- Striped option: even:bg-neutral-50.
- Hover: hover:bg-neutral-50.

## 7.5 Alerts

- Use semantic background-50 with border and text-600/800.
- Icon + message.
- role="alert" for errors, role="status" for success.

## 7.6 Badges

- rounded-full.
- Small text (text-xs).
- Semantic background-50 with text-600/700 and subtle border.

## 7.7 Navigation

Admin sidebar:
- Background neutral-900 or white depending on chosen scheme. For MVP use white sidebar with neutral-200 border.
- Active item: background primary-50, text primary-700, left border indicator.
- Hover: background neutral-50.

Public header:
- Background white.
- Border-b neutral-200.
- Active link: text primary-700.

## 7.8 Empty States

- Centered.
- Neutral icon.
- Clear title and description.
- Optional call-to-action button.
- No fake data.

---

# 8. Dark Mode Policy

## 8.1 MVP Decision

Dark mode is NOT required for the MVP.

The platform will ship with a light theme only for the initial release.

## 8.2 Future Support

If dark mode is added later:
- Use Tailwind darkMode: 'class'.
- Define dark variants for all tokens.
- Ensure contrast still meets WCAG AA.
- Add a user preference toggle with system default.

Do not implement dark mode until explicitly approved.

---

# 9. Tailwind Theme Extension

The following tokens must be added to tailwind.config.js under theme.extend.

colors:

primary:
  50: '#F0FDFA'
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
  50: '#F0FDF4'
  600: '#16A34A'

warning:
  50: '#FFFBEB'
  600: '#D97706'

danger:
  50: '#FEF2F2'
  600: '#DC2626'

info:
  50: '#F0F9FF'
  600: '#0284C7'

Note:
- Use Tailwind's built-in slate for neutral colors. Do not redefine neutral.
- If a full semantic scale is needed later, extend it explicitly via a change request.

fontFamily:

sans:
  ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif

mono:
  ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace

boxShadow:

card: '0 1px 2px 0 rgb(0 0 0 / 0.05)'

Rules:
- Do not add arbitrary color values outside this system.
- Do not add external font imports.
- Keep the token set minimal and consistent.

---

# 10. Accessibility Requirements

- All text must meet WCAG 2.2 AA contrast ratios.
- Primary buttons must have a visible focus ring.
- Links must be distinguishable from text (color plus underline or context).
- Interactive controls must have a minimum touch target of 44x44 CSS pixels.
- Focus order must be logical.
- Respect prefers-reduced-motion.
- Color must never be the only state indicator.

---

# 11. Implementation Steps

1. Add the color, font, and shadow tokens to tailwind.config.js.
2. Update resources/css/app.css with base body styles (background neutral-50, text neutral-900, font-sans).
3. Update existing UI components (button, input, alert, badge, card, table, empty-state) to use primary and semantic tokens instead of default colors.
4. Update admin sidebar, header, and navigation to use the defined active and hover states.
5. Update public header, footer, and resource cards to use the theme tokens.
6. Verify focus states and contrast across components.
7. Rebuild assets and visually verify key pages (home, admin dashboard, login, search).

---

# Document Completion Check

## Requirements Covered

- Design principles defined.
- Color system defined.
- Typography system defined.
- Spacing and layout rules defined.
- Border, radius, and shadow rules defined.
- Component visual specifications defined.
- Dark mode policy defined.
- Tailwind theme extension tokens defined.
- Accessibility requirements defined.
- Implementation steps defined.

## Decisions Established

- Primary color is clinical teal.
- Neutral color is slate.
- Semantic colors mapped to success, warning, danger, info.
- System font stack used, no external fonts.
- Minimal shadows, rounded-md/lg, no glassmorphism.
- Dark mode deferred (light theme only for MVP).
- All components must use design tokens, not arbitrary values.

## Dependencies

- Approved Phase 01 Project Charter.
- Approved Phase 02 Admin and Public Foundation.
- Completed Build 5 and Build 6 UI components.

## Files Affected

This document creates:

03_Resource_Architecture/10_Design_System_and_UI_Theme.md

It dictates updates to tailwind.config.js, app.css, and all UI components.

## Open Questions

1. Should the admin sidebar use a dark (neutral-900) background or the MVP white background?
2. Should a custom logo or wordmark be designed for the header?

## TBD Decisions

- TBD: Final admin sidebar background scheme.
- TBD: Logo/wordmark design.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE03-DOC10

## Context

LabGato requires a locked, professional design system so all current and future UI is consistent and brand-aligned. This document defines the color palette, typography, spacing, component specs, and Tailwind theme tokens.

This prompt implements the documentation outcome of the Design System and UI Theme document.

## Objective

Create the official LabGato Design System and UI Theme document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding visual specification for all UI.

## Existing Architecture

Relevant approved decisions:

- Tailwind CSS is the styling system.
- Components were built in Build 5 and Build 6 with default colors.
- Professional, accessible, no-decoration design philosophy is approved.

## Files to Inspect

Inspect, if available:

- docs/
- tailwind.config.js
- resources/css/app.css
- resources/views/components/ui/
- resources/views/components/admin/
- resources/views/components/public/

## Files to Create

Create:

03_Resource_Architecture/10_Design_System_and_UI_Theme.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes in this documentation step. UI changes occur in the theme implementation build.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact. This is an internal design document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the color palette and Tailwind tokens.
- The file defines the dark mode policy.

## Integration Requirements

This document must be used as the primary reference when applying the theme to tailwind.config.js and updating UI components.

## Definition of Done

This implementation is complete when:

- The Design System and UI Theme file exists.
- The file matches the approved Phase 03 file tree amendment.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.