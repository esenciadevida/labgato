<?php

namespace Tests\Feature\PublicSite\Interpretation;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\InterpretationResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterpretationDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_interpretation_detail_renders(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'content' => "# Interpreting Elevated Liver Enzymes\n\nElevated ALT and AST indicate liver cell injury.",
        ]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee($interpretation->name);
        $response->assertSee('Elevated ALT and AST indicate liver cell injury.');
    }

    public function test_interpretation_detail_renders_mandatory_disclaimer(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'mandatory_disclaimer' => 'Educational Interpretation Only: This content is for educational purposes.',
        ]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('Educational Interpretation Only');
        $response->assertSee('This content is for educational purposes.');
    }

    public function test_interpretation_detail_renders_clinical_context(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'clinical_context' => 'Correlate with the full clinical picture before drawing conclusions.',
        ]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('Clinical Context');
        $response->assertSee('Correlate with the full clinical picture');
    }

    public function test_interpretation_detail_renders_related_analytes(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'related_analytes' => ['ALT', 'AST', 'ALP'],
        ]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('Related Analytes');
        $response->assertSee('ALT');
        $response->assertSee('AST');
        $response->assertSee('ALP');
    }

    public function test_interpretation_detail_renders_related_resources(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $interpretation = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $interpretation->categories()->attach($category);

        $related = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $related->categories()->attach($category);

        InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('Related Interpretations');
        $response->assertSee($related->name);
    }

    public function test_interpretation_detail_renders_sanitized_rich_text_content(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'content' => "# Heading\n\n**Bold statement**<script>alert('xss')</script>",
        ]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('<h1>Heading</h1>', false);
        $response->assertSee('<strong>Bold statement</strong>', false);
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_interpretation_detail_renders_reading_time_and_metadata(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'reading_time_minutes' => 6,
            'author_name' => 'Lab Education Team',
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description.',
        ]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('6 min');
        $response->assertSee('Lab Education Team');
        $response->assertSee('Custom SEO Title');
        $response->assertSee('Custom SEO description.');
    }

    public function test_interpretation_detail_includes_json_ld_markup(): void
    {
        $interpretation = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('MedicalScholarlyArticle', false);
    }

    public function test_interpretation_detail_returns_404_for_unpublished_resource(): void
    {
        $draft = InterpretationResource::factory()->draft()->create();
        $archived = InterpretationResource::factory()->archived()->create();

        $this->get(route('public.interpretation.show', $draft->slug))->assertStatus(404);
        $this->get(route('public.interpretation.show', $archived->slug))->assertStatus(404);
    }

    public function test_interpretation_detail_returns_404_for_nonexistent_slug(): void
    {
        $this->get(route('public.interpretation.show', 'does-not-exist'))->assertStatus(404);
    }
}
