<?php

namespace Tests\Unit\Services;

use App\Enums\ResourceStatus;
use App\Models\ReferenceRange;
use App\Services\ReferenceRangeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferenceRangeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ReferenceRangeService::class);
    }

    private function range(array $attributes = []): ReferenceRange
    {
        return ReferenceRange::create(array_merge([
            'name' => 'MCV - Adult',
            'slug' => 'mcv-adult-test',
            'analyte_name' => 'MCV',
            'parameter_key' => 'mcv',
            'sex' => 'any',
            'unit' => 'fL',
            'lower_limit' => 80,
            'upper_limit' => 100,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ], $attributes));
    }

    public function test_for_parameter_returns_matching_published_range(): void
    {
        $this->range();

        $result = $this->service->forParameter('mcv');

        $this->assertNotNull($result);
        $this->assertSame('mcv', $result->parameter_key);
    }

    public function test_for_parameter_returns_null_when_none_match(): void
    {
        $this->range();

        $this->assertNull($this->service->forParameter('wbc'));
    }

    public function test_for_parameter_returns_null_when_only_draft_exists(): void
    {
        $this->range(['status' => ResourceStatus::DRAFT, 'published_at' => null]);

        $this->assertNull($this->service->forParameter('mcv'));
    }

    public function test_for_parameter_prefers_any_sex_row(): void
    {
        $this->range(['slug' => 'mcv-male', 'sex' => 'male']);
        $this->range(['slug' => 'mcv-female', 'sex' => 'female']);
        $this->range(['slug' => 'mcv-any', 'sex' => 'any']);

        $result = $this->service->forParameter('mcv');

        $this->assertSame('mcv-any', $result->slug);
    }

    public function test_evaluate_flags_low_normal_high_in_matching_units(): void
    {
        $range = $this->range();

        $this->assertSame('low', $this->service->evaluate(75, 'fL', $range));
        $this->assertSame('normal', $this->service->evaluate(90, 'fL', $range));
        $this->assertSame('high', $this->service->evaluate(110, 'fL', $range));
    }

    public function test_evaluate_converts_si_unit_using_conversion_factor(): void
    {
        $range = $this->range([
            'unit' => 'g/dL',
            'si_unit' => 'g/L',
            'conversion_factor' => 10,
            'lower_limit' => 12,
            'upper_limit' => 16,
        ]);

        $this->assertSame('normal', $this->service->evaluate(150, 'g/L', $range));
        $this->assertSame('high', $this->service->evaluate(180, 'g/L', $range));
    }

    public function test_evaluate_matches_superscript_units_for_counts(): void
    {
        $range = $this->range([
            'slug' => 'wbc-adult-test',
            'name' => 'WBC - Adult',
            'analyte_name' => 'WBC',
            'parameter_key' => 'wbc',
            'unit' => '×10³/µL',
            'si_unit' => '×10⁹/L',
            'conversion_factor' => 1.0,
            'lower_limit' => 4.5,
            'upper_limit' => 11,
        ]);

        $this->assertSame('normal', $this->service->evaluate(8, '10^9/L', $range));
        $this->assertSame('high', $this->service->evaluate(12, '10^9/L', $range));
    }

    public function test_evaluate_returns_null_when_units_are_not_comparable(): void
    {
        $range = $this->range();

        $this->assertNull($this->service->evaluate(90, 'mg/dL', $range));
    }

    public function test_evaluate_returns_null_when_range_is_open_ended(): void
    {
        $range = $this->range(['lower_limit' => null, 'upper_limit' => null]);

        $this->assertNull($this->service->evaluate(90, 'fL', $range));
    }
}
