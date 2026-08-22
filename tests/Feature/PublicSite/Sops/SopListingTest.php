<?php

namespace Tests\Feature\PublicSite\Sops;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\Sop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SopListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sop_listing_renders(): void
    {
        $sop = Sop::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.sops.index'));

        $response->assertStatus(200);
        $response->assertSee('Laboratory SOPs');
        $response->assertSee($sop->name);
    }

    public function test_sop_listing_displays_only_published_sops(): void
    {
        $published = Sop::factory()->create([
            'name' => 'Published SOP',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $draft = Sop::factory()->draft()->create(['name' => 'Draft SOP']);
        $archived = Sop::factory()->archived()->create(['name' => 'Archived SOP']);

        $response = $this->get(route('public.sops.index'));

        $response->assertStatus(200);
        $response->assertSee('Published SOP');
        $response->assertDontSee('Draft SOP');
        $response->assertDontSee('Archived SOP');
    }

    public function test_sop_listing_displays_empty_state_when_no_published_sops_exist(): void
    {
        Sop::factory()->draft()->create();

        $response = $this->get(route('public.sops.index'));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    public function test_sop_listing_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $inCategory = Sop::factory()->create([
            'name' => 'Category Matched SOP',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $inCategory->categories()->attach($category);
        $other = Sop::factory()->create([
            'name' => 'Unrelated SOP',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.sops.index', ['categories' => [$category->id]]));

        $response->assertStatus(200);
        $response->assertSee('Category Matched SOP');
        $response->assertDontSee('Unrelated SOP');
    }

    public function test_sop_listing_can_search_by_topic(): void
    {
        $matching = Sop::factory()->create([
            'name' => 'Venipuncture Procedure',
            'status' => ResourceStatus::PUBLISHED,
            'content' => 'Covers venipuncture and specimen collection.',
            'short_description' => 'Venipuncture guidance.',
        ]);
        $other = Sop::factory()->create([
            'name' => 'Unrelated SOP',
            'status' => ResourceStatus::PUBLISHED,
            'content' => 'Unrelated content about microscopy.',
            'short_description' => 'Not related.',
        ]);

        $response = $this->get(route('public.sops.index', ['q' => 'Venipuncture']));

        $response->assertStatus(200);
        $response->assertSee('Venipuncture Procedure');
        $response->assertDontSee('Unrelated SOP');
    }

    public function test_sop_listing_with_filters_sets_noindex(): void
    {
        Sop::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.sops.index', ['q' => 'Venipuncture']));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }
}
