<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    */

    'ai' => [
        'enabled' => (bool) env('AI_ENABLED', false),
        'provider' => (string) env('AI_PROVIDER', ''),
        'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 15),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),
        'temperature' => (float) env('AI_TEMPERATURE', 0.7),
        'limits' => [
            'cooldown_seconds' => (int) env('AI_COOLDOWN_SECONDS', 5),
            'daily_user' => (int) env('AI_DAILY_USER_LIMIT', 50),
            'daily_global' => (int) env('AI_DAILY_GLOBAL_LIMIT', 1000),
        ],
        'providers' => [
            'groq' => [
                'api_key' => (string) env('GROQ_API_KEY', ''),
                'model' => (string) env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                'base_url' => (string) env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            ],
        ],
    ],

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
        'password_reset_max_per_minute' => (int) env('PASSWORD_RESET_MAX_PER_MINUTE', 3),
        'reauthorize_timeout_minutes' => (int) env('REAUTHORIZE_TIMEOUT_MINUTES', 30),
        'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration_minutes' => (int) env('LOCKOUT_DURATION_MINUTES', 15),
        'password_min_length' => (int) env('PASSWORD_MIN_LENGTH', 8),
        'session_regeneration_interval' => (int) env('SESSION_REGENERATION_INTERVAL', 120),
        'admin_inactivity_minutes' => (int) env('ADMIN_INACTIVITY_MINUTES', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Configuration
    |--------------------------------------------------------------------------
    */

    'auth' => [
        'email_verification' => (bool) env('EMAIL_VERIFICATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Configuration
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'notify_email' => env('CONTACT_NOTIFY_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'search' => (bool) env('FEATURE_SEARCH', true),
        'calculators' => (bool) env('FEATURE_CALCULATORS', false),
        'tools' => (bool) env('FEATURE_TOOLS', false),
        'reference_ranges' => (bool) env('FEATURE_REFERENCE_RANGES', false),
        'sops' => (bool) env('FEATURE_SOPS', false),
        'interpretation' => (bool) env('FEATURE_INTERPRETATION', false),
        'knowledge' => (bool) env('FEATURE_KNOWLEDGE', false),
        'ai' => (bool) env('FEATURE_AI', false),
        'audit_log' => (bool) env('FEATURE_AUDIT_LOG', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 24),
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
    | SEO Configuration
    |--------------------------------------------------------------------------
    */

    'seo' => [
        'default_og_image' => env('DEFAULT_OG_IMAGE', '/assets/LabGoTo_og_banner_1200x630.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Seed Configuration
    |--------------------------------------------------------------------------
    */

    'seed' => [
        'admin' => [
            'enabled' => env('LABGOTO_SEED_ADMIN', false),
            'name' => env('LABGOTO_ADMIN_NAME', 'Admin'),
            'email' => env('LABGOTO_ADMIN_EMAIL'),
            'password' => env('LABGOTO_ADMIN_PASSWORD'),
        ],
        'categories' => [
            'enabled' => env('LABGOTO_SEED_CATEGORIES', false),
        ],
        'calculators' => [
            'enabled' => env('LABGOTO_SEED_CALCULATORS', false),
        ],
        'tools' => [
            'enabled' => env('LABGOTO_SEED_TOOLS', false),
        ],
        'master_calculators' => [
            'enabled' => env('LABGOTO_SEED_MASTER_CALCULATORS', false),
        ],
        'master_tools' => [
            'enabled' => env('LABGOTO_SEED_MASTER_TOOLS', false),
        ],
        'reference_ranges' => [
            'enabled' => env('LABGOTO_SEED_REFERENCE_RANGES', false),
        ],
        'interpretation' => [
            'enabled' => env('LABGOTO_SEED_INTERPRETATION', false),
        ],
        'knowledge' => [
            'enabled' => env('LABGOTO_SEED_KNOWLEDGE', false),
        ],
    ],

];
