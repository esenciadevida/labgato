<?php

namespace Tests\Unit\Support;

use App\Support\EnvironmentGuard;
use PHPUnit\Framework\TestCase;

class EnvironmentGuardTest extends TestCase
{
    public function test_allows_safe_production_config(): void
    {
        EnvironmentGuard::assertEnv('production', false, 'base64:'.str_repeat('a', 44));

        $this->assertTrue(true);
    }

    public function test_rejects_debug_in_production(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_DEBUG must be false in production.');

        EnvironmentGuard::assertEnv('production', true, 'base64:abc');
    }

    public function test_rejects_empty_app_key_in_production(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_KEY must be generated');

        EnvironmentGuard::assertEnv('production', false, '');
    }

    public function test_rejects_non_base64_app_key_in_production(): void
    {
        $this->expectException(\RuntimeException::class);

        EnvironmentGuard::assertEnv('production', false, 'some-raw-secret');
    }

    public function test_non_production_environments_are_never_guarded(): void
    {
        EnvironmentGuard::assertEnv('local', true, '');

        $this->assertTrue(true);
    }
}