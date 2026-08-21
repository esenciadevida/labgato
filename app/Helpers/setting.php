<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Get a setting value by key, with caching.
     * Falls back to config('labgoto.{key}') if the DB key doesn't exist.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $cache = null;

        if ($cache === null) {
            $cache = Cache::remember('settings.all', 3600, function () {
                return Setting::pluck('value', 'key')->toArray();
            });
        }

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        // Fallback to config if DB doesn't have it
        $configKey = 'labgoto.' . $key;
        if (config()->has($configKey)) {
            return config($configKey);
        }

        return $default;
    }
}

if (! function_exists('setting_bool')) {
    function setting_bool(string $key, bool $default = false): bool
    {
        $value = setting($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('setting_int')) {
    function setting_int(string $key, int $default = 0): int
    {
        $value = setting($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }
}
