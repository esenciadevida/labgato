<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Category;
use App\Models\LaboratoryTool;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['labgoto.search.log_queries' => false]);
    }

    public function test_live_search_returns_json_results(): void
    {
        Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->getJson(route('public.search.live', ['q' => 'anion']));

        $response->assertOk();
        $response->assertJsonStructure([
            'results' => [
                '*' => ['title', 'type', 'url'],
            ],
        ]);
        $response->assertJsonFragment(['title' => 'Anion Gap Calculator']);
    }

    public function test_live_search_returns_empty_for_short_query(): void
    {
        Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->getJson(route('public.search.live', ['q' => 'a']));

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }

    public function test_live_search_excludes_unpublished(): void
    {
        Calculator::factory()->create([
            'name' => 'Draft Calculator',
            'status' => ResourceStatus::DRAFT,
        ]);

        $response = $this->getJson(route('public.search.live', ['q' => 'draft']));

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }

    public function test_live_search_is_rate_limited(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->getJson(route('public.search.live', ['q' => 'test']));
        }

        $response = $this->getJson(route('public.search.live', ['q' => 'test']));

        $response->assertTooManyRequests();
    }

    public function test_live_search_returns_rich_payload(): void
    {
        $category = Category::factory()->create(['is_active' => true, 'name' => 'Electrolytes', 'slug' => 'electrolytes']);
        $tag = Tag::factory()->create(['name' => 'critical', 'slug' => 'critical']);

        $calculator = Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'short_description' => 'Calculates the serum anion gap.',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        $calculator->categories()->attach($category);
        $calculator->tags()->attach($tag);

        $response = $this->getJson(route('public.search.live', ['q' => 'anion']));

        $response->assertOk();
        $response->assertJsonStructure([
            'total',
            'results' => [
                '*' => [
                    'type',
                    'title',
                    'url',
                    'description',
                    'categoryName',
                    'tags',
                    'publishedAt',
                ],
            ],
        ]);
        $response->assertJsonFragment([
            'type' => 'calculator',
            'title' => 'Anion Gap Calculator',
            'description' => 'Calculates the serum anion gap.',
            'categoryName' => 'Electrolytes',
            'tags' => ['critical'],
        ]);
        $response->assertJsonPath('total', 1);
    }

    public function test_live_search_filters_by_type(): void
    {
        Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        LaboratoryTool::factory()->create([
            'name' => 'Anion Gap Tool',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->getJson(route('public.search.live', ['q' => 'anion', 'type' => 'calculator']));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['type' => 'calculator', 'title' => 'Anion Gap Calculator']);
        $response->assertJsonMissing(['title' => 'Anion Gap Tool']);
    }

    public function test_live_search_filters_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true, 'slug' => 'electrolytes']);
        $other = Category::factory()->create(['is_active' => true, 'slug' => 'liver']);

        $inCategory = Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        $inCategory->categories()->attach($category);

        $otherCalculator = Calculator::factory()->create([
            'name' => 'Anion Gap Variant',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        $otherCalculator->categories()->attach($other);

        $response = $this->getJson(route('public.search.live', ['q' => 'anion', 'category' => 'electrolytes']));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'Anion Gap Calculator']);
        $response->assertJsonMissing(['title' => 'Anion Gap Variant']);
    }

    public function test_live_search_filters_by_tag(): void
    {
        $tag = Tag::factory()->create(['slug' => 'critical']);
        $other = Tag::factory()->create(['slug' => 'routine']);

        $tagged = Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        $tagged->tags()->attach($tag);

        $otherCalculator = Calculator::factory()->create([
            'name' => 'Anion Gap Variant',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        $otherCalculator->tags()->attach($other);

        $response = $this->getJson(route('public.search.live', ['q' => 'anion', 'tag' => 'critical']));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'Anion Gap Calculator']);
        $response->assertJsonMissing(['title' => 'Anion Gap Variant']);
    }

    public function test_live_search_returns_total(): void
    {
        Calculator::factory()->create([
            'name' => 'Anion Gap Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
        Calculator::factory()->create([
            'name' => 'Anion Gap Calculator Variant',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->getJson(route('public.search.live', ['q' => 'anion']));

        $response->assertOk();
        $response->assertJsonCount(2, 'results');
        $response->assertJsonPath('total', 2);
    }

    public function test_live_search_respects_limit(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Calculator::factory()->create([
                'name' => "Anion Gap Calculator {$i}",
                'status' => ResourceStatus::PUBLISHED,
                'published_at' => now(),
            ]);
        }

        $response = $this->getJson(route('public.search.live', ['q' => 'anion', 'limit' => 2]));

        $response->assertOk();
        $response->assertJsonCount(2, 'results');
        $response->assertJsonPath('total', 3);
    }
}
