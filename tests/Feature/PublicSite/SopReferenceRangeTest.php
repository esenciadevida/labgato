<?php

namespace Tests\Feature\PublicSite;

use App\Models\ReferenceRange;
use App\Services\MasterContentImporter;
use Database\Seeders\ReferenceRangeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}