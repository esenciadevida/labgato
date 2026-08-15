labgato/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Member/
│   │       │   └── DashboardController.php
│   │       └── PublicSite/
│   │           ├── CalculatorController.php
│   │           ├── ToolController.php
│   │           ├── ReferenceRangeController.php
│   │           ├── SopController.php
│   │           ├── InterpretationController.php
│   │           └── KnowledgeController.php
│   ├── Models/
│   │   └── RecentlyViewed.php
│   └── Services/
│       ├── RecentlyViewedService.php
│       └── RecommendationService.php
├── database/
│   └── migrations/
│       └── xxxx_create_recently_viewed_table.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── public/
│       │       ├── recently-viewed.blade.php
│       │       └── recommended.blade.php
│       ├── member/
│       │   └── dashboard.blade.php
│       └── public/
│           └── home.blade.php
└── tests/
    └── Feature/
        └── PersonalizationTest.php
IMPLEMENTATION PROMPT ID: LABGATO-BUILD37-PERSONALIZATION

CONTEXT:
LabGato has a localStorage-based "recently viewed" feature (Build 23). We are upgrading it: members get server-side tracking (persists across devices), and we add a "Recommended for you" engine plus a personalized member dashboard.

OBJECTIVE:
Implement server-side recently viewed tracking for members, an enhanced recently viewed component, a recommendation engine based on viewing history, and a personalized member dashboard.

EXISTING ARCHITECTURE:
- 6 resource types (calculators, tools, reference-ranges, sops, interpretation, knowledge).
- Member dashboard exists (Build 27).
- localStorage recently-viewed component exists (Build 23).
- Categories/tags system exists.

FILES TO INSPECT:
- resources/views/components/public/recently-viewed.blade.php
- resources/views/member/dashboard.blade.php
- resources/views/public/home.blade.php
- app/Http/Controllers/Member/DashboardController.php
- app/Http/Controllers/PublicSite/ (all 6 resource controllers)
- app/Models/Category.php

FILES TO CREATE:
- database/migrations/xxxx_create_recently_viewed_table.php
- app/Models/RecentlyViewed.php
- app/Services/RecentlyViewedService.php
- app/Services/RecommendationService.php
- resources/views/components/public/recommended.blade.php
- tests/Feature/PersonalizationTest.php

FILES TO MODIFY:
- app/Http/Controllers/PublicSite/ (all 6 resource controllers — track views)
- app/Http/Controllers/Member/DashboardController.php
- resources/views/components/public/recently-viewed.blade.php
- resources/views/member/dashboard.blade.php
- resources/views/public/home.blade.php

TASK 1: SCHEMA

Create migration: create_recently_viewed_table.

Columns:
- id: primary key
- user_id: unsignedBigInteger, foreign key to users.id, cascade on delete, indexed
- resource_type: string (calculator, tool, reference-range, sop, interpretation, knowledge)
- resource_id: unsignedBigInteger
- viewed_at: timestamp
- created_at, updated_at

Indexes:
- Unique composite on (user_id, resource_type, resource_id).
- Index on (user_id, viewed_at desc).

TASK 2: MODEL + SERVICE

Create app/Models/RecentlyViewed.php:
- Fillable: user_id, resource_type, resource_id, viewed_at.
- Cast viewed_at to datetime.

Create app/Services/RecentlyViewedService.php:

Methods:
- track(User $user, string $resourceType, int $resourceId): void
  - Upsert: if the (user, type, id) exists, update viewed_at to now.
  - If not, insert.
  - Keep only the most recent 20 per user (delete older).

- getRecent(User $user, int $limit = 8): Collection
  - Return the user's recently viewed, ordered by viewed_at desc.
  - Resolve each to its actual resource model (fetch title, url, type).
  - Exclude resources that are no longer published.

TASK 3: TRACK VIEWS IN CONTROLLERS

