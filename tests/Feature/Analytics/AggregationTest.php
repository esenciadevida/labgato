<?php

namespace Tests\Feature\Analytics;

use App\Models\AnalyticsDailyMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregation_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('analytics:aggregate');

        $this->assertEquals(0, $exitCode);
    }

    public function test_aggregation_command_accepts_date_option(): void
    {
        $exitCode = Artisan::call('analytics:aggregate', ['--date' => '2026-01-15']);

        $this->assertEquals(0, $exitCode);

        $metrics = AnalyticsDailyMetric::where('date', '2026-01-15')->get();

        $this->assertNotEmpty($metrics);
    }

    public function test_aggregation_command_handles_empty_data(): void
    {
        $exitCode = Artisan::call('analytics:aggregate', ['--date' => '2026-01-15']);

        $this->assertEquals(0, $exitCode);

        $allMetrics = AnalyticsDailyMetric::where('date', '2026-01-15')
            ->where('resource_type', 'all')
            ->where('status', 'all')
            ->first();

        $this->assertNotNull($allMetrics);
        $this->assertEquals(0, $allMetrics->search_volume);
        $this->assertEquals(0, $allMetrics->search_zero_results);
        $this->assertEquals(0, $allMetrics->ai_requests_total);
        $this->assertEquals(0, $allMetrics->ai_requests_failed);
        $this->assertEquals(0, $allMetrics->ai_tokens_used);
    }
}
