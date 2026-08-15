labgato/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── AggregateAnalytics.php
│   └── Models/
│       └── AnalyticsDailyMetric.php
├── database/
│   └── migrations/
│       └── xxxx_create_analytics_daily_metrics_table.php
├── routes/
│   └── console.php
└── tests/
    ├── Unit/
    │   └── Console/
    │       └── AggregateAnalyticsTest.php
    └── Feature/
        └── Analytics/
            └── AggregationTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE05-BUILD19

CONTEXT:
LabGato AI Foundation and AI Content Generation (Builds 17-18) are complete. The ai_usage table exists and is tracking AI requests.

We are now implementing Build 19: Advanced Analytics Foundation (Slice 9).

This build creates the pre-aggregated analytics table and the nightly aggregation command. It does NOT create the Advanced Dashboard UI. That is scheduled for Build 20.

OBJECTIVE:
Implement the analytics_daily_metrics table, the AggregateAnalytics Artisan command, the 90-day log purge logic, and the Laravel Scheduler registration.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Historical analytics use a pre-aggregated analytics_daily_metrics table to protect shared hosting performance.
- Raw search and AI logs are purged after 90 days.
- The aggregation command runs nightly via the Laravel Scheduler (Cron).
- Resource counts are tracked by type and status.
- Search volume, zero-result searches, and AI utilization are tracked globally.

FILES TO INSPECT:
- app/Models/AiUsage.php
- app/Models/SearchQuery.php
- app/Models/Calculator.php (and all other resource models)
- routes/console.php (or app/Console/Kernel.php)
- database/migrations/ (existing tables)

FILES TO CREATE:
- database/migrations/xxxx_create_analytics_daily_metrics_table.php
- app/Models/AnalyticsDailyMetric.php
- app/Console/Commands/AggregateAnalytics.php
- tests/Unit/Console/AggregateAnalyticsTest.php
- tests/Feature/Analytics/AggregationTest.php

FILES TO MODIFY:
- routes/console.php (to register the scheduler)

TASK 1: ANALYTICS DAILY METRICS TABLE

Create the analytics_daily_metrics migration.

Columns:
- id: primary key
- date: date (indexed, part of composite unique)
- resource_type: string (e.g., 'calculator', 'knowledge', 'all')
- status: string (e.g., 'published', 'draft', 'all')
- total_count: integer, default 0
- new_count: integer, default 0
- search_volume: integer, default 0
- search_zero_results: integer, default 0
- ai_requests_total: integer, default 0
- ai_requests_failed: integer, default 0
- ai_tokens_used: integer, default 0
- created_at: timestamp
- updated_at: timestamp

Indexes:
- Unique composite index on (date, resource_type, status).
- Index on date.

Create app/Models/AnalyticsDailyMetric.php.
- Fillable: date, resource_type, status, total_count, new_count, search_volume, search_zero_results, ai_requests_total, ai_requests_failed, ai_tokens_used.
- Cast date to date.

TASK 2: AGGREGATE ANALYTICS COMMAND

Create app/Console/Commands/AggregateAnalytics.php.

Command signature: analytics:aggregate {--date= : The date to aggregate (YYYY-MM-DD). Defaults to yesterday.}

Responsibilities:
1. Determine the target date (default: yesterday).
2. Aggregate resource counts by type and status.
3. Aggregate search volume and zero-result searches.
4. Aggregate AI requests, failures, and tokens.
5. Insert or update the analytics_daily_metrics table.
6. Purge raw logs older than 90 days.

Step-by-step logic:

1. Determine target date:
   - If --date option is provided, use it.
   - Otherwise, use Carbon::yesterday().

2. Aggregate resource counts:
   - For each resource type (Calculator, LaboratoryTool, ReferenceRange, SopResource, InterpretationResource, KnowledgeResource):
     - Count total resources by status (draft, under_review, published, archived, obsolete).
     - Count new resources created on the target date.
   - Also calculate "all" totals across all resource types.

