<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RememberTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_logging_in_with_remember_stores_a_hashed_token(): void
    {
        $user = User::factory()->create(['is_active' => true, 'remember_token' => null]);

        Auth::login($user, true);

        $stored = $user->fresh()->remember_token;
        $this->assertTrue(Hash::isHashed($stored));
        $this->assertNotSame($user->getRememberToken(), $stored);
    }

    public function test_hashed_remember_token_round_trips_through_the_provider(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $provider = $this->app['auth']->createUserProvider('users');

        $raw = Str::random(60);
        $provider->updateRememberToken($user, $raw);

        $stored = $user->fresh()->remember_token;
        $this->assertNotSame($raw, $stored);
        $this->assertTrue(Hash::isHashed($stored));

        $this->assertSame($user->id, $provider->retrieveByToken($user->id, $raw)?->id);
        $this->assertNull($provider->retrieveByToken($user->id, Str::random(60)));
    }

    public function test_legacy_plaintext_remember_tokens_still_validate(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $plain = Str::random(60);
        $user->forceFill(['remember_token' => $plain])->save();

        $provider = $this->app['auth']->createUserProvider('users');

        $this->assertSame($user->id, $provider->retrieveByToken($user->id, $plain)?->id);
        $this->assertNull($provider->retrieveByToken($user->id, Str::random(60)));
    }
}