<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $settings = Cache::remember('settings.all', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        if (empty($settings)) {
            return;
        }

        // Wire AI settings
        $this->overrideConfig($settings, 'ai.timeout_seconds', 'labgoto.ai.timeout_seconds', 'intval');
        $this->overrideConfig($settings, 'ai.max_tokens', 'labgoto.ai.max_tokens', 'intval');
        $this->overrideConfig($settings, 'ai.temperature', 'labgoto.ai.temperature', 'floatval');

        // Wire Security settings
        $this->overrideConfig($settings, 'security.session_lifetime_minutes', 'session.lifetime', 'intval');
        $this->overrideConfig($settings, 'security.max_login_attempts', 'labgoto.security.max_login_attempts', 'intval');
        $this->overrideConfig($settings, 'security.password_min_length', 'labgoto.security.password_min_length', 'intval');

        // Wire Feature flags
        $featureMap = [
            'features.contributions' => 'labgoto.features.contributions',
            'features.public_registration' => 'labgoto.features.public_registration',
            'features.search_analytics' => 'labgoto.features.search_analytics',
            'features.contact_form' => 'labgoto.features.contact_form',
        ];

        foreach ($featureMap as $dbKey => $configKey) {
            if (isset($settings[$dbKey])) {
                config([$configKey => filter_var($settings[$dbKey], FILTER_VALIDATE_BOOLEAN)]);
            }
        }

        // Wire Content settings
        $this->overrideConfig($settings, 'content.items_per_page', 'labgoto.pagination.per_page', 'intval');

        // Wire SEO: site name as fallback
        if (isset($settings['general.site_name']) && $settings['general.site_name'] !== '') {
            config(['app.name' => $settings['general.site_name']]);
        }
    }

    protected function overrideConfig(array $settings, string $dbKey, string $configKey, string $caster): void
    {
        if (isset($settings[$dbKey]) && $settings[$dbKey] !== '') {
            config([$configKey => $caster($settings[$dbKey])]);
        }
    }
}
