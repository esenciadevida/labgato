<?php

namespace Tests\Feature\Performance;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\ReferenceRange;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private function countQueries(callable $callback): int
    {
        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });
        $callback();

        return $count;
    }

    public function test_calculator_listing_page_eager_loads_relations_without_n_plus_one(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        foreach (range(1, 5) as $i) {
            $calculator = Calculator::factory()->create([
                'name' => "Calculator {$i}",
                'status' => ResourceStatus::PUBLISHED,
            ]);
            $calculator->categories()->attach($category);
            $calculator->tags()->attach($tag);
        }

        $queries = $this->countQueries(function () {
            $this->get(route('public.calculators.index'))->assertOk();
        });

        $this->assertLessThanOrEqual(12, $queries);
    }

    public function test_reference_range_detail_page_eager_loads_without_n_plus_one(): void
    {
        $main = ReferenceRange::factory()->create([
            'analyte_name' => 'Sodium',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        ReferenceRange::factory()->count(10)->create([
            'analyte_name' => 'Sodium',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $queries = $this->countQueries(function () use ($main) {
            $this->get(route('public.reference-ranges.show', $main->slug))->assertOk();
        });

        $this->assertLessThanOrEqual(15, $queries);
    }

    public function test_knowledge_listing_page_does_not_select_longtext_content_column(): void
    {
        $knowledgeQueries = [];
        DB::listen(function ($query) use (&$knowledgeQueries) {
            if (str_starts_with(trim(strtolower($query->sql)), 'select')
                && str_contains($query->sql, 'knowledge_resources')) {
                $knowledgeQueries[] = $query->sql;
            }
        });

        KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'content' => 'This long content column should never be selected on listing pages.',
        ]);

        $this->get(route('public.knowledge.index'))->assertOk();

        $this->assertNotEmpty($knowledgeQueries);

        foreach ($knowledgeQueries as $sql) {
            $this->assertStringNotContainsString('content', $sql);
        }
    }

    public function test_listing_pages_enforce_pagination_limit(): void
    {
        $this->assertSame(24, config('labgoto.pagination.per_page'));

        foreach (range(1, 25) as $i) {
            KnowledgeResource::factory()->create([
                'name' => "Knowledge Resource {$i}",
                'status' => ResourceStatus::PUBLISHED,
            ]);
        }

        $response = $this->get(route('public.knowledge.index'));

        $response->assertOk();
        $response->assertSee('page=2', false);

        $cardMarkup = 'class="font-display text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-1"';
        $this->assertSame(24, substr_count($response->getContent(), $cardMarkup));
    }
}
