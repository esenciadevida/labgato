<?php

namespace Tests\Feature\Member;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('account.profile'));

        $response->assertRedirect(route('home'));
    }

    public function test_admin_cannot_access_member_profile_page(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('account.profile'));

        $response->assertRedirect(route('home'));
    }

    public function test_member_can_view_their_profile_page(): void
    {
        $member = User::factory()->member()->create(['bio' => 'Laboratory scientist.']);

        $response = $this->actingAs($member)->get(route('account.profile'));

        $response->assertOk();
        $response->assertSee('My Profile');
        $response->assertSee($member->email);
        $response->assertSee('Laboratory scientist.');
        $response->assertSee('Change Password');
        $response->assertSee('Account Details');
    }

    public function test_profile_page_is_distinct_from_dashboard(): void
    {
        $member = User::factory()->member()->create();

        $profile = $this->actingAs($member)->get(route('account.profile'));
        $dashboard = $this->actingAs($member)->get(route('account.dashboard'));

        $profile->assertOk();
        $dashboard->assertOk();
        $profile->assertSee('Account Details');
        $dashboard->assertSee('Submit a Contribution');
    }

    public function test_member_can_update_their_profile(): void
    {
        $member = User::factory()->member()->create(['name' => 'Old Name', 'bio' => 'Old bio.']);

        $this->actingAs($member)
            ->put(route('account.profile.update'), [
                'name' => 'New Name',
                'bio' => 'New bio about me.',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'New Name',
            'bio' => 'New bio about me.',
        ]);
    }

    public function test_member_can_clear_their_bio(): void
    {
        $member = User::factory()->member()->create(['name' => 'Scientist', 'bio' => 'Something']);

        $this->actingAs($member)
            ->put(route('account.profile.update'), [
                'name' => 'Scientist',
                'bio' => null,
            ])
            ->assertRedirect(route('account.profile'));

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Scientist',
            'bio' => null,
        ]);
    }

    public function test_member_profile_update_requires_a_name(): void
    {
        $member = User::factory()->member()->create(['name' => 'Original Name']);

        $this->actingAs($member)
            ->put(route('account.profile.update'), [
                'name' => '',
                'bio' => 'Something',
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Original Name',
        ]);
    }

    public function test_member_can_change_their_password(): void
    {
        $member = User::factory()->member()->create([
            'password' => bcrypt('OldPass123'),
        ]);

        $response = $this->actingAs($member)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPass123',
                'password' => 'NewPass456',
                'password_confirmation' => 'NewPass456',
            ]);

        $response->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $member->refresh();

        $this->assertTrue(Hash::check('NewPass456', $member->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.password.changed']);
    }

    public function test_change_password_requires_correct_current_password(): void
    {
        $member = User::factory()->member()->create([
            'password' => bcrypt('OldPass123'),
        ]);

        $this->actingAs($member)
            ->from(route('account.profile'))
            ->put(route('account.password.update'), [
                'current_password' => 'WrongPass999',
                'password' => 'NewPass456',
                'password_confirmation' => 'NewPass456',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHasErrors('current_password');

        $member->refresh();

        $this->assertTrue(Hash::check('OldPass123', $member->password));
    }

    public function test_change_password_validates_new_password_strength(): void
    {
        $member = User::factory()->member()->create([
            'password' => bcrypt('OldPass123'),
        ]);

        $this->actingAs($member)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPass123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $member->refresh();

        $this->assertTrue(Hash::check('OldPass123', $member->password));
    }

    public function test_change_password_requires_confirmation_match(): void
    {
        $member = User::factory()->member()->create([
            'password' => bcrypt('OldPass123'),
        ]);

        $this->actingAs($member)
            ->put(route('account.password.update'), [
                'current_password' => 'OldPass123',
                'password' => 'NewPass456',
                'password_confirmation' => 'Different789',
            ])
            ->assertSessionHasErrors('password');

        $member->refresh();

        $this->assertTrue(Hash::check('OldPass123', $member->password));
    }
}
