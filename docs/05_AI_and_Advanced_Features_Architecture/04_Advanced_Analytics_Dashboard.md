# 04 — Advanced Analytics Dashboard

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically Database Foundation, Admin Portal Foundation).
- The approved Phase 03 Resource Architecture (specifically Resource Foundation).
- The approved Phase 05 AI UI and Rate Limiting document (specifically the `ai_usage` table).
- The completed MVP hardening sprints (specifically Performance Audit).

## Defines

This document defines:

- The architecture for the Advanced Analytics Dashboard.
- The data sources and aggregation strategy optimized for shared hosting.
- The Key Performance Indicators (KPIs) and visualization rules.
- The search insights and content gap analysis strategy.
- The AI utilization tracking and reporting.
- The performance, caching, and accessibility requirements for analytical views.

## Affects

This document affects:

- The Post-MVP Build Order document.
- The Admin Dashboard controller and views.
- The database schema (analytics aggregation tables).
- The scheduled task (cron) configuration.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Replaces the basic MVP dashboard with actionable platform intelligence.
- Prevents shared hosting resource exhaustion by using pre-aggregated metrics.
- Provides administrators with insights into content gaps, search trends, and AI costs.
- Ensures analytical data is presented accessibly and performantly.

---

# 1. Purpose

This document establishes the Advanced Analytics Dashboard architecture for LabGato.

The MVP dashboard provides honest, basic counts of system entities. However, platform administrators need actionable insights to guide content creation, understand user intent, and monitor AI costs. 

Because LabGato runs on Hostinger shared hosting, running complex `GROUP BY` and `JOIN` queries across millions of `audit_logs`, `search_queries`, and `ai_usage` records on every page load will crash the server. This document defines a high-performance, pre-aggregated analytics architecture that delivers rich insights without compromising platform stability.

---

# 2. Data Sources & Aggregation Strategy

To ensure sub-500ms page loads on shared hosting, the dashboard will not query raw log tables for historical data. Instead, it will use a daily aggregation strategy.

## 2.1 Raw Data Sources

- **Resources**: `calculators`, `laboratory_tools`, `reference_ranges`, `sop_resources`, `interpretation_resources`, `knowledge_resources`.
- **Search**: `search_queries` table.
- **AI**: `ai_usage` table.
- **Activity**: `audit_logs` table.

## 2.2 The `analytics_daily_metrics` Table

Create a dedicated table to store pre-aggregated daily snapshots.

Columns:
- `id`: primary key
- `date`: date (unique index)
- `resource_type`: string (e.g., 'calculator', 'knowledge', 'all')
- `status`: string (e.g., 'published', 'draft', 'all')
- `total_count`: integer
- `new_count`: integer (created on this date)
- `search_volume`: integer (total searches performed)
- `search_zero_results`: integer (searches that returned 0 results)
- `ai_requests_total`: integer
- `ai_requests_failed`: integer
- `ai_tokens_used`: integer
- `created_at`, `updated_at`

## 2.3 The Aggregation Command

Create an Artisan command: `php artisan analytics:aggregate`

Responsibilities:
1. Run nightly via the Laravel Scheduler (Cron).
2. Query the raw tables for the *previous day's* data.
3. Insert or update the `analytics_daily_metrics` table.
4. Clean up raw `search_queries` and `ai_usage` records older than 90 days to prevent database bloat on shared hosting.

## 2.4 Real-Time "Today" Metrics

For the current day, the dashboard will run lightweight, strictly indexed `COUNT()` queries directly against the live tables. These specific queries will be cached using the database cache driver for 15 minutes to prevent repeated hits.

---

# 3. Dashboard Layout & KPIs

The Advanced Dashboard will be divided into four logical sections.

## 3.1 Row 1: High-Level KPI Cards

Four prominent cards displaying real-time or cached daily metrics:
1. **Total Published Resources**: Sum of all published resources across all 6 types.
2. **Pending Review**: Count of resources with `status = under_review`.
3. **Today's Search Volume**: Total searches performed today.
4. **AI Credits Used Today**: Total AI requests or tokens used today vs. the global daily quota (e.g., "450 / 1000").

## 3.2 Row 2: Resource Growth & Health (Last 30 Days)

- **Visual**: A stacked bar chart or CSS-based bar chart showing new resources created per day, grouped by type.
- **Data Source**: `analytics_daily_metrics` for the last 30 days.
- **Insight**: Helps admins see which content areas are growing and which are stagnant.

## 3.3 Row 3: Search Insights & Content Gaps

- **Top 5 Successful Searches**: The most common queries that yielded results.
- **Top 5 Zero-Result Searches (Content Gaps)**: The most common queries that returned 0 results.
- **Data Source**: Direct query on `search_queries` for the last 7 days, grouped by `query`, ordered by count. (Limited to top 5 to maintain performance).
- **Insight**: Directs administrators on what Knowledge or Interpretation articles to write next.

## 3.4 Row 4: AI Utilization & Health

