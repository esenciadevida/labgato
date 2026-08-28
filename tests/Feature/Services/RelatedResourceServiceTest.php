<?php

namespace Tests\Feature\Services;

use App\Models\KnowledgeResource;
use App\Services\RelatedResourceService;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RelatedResourceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_related_for_returns_collection_of_std_class_items(): void
    {
        $tag = Tag::factory()->create();

        $current = KnowledgeResource::factory()->create();
        $related = KnowledgeResource::factory()->create();
        $current->tags()->attach($tag);
        $related->tags()->attach($tag);

        $result = app(RelatedResourceService::class)->relatedFor($current, 6);

        $this->assertContainsOnlyInstancesOf(\stdClass::class, $result);
        $this->assertSame($related->name, $result->first()->title);
    }

    public function test_related_for_caches_a_plain_array_not_userland_objects(): void
    {
        $tag = Tag::factory()->create();

        $current = KnowledgeResource::factory()->create();
        $current->tags()->attach($tag);

        app(RelatedResourceService::class)->relatedFor($current, 6);

        $key = 'related:'.get_class($current).':'.$current->getKey().':6';
        $cached = Cache::get($key);

        $this->assertIsArray($cached);
        $this->assertContainsOnlyInstancesOf(\stdClass::class, $cached);
    }
}