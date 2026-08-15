<?php

namespace Tests\Feature\Admin\Sops;

use App\Enums\ResourceStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\SopVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SopVersionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermission(string $permissionSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $permissionSlug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Daily Hematology Analyzer Quality Control',
            'slug' => 'daily-hematology-analyzer-quality-control',
            'short_description' => 'Daily quality control procedure for the hematology analyzer.',
            'content' => "# Daily Quality Control\n\nRun controls before patient samples.",
            'department' => 'Hematology',
            'author_name' => 'Lab Education Team',
            'reviewed_date' => '2026-01-15',
            'mandatory_disclaimer' => 'Follow your institutional policy.',
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    public function test_creating_an_sop_creates_a_v1_snapshot(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $this->actingAs($user)->post(route('admin.sops.store'), $this->payload());

        $sop = Sop::query()->first();

        $this->assertSame('1.0', $sop->version);
        $this->assertDatabaseHas('sop_versions', [
            'sop_id' => $sop->id,
            'version_number' => '1.0',
            'change_summary' => 'Initial version',
            'created_by' => $user->id,
        ]);
    }

    public function test_saving_as_a_new_version_creates_a_snapshot(): void
    {
        $sop = Sop::factory()->draft()->create(['version' => '1.0']);
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->put(route('admin.sops.update', $sop), $this->payload([
            'content' => 'Updated content',
            'is_new_version' => '1',
            'version' => '2.0',
            'change_summary' => 'Rewrote the procedure',
        ]));

        $this->assertDatabaseHas('sops', [
            'id' => $sop->id,
            'version' => '2.0',
        ]);

        $this->assertDatabaseHas('sop_versions', [
            'sop_id' => $sop->id,
            'version_number' => '2.0',
            'change_summary' => 'Rewrote the procedure',
            'created_by' => $user->id,
        ]);

        $this->assertSame(1, SopVersion::where('sop_id', $sop->id)->count());
    }

    public function test_saving_without_a_new_version_does_not_create_a_snapshot(): void
    {
        $sop = Sop::factory()->draft()->create(['version' => '1.0']);
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->put(route('admin.sops.update', $sop), $this->payload([
            'content' => 'Minor tweak',
        ]));

        $this->assertSame(0, SopVersion::where('sop_id', $sop->id)->count());
    }

    public function test_version_history_lists_all_versions(): void
    {
        $sop = Sop::factory()->create(['version' => '1.0']);
        SopVersion::factory()->create(['sop_id' => $sop->id, 'version_number' => '1.0', 'change_summary' => 'Initial version']);
        SopVersion::factory()->create(['sop_id' => $sop->id, 'version_number' => '1.1', 'change_summary' => 'Clarified step 3']);
        $user = $this->createUserWithPermission('sops.view');

        $response = $this->actingAs($user)->get(route('admin.sops.versions.index', $sop));

        $response->assertStatus(200);
        $response->assertSee('1.0');
        $response->assertSee('1.1');
        $response->assertSee('Clarified step 3');
    }

    public function test_viewing_a_specific_version_shows_its_content(): void
    {
        $sop = Sop::factory()->create(['version' => '1.0']);
        $version = SopVersion::factory()->create([
            'sop_id' => $sop->id,
            'version_number' => '1.0',
            'content' => "# Old Procedure\n\nLegacy steps here.",
        ]);
        $user = $this->createUserWithPermission('sops.view');

        $response = $this->actingAs($user)->get(route('admin.sops.versions.show', [$sop, $version]));

        $response->assertStatus(200);
        $response->assertSee('Old Procedure');
    }

    public function test_restoring_a_version_creates_a_new_version_with_the_old_content(): void
    {
        $sop = Sop::factory()->create([
            'version' => '1.1',
            'content' => '# Current content',
        ]);
        SopVersion::factory()->create([
            'sop_id' => $sop->id,
            'version_number' => '1.0',
            'content' => '# Legacy content',
        ]);
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->post(route('admin.sops.versions.restore', [$sop, SopVersion::query()->first()]));

        $sop->refresh();

        $this->assertSame('1.2', $sop->version);
        $this->assertStringContainsString('Legacy content', $sop->content);

        $newSnapshot = SopVersion::query()->where('version_number', '1.2')->first();

        $this->assertNotNull($newSnapshot);
        $this->assertStringContainsString('Legacy content', $newSnapshot->content);
        $this->assertSame('Restored from version 1.0', $newSnapshot->change_summary);
    }

    public function test_restoring_a_version_is_audited(): void
    {
        $sop = Sop::factory()->create(['version' => '1.0']);
        $version = SopVersion::factory()->create(['sop_id' => $sop->id, 'version_number' => '1.0']);
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->post(route('admin.sops.versions.restore', [$sop, $version]));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sops.version_restored',
            'resource_type' => Sop::class,
            'resource_id' => $sop->id,
        ]);
    }

    public function test_user_without_view_permission_cannot_access_version_history(): void
    {
        $sop = Sop::factory()->create();
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.sops.versions.index', $sop));

        $response->assertStatus(403);
    }

    public function test_user_without_edit_permission_cannot_restore_a_version(): void
    {
        $sop = Sop::factory()->create(['version' => '1.0']);
        $version = SopVersion::factory()->create(['sop_id' => $sop->id, 'version_number' => '1.0']);
        $viewUser = $this->createUserWithPermission('sops.view');

        $response = $this->actingAs($viewUser)->post(route('admin.sops.versions.restore', [$sop, $version]));

        $response->assertStatus(403);
        $this->assertSame('1.0', $sop->fresh()->version);
    }

    public function test_public_sop_page_shows_current_version(): void
    {
        $sop = Sop::factory()->create([
            'version' => '2.1',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertStatus(200);
        $response->assertSee('2.1');
    }
}
