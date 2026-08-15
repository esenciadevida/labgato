LABGOTO/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── public.blade.php
│   │   │   ├── admin.blade.php
│   │   │   ├── auth.blade.php
│   │   │   └── error.blade.php
│   │   ├── components/
│   │   │   ├── ui/
│   │   │   │   ├── button.blade.php
│   │   │   │   ├── input.blade.php
│   │   │   │   ├── select.blade.php
│   │   │   │   ├── textarea.blade.php
│   │   │   │   ├── alert.blade.php
│   │   │   │   ├── badge.blade.php
│   │   │   │   ├── card.blade.php
│   │   │   │   ├── table.blade.php
│   │   │   │   └── empty-state.blade.php
│   │   │   ├── form/
│   │   │   │   ├── label.blade.php
│   │   │   │   ├── error.blade.php
│   │   │   │   └── section.blade.php
│   │   │   ├── admin/
│   │   │   │   ├── sidebar.blade.php
│   │   │   │   ├── header.blade.php
│   │   │   │   ├── navigation.blade.php
│   │   │   │   └── navigation-item.blade.php
│   │   │   └── public/
│   │   │       ├── header.blade.php
│   │   │       ├── footer.blade.php
│   │   │       ├── navigation.blade.php
│   │   │       ├── meta.blade.php
│   │   │       ├── breadcrumbs.blade.php
│   │   │       └── search/
│   │   │           ├── form.blade.php
│   │   │           └── result-item.blade.php
│   │   ├── public/
│   │   │   ├── home.blade.php
│   │   │   ├── search.blade.php
│   │   │   ├── calculators/
│   │   │   ├── tools/
│   │   │   ├── reference-ranges/
│   │   │   ├── sops/
│   │   │   ├── interpretation/
│   │   │   └── knowledge/
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── categories/
│   │   │   ├── tags/
│   │   │   ├── calculators/
│   │   │   ├── tools/
│   │   │   ├── reference-ranges/
│   │   │   ├── sops/
│   │   │   ├── interpretation/
│   │   │   └── knowledge/
│   │   └── livewire/
│   │       └── public/
│   │           ├── calculators/
│   │           │   └── calculator-runner.blade.php
│   │           └── tools/
│   │               └── tool-runner.blade.php
├── tailwind.config.js
└── tests/
    └── Feature/
        └── Accessibility/
            ├── KeyboardNavigationTest.php
            ├── FormAccessibilityTest.php
            ├── ColorContrastTest.php
            └── ScreenReaderSupportTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-HARDENING-SPRINT3

CONTEXT:
LABGOTO MVP feature development, security hardening, and performance/SEO optimization are complete. We are now in Sprint 3 of the MVP Hardening Phase, focusing on Accessibility and UI/UX Polish.

LABGOTO targets WCAG 2.2 Level AA compliance for all public and administrative interfaces. Because laboratory professionals may use assistive technologies, accessibility is not optional.

The approved design system defines a clinical teal primary color, slate neutrals, semantic colors, system fonts, and specific component visual specifications. All UI must be consistent with this system.

OBJECTIVE:
Audit and fix all accessibility issues across public and admin interfaces. Verify keyboard navigation, screen reader compatibility, color contrast, focus management, and touch targets. Ensure UI consistency with the approved design system. Write accessibility regression tests.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Target accessibility standard is WCAG 2.2 AA.
- Primary color is clinical teal.
- Neutral color is slate.
- Semantic colors are success, warning, danger, info.
- System font stack, no external fonts.
- Minimal shadows, rounded-md/lg, no glassmorphism.
- Light theme only for MVP.
- Skip links are mandatory.
- Focus states must be visible.
- Touch targets must be at least 44x44 CSS pixels.
- Reduced motion preferences must be respected.

FILES TO INSPECT:
- resources/views/layouts/ (all layouts)
- resources/views/components/ (all components)
- resources/views/public/ (all public views)
- resources/views/admin/ (all admin views)
- resources/views/livewire/ (all Livewire views)
- resources/css/app.css
- tailwind.config.js
- tests/Feature/ (existing tests)

