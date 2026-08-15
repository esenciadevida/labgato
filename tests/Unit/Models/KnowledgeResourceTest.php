<?php

namespace Tests\Unit\Models;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KnowledgeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_resource_casts_status_to_resource_status_enum(): void
    {
        $resource = KnowledgeResource::factory()->create(['status' => ResourceStatus::DRAFT]);

        $this->assertInstanceOf(ResourceStatus::class, $resource->status);
        $this->assertSame(ResourceStatus::DRAFT, $resource->status);
    }

    public function test_knowledge_resource_casts_published_at_to_datetime(): void
    {
        $resource = KnowledgeResource::factory()->create(['published_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $resource->published_at);
    }

    public function test_knowledge_resource_casts_learning_objectives_to_string(): void
    {
        $resource = KnowledgeResource::factory()->create([
            'learning_objectives' => 'Understand the CBC parameters.',
        ]);

        $this->assertIsString($resource->learning_objectives);
        $this->assertSame('Understand the CBC parameters.', $resource->learning_objectives);
    }

    public function test_knowledge_resource_belongs_to_many_categories(): void
    {
        $resource = KnowledgeResource::factory()->create();
        $category = Category::factory()->create();
        $resource->categories()->attach($category);

        $this->assertTrue($resource->categories->contains($category));
        $this->assertDatabaseHas('knowledge_resource_category', [
            'knowledge_resource_id' => $resource->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_knowledge_resource_belongs_to_many_tags(): void
    {
        $resource = KnowledgeResource::factory()->create();
        $tag = Tag::factory()->create();
        $resource->tags()->attach($tag);

        $this->assertTrue($resource->tags->contains($tag));
        $this->assertDatabaseHas('knowledge_resource_tag', [
            'knowledge_resource_id' => $resource->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_scope_published_filters_published_resources(): void
    {
        $published = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        KnowledgeResource::factory()->draft()->create();

        $results = KnowledgeResource::published()->get();

        $this->assertTrue($results->contains($published));
        $this->assertSame(1, $results->count());
    }

    public function test_scope_search_filters_by_name_description_or_content(): void
    {
        $byName = KnowledgeResource::factory()->create([
            'name' => 'Introduction to Hematology',
            'content' => 'Unrelated content here.',
        ]);
        $byDescription = KnowledgeResource::factory()->create([
            'name' => 'Something Else',
            'short_description' => 'Covers hematology basics',
            'content' => 'Unrelated content here.',
        ]);
        $byContent = KnowledgeResource::factory()->create([
            'name' => 'Another Resource',
            'content' => 'Deep dive into hematology concepts.',
        ]);
        KnowledgeResource::factory()->create([
            'name' => 'Unrelated Topic',
            'content' => 'Nothing about this topic here.',
            'short_description' => 'Nothing about this topic here.',
        ]);

        $results = KnowledgeResource::search('hematology')->get();

        $this->assertTrue($results->contains($byName));
        $this->assertTrue($results->contains($byDescription));
        $this->assertTrue($results->contains($byContent));
        $this->assertSame(3, $results->count());
    }

    public function test_scope_by_difficulty_filters_by_difficulty_level(): void
    {
        $beginner = KnowledgeResource::factory()->create(['difficulty_level' => 'Beginner']);
        KnowledgeResource::factory()->create(['difficulty_level' => 'Advanced']);

        $results = KnowledgeResource::byDifficulty('Beginner')->get();

        $this->assertTrue($results->contains($beginner));
        $this->assertSame(1, $results->count());
    }

    public function test_scope_search_matches_difficulty_using_like_queries(): void
    {
        $beginner = KnowledgeResource::factory()->create([
            'name' => 'Beginner Guide to the CBC',
            'difficulty_level' => 'Beginner',
        ]);

        $results = KnowledgeResource::search('CBC')->get();

        $this->assertTrue($results->contains($beginner));
    }
}
