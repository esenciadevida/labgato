labgato/
├── app/
│   └── Http/
│       └── Controllers/
│           └── PublicSite/
│               └── ContributorController.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── public/
│       │       ├── avatar.blade.php
│       │       └── contributor-credit.blade.php
│       └── public/
│           ├── contributors/
│           │   └── show.blade.php
│           ├── sops/
│           │   └── show.blade.php
│           ├── knowledge/
│           │   └── show.blade.php
│           └── interpretation/
│               └── show.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── PublicSite/
            ├── ContributorProfileTest.php
            └── ContributorAttributionTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE06-BUILD29

CONTEXT:
LabGato contribution submission and approval (Build 28) are complete. Members can submit contributions and admins can approve them. We are now implementing Build 29: Contributor Credit & Public Profiles (Slice 14), the final build of Phase 06.

This build implements public contributor profiles and resource page attribution.

OBJECTIVE:
Implement the public contributor profile page, the credit attribution component, resource detail page updates, JSON-LD structured data, and the deactivated member credit behavior.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Contributor profiles use profile_slug for stable URLs.
- Profiles are public and indexable.
- Deactivated members lose their profile page, but approved contributions remain with plain-text credit.
- Admin-created resources do not show a contributor credit block.
- JSON-LD Person schema is used for contributor profiles and resource authorship.

FILES TO INSPECT:
- app/Models/User.php
- app/Models/SopResource.php
- app/Models/KnowledgeResource.php
- app/Models/InterpretationResource.php
- resources/views/public/sops/show.blade.php
- resources/views/public/knowledge/show.blade.php
- resources/views/public/interpretation/show.blade.php
- resources/views/components/public/meta.blade.php
- routes/web.php

FILES TO CREATE:
- app/Http/Controllers/PublicSite/ContributorController.php
- resources/views/components/public/avatar.blade.php
- resources/views/components/public/contributor-credit.blade.php
- resources/views/public/contributors/show.blade.php
- tests/Feature/PublicSite/ContributorProfileTest.php
- tests/Feature/PublicSite/ContributorAttributionTest.php

FILES TO MODIFY:
- resources/views/public/sops/show.blade.php
- resources/views/public/knowledge/show.blade.php
- resources/views/public/interpretation/show.blade.php
- routes/web.php

TASK 1: AVATAR COMPONENT

Create resources/views/components/public/avatar.blade.php.

Props:
- name: string (the display name to derive initials from)
- userId: int (for color hashing)
- size: string (default 'md', options: 'sm', 'md', 'lg')

Behavior:
- Generate initials from the name (e.g., "John Doe" → "JD").
- Use a colored circle background derived from a hash of userId.
- Display the initials in white text.
- Sizes: sm (32px), md (40px), lg (64px).

This component is used in the member area, contributor profiles, and credit blocks.

TASK 2: CONTRIBUTOR PROFILE CONTROLLER

Create app/Http/Controllers/PublicSite/ContributorController.php.

Method: show(string $profile_slug)

Logic:
1. Find user by profile_slug.
2. If not found, return 404.
3. If user is_active is false, return 404.
4. Query published contributions across sop_resources, knowledge_resources, interpretation_resources where contributed_by = user id and status = 'published'.
5. If no published contributions, return 404.
6. Merge and sort contributions by published_at desc.
7. Paginate (12 per page).
8. Render the profile view.

TASK 3: CONTRIBUTOR PROFILE VIEW

Create resources/views/public/contributors/show.blade.php.

Layout:
- Use the public layout.
- Max width container (max-w-4xl).

Sections:
1. Header:
   - Large initials-based avatar.
   - Display name as h1.
   - Bio (if provided).
   - "Member since {Month, Year}".

2. Contributions:
   - Grid or list of published contributions.
   - Each item: Title, Type badge, Published date, short description.
   - Link to the public resource page.
   - Pagination.

SEO:
- Title: "{Display Name} | LabGato Contributor"
- Meta description from bio.
- JSON-LD ProfilePage and Person schema.

