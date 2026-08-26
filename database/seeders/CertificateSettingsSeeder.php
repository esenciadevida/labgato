<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CertificateSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'certificate.primary_color',
                'value' => '#1e3a8a',
                'type' => 'color',
                'group' => 'certificate',
                'label' => 'Primary Color',
                'description' => 'Used for the outer border, holder name, and headings.',
            ],
            [
                'key' => 'certificate.secondary_color',
                'value' => '#d4af37',
                'type' => 'color',
                'group' => 'certificate',
                'label' => 'Secondary Color',
                'description' => 'Used for the inner frame, gold rules, and ribbon accent.',
            ],
            [
                'key' => 'certificate.body_text',
                'value' => '',
                'type' => 'text',
                'group' => 'certificate',
                'label' => 'Body Text',
                'description' => 'The completion text below the holder name. Use {assessment} as a placeholder for the assessment title. Leave blank for the default.',
            ],
            [
                'key' => 'certificate.disclaimer_text',
                'value' => '',
                'type' => 'text',
                'group' => 'certificate',
                'label' => 'Disclaimer Text',
                'description' => 'The disclaimer shown at the bottom of the certificate. Leave blank for the default.',
            ],
            [
                'key' => 'certificate.show_logo',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'certificate',
                'label' => 'Show Logo',
                'description' => 'Display the LabGOTO logo in the certificate header.',
            ],
            [
                'key' => 'certificate.show_qr',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'certificate',
                'label' => 'Show QR Code',
                'description' => 'Display the verification QR code in the certificate footer.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