Modify the show method of all 6 public resource controllers:
- CalculatorController, ToolController, ReferenceRangeController, SopController, InterpretationController, KnowledgeController.

Logic in each show method:
- If the user is authenticated (auth()->check()), call RecentlyViewedService::track(auth()->user(), $type, $resource->id).
- Do this only for published resources.
- Do not track for guests (they use localStorage).

TASK 4: ENHANCED RECENTLY VIEWED COMPONENT

Modify resources/views/components/public/recently-viewed.blade.php.

Enhance the existing component:
- For members: read from the server (pass recently viewed data from the controller).
- For guests: keep the existing localStorage behavior.
- Improve the visual design:
  - Rich cards with type icon (calculator, tool, document, etc.).
  - Resource title, type badge, and "viewed X ago".
  - Horizontal scroll on mobile, grid on desktop.
- Add a "Clear" button.

TASK 5: RECOMMENDATION ENGINE

Create app/Services/RecommendationService.php.

Method:
- getRecommendedFor(User $user, int $limit = 6): Collection

Logic:
1. Get the user's recently viewed resources.
2. Extract the categories of those resources.
3. Find published resources in those same categories that the user has NOT recently viewed.
4. Prioritize newer resources.
5. Return up to $limit resources (merged across types).
6. If no recently viewed history, return the latest published resources (fallback).

Implementation note:
- Since resources are in 6 tables, query each type's category pivot for matching categories.
- Merge results, sort by published_at desc, and limit.

TASK 6: RECOMMENDED COMPONENT

Create resources/views/components/public/recommended.blade.php.

Props: resources (collection).

Layout:
- Section title: "Recommended for you".
- Grid of resource cards (title, type badge, short description).
- Link to each resource.
- Empty state if no recommendations.

TASK 7: PERSONALIZED MEMBER DASHBOARD

Modify app/Http/Controllers/Member/DashboardController.php:
- Fetch recently viewed via RecentlyViewedService.
- Fetch recommendations via RecommendationService.
- Pass both to the view.

Modify resources/views/member/dashboard.blade.php:
- Enhance the welcome header: "Good morning/afternoon/evening, {display_name}" (time-based greeting).
- Add a "Recently Viewed" section (using the enhanced component).
- Add a "Recommended for you" section.
- Keep the existing contribution stats and quick actions.

Layout suggestion:
- Top: Welcome + quick stats.
- Middle: Quick actions + Recently Viewed.
- Bottom: Recommended for you.

TASK 8: HOME PAGE ENHANCEMENT

Modify resources/views/public/home.blade.php:
- For members: show "Recently Viewed" (server-side) and "Recommended for you".
- For guests: show the existing localStorage recently-viewed.
- Ensure the sections only appear if there's data.

TASK 9: FEATURE TESTS

Create tests/Feature/PersonalizationTest.php:
- Test viewing a resource as a member records it in recently_viewed.
- Test viewing as a guest does NOT record in recently_viewed.
- Test recently viewed is limited to 20 per user.
- Test recently viewed excludes unpublished resources.
- Test recommendation returns resources in matching categories.
- Test recommendation excludes recently viewed resources.
- Test recommendation falls back to latest when no history.
- Test member dashboard shows recently viewed and recommendations.
- Test guest home page shows localStorage recently-viewed component.

PERFORMANCE REQUIREMENTS:
- Tracking a view must be fast (single upsert query).
- Fetching recently viewed must be efficient (indexed query).
- Recommendations should not run more than a handful of queries.
- Do not track views for bots/crawlers (check user agent if practical).

DEFINITION OF DONE:
- recently_viewed table exists with proper indexes.
- Member resource views are tracked server-side.
- Guest views remain localStorage-only.
- Enhanced recently viewed component works for both members and guests.
- Recommendation engine returns relevant, unpublished-excluded results.
- Member dashboard is personalized with greeting, recently viewed, and recommendations.
- Home page shows personalized sections for members.
- All feature tests pass.