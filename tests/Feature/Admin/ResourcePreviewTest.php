<?php

namespace Tests\Feature\Admin;

use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePreviewTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithPermission(string $permissionSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $permissionSlug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_draft_knowledge_is_hidden_publicly_but_viewable_in_admin_preview(): void
    {
        $draft = KnowledgeResource::factory()->draft()->create();

        $this->get(route('public.knowledge.show', $draft->slug))->assertNotFound();

        $admin = $this->adminWithPermission('knowledge.view');
        $this->actingAs($admin)
            ->get(route('admin.knowledge.preview', $draft))
            ->assertOk()
            ->assertSee($draft->name);
    }

    public function test_draft_sop_is_hidden_publicly_but_viewable_in_admin_preview(): void
    {
        $draft = Sop::factory()->draft()->create();

        $this->get(route('public.sops.show', $draft->slug))->assertNotFound();

        $admin = $this->adminWithPermission('sops.view');
        $this->actingAs($admin)
            ->get(route('admin.sops.preview', $draft))
            ->assertOk()
            ->assertSee($draft->name);
    }

    public function test_draft_calculator_is_hidden_publicly_but_viewable_in_admin_preview(): void
    {
        $draft = Calculator::factory()->draft()->create();

        $this->get(route('public.calculators.show', $draft->slug))->assertNotFound();

        $admin = $this->adminWithPermission('calculators.view');
        $this->actingAs($admin)
            ->get(route('admin.calculators.preview', $draft))
            ->assertOk()
            ->assertSee($draft->name);
    }

    public function test_draft_interpretation_is_hidden_publicly_but_viewable_in_admin_preview(): void
    {
        $draft = InterpretationResource::factory()->draft()->create();

        $this->get(route('public.interpretation.show', $draft->slug))->assertNotFound();

        $admin = $this->adminWithPermission('interpretation.view');
        $this->actingAs($admin)
            ->get(route('admin.interpretation.preview', $draft))
            ->assertOk()
            ->assertSee($draft->name);
    }

    public function test_preview_requires_admin_view_permission(): void
    {
        $draft = KnowledgeResource::factory()->draft()->create();

        $this->get(route('admin.knowledge.preview', $draft))
            ->assertRedirect(route('auth.login'));

        $member = User::factory()->create(['is_active' => true]);
        $this->actingAs($member)
            ->get(route('admin.knowledge.preview', $draft))
            ->assertForbidden();
    }
}
