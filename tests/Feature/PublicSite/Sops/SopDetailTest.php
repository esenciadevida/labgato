<?php

namespace Tests\Feature\PublicSite\Sops;

use App\Enums\ResourceStatus;
use App\Models\Sop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SopDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_sop_detail_renders_for_published_sop(): void
    {
        $sop = Sop::factory()->create([
            'name' => 'Standard Venipuncture Procedure',
            'status' => ResourceStatus::PUBLISHED,
            'department' => 'Phlebotomy',
            'version' => 'v3.0',
        ]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('Standard Venipuncture Procedure');
        $response->assertSee('Phlebotomy');
        $response->assertSee('v3.0');
    }

    public function test_sop_detail_returns_404_for_draft_sop(): void
    {
        $sop = Sop::factory()->draft()->create(['slug' => 'hidden-sop']);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(404);
    }

    public function test_sop_detail_renders_custom_disclaimer(): void
    {
        $sop = Sop::factory()->create([
            'name' => 'Quality Control SOP',
            'status' => ResourceStatus::PUBLISHED,
            'mandatory_disclaimer' => 'This is a custom institutional disclaimer.',
        ]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('This is a custom institutional disclaimer.');
    }

    public function test_sop_detail_renders_default_disclaimer_when_not_set(): void
    {
        $sop = Sop::factory()->create([
            'name' => 'Quality Control SOP',
            'status' => ResourceStatus::PUBLISHED,
            'mandatory_disclaimer' => null,
        ]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('Disclaimer');
    }

    public function test_sop_detail_renders_structured_data(): void
    {
        $sop = Sop::factory()->create([
            'name' => 'Structured Data SOP',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
    }
}
