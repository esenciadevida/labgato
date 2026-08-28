<?php

namespace App\Support;

use RuntimeException;

final class EnvironmentGuard
{
    public static function assertEnv(string $environment, bool $debug, string $appKey): void
    {
        if (! in_array($environment, ['production', 'staging'], true)) {
            return;
        }

        if ($debug) {
            throw new RuntimeException('APP_DEBUG must be false in production.');
        }

        if ($appKey === '' || ! str_starts_with($appKey, 'base64:')) {
            throw new RuntimeException('APP_KEY must be generated with "php artisan key:generate".');
        }
    }
}