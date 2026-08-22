<?php

namespace Tests\Feature\PublicSite;

use App\Models\ReferenceRange;
use Database\Seeders\ReferenceRangeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceRangeDataSeeder::class);
    }

    public function test_reference_range_listing_displays_real_analytes(): void
    {
        $response = $this->get(route('public.reference-ranges.index'));

        $response->assertStatus(200);
        $response->assertSee('Albumin');
        $response->assertSee('TSH');
        $response->assertSee('LDL');

        $pageTwo = $this->get(route('public.reference-ranges.index', ['page' => 2]));

        $pageTwo->assertStatus(200);
        $pageTwo->assertSee('Glucose');
        $pageTwo->assertSee('Creatinine');
        $pageTwo->assertSee('Sodium');

        $pageThree = $this->get(route('public.reference-ranges.index', ['page' => 3]));

        $pageThree->assertStatus(200);
        $pageThree->assertSee('Hemoglobin');
        $pageThree->assertSee('WBC');
    }

    public function test_reference_range_detail_shows_correct_range_values(): void
    {
        $response = $this->get(route('public.reference-ranges.show', 'hemoglobin-adult-male'));

        $response->assertStatus(200);
        $response->assertSee('13.8');
        $response->assertSee('17.2');
        $response->assertSee('g/dL');
    }

    public function test_reference_range_detail_shows_demographic_variations_grouped(): void
    {
        $response = $this->get(route('public.reference-ranges.show', 'hemoglobin-adult-male'));

        $response->assertStatus(200);
        $response->assertSee('Hemoglobin - Adult Female');
        $response->assertSee('Hemoglobin - Adult Male');
    }

    public function test_reference_range_detail_shows_critical_values_for_applicable_analytes(): void
    {
        $response = $this->get(route('public.reference-ranges.show', 'hemoglobin-adult-male'));

        $response->assertStatus(200);
        $response->assertSee('Critical low:');
        $response->assertSee('Critical high:');
    }

    public function test_reference_range_detail_shows_conversion_toggle_when_si_unit_is_set(): void
    {
        $response = $this->get(route('public.reference-ranges.show', 'glucose-fasting-adult'));

        $response->assertStatus(200);
        $response->assertSee('Show SI Units');
        $response->assertSee('Show Conventional Units');
    }

    public function test_reference_range_listing_has_at_least_40_published_ranges(): void
    {
        $count = ReferenceRange::query()->published()->count();

        $this->assertGreaterThanOrEqual(40, $count);
    }
}
