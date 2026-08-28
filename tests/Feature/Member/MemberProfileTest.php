<?php

namespace Tests\Feature\Member;

use App\Mail\ConfirmPasswordCreationEmail;
use App\Mail\PasswordUpdatedEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

    public function test_google_only_member_can_create_password_without_current_password(): void
    {
        Mail::fake();

        $member = User::factory()->member()->googleOnly()->create([
            'password' => bcrypt(Str::password(32)),
        ]);

        $this->assertNull($member->password_set_at);

        $this->actingAs($member)
            ->put(route('account.password.update'), [
                'password' => 'NewPass456',
                'password_confirmation' => 'NewPass456',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $member->refresh();

        $this->assertFalse(Hash::check('NewPass456', $member->password));
        $this->assertNull($member->password_set_at);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'account.password.set']);

        Mail::assertQueued(ConfirmPasswordCreationEmail::class, function (ConfirmPasswordCreationEmail $mail) use ($member) {
            return $mail->hasTo($member->email);
        });

        $confirmMailable = Mail::queued(ConfirmPasswordCreationEmail::class)->first();
        $url = $confirmMailable->confirmationUrl;

        $this->actingAs($member)->get($url)->assertRedirect(route('account.profile'));

        $member->refresh();

        $this->assertTrue(Hash::check('NewPass456', $member->password));
        $this->assertNotNull($member->password_set_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.password.set']);

        Mail::assertQueued(PasswordUpdatedEmail::class, function (PasswordUpdatedEmail $mail) use ($member) {
            return $mail->hasTo($member->email) && $mail->created === true;
        });
    }

    public function test_google_only_member_sees_create_password_card_on_profile(): void
    {
        $member = User::factory()->member()->googleOnly()->create([
            'password' => bcrypt(Str::password(32)),
        ]);

        $this->actingAs($member)
            ->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Create a Password')
            ->assertSee('Create Password')
            ->assertDontSee('Current password');
    }

    public function test_after_creating_password_current_password_is_required_for_changes(): void
    {
        Mail::fake();

        $member = User::factory()->member()->googleOnly()->create([
            'password' => bcrypt(Str::password(32)),
        ]);

        $this->actingAs($member)
            ->put(route('account.password.update'), [
                'password' => 'FirstPass123',
                'password_confirmation' => 'FirstPass123',
            ])
            ->assertRedirect(route('account.profile'));

        $confirmMailable = Mail::queued(ConfirmPasswordCreationEmail::class)->first();
        $this->actingAs($member)->get($confirmMailable->confirmationUrl)->assertRedirect(route('account.profile'));

        $this->actingAs($member->fresh())
            ->put(route('account.password.update'), [
                'password' => 'SecondPass456',
                'password_confirmation' => 'SecondPass456',
            ])
            ->assertSessionHasErrors('current_password');

        $member->refresh();

        $this->assertTrue(Hash::check('FirstPass123', $member->password));
    }
}
