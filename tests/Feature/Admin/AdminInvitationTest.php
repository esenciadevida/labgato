<?php

namespace Tests\Feature\Admin;

use App\Mail\AdminInvitationEmail;
use App\Mail\AdminWelcomeEmail;
use App\Models\AdminInvitation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true, 'user_type' => 'admin']);
        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $role->permissions()->attach(
            Permission::factory()->create(['slug' => 'admin.full-access'])
        );
        $user->roles()->attach($role);

        return $user;
    }

    private function invite(string $email, ?Role $role = null): array
    {
        Mail::fake();

        $superAdmin = $this->superAdmin();
        $role ??= Role::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.administrators.store'), [
            'name' => 'Invited Admin',
            'email' => $email,
            'role_id' => $role->id,
        ]);

        $invitation = AdminInvitation::where('email', $email)->firstOrFail();
        $plainToken = $this->captureToken();

        $this->post(route('logout'));

        Mail::fake();

        return [$invitation, $plainToken, $role];
    }

    private function captureToken(): string
    {
        $mailable = Mail::sent(AdminInvitationEmail::class)->last();

        return (string) Str::after($mailable->acceptUrl, '/admin/invitation/');
    }

    public function test_invited_admin_can_accept_and_set_password(): void
    {
        [$invitation, $plainToken, $role] = $this->invite('invitee@example.com');

        $show = $this->get(route('admin.invitation.show', ['token' => $plainToken]));

        $show->assertOk();
        $show->assertSee('Set up your account');
        $show->assertSee($invitation->email);

        $response = $this->post(route('admin.invitation.accept', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        Mail::assertQueued(AdminWelcomeEmail::class, function ($mail) use ($invitation, $role) {
            return $mail->hasTo($invitation->email)
                && $mail->roleName === $role->name
                && Str::contains($mail->signInUrl, '/login');
        });

        $admin = User::where('email', $invitation->email)->firstOrFail();
        $this->assertSame('admin', $admin->user_type);
        $this->assertTrue($admin->is_active);
        $this->assertNotNull($admin->password_set_at);
        $this->assertTrue($admin->roles->pluck('id')->contains($role->id));
        $this->assertNotNull($invitation->fresh()->accepted_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'administrators.invitation_accepted',
            'resource_id' => $admin->id,
        ]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $response = $this->get(route('admin.invitation.show', ['token' => str_repeat('x', 48)]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        [$invitation, $plainToken] = $this->invite('invitee@example.com');

        $invitation->update(['expires_at' => now()->subDay()]);

        $show = $this->get(route('admin.invitation.show', ['token' => $plainToken]));
        $show->assertRedirect(route('home'));

        $accept = $this->post(route('admin.invitation.accept', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $accept->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'invitee@example.com']);
    }

    public function test_already_accepted_invitation_cannot_be_reused(): void
    {
        [, $plainToken] = $this->invite('invitee@example.com');

        $this->post(route('admin.invitation.accept', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $this->post(route('logout'));

        $second = $this->post(route('admin.invitation.accept', ['token' => $plainToken]), [
            'password' => 'AnotherPass123!',
            'password_confirmation' => 'AnotherPass123!',
        ]);

        $second->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_resend_generates_new_token_and_invalidates_old_one(): void
    {
        Mail::fake();

        $superAdmin = $this->superAdmin();
        $role = Role::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.administrators.store'), [
            'name' => 'Resend Admin',
            'email' => 'resend@example.com',
            'role_id' => $role->id,
        ]);

        $oldToken = $this->captureToken();
        $invitation = AdminInvitation::where('email', 'resend@example.com')->firstOrFail();

        $this->post(route('logout'));

        Mail::fake();

        $this->actingAs($superAdmin)->post(route('admin.administrators.invitations.resend', $invitation));

        Mail::assertSent(AdminInvitationEmail::class, fn ($mail) => $mail->hasTo('resend@example.com'));

        $newToken = $this->captureToken();
        $this->assertNotSame($oldToken, $newToken);

        $this->post(route('logout'));

        $oldLink = $this->get(route('admin.invitation.show', ['token' => $oldToken]));
        $oldLink->assertRedirect(route('home'));

        $newLink = $this->get(route('admin.invitation.show', ['token' => $newToken]));
        $newLink->assertOk();
    }

    public function test_revoke_prevents_acceptance(): void
    {
        Mail::fake();

        $superAdmin = $this->superAdmin();
        $role = Role::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.administrators.store'), [
            'name' => 'Revoked Admin',
            'email' => 'revoked@example.com',
            'role_id' => $role->id,
        ]);

        $token = $this->captureToken();
        $invitation = AdminInvitation::where('email', 'revoked@example.com')->firstOrFail();

        $this->post(route('logout'));

        $this->actingAs($superAdmin)->delete(route('admin.administrators.invitations.revoke', $invitation));

        $this->assertDatabaseMissing('admin_invitations', ['email' => 'revoked@example.com']);

        $this->post(route('logout'));

        $response = $this->post(route('admin.invitation.accept', ['token' => $token]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_cannot_invite_email_that_already_has_an_account(): void
    {
        Mail::fake();

        $superAdmin = $this->superAdmin();
        User::factory()->create(['email' => 'taken@example.com']);
        $role = Role::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.administrators.store'), [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'role_id' => $role->id,
        ]);

        $response->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }
}
