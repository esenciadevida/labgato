<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // GENERAL
            [
                'key' => 'general.site_name',
                'value' => 'Labgoto',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Site Name',
                'description' => 'The name displayed across the platform.',
            ],
            [
                'key' => 'general.site_tagline',
                'value' => 'Professional Laboratory Toolkit',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Site Tagline',
                'description' => null,
            ],
            [
                'key' => 'general.site_description',
                'value' => 'A comprehensive toolkit for laboratory professionals including calculators, reference ranges, SOPs, and educational resources.',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Description',
                'description' => null,
            ],
            [
                'key' => 'general.contact_email',
                'value' => 'contact@labgoto.com',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Contact Email',
                'description' => 'Public contact email displayed on the contact page.',
            ],
            [
                'key' => 'general.support_email',
                'value' => 'support@labgoto.com',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Support Email',
                'description' => null,
            ],

            // FEATURES
            [
                'key' => 'features.contributions',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Community Contributions',
                'description' => 'Allow members to submit SOPs, Knowledge, and Interpretation content.',
            ],
            [
                'key' => 'features.public_registration',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Public Registration',
                'description' => 'Allow new users to register accounts.',
            ],
            [
                'key' => 'features.search_analytics',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Search Analytics',
                'description' => null,
            ],
            [
                'key' => 'features.contact_form',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Enable Contact Form',
                'description' => null,
            ],

            // CONTENT
            [
                'key' => 'content.default_disclaimer',
                'value' => 'This resource is for educational and reference purposes only. Always verify with your institution\'s validated procedures and reference intervals.',
                'type' => 'text',
                'group' => 'content',
                'label' => 'Default Educational Disclaimer',
                'description' => null,
            ],
            [
                'key' => 'content.default_reference_source',
                'value' => 'Tietz Textbook of Clinical Chemistry and Molecular Diagnostics',
                'type' => 'string',
                'group' => 'content',
                'label' => 'Default Reference Source',
                'description' => null,
            ],
            [
                'key' => 'content.items_per_page',
                'value' => '24',
                'type' => 'number',
                'group' => 'content',
                'label' => 'Items Per Page (Listings)',
                'description' => null,
            ],

            // SEO
            [
                'key' => 'seo.default_title',
                'value' => 'LabGato — Professional Laboratory Toolkit',
                'type' => 'string',
                'group' => 'seo',
                'label' => 'Default SEO Title',
                'description' => null,
            ],
            [
                'key' => 'seo.default_description',
                'value' => 'Calculators, reference ranges, SOPs, and educational resources for laboratory professionals.',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Default SEO Description',
                'description' => null,
            ],
            [
                'key' => 'seo.google_site_verification',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'label' => 'Google Site Verification',
                'description' => 'Paste the content value of the google-site-verification meta tag from Search Console.',
            ],

            // ANALYTICS
            [
                'key' => 'analytics.ga4_measurement_id',
                'value' => '',
                'type' => 'string',
                'group' => 'analytics',
                'label' => 'GA4 Measurement ID',
                'description' => 'Paste your Google Analytics 4 Measurement ID (e.g., G-XXXXXXXXXX).',
            ],

            // SECURITY
            [
                'key' => 'security.session_lifetime_minutes',
                'value' => '120',
                'type' => 'number',
                'group' => 'security',
                'label' => 'Session Lifetime (minutes)',
                'description' => null,
            ],
            [
                'key' => 'security.max_login_attempts',
                'value' => '5',
                'type' => 'number',
                'group' => 'security',
                'label' => 'Max Login Attempts',
                'description' => null,
            ],
            [
                'key' => 'security.password_min_length',
                'value' => '8',
                'type' => 'number',
                'group' => 'security',
                'label' => 'Minimum Password Length',
                'description' => null,
            ],

            // AI (non-sensitive only)
            [
                'key' => 'ai.timeout_seconds',
                'value' => '15',
                'type' => 'number',
                'group' => 'ai',
                'label' => 'AI Timeout (seconds)',
                'description' => 'Maximum time to wait for an AI response.',
            ],
            [
                'key' => 'ai.max_tokens',
                'value' => '2048',
                'type' => 'number',
                'group' => 'ai',
                'label' => 'AI Max Tokens',
                'description' => null,
            ],
            [
                'key' => 'ai.temperature',
                'value' => '0.7',
                'type' => 'number',
                'group' => 'ai',
                'label' => 'AI Temperature',
                'description' => 'Controls AI creativity (0.0 = precise, 1.0 = creative).',
            ],
        ];

        foreach ($settings as $data) {
            $setting = Setting::firstOrCreate(['key' => $data['key']], $data);

            if (! $setting->wasRecentlyCreated) {
                $setting->forceFill(Arr::except($data, ['value']))->save();
            }
        }
    }
}
