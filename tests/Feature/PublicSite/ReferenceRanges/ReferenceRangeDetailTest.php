<?php

namespace Tests\Feature\PublicSite\ReferenceRanges;

use App\Enums\ResourceStatus;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_reference_range_detail_renders(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertStatus(200);
        $response->assertSee($referenceRange->name);
    }

    public function test_draft_reference_range_returns_404(): void
    {
        $referenceRange = ReferenceRange::factory()->draft()->create();

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertNotFound();
    }

    public function test_archived_reference_range_returns_404(): void
    {
        $referenceRange = ReferenceRange::factory()->archived()->create();

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertNotFound();
    }

    public function test_nonexistent_reference_range_returns_404(): void
    {
        $response = $this->get(route('public.reference-ranges.show', 'does-not-exist'));

        $response->assertNotFound();
    }

    public function test_reference_range_detail_displays_disclaimer(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertStatus(200);
        $response->assertSee('Educational Reference Only');
    }

    public function test_reference_range_detail_displays_breadcrumbs(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertStatus(200);
        $response->assertSee('Home');
        $response->assertSee('Reference Ranges');
        $response->assertSee($referenceRange->name);
    }

    public function test_reference_range_detail_displays_variations_for_same_analyte(): void
    {
        $primary = ReferenceRange::factory()->create([
            'name' => 'Hemoglobin - Adult Male',
            'analyte_name' => 'Hemoglobin',
            'sex' => 'male',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $variation = ReferenceRange::factory()->create([
            'name' => 'Hemoglobin - Adult Female',
            'analyte_name' => 'Hemoglobin',
            'sex' => 'female',
            'status' => ResourceStatus::PUBLISHED,
        ]);
        $other = ReferenceRange::factory()->create([
            'name' => 'Glucose - Adult',
            'analyte_name' => 'Glucose',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.reference-ranges.show', $primary->slug));

        $response->assertStatus(200);
        $response->assertSee($variation->name);
        $response->assertDontSee($other->name);
    }

    public function test_reference_range_detail_includes_meta_tags(): void
    {
        $referenceRange = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.reference-ranges.show', $referenceRange->slug));

        $response->assertStatus(200);
        $response->assertSee($referenceRange->name.' - '.config('app.name'), false);
    }
}
