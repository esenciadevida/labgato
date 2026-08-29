<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Services\MasterContentImporter;
use Database\Seeders\ReferenceRangeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SopReferenceRangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceRangeDataSeeder::class);
    }

    public function test_importer_links_reference_ranges_from_dump_slugs(): void
    {
        $importer = app(MasterContentImporter::class);

        $importer->upsertSop([
            'slug' => 'sop-aptt-link-test',
            'name' => 'APTT Procedure',
            'short_description' => 'Test SOP',
            'content' => "# APTT Procedure\n\nStandard procedure text for testing.",
            'status' => 'published',
            'categories' => ['Coagulation'],
            'reference_ranges' => ['aptt-adult'],
        ]);

        $this->assertDatabaseHas('sops', ['slug' => 'sop-aptt-link-test']);
        $this->assertDatabaseHas('sop_reference_range', [
            'sop_id' => \App\Models\Sop::where('slug', 'sop-aptt-link-test')->value('id'),
            'reference_range_id' => ReferenceRange::where('slug', 'aptt-adult')->value('id'),
        ]);
    }

    public function test_sop_show_page_renders_linked_reference_ranges_panel(): void
    {
        app(MasterContentImporter::class)->upsertSop([
            'slug' => 'sop-aptt-link-test',
            'name' => 'APTT Procedure',
            'short_description' => 'Test SOP',
            'content' => "# APTT Procedure\n\nStandard procedure text for testing.",
            'status' => 'published',
            'categories' => ['Coagulation'],
            'reference_ranges' => ['aptt-adult'],
        ]);

        $range = ReferenceRange::where('slug', 'aptt-adult')->firstOrFail();

        $response = $this->get(route('public.sops.show', 'sop-aptt-link-test'));

        $response->assertStatus(200);
        $response->assertSee('Reference Ranges');
        $response->assertSee($range->name);
    }

    public function test_reference_range_show_lists_covered_sops(): void
    {
        app(MasterContentImporter::class)->upsertSop([
            'slug' => 'sop-aptt-link-test',
            'name' => 'APTT Procedure',
            'short_description' => 'Test SOP',
            'content' => "# APTT Procedure\n\nStandard procedure text for testing.",
            'status' => 'published',
            'categories' => ['Coagulation'],
            'reference_ranges' => ['aptt-adult'],
        ]);

        $range = ReferenceRange::where('slug', 'aptt-adult')->firstOrFail();

        $response = $this->get(route('public.reference-ranges.show', $range->slug));

        $response->assertStatus(200);
        $response->assertSee('Covered in SOPs');
        $response->assertSee('APTT Procedure');
    }

    public function test_reference_range_seeder_adds_esr_and_d_dimer_records(): void
    {
        $this->assertDatabaseHas('reference_ranges', ['slug' => 'esr-adult-male-under-50', 'parameter_key' => 'esr']);
        $this->assertDatabaseHas('reference_ranges', ['slug' => 'd-dimer-adult', 'parameter_key' => 'd_dimer']);
    }

    public function test_importer_preserves_links_and_warns_on_unresolved_slugs(): void
    {
        Log::spy();

        $importer = app(MasterContentImporter::class);

        $importer->upsertSop([
            'slug' => 'sop-unknown-slug-link-test',
            'name' => 'Link Stability Test',
            'short_description' => 'Test SOP',
            'content' => "# Link Stability Test\n\nStandard procedure text for testing.",
            'status' => 'published',
            'categories' => ['Coagulation'],
            'reference_ranges' => ['aptt-adult', 'inr-adult'],
        ]);

        $sop = Sop::where('slug', 'sop-unknown-slug-link-test')->firstOrFail();
        $this->assertCount(2, $sop->referenceRanges);

        $importer->upsertSop([
            'slug' => 'sop-unknown-slug-link-test',
            'name' => 'Link Stability Test',
            'short_description' => 'Test SOP',
            'content' => "# Link Stability Test\n\nStandard procedure text for testing.",
            'status' => 'published',
            'categories' => ['Coagulation'],
            'reference_ranges' => ['aptt-adult', 'inr-adult', 'totally-unknown-slug'],
        ]);

        $sop->refresh();

        $this->assertCount(2, $sop->referenceRanges, 'Known links must survive an unknown slug in the list.');
        $this->assertTrue($sop->referenceRanges->contains('slug', 'aptt-adult'));
        $this->assertTrue($sop->referenceRanges->contains('slug', 'inr-adult'));

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message) => str_contains($message, 'totally-unknown-slug'));
    }

    public function test_reference_range_show_lists_calculators_using_its_parameter_key(): void
    {
        Calculator::factory()->create([
            'slug' => 'mcv-calculator',
            'name' => 'MCV Calculator',
            'output_schema' => [
                ['key' => 'mcv', 'label' => 'MCV', 'unit' => 'fL', 'precision' => 1, 'reference_key' => 'mcv'],
            ],
            'references' => '[]',
        ]);

        $range = ReferenceRange::where('slug', 'mcv-adult')->firstOrFail();

        $response = $this->get(route('public.reference-ranges.show', $range->slug));

        $response->assertStatus(200);
        $response->assertSee('Used by calculators');
        $response->assertSee('MCV Calculator');
    }

    public function test_reference_range_show_omits_calculator_panel_when_unused(): void
    {
        $range = ReferenceRange::where('slug', 'wbc-adult')->firstOrFail();

        $response = $this->get(route('public.reference-ranges.show', $range->slug));

        $response->assertStatus(200);
        $response->assertDontSee('Used by calculators');
    }
}