<?php

namespace Tests\Feature\PublicSite\Tools;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\LaboratoryTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tool_listing_renders(): void
    {
        $tool = LaboratoryTool::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.tools.index'));

        $response->assertStatus(200);
        $response->assertSee('Laboratory Tools');
        $response->assertSee($tool->name);
    }

    public function test_tool_listing_displays_only_published_tools(): void
    {
        $published = LaboratoryTool::factory()->create([
            'name' => 'Published Tool',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $draft = LaboratoryTool::factory()->draft()->create(['name' => 'Draft Tool']);
        $archived = LaboratoryTool::factory()->archived()->create(['name' => 'Archived Tool']);

        $response = $this->get(route('public.tools.index'));

        $response->assertStatus(200);
        $response->assertSee('Published Tool');
        $response->assertDontSee('Draft Tool');
        $response->assertDontSee('Archived Tool');
    }

    public function test_tool_listing_displays_empty_state_when_no_published_tools_exist(): void
    {
        LaboratoryTool::factory()->draft()->create();

        $response = $this->get(route('public.tools.index'));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    public function test_tool_listing_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $inCategory = LaboratoryTool::factory()->create([
            'name' => 'Category Matched Tool',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $inCategory->categories()->attach($category);
        $other = LaboratoryTool::factory()->create([
            'name' => 'Unrelated Tool',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.tools.index', ['categories' => [$category->id]]));

        $response->assertStatus(200);
        $response->assertSee('Category Matched Tool');
        $response->assertDontSee('Unrelated Tool');
    }

    public function test_tool_listing_can_search_by_name(): void
    {
        $matching = LaboratoryTool::factory()->create([
            'name' => 'Dilution Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'short_description' => 'Calculate dilution volumes.',
        ]);
        $other = LaboratoryTool::factory()->create([
            'name' => 'Unrelated Tool',
            'status' => ResourceStatus::PUBLISHED,
            'short_description' => 'Not related.',
        ]);

        $response = $this->get(route('public.tools.index', ['q' => 'Dilution']));

        $response->assertStatus(200);
        $response->assertSee('Dilution Calculator');
        $response->assertDontSee('Unrelated Tool');
    }

    public function test_tool_listing_with_filters_sets_noindex(): void
    {
        LaboratoryTool::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.tools.index', ['q' => 'Dilution']));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }
}
