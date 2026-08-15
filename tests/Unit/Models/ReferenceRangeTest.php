<?php

namespace Tests\Unit\Models;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\ReferenceRange;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReferenceRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_range_casts_numeric_limits_to_floats(): void
    {
        $referenceRange = ReferenceRange::factory()->create([
            'lower_limit' => 13.0,
            'upper_limit' => 17.0,
            'critical_low' => 7.0,
            'critical_high' => 22.0,
            'conversion_factor' => 10.0,
            'age_min' => 18.0,
            'age_max' => 65.0,
        ]);

        $this->assertSame(13.0, $referenceRange->lower_limit);
        $this->assertSame(17.0, $referenceRange->upper_limit);
        $this->assertSame(7.0, $referenceRange->critical_low);
        $this->assertSame(22.0, $referenceRange->critical_high);
        $this->assertSame(10.0, $referenceRange->conversion_factor);
        $this->assertSame(18.0, $referenceRange->age_min);
        $this->assertSame(65.0, $referenceRange->age_max);
    }

    public function test_reference_range_casts_status_to_resource_status_enum(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::DRAFT]);

        $this->assertInstanceOf(ResourceStatus::class, $referenceRange->status);
        $this->assertSame(ResourceStatus::DRAFT, $referenceRange->status);
    }

    public function test_reference_range_casts_effective_date_to_date(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['effective_date' => '2026-01-01']);

        $this->assertInstanceOf(Carbon::class, $referenceRange->effective_date);
        $this->assertSame('2026-01-01', $referenceRange->effective_date->toDateString());
    }

    public function test_reference_range_belongs_to_many_categories_using_custom_pivot(): void
    {
        $referenceRange = ReferenceRange::factory()->create();
        $category = Category::factory()->create();
        $referenceRange->categories()->attach($category);

        $this->assertTrue($referenceRange->categories->contains($category));
        $this->assertDatabaseHas('reference_range_category', [
            'reference_range_id' => $referenceRange->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_reference_range_belongs_to_many_tags_using_custom_pivot(): void
    {
        $referenceRange = ReferenceRange::factory()->create();
        $tag = Tag::factory()->create();
        $referenceRange->tags()->attach($tag);

        $this->assertTrue($referenceRange->tags->contains($tag));
        $this->assertDatabaseHas('reference_range_tag', [
            'reference_range_id' => $referenceRange->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_scope_search_filters_by_name_analyte_or_description(): void
    {
        $byName = ReferenceRange::factory()->create(['name' => 'Potassium - Adult', 'analyte_name' => 'Sodium']);
        $byAnalyte = ReferenceRange::factory()->create(['name' => 'Something Else', 'analyte_name' => 'Potassium']);
        $byDescription = ReferenceRange::factory()->create(['name' => 'Creatinine - Adult', 'analyte_name' => 'Creatinine', 'short_description' => 'Potassium level check']);
        ReferenceRange::factory()->create(['name' => 'Glucose - Adult', 'analyte_name' => 'Glucose']);

        $results = ReferenceRange::search('Potassium')->get();

        $this->assertTrue($results->contains($byName));
        $this->assertTrue($results->contains($byAnalyte));
        $this->assertTrue($results->contains($byDescription));
        $this->assertSame(3, $results->count());
    }

    public function test_scope_for_analyte_filters_by_analyte_name(): void
    {
        $hemoglobin = ReferenceRange::factory()->create(['analyte_name' => 'Hemoglobin']);
        ReferenceRange::factory()->create(['analyte_name' => 'Glucose']);

        $results = ReferenceRange::forAnalyte('Hemoglobin')->get();

        $this->assertTrue($results->contains($hemoglobin));
        $this->assertSame(1, $results->count());
    }

    public function test_scope_published_filters_published_reference_ranges(): void
    {
        $published = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        ReferenceRange::factory()->draft()->create();

        $results = ReferenceRange::published()->get();

        $this->assertTrue($results->contains($published));
        $this->assertSame(1, $results->count());
    }
}
