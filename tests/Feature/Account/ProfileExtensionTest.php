<?php

namespace Tests\Feature\Account;

use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileExtensionTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attributes = []): User
    {
        return User::factory()->member()->create(array_merge(['is_active' => true], $attributes));
    }

    public function test_registration_requires_professional_status_and_creates_profile(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
        ]);

        $response->assertSessionHasErrors('professional_status');

        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
            'professional_status' => 'student',
        ]);

        $response->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'professional_status' => 'student',
        ]);
    }

    public function test_student_can_save_conditional_student_fields(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'display_name' => 'Jane the Student',
            'country' => 'Nigeria',
            'professional_status' => 'student',
            'institution' => 'University of Lagos',
            'programme' => 'Medical Laboratory Science',
            'level' => '300 Level',
            'graduation_year' => '2027',
            'show_country' => '1',
            'show_institution' => '1',
            'show_on_leaderboard' => '1',
        ]);

        $response->assertRedirect(route('account.profile'));

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'professional_status' => 'student',
            'institution' => 'University of Lagos',
            'programme' => 'Medical Laboratory Science',
            'level' => '300 Level',
            'graduation_year' => 2027,
        ]);

        $this->assertTrue($user->fresh()->isStudent());
    }

    public function test_professional_can_save_conditional_professional_fields(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'country' => 'Ghana',
            'professional_status' => 'scientist',
            'employer' => 'Accra Teaching Hospital',
            'job_title' => 'Senior Laboratory Scientist',
            'years_of_experience' => '8',
            'specialization' => 'Clinical Chemistry',
            'show_country' => '1',
            'show_institution' => '1',
            'show_on_leaderboard' => '0',
        ]);

        $response->assertRedirect(route('account.profile'));

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'professional_status' => 'scientist',
            'employer' => 'Accra Teaching Hospital',
            'job_title' => 'Senior Laboratory Scientist',
            'years_of_experience' => 8,
            'specialization' => 'Clinical Chemistry',
            'show_on_leaderboard' => false,
        ]);

        $this->assertTrue($user->fresh()->isProfessional());
    }

    public function test_photo_upload_stores_file_and_sets_path(): void
    {
        Storage::fake('public');

        $user = $this->member();

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'photo' => UploadedFile::fake()->image('avatar.png', 200, 200),
        ]);

        $response->assertRedirect(route('account.profile'));

        $profile = $user->fresh()->profile;

        $this->assertNotNull($profile->photo_path);
        Storage::disk('public')->assertExists($profile->photo_path);
    }

    public function test_privacy_flags_control_public_display(): void
    {
        $user = $this->member();
        Sop::factory()->create([
            'contributed_by' => $user->id,
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
        ]);

        // Hide everything.
        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'country' => 'Nigeria',
            'professional_status' => 'technician',
            'employer' => 'Lagos Central Laboratory',
            'show_country' => '0',
            'show_institution' => '0',
        ])->assertRedirect(route('account.profile'));

        $this->get(route('contributors.show', $user->profile_slug))
            ->assertOk()
            ->assertDontSee('Nigeria')
            ->assertDontSee('Lagos Central Laboratory');

        // Reveal both.
        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'country' => 'Nigeria',
            'professional_status' => 'technician',
            'employer' => 'Lagos Central Laboratory',
            'show_country' => '1',
            'show_institution' => '1',
        ])->assertRedirect(route('account.profile'));

        $this->get(route('contributors.show', $user->profile_slug))
            ->assertOk()
            ->assertSee('Nigeria')
            ->assertSee('Lagos Central Laboratory')
            ->assertSee('Laboratory Technician');
    }

    public function test_show_on_leaderboard_flag_is_persisted(): void
    {
        $user = $this->member();

        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'show_on_leaderboard' => '0',
        ])->assertRedirect(route('account.profile'));

        $this->assertFalse($user->fresh()->profile->show_on_leaderboard);

        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'show_on_leaderboard' => '1',
        ])->assertRedirect(route('account.profile'));

        $this->assertTrue($user->fresh()->profile->show_on_leaderboard);
    }
}
