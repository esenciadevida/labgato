labgato/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Admin/
│   │           └── DashboardController.php
│   └── Services/
│       └── Analytics/
│           └── AnalyticsDashboardService.php
├── resources/
│   └── views/
│       └── admin/
│           ├── dashboard-advanced.blade.php
│           └── partials/
│               └── analytics/
│                   ├── kpi-cards.blade.php
│                   ├── resource-growth.blade.php
│                   ├── search-insights.blade.php
│                   └── ai-utilization.blade.php
└── tests/
    └── Feature/
        └── Admin/
            └── DashboardAdvancedTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE05-BUILD20

CONTEXT:
LabGato AI and Analytics foundations (Builds 17-19) are complete. The analytics_daily_metrics table is populated by the nightly aggregation command, and the ai_usage table is tracking AI requests.

We are now implementing Build 20: Advanced Analytics Dashboard UI (Slice 10). This is the final build of Phase 05.

This build creates the Advanced Analytics Dashboard views, the RBAC routing logic, the analytics service with caching, and the accessible CSS-based charts.

OBJECTIVE:
Implement the Advanced Analytics Dashboard UI, ensuring it is performant, accessible, and restricted to users with the analytics.view permission.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Users with analytics.view see the Advanced Dashboard; others see the Basic MVP Dashboard.
- Historical metrics use the analytics_daily_metrics table.
- "Today's" real-time metrics use lightweight, cached, indexed queries.
- Visualizations use Tailwind CSS-based bar charts with mandatory sr-only HTML table fallbacks.
- Search insights identify "Content Gaps" (zero-result searches).

FILES TO INSPECT:
- app/Http/Controllers/Admin/DashboardController.php
- app/Models/AnalyticsDailyMetric.php
- app/Models/AiUsage.php
- app/Models/SearchQuery.php
- resources/views/admin/dashboard.blade.php (Basic MVP dashboard)
- database/migrations/ (analytics_daily_metrics table)

FILES TO CREATE:
- app/Services/Analytics/AnalyticsDashboardService.php
- resources/views/admin/dashboard-advanced.blade.php
- resources/views/admin/partials/analytics/kpi-cards.blade.php
- resources/views/admin/partials/analytics/resource-growth.blade.php
- resources/views/admin/partials/analytics/search-insights.blade.php
- resources/views/admin/partials/analytics/ai-utilization.blade.php
- tests/Feature/Admin/DashboardAdvancedTest.php

FILES TO MODIFY:
- app/Http/Controllers/Admin/DashboardController.php

TASK 1: ANALYTICS DASHBOARD SERVICE

Create app/Services/Analytics/AnalyticsDashboardService.php.

Responsibilities:
- Fetch KPI metrics (Total Published, Pending Review, Today's Search Volume, Today's AI Usage).
- Fetch Resource Growth data for the last 30 days.
- Fetch Search Insights (Top 5 successful searches, Top 5 zero-result searches).
- Fetch AI Utilization metrics for the last 7 days.
- Cache "Today's" real-time metrics for 15 minutes using the database cache driver.

Methods:
- getKpiMetrics(): array
- getResourceGrowth(): array
- getSearchInsights(): array
- getAiUtilization(): array

Implementation rules:
- Use AnalyticsDailyMetric for historical data (last 30 days).
- Use direct, indexed queries on live tables for "Today's" KPIs, wrapped in Cache::remember('analytics:today:kpis', 900, ...).
- For Search Insights, query the search_queries table directly for the last 7 days, grouped by query, ordered by count, limited to 5.
- For AI Utilization, query the ai_usage table for the last 7 days.

TASK 2: DASHBOARD CONTROLLER RBAC ROUTING

Modify app/Http/Controllers/Admin/DashboardController.php.

Update the index method to resolve the correct view based on permissions:

