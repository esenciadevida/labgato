<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePageImprovementTest extends TestCase
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

        $this->get("/calculators/{$calculator->slug}")->assertSee($calculator->name);
        $this->get("/tools/{$tool->slug}")->assertSee($tool->name);
        $this->get("/reference-ranges/{$referenceRange->slug}")->assertSee($referenceRange->name);
        $this->get("/sops/{$sop->slug}")->assertSee($sop->name);
        $this->get("/interpretation/{$interpretation->slug}")->assertSee($interpretation->name);
        $this->get("/knowledge/{$knowledge->slug}")->assertSee($knowledge->name);
    }

    public function test_resource_hero_displays_title_and_type_badge(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee($calculator->name)
            ->assertSee('Calculator');
    }

    public function test_show_page_displays_tag_chips(): void
    {
        $tag = Tag::factory()->create(['name' => 'Test Tag']);
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->tags()->attach($tag);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('Test Tag');
    }

    public function test_share_button_is_present(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('Share')
            ->assertSee('Link copied!');
    }

    public function test_related_resources_section_appears_when_related_resources_exist(): void
    {
        $tag = Tag::factory()->create();
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->tags()->attach($tag);

        $related = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $related->tags()->attach($tag);

        $this->get("/calculators/{$calculator->slug}")
            ->assertSee('Related Calculators');
    }

    public function test_related_resources_section_is_hidden_when_none_exist(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/calculators/{$calculator->slug}")
            ->assertDontSee('Related Calculators');
    }

    public function test_contributor_credit_appears_on_contributed_resources(): void
    {
        $user = User::factory()->member()->create([
            'name' => 'Jane Smith',
            'profile_slug' => 'jane-smith',
            'is_active' => true,
        ]);
        $knowledge = KnowledgeResource::factory()->create([
            'contributed_by' => $user->id,
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $this->get("/knowledge/{$knowledge->slug}")
            ->assertSee('Contributed by Jane Smith');
    }

    public function test_resource_content_class_is_applied_to_content_pages(): void
    {
        $knowledge = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get("/knowledge/{$knowledge->slug}")
            ->assertSee('rich-content');
    }

    public function test_resource_page_renders_in_dark_mode(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->withHeader('Accept-Dark-Theme', 'true')
            ->get("/calculators/{$calculator->slug}")
            ->assertSee('dark');
    }
}
