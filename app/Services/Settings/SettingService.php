<?php

namespace App\Services\Settings;

use App\Models\Setting;

class SettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        Setting::setValue($key, $value);
    }

    public function getMultiple(array $keys): array
    {
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->get($key);
        }
        return $settings;
    }
}
