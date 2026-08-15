<?php

namespace Tests\Unit\Services;

use App\Models\SearchQuery;
use App\Models\User;
use App\Services\SearchService;
use App\Support\SearchResult;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_service_returns_empty_collection_when_no_providers_registered(): void
    {
        $service = app(SearchService::class);

        $results = $service->search('hematology');

        $this->assertCount(0, $results->items());
        $this->assertSame(0, $results->total());
    }

    public function test_search_service_can_register_provider_and_return_results(): void
    {
        $service = app(SearchService::class);

        $service->registerProvider('tool', fn () => collect([
            new SearchResult(
                type: 'tool',
                title: 'Unit Converter',
                url: '/tools/unit-converter',
                description: 'Convert between laboratory units.',
            ),
        ]));

        $results = $service->search('converter');

        $this->assertCount(1, $results->items());
        $this->assertSame('Unit Converter', $results->items()[0]->title);
    }

    public function test_search_service_applies_type_filter(): void
    {
        $service = app(SearchService::class);

        $service->registerProvider('calculator', fn () => collect([
            new SearchResult(type: 'calculator', title: 'Anion Gap', url: '/calculators/anion-gap'),
        ]));

        $service->registerProvider('tool', fn () => collect([
            new SearchResult(type: 'tool', title: 'Unit Converter', url: '/tools/unit-converter'),
        ]));

        $results = $service->search('gap', ['type' => 'calculator']);

        $this->assertCount(1, $results->items());
        $this->assertSame('calculator', $results->items()[0]->type);
        $this->assertSame('Anion Gap', $results->items()[0]->title);
    }

    public function test_search_service_all_type_filter_returns_all_results(): void
    {
        $service = app(SearchService::class);

        $service->registerProvider('calculator', fn () => collect([
            new SearchResult(type: 'calculator', title: 'Anion Gap', url: '/calculators/anion-gap'),
        ]));

        $service->registerProvider('tool', fn () => collect([
            new SearchResult(type: 'tool', title: 'Unit Converter', url: '/tools/unit-converter'),
        ]));

        $results = $service->search('test', ['type' => 'all']);

        $this->assertCount(2, $results->items());
    }

    public function test_search_service_does_not_expose_unpublished_results(): void
    {
        $service = app(SearchService::class);

        $service->registerProvider('tool', fn () => collect([
            new SearchResult(type: 'tool', title: 'Published Tool', url: '/tools/published-tool'),
        ]));

        $results = $service->search('tool');

        $this->assertCount(1, $results->items());
        $this->assertSame('Published Tool', $results->items()[0]->title);
        $this->assertSame('/tools/published-tool', $results->items()[0]->url);
    }

    public function test_search_service_logs_query_when_logging_enabled(): void
    {
        config(['labgoto.search.log_queries' => true]);

        $service = app(SearchService::class);
        $service->search('coagulation');

        $this->assertDatabaseHas('search_queries', [
            'query' => 'coagulation',
        ]);
    }

    public function test_search_service_does_not_log_query_when_logging_disabled(): void
    {
        config(['labgoto.search.log_queries' => false]);

        $service = app(SearchService::class);
        $service->search('coagulation');

        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_search_service_sorts_results_by_published_at_desc(): void
    {
        $service = app(SearchService::class);

        $service->registerProvider('tool', fn () => collect([
            new SearchResult(
                type: 'tool',
                title: 'Older Tool',
                url: '/tools/older',
                publishedAt: new DateTimeImmutable('2024-01-01'),
            ),
            new SearchResult(
                type: 'tool',
                title: 'Newer Tool',
                url: '/tools/newer',
                publishedAt: new DateTimeImmutable('2025-01-01'),
            ),
        ]));

        $results = $service->search('tool');

        $this->assertSame('Newer Tool', $results->items()[0]->title);
        $this->assertSame('Older Tool', $results->items()[1]->title);
    }

    public function test_search_service_paginates_results(): void
    {
        $service = app(SearchService::class);

        $service->registerProvider('tool', function () {
            $items = [];

            for ($i = 1; $i <= 25; $i++) {
                $items[] = new SearchResult(type: 'tool', title: "Tool {$i}", url: "/tools/tool-{$i}");
            }

            return collect($items);
        });

        $results = $service->search('tool', [], perPage: 10, page: 2);

        $this->assertSame(25, $results->total());
        $this->assertCount(10, $results->items());
        $this->assertSame(2, $results->currentPage());
        $this->assertSame('Tool 11', $results->items()[0]->title);
    }

    public function test_search_service_handles_empty_query_without_logging(): void
    {
        config(['labgoto.search.log_queries' => true]);

        $service = app(SearchService::class);
        $service->search('   ');

        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_search_service_sanitizes_query_before_logging(): void
    {
        config(['labgoto.search.log_queries' => true]);

        $service = app(SearchService::class);
        $service->search('  <b>coagulation</b>  ');

        $this->assertDatabaseHas('search_queries', [
            'query' => 'coagulation',
        ]);
    }

    public function test_search_service_logs_authenticated_user_id(): void
    {
        config(['labgoto.search.log_queries' => true]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $service = app(SearchService::class);
        $service->search('hematology');

        $this->assertDatabaseHas('search_queries', [
            'query' => 'hematology',
            'user_id' => $user->id,
        ]);
    }

    public function test_search_service_truncates_long_queries_when_logging(): void
    {
        config(['labgoto.search.log_queries' => true]);

        $longQuery = str_repeat('a', 400);

        $service = app(SearchService::class);
        $service->search($longQuery);

        $logged = SearchQuery::latest('created_at')->first();

        $this->assertNotNull($logged);
        $this->assertLessThanOrEqual(255, mb_strlen($logged->query));
    }
}
