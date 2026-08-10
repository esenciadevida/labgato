<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Feature Flag
    |--------------------------------------------------------------------------
    */

    'ai_enabled' => (bool) env('AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_size_kb' => (int) env('UPLOAD_MAX_SIZE_KB', 5120),
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'security' => [
        'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration_minutes' => (int) env('LOCKOUT_DURATION_MINUTES', 15),
        'password_min_length' => (int) env('PASSWORD_MIN_LENGTH', 8),
        'session_regeneration_interval' => (int) env('SESSION_REGENERATION_INTERVAL', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'search' => (bool) env('FEATURE_SEARCH', true),
        'calculators' => (bool) env('FEATURE_CALCULATORS', false),
        'reference_ranges' => (bool) env('FEATURE_REFERENCE_RANGES', false),
        'sops' => (bool) env('FEATURE_SOPS', false),
        'interpretation' => (bool) env('FEATURE_INTERPRETATION', false),
        'knowledge' => (bool) env('FEATURE_KNOWLEDGE', false),
        'audit_log' => (bool) env('FEATURE_AUDIT_LOG', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Seed Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the admin seeder behavior. Only runs in local environment.
    |
    */

    'seed' => [
        'admin' => [
            'enabled' => env('LABGATO_SEED_ADMIN', false),
            'email' => env('LABGATO_ADMIN_EMAIL'),
            'password' => env('LABGATO_ADMIN_PASSWORD'),
        ],
    ],

];
