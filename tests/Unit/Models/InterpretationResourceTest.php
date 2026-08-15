<?php

namespace Tests\Unit\Models;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\InterpretationResource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InterpretationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_interpretation_resource_casts_status_to_resource_status_enum(): void
    {
        $resource = InterpretationResource::factory()->create(['status' => ResourceStatus::DRAFT]);

        $this->assertInstanceOf(ResourceStatus::class, $resource->status);
        $this->assertSame(ResourceStatus::DRAFT, $resource->status);
    }

    public function test_interpretation_resource_casts_published_at_to_datetime(): void
    {
        $resource = InterpretationResource::factory()->create(['published_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $resource->published_at);
    }

    public function test_interpretation_resource_casts_related_analytes_to_array(): void
    {
        $resource = InterpretationResource::factory()->create([
            'related_analytes' => ['ALT', 'AST', 'ALP'],
        ]);

        $this->assertIsArray($resource->related_analytes);
        $this->assertSame(['ALT', 'AST', 'ALP'], $resource->related_analytes);
    }

    public function test_interpretation_resource_belongs_to_many_categories(): void
    {
        $resource = InterpretationResource::factory()->create();
        $category = Category::factory()->create();
        $resource->categories()->attach($category);

        $this->assertTrue($resource->categories->contains($category));
        $this->assertDatabaseHas('interpretation_resource_category', [
            'interpretation_resource_id' => $resource->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_interpretation_resource_belongs_to_many_tags(): void
    {
        $resource = InterpretationResource::factory()->create();
        $tag = Tag::factory()->create();
        $resource->tags()->attach($tag);

        $this->assertTrue($resource->tags->contains($tag));
        $this->assertDatabaseHas('interpretation_resource_tag', [
            'interpretation_resource_id' => $resource->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_scope_published_filters_published_resources(): void
    {
        $published = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        InterpretationResource::factory()->draft()->create();

        $results = InterpretationResource::published()->get();

        $this->assertTrue($results->contains($published));
        $this->assertSame(1, $results->count());
    }

    public function test_scope_search_filters_by_name_description_or_clinical_context(): void
    {
        $byName = InterpretationResource::factory()->create([
            'name' => 'Interpreting Elevated Liver Enzymes',
            'content' => 'Unrelated content here.',
        ]);
        $byDescription = InterpretationResource::factory()->create([
            'name' => 'Something Else',
            'short_description' => 'Covers liver enzyme patterns',
            'content' => 'Unrelated content here.',
        ]);
        $byContext = InterpretationResource::factory()->create([
            'name' => 'Another Guide',
            'clinical_context' => 'Discusses liver enzyme elevation',
            'content' => 'Unrelated content here.',
        ]);
        InterpretationResource::factory()->create([
            'name' => 'Unrelated Topic',
            'content' => 'Nothing about this topic here.',
            'short_description' => 'Nothing about this topic here.',
        ]);

        $results = InterpretationResource::search('liver')->get();

        $this->assertTrue($results->contains($byName));
        $this->assertTrue($results->contains($byDescription));
        $this->assertTrue($results->contains($byContext));
        $this->assertSame(3, $results->count());
    }

    public function test_interpretation_resource_is_published_helper(): void
    {
        $published = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $draft = InterpretationResource::factory()->draft()->create();

        $this->assertTrue($published->isPublished());
        $this->assertFalse($draft->isPublished());
        $this->assertTrue($draft->isDraft());
    }

    public function test_interpretation_resource_publish_sets_published_at(): void
    {
        $resource = InterpretationResource::factory()->draft()->create();

        $resource->publish();

        $this->assertTrue($resource->isPublished());
        $this->assertNotNull($resource->published_at);
    }
}
