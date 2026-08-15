<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAreaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_home_when_accessing_member_area(): void
    {
        $response = $this->get(route('account.dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_member_can_access_member_dashboard(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_member_area(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_inactive_member_is_logged_out_when_accessing_member_area(): void
    {
        $user = User::factory()->member()->create(['is_active' => false]);

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertRedirect(route('auth.login'));
        $this->assertGuest();
    }
}
