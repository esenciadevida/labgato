<?php

namespace Tests\Feature\Admin;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionApprovalTest extends TestCase
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
            'content' => "## Purpose\n\nEnsure accurate and reliable daily results.\n\n## Scope\n\nApplies to all hematology laboratory staff.\n\n## Procedure\n\nRun controls before patient samples.",
        ], $attributes));
    }

    private function approvePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Daily Hematology Analyzer Quality Control',
            'short_description' => 'Daily quality control procedure for the hematology analyzer.',
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
            'quality_control' => '',
            'safety_information' => '',
            'references' => '',
            'categories' => [],
            'tags' => [],
            'seo_title' => null,
            'seo_description' => null,
        ], $overrides);
    }

    public function test_admin_can_approve_a_contribution(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $response = $this->actingAs($admin)->post(
            route('admin.contributions.approve', ['type' => 'sop', 'id' => $contribution->id]),
            $this->approvePayload()
        );

        $response->assertRedirect(route('admin.contributions.index'));
        $response->assertSessionHas('success');

        $contribution->refresh();

        $this->assertSame(ResourceStatus::PUBLISHED, $contribution->status);
        $this->assertNotNull($contribution->published_at);
    }

    public function test_approval_preserves_contributed_by(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $this->actingAs($admin)->post(
            route('admin.contributions.approve', ['type' => 'sop', 'id' => $contribution->id]),
            $this->approvePayload()
        );

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'contributed_by' => $member->id,
        ]);
    }

    public function test_admin_can_reject_a_contribution_with_a_reason(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $response = $this->actingAs($admin)->post(
            route('admin.contributions.reject', ['type' => 'sop', 'id' => $contribution->id]),
            ['rejection_reason' => 'The procedure steps are not detailed enough to publish.']
        );

        $response->assertRedirect(route('admin.contributions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'status' => ResourceStatus::REJECTED->value,
            'rejection_reason' => 'The procedure steps are not detailed enough to publish.',
        ]);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $response = $this->actingAs($admin)->post(
            route('admin.contributions.reject', ['type' => 'sop', 'id' => $contribution->id]),
            ['rejection_reason' => '']
        );

        $response->assertSessionHasErrors('rejection_reason');

        $this->assertDatabaseHas('sops', [
            'id' => $contribution->id,
            'status' => ResourceStatus::UNDER_REVIEW->value,
        ]);
    }

    public function test_admin_can_edit_contribution_before_approving(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $contribution = $this->pendingSop($member);

        $this->actingAs($admin)->post(
            route('admin.contributions.approve', ['type' => 'sop', 'id' => $contribution->id]),
            $this->approvePayload([
                'name' => 'Edited Quality Control Procedure',
                'seo_title' => 'Edited SEO title',
                'categories' => [$category->id],
                'tags' => [$tag->id],
            ])
        );

        $contribution->refresh();

        $this->assertSame('Edited Quality Control Procedure', $contribution->name);
        $this->assertSame('Edited SEO title', $contribution->seo_title);
        $this->assertTrue($contribution->categories->contains($category));
        $this->assertTrue($contribution->tags->contains($tag));
    }

    public function test_approval_is_audit_logged(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $this->actingAs($admin)->post(
            route('admin.contributions.approve', ['type' => 'sop', 'id' => $contribution->id]),
            $this->approvePayload()
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'contributions.approved',
            'resource_type' => Sop::class,
            'resource_id' => $contribution->id,
        ]);
    }

    public function test_rejection_is_audit_logged(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $this->actingAs($admin)->post(
            route('admin.contributions.reject', ['type' => 'sop', 'id' => $contribution->id]),
            ['rejection_reason' => 'The procedure steps are not detailed enough to publish.']
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'contributions.rejected',
            'resource_type' => Sop::class,
            'resource_id' => $contribution->id,
        ]);
    }
}
