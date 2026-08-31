<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SearchQuery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::factory()->create(['slug' => $slug]);
            $role->permissions()->attach($permission);
        }

        $user->roles()->attach($role);

        return $user;
    }

    public function test_user_with_dashboard_view_sees_advanced_dashboard(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Published Resources');
        $response->assertSee('Pending Review');
        $response->assertSee('Searches Today');
        $response->assertSee('AI Usage Today');
    }

    public function test_advanced_dashboard_displays_search_insights(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        SearchQuery::factory()->count(3)->create([
            'query' => 'anion gap calculator',
            'results_count' => 5,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        SearchQuery::factory()->count(2)->create([
            'query' => 'nonexistent lab test',
            'results_count' => 0,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('anion gap calculator');
        $response->assertSee('nonexistent lab test');
        $response->assertSee('Content Gaps');
    }

    public function test_advanced_dashboard_handles_empty_data(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('No search data available yet.');
        $response->assertSee('No AI usage data yet');
        $response->assertSee('No resource growth yet');
    }
}
