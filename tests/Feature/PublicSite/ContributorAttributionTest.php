<?php

namespace Tests\Feature\PublicSite;

use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributorAttributionTest extends TestCase
{
    use RefreshDatabase;

    private function contributor(array $attributes = []): User
    {
        return User::factory()->member()->create(array_merge([
            'name' => 'Jane Smith',
            'profile_slug' => 'jane-smith',
            'is_active' => true,
        ], $attributes));
    }

    public function test_member_contributed_sop_displays_credit_block(): void
    {
        $user = $this->contributor();
        $sop = Sop::factory()->create(['contributed_by' => $user->id]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('Contributed by Jane Smith');
    }

    public function test_member_contributed_knowledge_displays_credit_block(): void
    {
        $user = $this->contributor();
        $knowledge = KnowledgeResource::factory()->create(['contributed_by' => $user->id]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertStatus(200);
        $response->assertSee('Contributed by Jane Smith');
    }

    public function test_member_contributed_interpretation_displays_credit_block(): void
    {
        $user = $this->contributor();
        $interpretation = InterpretationResource::factory()->create(['contributed_by' => $user->id]);

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertStatus(200);
        $response->assertSee('Contributed by Jane Smith');
    }

    public function test_admin_created_resource_does_not_display_credit_block(): void
    {
        $sop = Sop::factory()->create(['contributed_by' => null]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertDontSee('Contributed by');
    }

    public function test_credit_block_links_to_profile_for_active_contributor(): void
    {
        $user = $this->contributor();
        $sop = Sop::factory()->create(['contributed_by' => $user->id]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee(route('contributors.show', $user->profile_slug), false);
    }

    public function test_credit_block_is_plain_text_for_deactivated_contributor(): void
    {
        $user = $this->contributor(['is_active' => false]);
        $sop = Sop::factory()->create(['contributed_by' => $user->id]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('Contributed by Jane Smith');
        $response->assertDontSee(route('contributors.show', $user->profile_slug), false);
    }

    public function test_resource_json_ld_includes_author_for_contributed_resource(): void
    {
        $user = $this->contributor();
        $sop = Sop::factory()->create(['contributed_by' => $user->id]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('"@type": "Person"', false);
        $response->assertSee('"name": "Jane Smith"', false);
    }

    public function test_resource_json_ld_uses_organization_for_admin_created_resource(): void
    {
        $sop = Sop::factory()->create(['contributed_by' => null]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('"@type": "Organization"', false);
        $response->assertDontSee('"@type": "Person"', false);
    }
}