FILES TO CREATE:
- tests/Feature/Accessibility/KeyboardNavigationTest.php
- tests/Feature/Accessibility/FormAccessibilityTest.php
- tests/Feature/Accessibility/ColorContrastTest.php
- tests/Feature/Accessibility/ScreenReaderSupportTest.php

FILES TO MODIFY:
- Any view or component with accessibility issues.
- resources/css/app.css if focus states or reduced motion need adjustment.
- tailwind.config.js if color contrast needs adjustment.

TASK 1: SKIP LINK VERIFICATION

Verify all layouts include skip links.

Requirements:
1. Public layout must include a "Skip to main content" link as the first focusable element.
2. Admin layout must include a "Skip to main content" link as the first focusable element.
3. Auth layout must include a "Skip to main content" link.
4. Error layout must include a "Skip to main content" link.
5. Skip links must be visually hidden until focused.
6. Skip links must point to the main content area using an anchor.

Example:
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-md">Skip to main content</a>

TASK 2: KEYBOARD NAVIGATION AUDIT

Test all interactive elements for keyboard accessibility.

Requirements:
1. All links, buttons, inputs, selects, and textareas must be reachable via Tab key.
2. Tab order must be logical and follow visual order.
3. All interactive elements must have visible focus states.
4. Modal dialogs must trap focus when open.
5. Modal dialogs must return focus to trigger element when closed.
6. Mobile menus must support Escape key to close.
7. Dropdown menus must support arrow key navigation.
8. No keyboard traps must exist.

Focus state requirements:
- Use focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 for interactive elements.
- Ensure focus ring is visible on all backgrounds.
- Do not remove focus outlines without providing an alternative.

TASK 3: SCREEN READER COMPATIBILITY AUDIT

Test all pages for screen reader compatibility.

Requirements:
1. All form inputs must have associated labels using for and id attributes.
2. All buttons must have accessible text (either visible text or aria-label).
3. All images must have alt text. Decorative images must use alt="".
4. All icons must have aria-hidden="true" if decorative, or accessible text if informative.
5. All alerts and status messages must use appropriate ARIA roles:
   - role="alert" for errors and warnings.
   - role="status" for success and informational messages.
6. All tables must use proper <th scope="col"> and <th scope="row"> attributes.
7. All navigation landmarks must use semantic HTML (<nav>, <main>, <header>, <footer>).
8. All heading levels must be logical (no skipped levels).
9. All dynamic content updates (Livewire) must use aria-live regions.

TASK 4: COLOR CONTRAST VERIFICATION

Verify all text meets WCAG 2.2 AA contrast ratios.

Requirements:
1. Normal text must have at least 4.5:1 contrast ratio.
2. Large text (18pt or 14pt bold) must have at least 3:1 contrast ratio.
3. UI components and graphical objects must have at least 3:1 contrast ratio.

Check the following:
- Body text (neutral-900 on neutral-50).
- Secondary text (neutral-500 on neutral-50).
- Primary buttons (white on primary-600).
- Secondary buttons (neutral-700 on white).
- Danger buttons (white on danger-600).
- Links (primary-700 on neutral-50).
- Alert text (semantic-600 on semantic-50).
- Badge text (semantic-600/700 on semantic-50).
- Form labels (neutral-700 on white).
- Placeholder text (neutral-400 on white).

If any contrast ratio fails, adjust the color tokens in tailwind.config.js.

TASK 5: TOUCH TARGET SIZE VERIFICATION

Verify all interactive elements meet minimum touch target size.

Requirements:
1. All buttons, links, inputs, and interactive elements must have a minimum touch target of 44x44 CSS pixels.
2. Use padding to increase touch targets if the visual element is smaller.
3. Ensure adequate spacing between interactive elements to prevent accidental activation.

TASK 6: REDUCED MOTION SUPPORT

Verify all animations and transitions respect prefers-reduced-motion.

