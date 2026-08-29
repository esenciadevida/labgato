<?php

namespace Tests\Feature\Console;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Sop;
use Database\Seeders\ReferenceRangeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeGovernanceCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceRangeDataSeeder::class);
    }

    public function test_calculator_references_clean_removes_range_authority_links(): void
    {
        Calculator::create([
            'name' => 'Mixed Sources',
            'slug' => 'mixed-sources',
            'short_description' => 'test',
            'formula_display' => 'x = y',
            'input_schema' => [],
            'output_schema' => [],
            'engine' => 'php_class',
            'engine_reference' => 'App\Services\Calculators\Engines\McvCalculator',
            'interpretation_notes' => 'test',
            'references' => json_encode([
                ['title' => 'Valid literature', 'url' => 'https://pubmed.ncbi.nlm.nih.gov/2240729/'],
                ['title' => 'Medscape', 'url' => 'https://reference.medscape.com/calculator/477/urine-anion-gap-or-urine-net-charge'],
            ]),
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->artisan('calculator-references:clean')
            ->expectsOutputToContain('Range-authority links removed: 1')
            ->assertExitCode(0);

        $calculator = Calculator::where('slug', 'mixed-sources')->firstOrFail();

        $this->assertStringNotContainsString('reference.medscape.com', (string) $calculator->references);
        $this->assertStringContainsString('pubmed.ncbi.nlm.nih.gov', (string) $calculator->references);
    }

    public function test_ranges_audit_flags_unlinked_output_fields_and_external_links(): void
    {
        Sop::factory()->create([
            'name' => 'Hardcoded Interval SOP',
            'slug' => 'hardcoded-interval-sop',
            'content' => "## 7. Reference Intervals\n- **Na:** 135-145 mmol/L.",
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Calculator::create([
            'name' => 'Stray External',
            'slug' => 'stray-external',
            'short_description' => 'test',
            'formula_display' => 'x = y',
            'input_schema' => [],
            'output_schema' => [['key' => 'mcv', 'label' => 'MCV']],
            'engine' => 'php_class',
            'engine_reference' => 'App\Services\Calculators\Engines\McvCalculator',
            'interpretation_notes' => 'test',
            'references' => json_encode([
                ['title' => 'Lab Tests Guide', 'url' => 'https://www.labtestsguide.com/chloride-correction-calculator-when-sodium-is-corrected'],
            ]),
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->artisan('ranges:audit')
            ->expectsOutputToContain('no reference_key: stray-external / mcv')
            ->expectsOutputToContain('stray-external -> https://www.labtestsguide.com/')
            ->expectsOutputToContain('Published SOPs with no linked reference ranges:')
            ->assertExitCode(0);
    }
}