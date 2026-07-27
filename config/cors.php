<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | The paths that should be subject to CORS handling. By default, we
    | include all API routes. You can add Sanctum's CSRF cookie path
    | if needed for SPA authentication.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed HTTP Methods
    |--------------------------------------------------------------------------
    |
    | You may use '*' to allow all methods. Be specific to restrict which
    | HTTP methods are allowed for cross-origin requests.
    |
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Use '*' to allow all origins in development. For production, specify
    | the exact origins (e.g., ['https://example.com']).
    |
    */

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'http://localhost:8000',
        'null', // Allow file:// protocol for local HTML files
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | You can use regex patterns to match allowed origins dynamically.
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Use '*' to allow all headers. Specify individual headers for tighter
    | security.
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Headers that the browser is allowed to access in the response.
    | 'Authorization' is needed so the frontend can read the JWT token.
    |
    */

    'exposed_headers' => ['Authorization'],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) the browser should cache the preflight (OPTIONS)
    | response. 0 means no caching; 86400 = 24 hours.
    |
    */

    'max_age' => 86400,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | When true, the 'Access-Control-Allow-Credentials' header is set to
    | 'true'. Required when the frontend sends cookies or auth headers.
    | Note: When true, 'allowed_origins' must NOT be '*'.
    |
    */

    'supports_credentials' => true,

];
