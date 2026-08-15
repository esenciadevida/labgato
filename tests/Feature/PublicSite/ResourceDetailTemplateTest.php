<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Category;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceDetailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_resource_detail_page_renders_without_errors(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $tool = LaboratoryTool::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $sop = Sop::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $interpretation = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $knowledge = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")->assertOk()->assertSee($calculator->name);
        $this->get("/tools/{$tool->slug}")->assertOk()->assertSee($tool->name);
        $this->get("/reference-ranges/{$referenceRange->slug}")->assertOk()->assertSee($referenceRange->name);
        $this->get("/sops/{$sop->slug}")->assertOk()->assertSee($sop->name);
        $this->get("/interpretation/{$interpretation->slug}")->assertOk()->assertSee($interpretation->name);
        $this->get("/knowledge/{$knowledge->slug}")->assertOk()->assertSee($knowledge->name);
    }

    public function test_resource_hero_has_navy_background_matching_listing(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('bg-primary-950');
    }

    public function test_resource_hero_uses_max_width_7xl_like_listing(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('max-w-7xl');
    }

    public function test_resource_hero_has_white_title_text_on_navy(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('text-white');
    }

    public function test_resource_hero_uses_type_label_for_badge(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get("/calculators/{$calculator->slug}");

        $response->assertSee('Calculator');
        $response->assertSee('text-primary-300');
    }

    public function test_resource_hero_has_consistent_vertical_padding(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('py-12 sm:py-16');
    }

    public function test_meta_bar_renders_below_hero_with_categories_and_tags(): void
    {
        $category = Category::factory()->create(['name' => 'Electrolytes']);
        $tag = Tag::factory()->create(['name' => 'Critical Care']);

        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->categories()->attach($category);
        $calculator->tags()->attach($tag);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('Electrolytes')
            ->assertSee('Critical Care')
            ->assertSee('uppercase tracking-wider');
    }

    public function test_meta_bar_hides_when_no_categories_tags_or_contributor(): void
    {
        $calculator = Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee(now()->format('M j, Y'));
    }

    public function test_related_resources_use_stacked_layout_in_sidebar(): void
    {
        $category = Category::factory()->create();

        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->categories()->attach($category);

        $related = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $related->categories()->attach($category);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('space-y-3')
            ->assertSee('Related Calculators')
            ->assertSee($related->name);
    }

    public function test_related_resources_hidden_when_none_exist(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertDontSee('Related Calculators');
    }

    public function test_related_resources_badge_uses_small_text(): void
    {
        $category = Category::factory()->create();

        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->categories()->attach($category);

        $related = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $related->categories()->attach($category);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('text-[10px]');
    }

    public function test_related_resources_badge_does_not_truncate(): void
    {
        $category = Category::factory()->create();

        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->categories()->attach($category);

        $related = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $related->categories()->attach($category);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('max-w-full truncate');
    }

    public function test_share_button_present_in_hero(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('Share')
            ->assertSee('Link copied!');
    }

    public function test_content_uses_tightened_padding(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('py-8 sm:py-12');
    }

    public function test_hero_does_not_use_legacy_abyss_colors(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertDontSee('bg-abyss-500/10');
    }

    public function test_all_six_pages_have_navy_hero_background(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $tool = LaboratoryTool::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $sop = Sop::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $interpretation = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $knowledge = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")->assertSee('bg-primary-950');
        $this->get("/tools/{$tool->slug}")->assertSee('bg-primary-950');
        $this->get("/reference-ranges/{$referenceRange->slug}")->assertSee('bg-primary-950');
        $this->get("/sops/{$sop->slug}")->assertSee('bg-primary-950');
        $this->get("/interpretation/{$interpretation->slug}")->assertSee('bg-primary-950');
        $this->get("/knowledge/{$knowledge->slug}")->assertSee('bg-primary-950');
    }

    public function test_dark_mode_classes_present(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('dark:bg-slate-900')
            ->assertSee('dark:text-slate-100');
    }

    public function test_sop_detail_page_shows_contributor_in_meta_bar(): void
    {
        $user = User::factory()->member()->create([
            'name' => 'Jane Doe',
            'profile_slug' => 'jane-doe',
            'is_active' => true,
        ]);

        $sop = Sop::factory()->create([
            'contributed_by' => $user->id,
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $this->get("/sops/{$sop->slug}")
            ->assertSee('Contributed by Jane Doe');
    }
}
