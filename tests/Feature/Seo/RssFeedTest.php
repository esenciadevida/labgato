<?php

namespace Tests\Feature\Seo;

use App\Enums\ResourceStatus;
use App\Models\KnowledgeResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RssFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_lists_published_knowledge_articles(): void
    {
        $published = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('rss.knowledge'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0"', false)
            ->assertSee('<rss version="2.0"', false)
            ->assertSee($published->name)
            ->assertSee('<guid isPermaLink="true">'.route('public.knowledge.show', $published->slug).'</guid>', false);
    }

    public function test_feed_excludes_non_published_content(): void
    {
        $draft = KnowledgeResource::factory()->create(['status' => ResourceStatus::UNDER_REVIEW]);

        $response = $this->get(route('rss.knowledge'));

        $response->assertOk();
        $this->assertStringNotContainsString($draft->name, $response->getContent());
    }

    public function test_feed_has_maximum_of_fifty_items(): void
    {
        KnowledgeResource::factory()->count(55)->create(['status' => ResourceStatus::PUBLISHED]);

        $content = $this->get(route('rss.knowledge'))->getContent();

        $this->assertSame(50, substr_count($content, '<item>'));
    }
}