<?php

namespace Tests\Feature\Member;

use App\Enums\ResourceStatus;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->member()->create(['is_active' => true]);
    }

    private function contribution(User $user, array $attributes = []): Sop
    {
        return Sop::factory()->create(array_merge([
            'status' => ResourceStatus::DRAFT,
            'published_at' => null,
            'contributed_by' => $user->id,
            'created_by' => $user->id,
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
        ], $attributes));
    }

    public function test_member_can_view_their_contribution_list(): void
    {
        $user = $this->member();
        $this->contribution($user, ['name' => 'My Quality Control Procedure']);

        $response = $this->actingAs($user)->get(route('account.contributions.index'));

        $response->assertStatus(200);
        $response->assertSee('My Quality Control Procedure');
    }

    public function test_member_can_view_their_contribution_detail(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user);

        $response = $this->actingAs($user)->get(
            route('account.contributions.show', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertStatus(200);
        $response->assertSee($contribution->name);
    }

    public function test_member_can_edit_a_draft_contribution(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user);

        $response = $this->actingAs($user)->put(
            route('account.contributions.update', ['type' => 'sop', 'id' => $contribution->id]),
            [
                'name' => 'Updated Quality Control Procedure',
                'short_description' => 'Updated daily quality control procedure for the hematology analyzer.',
                'purpose' => 'Ensure accurate and reliable daily results.',
                'scope' => 'Applies to all hematology laboratory staff.',
                'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
                'quality_control' => '',
                'safety_information' => '',
                'references' => '',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'name' => 'Updated Quality Control Procedure',
            'status' => ResourceStatus::DRAFT->value,
        ]);
    }

    public function test_member_cannot_edit_an_under_review_contribution(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user, ['status' => ResourceStatus::UNDER_REVIEW]);

        $response = $this->actingAs($user)->get(
            route('account.contributions.edit', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertStatus(403);
    }

    public function test_member_can_withdraw_an_under_review_contribution(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user, ['status' => ResourceStatus::UNDER_REVIEW]);

        $response = $this->actingAs($user)->post(
            route('account.contributions.withdraw', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'status' => ResourceStatus::DRAFT->value,
        ]);
    }

    public function test_member_can_delete_a_draft_contribution(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user);

        $response = $this->actingAs($user)->delete(
            route('account.contributions.destroy', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('sops', ['id' => $contribution->id]);
    }

    public function test_member_cannot_delete_a_published_contribution(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user, ['status' => ResourceStatus::PUBLISHED]);

        $response = $this->actingAs($user)->delete(
            route('account.contributions.destroy', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertStatus(403);
        $this->assertDatabaseHas('sops', ['id' => $contribution->id]);
    }

    public function test_member_can_revise_and_resubmit_a_rejected_contribution(): void
    {
        $user = $this->member();
        $contribution = $this->contribution($user, [
            'status' => ResourceStatus::REJECTED,
            'rejection_reason' => 'Procedure steps are not detailed enough.',
        ]);

        $this->actingAs($user)->put(
            route('account.contributions.update', ['type' => 'sop', 'id' => $contribution->id]),
            [
                'name' => 'Revised Quality Control Procedure',
                'short_description' => 'A revised and more detailed quality control procedure for the analyzer.',
                'purpose' => 'Ensure accurate and reliable daily results.',
                'scope' => 'Applies to all hematology laboratory staff.',
                'procedure_content' => "## Procedure\n\n1. Power on the analyzer.\n2. Run controls.\n3. Review results.",
                'quality_control' => '',
                'safety_information' => '',
                'references' => '',
            ]
        );

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'name' => 'Revised Quality Control Procedure',
            'status' => ResourceStatus::REJECTED->value,
        ]);

        $response = $this->actingAs($user)->post(
            route('account.contributions.resubmit', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'status' => ResourceStatus::UNDER_REVIEW->value,
            'rejection_reason' => null,
        ]);
    }

    public function test_member_cannot_access_another_members_contribution(): void
    {
        $owner = $this->member();
        $intruder = $this->member();
        $contribution = $this->contribution($owner);

        $response = $this->actingAs($intruder)->get(
            route('account.contributions.show', ['type' => 'sop', 'id' => $contribution->id])
        );

        $response->assertStatus(403);
    }
}