Requirements:
1. Add a CSS media query to disable non-essential animations and transitions when prefers-reduced-motion is set.

Example:
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

2. Essential animations (e.g., loading indicators) may remain but should be subtle.

TASK 7: FORM ACCESSIBILITY AUDIT

Verify all forms are accessible.

Requirements:
1. All form fields must have visible labels.
2. Required fields must be indicated visually and programmatically.
3. Validation errors must be displayed near the relevant field.
4. Validation errors must be associated with the field using aria-describedby.
5. Invalid fields must have aria-invalid="true".
6. Error messages must use text-danger-600 color.
7. Form groups must use fieldset and legend where appropriate.
8. Autocomplete attributes must be used where appropriate (e.g., autocomplete="email", autocomplete="current-password").

TASK 8: MOBILE RESPONSIVENESS VERIFICATION

Verify all pages are responsive and usable on mobile devices.

Requirements:
1. Public pages must be usable on screens as small as 320px wide.
2. Admin pages must be usable on screens as small as 320px wide.
3. Navigation must collapse into mobile menu on small screens.
4. Tables must scroll horizontally on small screens.
5. Forms must stack vertically on small screens.
6. Touch targets must be large enough on mobile.
7. Text must be readable without horizontal scrolling.

TASK 9: UI CONSISTENCY AUDIT

Verify all UI components are consistent with the approved design system.

Requirements:
1. All buttons must use the theme button variants (primary, secondary, danger, ghost).
2. All inputs must use the theme input styles.
3. All cards must use the theme card styles.
4. All alerts must use the theme alert styles.
5. All badges must use the theme badge styles.
6. All tables must use the theme table styles.
7. No default Tailwind blue or inconsistent colors should remain.
8. No gradients, glassmorphism, or heavy shadows should exist.

TASK 10: ACCESSIBILITY REGRESSION TESTS

Create the following test files to lock in accessibility requirements.

1. tests/Feature/Accessibility/KeyboardNavigationTest.php
   - Test that skip links exist on public layout.
   - Test that skip links exist on admin layout.
   - Test that all interactive elements have focus states.
   - Test that mobile menu supports Escape key.

2. tests/Feature/Accessibility/FormAccessibilityTest.php
   - Test that all form inputs have associated labels.
   - Test that required fields are indicated.
   - Test that validation errors are associated with fields.
   - Test that invalid fields have aria-invalid.

3. tests/Feature/Accessibility/ColorContrastTest.php
   - Test that body text meets contrast requirements.
   - Test that primary buttons meet contrast requirements.
   - Test that links meet contrast requirements.
   - Test that alert text meets contrast requirements.

4. tests/Feature/Accessibility/ScreenReaderSupportTest.php
   - Test that all images have alt text.
   - Test that all buttons have accessible text.
   - Test that alerts use appropriate ARIA roles.
   - Test that tables use proper th scope attributes.
   - Test that navigation uses semantic HTML.

ACCESSIBILITY REQUIREMENTS:
- All pages must pass WCAG 2.2 AA automated checks.
- All pages must pass manual keyboard navigation testing.
- All pages must be usable with screen readers.
- All color contrast must meet AA ratios.
- All touch targets must be at least 44x44 pixels.
- All animations must respect reduced motion preferences.

UI/UX REQUIREMENTS:
- All components must be consistent with the design system.
- No default Tailwind colors should remain.
- No gradients, glassmorphism, or heavy shadows.
- All empty states must be honest and useful.
- All loading states must be clear.
- All error states must be safe and helpful.

DEFINITION OF DONE:
- All skip links are present and functional.
- All interactive elements are keyboard accessible.
- All form inputs have associated labels.
- All images have alt text.
- All color contrast meets WCAG 2.2 AA.
- All touch targets are at least 44x44 pixels.
- All animations respect reduced motion.
- All pages are responsive on mobile.
- All components are consistent with the design system.
- All accessibility regression tests pass.
- No existing functionality is broken.