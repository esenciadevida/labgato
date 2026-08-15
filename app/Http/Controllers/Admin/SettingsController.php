<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    private const CATEGORY_ORDER = [
        'general' => 'General',
        'features' => 'Features',
        'content' => 'Content',
        'seo' => 'SEO',
        'security' => 'Security',
        'ai' => 'AI',
    ];

    private const SENSITIVE_KEYS = [
        'groq_api_key',
        'openai_api_key',
        'anthropic_api_key',
        'database_password',
        'redis_password',
        'mail_password',
        'aws_secret_access_key',
        'aws_access_key_id',
    ];

    private function isSensitiveKey(string $key): bool
    {
        $baseKey = Str::afterLast($key, '.');

        return in_array($baseKey, self::SENSITIVE_KEYS, true);
    }

    public function index()
    {
        $settings = Setting::all()
            ->reject(fn (Setting $setting) => $this->isSensitiveKey($setting->key))
            ->groupBy('group');

        $grouped = collect(self::CATEGORY_ORDER)
            ->mapWithKeys(function (string $label, string $group) use ($settings) {
                return $settings->has($group)
                    ? [$group => ['label' => $label, 'settings' => $settings[$group]->values()]]
                    : [];
            });

        return view('admin.settings.index', compact('grouped'));
    }

    public function update(Request $request)
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdministrator()) {
            abort(403, 'Only Super Administrators can modify settings.');
        }

        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string'],
        ]);

        $auditChanges = [];
        $settings = Setting::all()->keyBy('key');

        foreach ($data['settings'] as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                continue;
            }

            $oldValue = $settings[$key]->value ?? null;
            $newValue = $value !== null ? (string) $value : null;

            if ($oldValue !== $newValue) {
                Setting::updateOrCreate(['key' => $key], ['value' => $newValue]);
                $auditChanges[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        if (! empty($auditChanges)) {
            app(AuditLogService::class)->log('settings.updated', 'settings', 1, $auditChanges, auth()->id());
        }

        Cache::forget('settings.all');

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
}