- **AI Success vs. Failure Rate**: A simple donut or progress bar showing successful generations vs. timeouts/filters.
- **Most Used AI Feature**: E.g., "Draft Knowledge" vs "Summarize SOP".
- **Data Source**: `ai_usage` table (last 7 days).

---

# 4. Visualization & Accessibility Rules

Charts and graphs often introduce severe accessibility and performance issues. LabGato will use strict rules for data visualization.

## 4.1 Charting Library

For the MVP, **do not use heavy JavaScript charting libraries** (like Highcharts or ECharts) to preserve shared hosting performance and minimize JS payload.

Instead, use **Tailwind CSS-based bar charts** (using `div` widths and percentages) or a lightweight, accessible library like Chart.js loaded only on the dashboard page.

## 4.2 Accessibility Requirements (WCAG 2.2 AA)

- **Data Tables Fallback**: Every visual chart must be accompanied by a visually hidden (`sr-only`) or collapsible HTML `<table>` containing the exact same data. Screen readers must read the table, not the chart.
- **Color Blindness**: Do not rely solely on color to differentiate chart segments (e.g., use patterns or direct labels).
- **Contrast**: Chart bars and text must meet the 3:1 contrast ratio for graphical objects.
- **ARIA**: Chart containers must have `role="img"` and an `aria-label` summarizing the trend (e.g., "Bar chart showing resource creation. Highest on Tuesday with 5 new calculators.").

---

# 5. Security & Permissions

## 5.1 RBAC Integration

- The Advanced Dashboard replaces the basic MVP dashboard for users with the `analytics.view` permission.
- Users without `analytics.view` (but with `dashboard.view`) will continue to see the basic MVP dashboard (honest counts only).
- Route: `GET /admin/dashboard` (The controller will resolve the appropriate view based on permissions).

## 5.2 Data Privacy

- Search queries must be sanitized before display.
- The dashboard must never display PII (e.g., IP addresses or specific user names associated with search queries).
- AI prompts and responses must never be displayed on the dashboard; only aggregated counts and token metrics.

---

# 6. Testing Requirements

## 6.1 Unit Tests (Aggregation)

- Test that the `analytics:aggregate` command correctly rolls up raw data into the metrics table.
- Test that the command correctly deletes raw log data older than 90 days.
- Test that "Today's" real-time queries are correctly cached.

## 6.2 Feature Tests (Dashboard UI)

- Test that a user with `analytics.view` sees the advanced KPI cards and charts.
- Test that a user without `analytics.view` (but with `dashboard.view`) sees the basic MVP dashboard.
- Test that the "Zero-Result Searches" widget correctly displays data from the `search_queries` table.
- Test that the dashboard loads within acceptable time limits (< 500ms) even when raw tables contain 10,000+ records.

---

# Document Completion Check

## Requirements Covered

- Data sources and aggregation strategy defined.
- Dashboard layout and KPIs defined.
- Visualization and accessibility rules defined.
- Security and permission rules defined.
- Testing requirements defined.

## Decisions Established

- Historical analytics use a pre-aggregated `analytics_daily_metrics` table to protect shared hosting performance.
- Raw search and AI logs are purged after 90 days.
- "Today's" metrics use lightweight, cached, indexed queries.
- Visualizations prioritize CSS-based charts or lightweight JS, with mandatory HTML table fallbacks for screen readers.
- Search insights are used to identify "Content Gaps" (zero-result searches).

## Dependencies

- Approved Phase 05 AI UI and Rate Limiting (`ai_usage` table).
- Approved Phase 02 Database Foundation.
- Completed MVP Performance Audit.

## Files Affected

This document creates:

05_AI_and_Advanced_Features_Architecture/04_Advanced_Analytics_Dashboard.md

It dictates the implementation of the analytics aggregation command, the metrics table, and the advanced dashboard UI.

## Open Questions

1. Should the aggregation command run daily at midnight, or hourly to provide near-real-time historical updates?
2. Should administrators be able to export the analytics data to CSV?

## TBD Decisions

- TBD: Final aggregation frequency (Daily vs. Hourly).
- TBD: CSV export feature for MVP.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE05-DOC04

## Context

LabGato requires an advanced analytics dashboard that provides actionable insights without crashing the shared hosting environment. This document defines the pre-aggregation strategy, KPIs, content gap analysis, and accessible visualization rules.

This prompt implements the documentation outcome of the Advanced Analytics Dashboard document.

## Objective

Create the official LabGato Advanced Analytics Dashboard document inside the approved Phase 05 documentation structure.

## Existing Architecture

Relevant approved decisions:

- Hostinger shared hosting requires optimized queries and no heavy OLAP processing.
- Database cache driver is used for short-term caching.
- WCAG 2.2 AA requires data table fallbacks for charts.

## Files to Create

Create:

05_AI_and_Advanced_Features_Architecture/04_Advanced_Analytics_Dashboard.md

Use the approved document content from this documentation output.

## Definition of Done

This implementation is complete when:

- The Advanced Analytics Dashboard file exists.
- The file matches the approved Phase 05 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.