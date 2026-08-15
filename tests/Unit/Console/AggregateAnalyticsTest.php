<?php

namespace Tests\Unit\Console;

use App\Enums\ResourceStatus;
use App\Models\AiUsage;
use App\Models\AnalyticsDailyMetric;
use App\Models\Calculator;
use App\Models\KnowledgeResource;
use App\Models\SearchQuery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AggregateAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function runCommand(?string $date = null): int
    {
        $args = [];
        if ($date !== null) {
            $args['--date'] = $date;
        }

        return Artisan::call('analytics:aggregate', $args);
    }

    public function test_command_aggregates_resource_counts_by_type_and_status(): void
    {
        $user = User::factory()->create();

        Calculator::factory()->count(3)->create(['status' => ResourceStatus::PUBLISHED]);
        Calculator::factory()->count(2)->create(['status' => ResourceStatus::DRAFT]);
        KnowledgeResource::factory()->count(4)->create(['status' => ResourceStatus::PUBLISHED]);

        $targetDate = '2026-01-15';
        $exitCode = $this->runCommand($targetDate);

        $this->assertEquals(0, $exitCode);

        $calculatorPublished = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'calculator')
            ->where('status', 'published')
            ->first();

        $this->assertNotNull($calculatorPublished);
        $this->assertEquals(3, $calculatorPublished->total_count);

        $calculatorDraft = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'calculator')
            ->where('status', 'draft')
            ->first();

        $this->assertNotNull($calculatorDraft);
        $this->assertEquals(2, $calculatorDraft->total_count);

        $knowledgePublished = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'knowledge_resource')
            ->where('status', 'published')
            ->first();

        $this->assertNotNull($knowledgePublished);
        $this->assertEquals(4, $knowledgePublished->total_count);

        $allPublished = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'all')
            ->where('status', 'published')
            ->first();

        $this->assertNotNull($allPublished);
        $this->assertEquals(7, $allPublished->total_count);
    }

    public function test_command_aggregates_search_metrics(): void
    {
        $targetDate = '2026-01-15';

        SearchQuery::factory()->count(10)->create([
            'results_count' => 5,
            'created_at' => Carbon::parse($targetDate),
        ]);

        SearchQuery::factory()->count(3)->create([
            'results_count' => 0,
            'created_at' => Carbon::parse($targetDate),
        ]);

        $exitCode = $this->runCommand($targetDate);

        $this->assertEquals(0, $exitCode);

        $allMetrics = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'all')
            ->where('status', 'all')
            ->first();

        $this->assertNotNull($allMetrics);
        $this->assertEquals(13, $allMetrics->search_volume);
        $this->assertEquals(3, $allMetrics->search_zero_results);
    }

    public function test_command_aggregates_ai_metrics(): void
    {
        $targetDate = '2026-01-15';
        $user = User::factory()->create();

        AiUsage::factory()->count(5)->create([
            'prompt_tokens' => 100,
            'completion_tokens' => 50,
            'status' => 'success',
            'created_at' => Carbon::parse($targetDate),
        ]);

        AiUsage::factory()->count(2)->create([
            'prompt_tokens' => 80,
            'completion_tokens' => 40,
            'status' => 'failed',
            'created_at' => Carbon::parse($targetDate),
        ]);

        AiUsage::factory()->count(1)->create([
            'prompt_tokens' => 60,
            'completion_tokens' => 30,
            'status' => 'filtered',
            'created_at' => Carbon::parse($targetDate),
        ]);

        $exitCode = $this->runCommand($targetDate);

        $this->assertEquals(0, $exitCode);

        $allMetrics = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'all')
            ->where('status', 'all')
            ->first();

        $this->assertNotNull($allMetrics);
        $this->assertEquals(8, $allMetrics->ai_requests_total);
        $this->assertEquals(3, $allMetrics->ai_requests_failed);
        $this->assertEquals(1080, $allMetrics->ai_tokens_used);
    }

    public function test_command_purges_old_search_queries(): void
    {
        $oldDate = Carbon::now()->subDays(91);
        $recentDate = Carbon::now()->subDays(10);

        SearchQuery::factory()->count(3)->create([
            'created_at' => $oldDate,
        ]);

        SearchQuery::factory()->count(2)->create([
            'created_at' => $recentDate,
        ]);

        $exitCode = $this->runCommand();

        $this->assertEquals(0, $exitCode);

        $this->assertEquals(2, SearchQuery::count());
        $this->assertNull(SearchQuery::where('created_at', '<', Carbon::now()->subDays(90))->first());
    }

    public function test_command_purges_old_ai_usage(): void
    {
        $oldDate = Carbon::now()->subDays(91);
        $recentDate = Carbon::now()->subDays(10);
        $user = User::factory()->create();

        AiUsage::factory()->count(4)->create([
            'created_at' => $oldDate,
        ]);

        AiUsage::factory()->count(2)->create([
            'created_at' => $recentDate,
        ]);

        $exitCode = $this->runCommand();

        $this->assertEquals(0, $exitCode);

        $this->assertEquals(2, AiUsage::count());
        $this->assertNull(AiUsage::where('created_at', '<', Carbon::now()->subDays(90))->first());
    }

    public function test_command_uses_update_or_create_for_idempotency(): void
    {
        $user = User::factory()->create();

        Calculator::factory()->count(3)->create(['status' => ResourceStatus::PUBLISHED]);

        $targetDate = '2026-01-15';

        $this->runCommand($targetDate);

        $initialCount = AnalyticsDailyMetric::where('date', $targetDate)->count();

        $this->runCommand($targetDate);

        $finalCount = AnalyticsDailyMetric::where('date', $targetDate)->count();

        $this->assertEquals($initialCount, $finalCount);

        $calculatorPublished = AnalyticsDailyMetric::where('date', $targetDate)
            ->where('resource_type', 'calculator')
            ->where('status', 'published')
            ->first();

        $this->assertEquals(3, $calculatorPublished->total_count);
    }
}
