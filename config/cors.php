<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The app is server-rendered, so CORS is locked down to a minimum.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET'],

    'allowed_origins' => [env('APP_URL', 'http://localhost:8000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