3. Aggregate search metrics:
   - Count total search queries on the target date from search_queries table.
   - Count search queries with results_count = 0 on the target date.

4. Aggregate AI metrics:
   - Count total AI requests on the target date from ai_usage table.
   - Count AI requests with status = 'failed' or 'filtered'.
   - Sum prompt_tokens + completion_tokens for the target date.

5. Insert or update analytics_daily_metrics:
   - Use updateOrCreate for each (date, resource_type, status) combination.
   - The "all/all" row should include search and AI metrics.
   - Resource-specific rows should only include resource counts.

6. Purge old logs:
   - Delete search_queries records where created_at < 90 days ago.
   - Delete ai_usage records where created_at < 90 days ago.
   - Log the number of purged records.

Error handling:
- Wrap the aggregation in a database transaction.
- If an error occurs, roll back the transaction and log the error.
- Do not let aggregation failures crash the scheduler.

TASK 3: SCHEDULER REGISTRATION

Register the AggregateAnalytics command in the Laravel Scheduler.

In routes/console.php (or app/Console/Kernel.php depending on Laravel version):

use Illuminate\Support\Facades\Schedule;

Schedule::command('analytics:aggregate')->dailyAt('01:00');

Rules:
- Run at 1:00 AM daily to avoid peak usage hours.
- Use withoutOverlapping() to prevent concurrent runs.
- Use onOneServer() if applicable (for shared hosting, this may not be necessary).

TASK 4: UNIT TESTS

Create tests/Unit/Console/AggregateAnalyticsTest.php.

Required tests:
1. test_command_aggregates_resource_counts_by_type_and_status
   - Create sample resources with different types and statuses.
   - Run the command with a specific date.
   - Assert analytics_daily_metrics contains correct counts.
2. test_command_aggregates_search_metrics
   - Create sample search_queries records.
   - Run the command.
   - Assert search_volume and search_zero_results are correct.
3. test_command_aggregates_ai_metrics
   - Create sample ai_usage records.
   - Run the command.
   - Assert ai_requests_total, ai_requests_failed, and ai_tokens_used are correct.
4. test_command_purges_old_search_queries
   - Create search_queries records older than 90 days.
   - Run the command.
   - Assert old records are deleted.
5. test_command_purges_old_ai_usage
   - Create ai_usage records older than 90 days.
   - Run the command.
   - Assert old records are deleted.
6. test_command_uses_update_or_create_for_idempotency
   - Run the command twice for the same date.
   - Assert no duplicate records are created.

TASK 5: FEATURE TESTS

Create tests/Feature/Analytics/AggregationTest.php.

Required tests:
1. test_aggregation_command_runs_successfully
   - Run php artisan analytics:aggregate.
   - Assert exit code is 0.
2. test_aggregation_command_accepts_date_option
   - Run php artisan analytics:aggregate --date=2026-01-15.
   - Assert metrics are aggregated for the specified date.
3. test_aggregation_command_handles_empty_data
   - Run the command with no data in raw tables.
   - Assert analytics_daily_metrics is created with zero counts.

PERFORMANCE REQUIREMENTS:
- The aggregation command must complete within 30 seconds for tables with up to 100,000 records.
- Use database transactions to ensure atomicity.
- Use batch deletes for log purging (e.g., chunked deletes) to avoid locking the table.

SECURITY REQUIREMENTS:
- The aggregation command must not expose sensitive data.
- Purged logs must be permanently deleted, not soft-deleted.

DEFINITION OF DONE:
- analytics_daily_metrics table and model exist.
- AggregateAnalytics command correctly aggregates resource, search, and AI metrics.
- 90-day log purge logic works correctly.
- Command is registered in the Laravel Scheduler.
- Unit and Feature tests pass.
- Command is idempotent (safe to run multiple times).
- No Advanced Dashboard UI is implemented in this build.