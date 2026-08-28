<?php

namespace Tests\Feature\Seo;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaTagVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_single_title_and_full_meta_tags(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), '<title>'));
        $response->assertSee('meta name="description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('property="og:url"', false);
        $response->assertSee('property="og:type"', false);
        $response->assertSee('content="index, follow"', false);
    }

    public function test_calculator_detail_page_has_correct_canonical_url(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $url = route('public.calculators.show', $calculator->slug);

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('<title>'.$calculator->name.' - '.config('app.name').'</title>', false);
        $response->assertSee('rel="canonical" href="'.$url.'"', false);
        $response->assertSee('property="og:url" content="'.$url.'"', false);
    }

    public function test_all_resource_detail_pages_include_required_meta_tags(): void
    {
        $pages = [
            route('public.calculators.show', Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED])->slug),
            route('public.reference-ranges.show', ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED])->slug),
            route('public.interpretation.show', InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED])->slug),
            route('public.knowledge.show', KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED])->slug),
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertSee('<meta name="description"', false);
            $response->assertSee('rel="canonical"', false);
            $response->assertSee('property="og:title"', false);
            $response->assertSee('property="og:description"', false);
            $response->assertSee('property="og:url"', false);
            $response->assertSee('property="og:type"', false);
        }
    }

    public function test_knowledge_detail_page_emits_article_json_ld(): void
    {
        $author = \App\Models\User::factory()->create(['name' => 'Dr Jane Doe']);
        $knowledge = KnowledgeResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'contributed_by' => $author->id,
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertOk();
        $response->assertSee('<script type="application/ld+json">', false);
        $content = $response->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches);
        $schemas = array_map(
            fn ($json) => json_decode($json, true),
            $matches[1],
        );
        $article = collect($schemas)->first(fn ($schema) => ($schema['@type'] ?? null) === 'Article');

        $this->assertNotNull($article);
        $this->assertSame($knowledge->name, $article['headline']);
        $this->assertSame('Dr Jane Doe', $article['author']['name']);
        $this->assertNotEmpty($article['datePublished']);
        $this->assertSame('Organization', $article['publisher']['@type']);
    }

    public function test_listing_pages_do_not_emit_article_json_ld(): void
    {
        Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.index'));

        $response->assertOk();
        $this->assertStringNotContainsString('"@type":"Article"', $response->getContent());
    }

    public function test_filtered_listing_page_is_noindex(): void
    {
        $response = $this->get(route('public.reference-ranges.index', ['category' => 'nonexistent']));

        $response->assertOk();
        $response->assertSee('content="noindex, nofollow"', false);
    }
}
