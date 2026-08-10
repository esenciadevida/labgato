<?php

namespace Tests\Feature\PublicSite;

use App\Models\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['labgato.search.log_queries' => true]);
        config(['labgato.search.rate_limit_per_minute' => 30]);
    }

    public function test_search_page_renders_successfully(): void
    {
        $response = $this->get(route('public.search'));

        $response->assertStatus(200);
        $response->assertSee('Search LabGato');
        $response->assertSee('search-input');
    }

    public function test_search_page_is_noindex(): void
    {
        $response = $this->get(route('public.search'));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }

    public function test_empty_query_does_not_log_search_query(): void
    {
        $this->get(route('public.search'));

        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_non_empty_query_logs_search_query(): void
    {
        $this->get(route('public.search', ['q' => 'hematology']));

        $this->assertDatabaseHas('search_queries', [
            'query' => 'hematology',
            'results_count' => 0,
        ]);
    }

    public function test_search_page_displays_empty_state_when_no_results_exist(): void
    {
        $response = $this->get(route('public.search', ['q' => 'nonexistentterm']));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    public function test_search_page_displays_result_count(): void
    {
        $response = $this->get(route('public.search', ['q' => 'hematology']));

        $response->assertStatus(200);
        $response->assertSee('result');
    }

    public function test_search_query_length_is_limited(): void
    {
        $response = $this->get(route('public.search', ['q' => str_repeat('a', 300)]));

        $response->assertSessionHasErrors('q');
        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_empty_query_does_not_log_when_searching_with_spaces(): void
    {
        $this->get(route('public.search', ['q' => '   ']));

        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_search_page_preserves_query_in_input(): void
    {
        $response = $this->get(route('public.search', ['q' => 'hemoglobin']));

        $response->assertStatus(200);
        $response->assertSee('value="hemoglobin"', false);
    }

    public function test_search_rate_limiter_is_applied(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get(route('public.search'));
        }

        $response = $this->get(route('public.search'));

        $response->assertTooManyRequests();
        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_search_logged_queries_are_sanitized(): void
    {
        $this->get(route('public.search', ['q' => '<script>alert(1)</script>']));

        $this->assertDatabaseHas('search_queries', [
            'query' => 'alert(1)',
        ]);
    }

    public function test_search_page_does_not_expose_admin_urls(): void
    {
        $response = $this->get(route('public.search', ['q' => 'test']));

        $response->assertStatus(200);
        $response->assertDontSee('/admin');
    }

    public function test_search_query_records_created_at_timestamp(): void
    {
        $this->get(route('public.search', ['q' => 'coagulation']));

        $query = SearchQuery::latest('created_at')->first();

        $this->assertNotNull($query);
        $this->assertNotNull($query->created_at);
        $this->assertSame(0, $query->results_count);
    }
}
