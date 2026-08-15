<?php

namespace Tests\Feature\Performance;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_results_are_paginated_at_default_limit(): void
    {
        foreach (range(1, 25) as $i) {
            Calculator::factory()->create([
                'name' => "Searchable Hematology Calculator {$i}",
                'status' => ResourceStatus::PUBLISHED,
            ]);
        }

        $response = $this->get(route('public.search', ['q' => 'Hematology']));

        $response->assertOk();
        $response->assertSee('page=2', false);
        $response->assertSee('25 results', false);
    }

    public function test_search_page_executes_a_bounded_number_of_queries(): void
    {
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $this->get(route('public.search', ['q' => 'hematology']))->assertOk();

        $this->assertLessThanOrEqual(30, $count);
    }

    public function test_search_rate_limiter_is_applied(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get(route('public.search'));
        }

        $this->get(route('public.search'))->assertTooManyRequests();
    }

    public function test_search_pagination_limit_is_within_budget(): void
    {
        $this->assertLessThanOrEqual(20, config('labgoto.search.per_page'));
    }
}
