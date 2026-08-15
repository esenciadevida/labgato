<?php

namespace Tests\Feature\Seo;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $blocks = [];
        foreach ($matches[1] as $json) {
            $decoded = json_decode(trim($json), true);
            $this->assertIsArray($decoded, 'Structured data must be valid JSON.');
            $blocks[] = $decoded;
        }

        return $blocks;
    }

    public function test_calculator_detail_page_has_valid_structured_data(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertOk();
        $blocks = $this->jsonLdBlocks($response->getContent());

        $webApp = collect($blocks)->firstWhere('@type', 'WebApplication');
        $this->assertNotNull($webApp, 'WebApplication structured data missing.');
        $this->assertArrayHasKey('name', $webApp);
        $this->assertArrayHasKey('url', $webApp);
        $this->assertArrayHasKey('description', $webApp);
    }

    public function test_all_resource_detail_pages_include_valid_structured_data(): void
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

            $blocks = $this->jsonLdBlocks($response->getContent());
            $this->assertNotEmpty($blocks, "No structured data on {$url}");
        }
    }

    public function test_all_resource_detail_pages_include_breadcrumb_list(): void
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

            $blocks = $this->jsonLdBlocks($response->getContent());
            $breadcrumb = collect($blocks)->firstWhere('@type', 'BreadcrumbList');

            $this->assertNotNull($breadcrumb, "BreadcrumbList missing on {$url}");
            $this->assertArrayHasKey('itemListElement', $breadcrumb);
            $this->assertGreaterThanOrEqual(2, count($breadcrumb['itemListElement']));
        }
    }
}
