<?php

namespace Tests\Feature\PublicSite\Knowledge;

use App\Enums\ResourceStatus;
use App\Models\KnowledgeResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_detail_renders(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'content' => "# Introduction to Hematology\n\nHematology is the study of blood.",
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee($knowledge->name);
        $response->assertSee('Hematology is the study of blood.');
    }

    public function test_knowledge_detail_renders_learning_objectives_at_top(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'learning_objectives' => "By the end of this resource you should be able to:\n\n- Describe the components of blood.",
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('Learning Objectives');
        $response->assertSee('Describe the components of blood.');
    }

    public function test_knowledge_detail_does_not_render_learning_objectives_section_when_empty(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'learning_objectives' => null,
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertDontSee('Learning Objectives');
    }

    public function test_knowledge_detail_renders_featured_image_when_present(): void
    {
        Storage::fake('public');

        $imagePath = 'content/featured.jpg';
        Storage::disk('public')->put($imagePath, 'fake-image');

        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'featured_image' => $imagePath,
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('/storage/'.$imagePath, false);
    }

    public function test_knowledge_detail_renders_external_featured_image_url_as_is(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'featured_image' => 'https://media.licdn.com/dms/image/example.jpg',
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('https://media.licdn.com/dms/image/example.jpg', false);
        $response->assertSee('og:image', false);
        $response->assertSee('content="https://media.licdn.com/dms/image/example.jpg"', false);
    }

    public function test_knowledge_detail_renders_sanitized_rich_text_content(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'content' => "# Heading\n\n**Bold statement**<script>alert('xss')</script>",
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('<h1>Heading</h1>', false);
        $response->assertSee('<strong>Bold statement</strong>', false);
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_knowledge_detail_renders_difficulty_and_metadata(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'difficulty_level' => 'Beginner',
            'topic_category' => 'Hematology',
            'reading_time_minutes' => 8,
            'author_name' => 'Lab Education Team',
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description.',
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('Beginner');
        $response->assertSee('Hematology');
        $response->assertSee('8 min');
        $response->assertSee('Lab Education Team');
        $response->assertSee('Custom SEO Title');
        $response->assertSee('Custom SEO description.');
    }

    public function test_knowledge_detail_includes_json_ld_markup(): void
    {
        $knowledge = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('MedicalScholarlyArticle', false);
    }

    public function test_knowledge_detail_returns_404_for_unpublished_resource(): void
    {
        $draft = KnowledgeResource::factory()->draft()->create();
        $archived = KnowledgeResource::factory()->archived()->create();

        $this->get(route('public.knowledge.show', $draft->slug))->assertStatus(404);
        $this->get(route('public.knowledge.show', $archived->slug))->assertStatus(404);
    }

    public function test_knowledge_detail_returns_404_for_nonexistent_slug(): void
    {
        $this->get(route('public.knowledge.show', 'does-not-exist'))->assertStatus(404);
    }
}
