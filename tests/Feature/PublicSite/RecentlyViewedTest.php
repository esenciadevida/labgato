<?php

namespace Tests\Feature\PublicSite;

use App\Models\Calculator;
use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\InterpretationResource;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentlyViewedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calculatorCategory = Category::factory()->create(['is_active' => true]);
        $this->knowledgeCategory = Category::factory()->create(['is_active' => true]);
        $this->interpretationCategory = Category::factory()->create(['is_active' => true]);
        $this->referenceRangeCategory = Category::factory()->create(['is_active' => true]);
        $this->sopCategory = Category::factory()->create(['is_active' => true]);
    }

    /** @test */
    public function test_resource_detail_page_excludes_recently_viewed_component()
    {
        $calculator = Calculator::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $calculator->categories()->attach($this->calculatorCategory);

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertOk();
        $response->assertDontSee('Recently Viewed');
    }

    /** @test */
    public function test_reference_range_detail_page_excludes_recently_viewed_component()
    {
        $referenceRange = ReferenceRange::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $referenceRange->categories()->attach($this->referenceRangeCategory);

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertOk();
        $response->assertDontSee('Recently Viewed');
    }

    /** @test */
    public function test_sop_detail_page_excludes_recently_viewed_component()
    {
        $sop = Sop::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $sop->categories()->attach($this->sopCategory);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertOk();
        $response->assertDontSee('Recently Viewed');
    }

    /** @test */
    public function test_knowledge_detail_page_excludes_recently_viewed_component()
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $knowledge->categories()->attach($this->knowledgeCategory);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertOk();
        $response->assertDontSee('Recently Viewed');
    }

    /** @test */
    public function test_interpretation_detail_page_excludes_recently_viewed_component()
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $interpretation->categories()->attach($this->interpretationCategory);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertOk();
        $response->assertDontSee('Recently Viewed');
    }

    /** @test */
    public function test_home_page_excludes_recently_viewed_section()
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Recently Viewed');
    }
}
