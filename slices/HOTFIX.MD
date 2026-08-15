labgato/
├── app/
│   ├── Models/
│   │   └── Setting.php
│   └── Http/
│       └── Controllers/
│           └── Admin/
│               └── SettingsController.php
├── database/
│   └── seeders/
│       ├── SettingsSeeder.php
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       └── admin/
│           └── settings/
│               └── index.blade.php
└── tests/
    └── Feature/
        └── Admin/
            └── SettingsPageTest.php

IMPLEMENTATION PROMPT ID: LABGATO-HOTFIX-SETTINGS

CONTEXT:
The admin Settings page (/admin/settings) is empty because the settings table has no records. This hotfix seeds comprehensive default settings grouped by category so the page displays meaningful, editable configuration.

OBJECTIVE:
Populate the settings table with default values, ensure the SettingsController groups and displays them correctly, and verify the page is no longer empty.

EXISTING ARCHITECTURE:
- settings table exists (Build 2).
- SettingsController exists (Build 24) with index and update methods.
- Settings are grouped by category.
- Sensitive values (API keys, DB credentials) must NOT be in the settings table.

FILES TO INSPECT:
- app/Models/Setting.php
- app/Http/Controllers/Admin/SettingsController.php
- resources/views/admin/settings/index.blade.php
- database/migrations/ (settings table structure)
- database/seeders/DatabaseSeeder.php

FILES TO CREATE:
- database/seeders/SettingsSeeder.php
- tests/Feature/Admin/SettingsPageTest.php

FILES TO MODIFY:
- database/seeders/DatabaseSeeder.php
- app/Http/Controllers/Admin/SettingsController.php (if grouping needs adjustment)
- resources/views/admin/settings/index.blade.php (if display needs adjustment)

TASK 1: VERIFY SETTING MODEL

Inspect app/Models/Setting.php.

Ensure it has these fields in $fillable:
- key (unique identifier, e.g., "site_name")
- value (the setting value)
- type (string, boolean, number, text, select)
- group (General, Features, Content, SEO, Security, AI)
- label (human-readable label)
- description (helper text)
- options (nullable, for select types — JSON array of choices)

If the table/model is missing any of these columns, create a migration to add them.

TASK 2: CREATE SETTINGS SEEDER

Create database/seeders/SettingsSeeder.php.

Use firstOrCreate by key to make it idempotent. Populate the following settings:

=== GENERAL GROUP ===

site_name
- label: "Site Name"
- value: "LabGato"
- type: string
- description: "The name displayed across the platform."

site_tagline
- label: "Site Tagline"
- value: "Professional Laboratory Toolkit"
- type: string

site_description
- label: "Site Description"
- value: "A comprehensive toolkit for laboratory professionals including calculators, reference ranges, SOPs, and educational resources."
- type: text

contact_email
- label: "Contact Email"
- value: "contact@labgato.local"
- type: string
- description: "Public contact email displayed on the contact page."

support_email
- label: "Support Email"
- value: "support@labgato.local"
- type: string

=== FEATURES GROUP ===

features.contributions
- label: "Enable Community Contributions"
- value: "true"
- type: boolean
- description: "Allow members to submit SOPs, Knowledge, and Interpretation content."

features.public_registration
- label: "Enable Public Registration"
- value: "true"
- type: boolean
- description: "Allow new users to register accounts."

features.search_analytics
- label: "Enable Search Analytics"
- value: "true"
- type: boolean

features.contact_form
- label: "Enable Contact Form"
- value: "true"
- type: boolean

=== CONTENT GROUP ===

content.default_disclaimer
- label: "Default Educational Disclaimer"
- value: "This resource is for educational and reference purposes only. Always verify with your institution's validated procedures and reference intervals."
- type: text

content.default_reference_source
- label: "Default Reference Source"
- value: "Tietz Textbook of Clinical Chemistry and Molecular Diagnostics"
- type: string

content.items_per_page
- label: "Items Per Page (Listings)"
- value: "24"
- type: number

=== SEO GROUP ===

seo.default_title
- label: "Default SEO Title"
- value: "LabGato — Professional Laboratory Toolkit"
- type: string

seo.default_description
- label: "Default SEO Description"
- value: "Calculators, reference ranges, SOPs, and educational resources for laboratory professionals."
- type: text

=== SECURITY GROUP ===

security.session_lifetime_minutes
- label: "Session Lifetime (minutes)"
- value: "120"
- type: number

security.max_login_attempts
- label: "Max Login Attempts"
- value: "5"
- type: number

security.password_min_length
- label: "Minimum Password Length"
- value: "8"
- type: number

=== AI GROUP (non-sensitive only) ===

ai.timeout_seconds
- label: "AI Timeout (seconds)"
- value: "15"
- type: number
- description: "Maximum time to wait for an AI response."

ai.max_tokens
- label: "AI Max Tokens"
- value: "2048"
- type: number

ai.temperature
- label: "AI Temperature"
- value: "0.7"
- type: number
- description: "Controls AI creativity (0.0 = precise, 1.0 = creative)."

IMPORTANT: Do NOT add GROQ_API_KEY or any secrets to the settings table. Those stay in .env only.

TASK 3: REGISTER IN DATABASE SEEDER

Modify database/seeders/DatabaseSeeder.php:
- Call SettingsSeeder after roles/permissions are seeded.

TASK 4: VERIFY SETTINGSCONTROLLER DISPLAY

Inspect app/Http/Controllers/Admin/SettingsController.php.

Ensure the index method:
- Fetches all settings.
- Groups them by the group field.
- Passes grouped settings to the view.

Example:
$settings = Setting::all()->groupBy('group');
return view('admin.settings.index', compact('settings'));

TASK 5: VERIFY SETTINGS VIEW

Inspect resources/views/admin/settings/index.blade.php.

Ensure it:
- Iterates over each group.
- Displays a section/card per group with the group name as heading.
- Renders each setting with the appropriate input type:
  - string → text input
  - text → textarea
  - boolean → toggle/checkbox
  - number → number input
  - select → dropdown (using options field)
- Shows the label and description for each setting.
- Has a save button per group or a global save.

If the view is incomplete or empty, rebuild it to properly render grouped settings.

TASK 6: FEATURE TEST

Create tests/Feature/Admin/SettingsPageTest.php:
- Test the settings page is accessible to super admin.
- Test the settings page displays grouped settings.
- Test the General group shows site_name.
- Test the Features group shows feature toggles.
- Test sensitive values (API keys) are NOT displayed.
- Test updating a setting persists the change.
- Test non-super-admin cannot access settings.

TASK 7: RUN SEEDER AND VERIFY

Run:
php artisan db:seed --class=SettingsSeeder

Then:
- Navigate to /admin/settings.
- Confirm all groups display with their settings.
- Confirm inputs are editable.
- Confirm saving a setting persists it.

DEFINITION OF DONE:
- settings table is populated with comprehensive default settings.
- Settings are grouped by category (General, Features, Content, SEO, Security, AI).
- The admin settings page displays all settings with appropriate input types.
- Settings can be edited and saved.
- Sensitive values (API keys) are NOT in the settings table.
- Seeder is idempotent.
- Feature tests pass.