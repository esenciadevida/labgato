<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Feature Flag
    |--------------------------------------------------------------------------
    |
    | Master switch for AI features across the application.
    | When false, all AI-related routes, services, and UI elements are disabled.
    |
    */

    'ai_enabled' => (bool) env('AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Maximum file upload sizes and allowed MIME types.
    |
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
    |
    | Security-related settings for the application.
    |
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
    |
    | Toggle individual features on or off.
    |
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
    |
    | Default pagination settings.
    |
    */

    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 20),
    ],

];
