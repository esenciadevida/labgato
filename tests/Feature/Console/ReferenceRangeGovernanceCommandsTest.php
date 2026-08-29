<?php

namespace Tests\Feature\Console;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\ReferenceRange;
use App\Models\Sop;
use Database\Seeders\ReferenceRangeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
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

    public function test_every_sop_dump_reference_range_slug_resolves_to_published_range(): void
    {
        $published = ReferenceRange::query()
            ->published()
            ->pluck('slug')
            ->all();

        foreach (File::files(base_path('dump')) as $file) {
            if (! str_contains($file->getFilename(), 'sop')) {
                continue;
            }

            $record = json_decode((string) $file->getContents(), true)[0] ?? null;

            if (! is_array($record) || ! array_key_exists('reference_ranges', $record)) {
                continue;
            }

            foreach ((array) $record['reference_ranges'] as $slug) {
                $this->assertContains(
                    $slug,
                    $published,
                    sprintf('%s references uncompiled slug "%s"', $file->getFilename(), $slug)
                );
            }
        }
    }

    public function test_ranges_audit_is_clean_against_current_dumps_without_fixtures(): void
    {
        $this->artisan('ranges:audit')
            ->doesntExpectOutputToContain('[!]')
            ->assertExitCode(0);
    }

    public function test_ranges_drift_detects_stray_database_records(): void
    {
        $this->artisan('ranges:drift')
            ->expectsOutputToContain('No drift')
            ->assertExitCode(0);

        ReferenceRange::factory()->create([
            'slug' => 'legacy-glucose-random',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->artisan('ranges:drift')
            ->expectsOutputToContain('legacy-glucose-random')
            ->assertExitCode(1);
    }

    public function test_ranges_drift_detects_missing_canonical_records(): void
    {
        ReferenceRange::query()->where('slug', 'sodium-adult')->delete();

        $this->artisan('ranges:drift')
            ->expectsOutputToContain('sodium-adult')
            ->assertExitCode(1);
    }
}