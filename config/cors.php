<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de Intercambio de Recursos de Origen Cruzado (CORS)
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar los ajustes para el intercambio de recursos de
    | origen cruzado (CORS). Esto determina qué operaciones entre orígenes
    | pueden ejecutarse en los navegadores web.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => explode(',', env('CORS_ALLOWED_METHODS', '*')),

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => explode(',', env('CORS_ALLOWED_HEADERS', '*')),

    'exposed_headers' => explode(',', env('CORS_EXPOSED_HEADERS', '')),

    'max_age' => env('CORS_MAX_AGE', 0),

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];


