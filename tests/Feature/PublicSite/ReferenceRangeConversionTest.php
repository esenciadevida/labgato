<?php

namespace Tests\Feature\PublicSite;

use App\Models\Category;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ReferenceRangeSeeder::class);
    }

    public function test_reference_range_detail_shows_unit_toggle_when_conversion_factor_exists(): void
    {
        $category = Category::factory()->create(['slug' => 'chemistry', 'name' => 'Chemistry']);
        
        $range = ReferenceRange::factory()->create([
            'name' => 'Sodium Test',
            'slug' => 'sodium-test',
            'analyte_name' => 'Sodium',
            'unit' => 'mmol/L',
            'si_unit' => 'mmol/L',
            'conversion_factor' => 1.0,
            'lower_limit' => 135,
            'upper_limit' => 145,
        ]);
        $range->categories()->attach($category);

        $response = $this->get(route('public.reference-ranges.show', $range->slug));

        $response->assertStatus(200);
        $response->assertSee('Show SI Units');
        $response->assertSee('Show Conventional Units');
    }

    public function test_reference_range_detail_hides_unit_toggle_when_no_conversion_factor(): void
    {
        $category = Category::factory()->create(['slug' => 'hematology', 'name' => 'Hematology']);
        
        $range = ReferenceRange::factory()->create([
            'name' => 'Hemoglobin Test',
            'slug' => 'hemoglobin-test',
            'analyte_name' => 'Hemoglobin',
            'unit' => 'g/dL',
            'si_unit' => null,
            'conversion_factor' => null,
            'lower_limit' => 12,
            'upper_limit' => 16,
        ]);
        $range->categories()->attach($category);

        $response = $this->get(route('public.reference-ranges.show', $range->slug));

        $response->assertStatus(200);
        $response->assertDontSee('Show SI Units');
        $response->assertDontSee('Show Conventional Units');
    }

    public function test_reference_range_shows_copy_button(): void
    {
        $category = Category::factory()->create(['slug' => 'chemistry', 'name' => 'Chemistry']);
        
        $range = ReferenceRange::factory()->create([
            'name' => 'Potassium Test',
            'slug' => 'potassium-test',
            'analyte_name' => 'Potassium',
            'unit' => 'mmol/L',
            'lower_limit' => 3.5,
            'upper_limit' => 5.0,
        ]);
        $range->categories()->attach($category);

        $response = $this->get(route('public.reference-ranges.show', $range->slug));

        $response->assertStatus(200);
        $response->assertSee('Copy reference range');
        $response->assertSee('Copy');
    }
}