public function index(AnalyticsDashboardService $analyticsService)
{
    if (auth()->user()->can('analytics.view')) {
        $data = [
            'kpis' => $analyticsService->getKpiMetrics(),
            'resourceGrowth' => $analyticsService->getResourceGrowth(),
            'searchInsights' => $analyticsService->getSearchInsights(),
            'aiUtilization' => $analyticsService->getAiUtilization(),
        ];
        return view('admin.dashboard-advanced', $data);
    }

    // Fallback to Basic MVP Dashboard
    return view('admin.dashboard', [
        // existing basic dashboard data
    ]);
}

TASK 3: ADVANCED DASHBOARD VIEWS

Create resources/views/admin/dashboard-advanced.blade.php.

Layout:
- Use the admin layout.
- Display page title: "Analytics Dashboard".
- Render the four partials in a responsive grid.

Create resources/views/admin/partials/analytics/kpi-cards.blade.php.

Display four KPI cards:
1. Total Published Resources (sum of published across all types).
2. Pending Review (count of under_review resources).
3. Today's Search Volume.
4. AI Usage Today (e.g., "45 / 1000 requests").

Create resources/views/admin/partials/analytics/resource-growth.blade.php.

Display a bar chart showing new resources created per day over the last 30 days.

Implementation:
- Use Tailwind CSS flexbox/divs to render horizontal or vertical bars.
- Calculate bar width/height as a percentage of the maximum value.
- Include a visually hidden (<table class="sr-only">) containing the exact data for screen readers.
- Add aria-label to the chart container summarizing the trend.

Create resources/views/admin/partials/analytics/search-insights.blade.php.

Display two lists:
1. Top 5 Successful Searches (query + count).
2. Top 5 Content Gaps (zero-result searches + count).

Rules:
- Display empty states if no search data exists.
- Highlight "Content Gaps" with a warning/info badge to draw administrator attention.

Create resources/views/admin/partials/analytics/ai-utilization.blade.php.

Display AI metrics for the last 7 days:
1. Total AI Requests.
2. Success vs. Failure rate (simple progress bar or percentage).
3. Total Tokens Used.

TASK 4: ACCESSIBILITY & PERFORMANCE

Accessibility requirements:
- All charts must have sr-only HTML table fallbacks.
- Chart containers must have role="img" and descriptive aria-labels.
- KPI cards must use semantic heading structure.
- Color contrast must meet WCAG 2.2 AA.

Performance requirements:
- The Advanced Dashboard must load within 500ms.
- "Today's" KPI queries must be cached for 15 minutes.
- Historical queries must use the analytics_daily_metrics table, not raw log tables.

TASK 5: FEATURE TESTS

Create tests/Feature/Admin/DashboardAdvancedTest.php.

Required tests:
1. test_user_with_analytics_view_sees_advanced_dashboard
   - Authenticate a user with analytics.view permission.
   - Visit admin.dashboard.
   - Assert the advanced dashboard view is rendered.
   - Assert KPI cards are visible.
2. test_user_without_analytics_view_sees_basic_dashboard
   - Authenticate a user without analytics.view permission (but with dashboard.view).
   - Visit admin.dashboard.
   - Assert the basic dashboard view is rendered.
   - Assert advanced widgets are NOT visible.
3. test_advanced_dashboard_displays_search_insights
   - Seed search_queries data.
   - Visit admin.dashboard as authorized user.
   - Assert search insights widget displays data.
4. test_advanced_dashboard_handles_empty_data
   - Visit admin.dashboard with no analytics data.
   - Assert empty states are displayed gracefully.

DEFINITION OF DONE:
- AnalyticsDashboardService fetches and caches metrics correctly.
- DashboardController routes users to the correct view based on analytics.view permission.
- Advanced Dashboard renders KPI cards, Resource Growth chart, Search Insights, and AI Utilization.
- All charts include sr-only HTML table fallbacks.
- "Today's" metrics are cached for 15 minutes.
- Feature tests for RBAC routing and widget rendering pass.
- Dashboard loads within 500ms.
- No raw log tables are queried for historical data.