TASK 4: CREDIT ATTRIBUTION COMPONENT

Create resources/views/components/public/contributor-credit.blade.php.

Props:
- contributor: the User model (or null).

Behavior:
- If contributor is null, render nothing.
- If contributor is_active is true:
  - Render avatar + "Contributed by [Display Name]" with link to /contributors/{profile_slug}.
- If contributor is_active is false:
  - Render avatar + "Contributed by [Display Name]" as plain text (no link).

Visual:
- Subtle, professional layout.
- Small avatar (sm size).
- Text in neutral-600.
- Positioned below resource title/metadata, before main content.

TASK 5: UPDATE RESOURCE DETAIL PAGES

Modify the three public resource detail pages:

resources/views/public/sops/show.blade.php
resources/views/public/knowledge/show.blade.php
resources/views/public/interpretation/show.blade.php

Add the contributor credit component:
- Pass the resource's contributed_by user.
- Position it below the title and metadata, before the main content.
- Ensure it appears after any mandatory disclaimers.

Example:
<x-public.contributor-credit :contributor="$resource->contributor" />

Ensure the resource models have a contributor() relationship:
- Add to SopResource, KnowledgeResource, InterpretationResource:
  public function contributor() { return $this->belongsTo(User::class, 'contributed_by'); }

TASK 6: JSON-LD STRUCTURED DATA

Update the contributor profile view to include JSON-LD:

{
  "@context": "https://schema.org",
  "@type": "ProfilePage",
  "mainEntity": {
    "@type": "Person",
    "name": "{display_name}",
    "description": "{bio}",
    "url": "{profile_url}"
  }
}

Update the three resource detail pages to include author in their existing JSON-LD:

If contributed_by is set:
"author": {
  "@type": "Person",
  "name": "{display_name}",
  "url": "{profile_url}"
}

If contributed_by is null (admin-created), omit the author field or use the organization.

TASK 7: ROUTES

Add contributor profile route to routes/web.php:

Route::get('/contributors/{profile_slug}', [ContributorController::class, 'show'])->name('contributors.show');

TASK 8: FEATURE TESTS

Create tests/Feature/PublicSite/ContributorProfileTest.php:
- Test active member's profile renders at /contributors/{slug}.
- Test profile displays display_name, bio, and member since.
- Test profile lists only published contributions.
- Test profile does not list draft, under_review, or rejected contributions.
- Test deactivated member's profile returns 404.
- Test profile with zero published contributions returns 404.
- Test nonexistent profile_slug returns 404.
- Test profile includes JSON-LD Person schema.

Create tests/Feature/PublicSite/ContributorAttributionTest.php:
- Test member-contributed SOP displays credit block.
- Test member-contributed Knowledge displays credit block.
- Test member-contributed Interpretation displays credit block.
- Test admin-created resource does not display credit block.
- Test credit block links to profile for active contributor.
- Test credit block is plain text for deactivated contributor.
- Test resource detail JSON-LD includes author for contributed resources.

SEO REQUIREMENTS:
- Contributor profiles are indexable (no noindex).
- Profiles with zero published contributions return 404 (not indexed).
- JSON-LD ProfilePage and Person schema on profile pages.
- JSON-LD author property on contributed resource pages.

ACCESSIBILITY REQUIREMENTS:
- Profile page has one h1 (display name).
- Contributions list uses semantic markup.
- Credit block is readable and not color-only.
- Avatar has appropriate aria-label or is aria-hidden if decorative.

DEFINITION OF DONE:
- Avatar component renders initials-based avatars.
- Public contributor profile renders for active members with published contributions.
- Deactivated members' profiles return 404.
- Credit attribution appears on contributed SOP, Knowledge, and Interpretation pages.
- Admin-created resources do not show credit.
- Deactivated contributors show plain-text credit (no link).
- JSON-LD Person schema is present on profiles and contributed resources.
- All feature tests pass.