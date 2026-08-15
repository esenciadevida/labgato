<?php

namespace Tests\Feature\PublicSite\ReferenceRanges;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_range_listing_renders(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.reference-ranges.index'));

        $response->assertStatus(200);
        $response->assertSee('Reference Ranges');
        $response->assertSee($referenceRange->name);
    }

    public function test_reference_range_listing_displays_only_published_reference_ranges(): void
    {
        $published = ReferenceRange::factory()->create([
            'name' => 'Published Range Unique AAA',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $draft = ReferenceRange::factory()->draft()->create(['name' => 'Draft Range Unique BBB']);
        $archived = ReferenceRange::factory()->archived()->create(['name' => 'Archived Range Unique CCC']);
        $obsolete = ReferenceRange::factory()->obsolete()->create(['name' => 'Obsolete Range Unique DDD']);

        $response = $this->get(route('public.reference-ranges.index'));

        $response->assertStatus(200);
        $response->assertSee('Published Range Unique AAA');
        $response->assertDontSee('Draft Range Unique BBB');
        $response->assertDontSee('Archived Range Unique CCC');
        $response->assertDontSee('Obsolete Range Unique DDD');
    }

    public function test_reference_range_listing_displays_empty_state_when_no_published_reference_ranges_exist(): void
    {
        ReferenceRange::factory()->draft()->create();

        $response = $this->get(route('public.reference-ranges.index'));

        $response->assertStatus(200);
        $response->assertSee('No reference ranges found');
    }

    public function test_reference_range_listing_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $inCategory = ReferenceRange::factory()->create([
            'name' => 'Category Filter Range AAA',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $inCategory->categories()->attach($category);
        $other = ReferenceRange::factory()->create([
            'name' => 'Uncategorized Range BBB',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.reference-ranges.index', ['category' => $category->slug]));

        $response->assertStatus(200);
        $response->assertSee($inCategory->name);
        $response->assertDontSee($other->name);
    }

    public function test_reference_range_listing_can_search_by_analyte_name(): void
    {
        ReferenceRange::factory()->create([
            'analyte_name' => 'Hemoglobin',
            'name' => 'Hemoglobin - Adult Male',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $other = ReferenceRange::factory()->create([
            'name' => 'Unrelated Range',
            'analyte_name' => 'Potassium',
            'short_description' => 'About a different analyte.',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.reference-ranges.index', ['q' => 'Hemoglobin']));

        $response->assertStatus(200);
        $response->assertSee('Hemoglobin - Adult Male');
        $response->assertDontSee('Unrelated Range');
    }

    public function test_reference_range_listing_with_filters_sets_noindex(): void
    {
        ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.reference-ranges.index', ['q' => 'Hemoglobin']));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }
}
