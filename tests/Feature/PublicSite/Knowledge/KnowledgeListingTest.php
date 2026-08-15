<?php

namespace Tests\Feature\PublicSite\Knowledge;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_listing_renders(): void
    {
        $knowledge = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.knowledge.index'));

        $response->assertStatus(200);
        $response->assertSee('Knowledge Hub');
        $response->assertSee($knowledge->name);
    }

    public function test_knowledge_listing_displays_only_published_resources(): void
    {
        $published = KnowledgeResource::factory()->create([
            'name' => 'Published Knowledge Resource',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $draft = KnowledgeResource::factory()->draft()->create(['name' => 'Draft Knowledge Resource']);
        $archived = KnowledgeResource::factory()->archived()->create(['name' => 'Archived Knowledge Resource']);

        $response = $this->get(route('public.knowledge.index'));

        $response->assertStatus(200);
        $response->assertSee('Published Knowledge Resource');
        $response->assertDontSee('Draft Knowledge Resource');
        $response->assertDontSee('Archived Knowledge Resource');
    }

    public function test_knowledge_listing_displays_empty_state_when_no_published_resources_exist(): void
    {
        KnowledgeResource::factory()->draft()->create();

        $response = $this->get(route('public.knowledge.index'));

        $response->assertStatus(200);
        $response->assertSee('No knowledge resources found');
    }

    public function test_knowledge_listing_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $inCategory = KnowledgeResource::factory()->create([
            'name' => 'Category Matched Knowledge',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $inCategory->categories()->attach($category);
        $other = KnowledgeResource::factory()->create([
            'name' => 'Unrelated Knowledge Resource',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.knowledge.index', ['category' => $category->slug]));

        $response->assertStatus(200);
        $response->assertSee('Category Matched Knowledge');
        $response->assertDontSee('Unrelated Knowledge Resource');
    }

    public function test_knowledge_listing_can_filter_by_tag(): void
    {
        $tag = Tag::factory()->create();
        $withTag = KnowledgeResource::factory()->create([
            'name' => 'Tag Matched Knowledge',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $withTag->tags()->attach($tag);
        $other = KnowledgeResource::factory()->create([
            'name' => 'Unrelated Knowledge Resource',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.knowledge.index', ['tag' => $tag->slug]));

        $response->assertStatus(200);
        $response->assertSee('Tag Matched Knowledge');
        $response->assertDontSee('Unrelated Knowledge Resource');
    }

    public function test_knowledge_listing_can_filter_by_difficulty_level(): void
    {
        $beginner = KnowledgeResource::factory()->create([
            'name' => 'Beginner Knowledge Resource',
            'status' => ResourceStatus::PUBLISHED,
            'difficulty_level' => 'Beginner',
        ]);
        $advanced = KnowledgeResource::factory()->create([
            'name' => 'Advanced Knowledge Resource',
            'status' => ResourceStatus::PUBLISHED,
            'difficulty_level' => 'Advanced',
        ]);

        $response = $this->get(route('public.knowledge.index', ['difficulty' => 'Beginner']));

        $response->assertStatus(200);
        $response->assertSee('Beginner Knowledge Resource');
        $response->assertDontSee('Advanced Knowledge Resource');
    }

    public function test_knowledge_listing_can_search_by_topic(): void
    {
        $matching = KnowledgeResource::factory()->create([
            'name' => 'Introduction to Hematology',
            'status' => ResourceStatus::PUBLISHED,
            'topic_category' => 'Hematology',
            'content' => 'Covers hematology fundamentals and complete blood counts.',
            'short_description' => 'Hematology fundamentals.',
        ]);
        $other = KnowledgeResource::factory()->create([
            'name' => 'Unrelated Knowledge Resource',
            'status' => ResourceStatus::PUBLISHED,
            'topic_category' => 'Clinical Chemistry',
            'content' => 'Unrelated content about clinical chemistry quality control.',
            'short_description' => 'Not related to this topic.',
        ]);

        $response = $this->get(route('public.knowledge.index', ['q' => 'Hematology']));

        $response->assertStatus(200);
        $response->assertSee('Introduction to Hematology');
        $response->assertDontSee('Unrelated Knowledge Resource');
    }

    public function test_knowledge_listing_with_filters_sets_noindex(): void
    {
        KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.knowledge.index', ['q' => 'Hematology']));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }
}
