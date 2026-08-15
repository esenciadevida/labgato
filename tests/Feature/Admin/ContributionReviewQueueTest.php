<?php

namespace Tests\Feature\Admin;

use App\Enums\ResourceStatus;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    private function reviewer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'contributions.review']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    private function pendingSop(User $contributor, array $attributes = []): Sop
    {
        return Sop::factory()->create(array_merge([
            'status' => ResourceStatus::UNDER_REVIEW,
            'published_at' => null,
            'contributed_by' => $contributor->id,
            'created_by' => $contributor->id,
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
        ], $attributes));
    }

    public function test_admin_with_contributions_review_can_view_the_review_queue(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $this->pendingSop($member, ['name' => 'Pending SOP For Review']);

        $response = $this->actingAs($admin)->get(route('admin.contributions.index'));

        $response->assertStatus(200);
        $response->assertSee('Contribution Review Queue');
        $response->assertSee('Pending SOP For Review');
    }

    public function test_admin_without_contributions_review_cannot_view_the_review_queue(): void
    {
        $admin = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.contributions.index'));

        $response->assertStatus(403);
    }

    public function test_review_queue_lists_only_under_review_contributions_with_contributed_by(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();

        $this->pendingSop($member, ['name' => 'Pending Contribution']);

        Sop::factory()->create([
            'name' => 'Draft Contribution',
            'status' => ResourceStatus::DRAFT,
            'contributed_by' => $member->id,
        ]);

        Sop::factory()->create([
            'name' => 'Unassigned Pending Contribution',
            'status' => ResourceStatus::UNDER_REVIEW,
            'contributed_by' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.contributions.index'));

        $response->assertStatus(200);
        $response->assertSee('Pending Contribution');
        $response->assertDontSee('Draft Contribution');
        $response->assertDontSee('Unassigned Pending Contribution');
    }

    public function test_review_queue_can_filter_by_type(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();

        $this->pendingSop($member, ['name' => 'Pending SOP Contribution']);

        KnowledgeResource::factory()->create([
            'name' => 'Pending Knowledge Contribution',
            'status' => ResourceStatus::UNDER_REVIEW,
            'published_at' => null,
            'contributed_by' => $member->id,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.contributions.index', ['type' => 'sop'])
        );

        $response->assertStatus(200);
        $response->assertSee('Pending SOP Contribution');
        $response->assertDontSee('Pending Knowledge Contribution');
    }

    public function test_review_queue_shows_empty_state_when_no_pending_contributions(): void
    {
        $admin = $this->reviewer();

        $response = $this->actingAs($admin)->get(route('admin.contributions.index'));

        $response->assertStatus(200);
        $response->assertSee('No pending contributions');
    }

    public function test_review_queue_does_not_include_published_resources(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();

        InterpretationResource::factory()->create([
            'name' => 'Already Published Resource',
            'status' => ResourceStatus::PUBLISHED,
            'contributed_by' => $member->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.contributions.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Already Published Resource');
    }
}
