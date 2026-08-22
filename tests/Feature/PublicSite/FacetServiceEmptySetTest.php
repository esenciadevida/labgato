<?php

namespace Tests\Feature\PublicSite;

use App\Models\Sop;
use App\Services\FacetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FacetServiceEmptySetTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_facets_use_safe_in_clause_when_no_resources_exist(): void
    {
        \App\Models\Category::factory()->count(2)->create(['is_active' => true]);

        $statements = [];
        DB::listen(function ($query) use (&$statements) {
            $statements[] = $query->sql;
        });

        $service = app(FacetService::class);
        $base = $service->baseQuery(Sop::class);

        $categories = $service->categoriesWithCounts($base, Sop::class);

        $this->assertNotEmpty($categories);
        $this->assertTrue($categories->every(fn ($category) => (int) $category->count === 0));

        $facetSql = collect($statements)->first(fn ($sql) => str_contains($sql, 'COALESCE'));
        $this->assertNotNull($facetSql);
        $this->assertStringNotContainsString('IN ()', $facetSql, 'Empty resource sets must not produce IN (), which MySQL rejects.');
    }
}
