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
        'login_max_attempts' => (int) env('LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'login_decay_minutes' => (int) env('LOGIN_RATE_LIMIT_DECAY_MINUTES', 15),
        'password_reset_expiry_minutes' => (int) env('PASSWORD_RESET_EXPIRY_MINUTES', 60),
        'reauthorize_timeout_minutes' => (int) env('REAUTHORIZE_TIMEOUT_MINUTES', 30),
        'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration_minutes' => (int) env('LOCKOUT_DURATION_MINUTES', 15),
        'password_min_length' => (int) env('PASSWORD_MIN_LENGTH', 12),
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
    | Search Configuration
    |--------------------------------------------------------------------------
    */

    'search' => [
        'per_page' => (int) env('SEARCH_PER_PAGE', 10),
        'rate_limit_per_minute' => (int) env('SEARCH_RATE_LIMIT_PER_MINUTE', 30),
        'log_queries' => env('SEARCH_QUERY_LOGGING', true),
        'log_ip' => env('SEARCH_QUERY_LOG_IP', false),
        'max_query_length' => env('SEARCH_MAX_QUERY_LENGTH', 255),
    ],

    /*
    |--------------------------------------------------------------------------
    | Seed Configuration
    |--------------------------------------------------------------------------
    */

    'seed' => [
        'admin' => [
            'enabled' => env('LABGATO_SEED_ADMIN', false),
            'email' => env('LABGATO_ADMIN_EMAIL'),
            'password' => env('LABGATO_ADMIN_PASSWORD'),
        ],
        'categories' => [
            'enabled' => env('LABGATO_SEED_CATEGORIES', false),
        ],
    ],

];
