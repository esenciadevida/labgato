<?php

namespace Tests\Feature\PublicSite\Interpretation;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\InterpretationResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterpretationListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_interpretation_listing_renders(): void
    {
        $interpretation = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.interpretation.index'));

        $response->assertStatus(200);
        $response->assertSee('Interpretations');
        $response->assertSee($interpretation->name);
    }

    public function test_interpretation_listing_shows_disclaimer(): void
    {
        $response = $this->get(route('public.interpretation.index'));

        $response->assertStatus(200);
        $response->assertSee('Educational Interpretation Only');
    }

    public function test_interpretation_listing_displays_only_published_resources(): void
    {
        $published = InterpretationResource::factory()->create([
            'name' => 'Published Interpretation Guide',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $draft = InterpretationResource::factory()->draft()->create(['name' => 'Draft Interpretation Guide']);
        $archived = InterpretationResource::factory()->archived()->create(['name' => 'Archived Interpretation Guide']);

        $response = $this->get(route('public.interpretation.index'));

        $response->assertStatus(200);
        $response->assertSee('Published Interpretation Guide');
        $response->assertDontSee('Draft Interpretation Guide');
        $response->assertDontSee('Archived Interpretation Guide');
    }

    public function test_interpretation_listing_displays_empty_state_when_no_published_resources_exist(): void
    {
        InterpretationResource::factory()->draft()->create();

        $response = $this->get(route('public.interpretation.index'));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    public function test_interpretation_listing_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $inCategory = InterpretationResource::factory()->create([
            'name' => 'Category Matched Interpretation',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $inCategory->categories()->attach($category);
        $other = InterpretationResource::factory()->create([
            'name' => 'Unrelated Interpretation',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.interpretation.index', ['categories' => [$category->id]]));

        $response->assertStatus(200);
        $response->assertSee('Category Matched Interpretation');
        $response->assertDontSee('Unrelated Interpretation');
    }

    public function test_interpretation_listing_can_search_by_topic(): void
    {
        $matching = InterpretationResource::factory()->create([
            'name' => 'Interpreting Elevated Liver Enzymes',
            'status' => ResourceStatus::PUBLISHED,
            'content' => 'A guide to interpreting elevated liver enzymes.',
            'short_description' => 'Liver enzyme interpretation guide.',
        ]);
        $other = InterpretationResource::factory()->create([
            'name' => 'Unrelated Interpretation',
            'status' => ResourceStatus::PUBLISHED,
            'content' => 'Unrelated content about thyroid function testing.',
            'short_description' => 'About a different clinical topic.',
        ]);

        $response = $this->get(route('public.interpretation.index', ['q' => 'Liver']));

        $response->assertStatus(200);
        $response->assertSee('Interpreting Elevated Liver Enzymes');
        $response->assertDontSee('Unrelated Interpretation');
    }

    public function test_interpretation_listing_with_filters_sets_noindex(): void
    {
        InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.interpretation.index', ['q' => 'Liver']));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }
}
