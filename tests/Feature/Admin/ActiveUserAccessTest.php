<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_inactive_user_is_logged_out_when_accessing_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('auth.login'));
        $response->assertSessionHas('error', 'Your account is no longer active.');
        $this->assertGuest();
    }
}
