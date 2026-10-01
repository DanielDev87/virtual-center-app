<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicios de Terceros
    |--------------------------------------------------------------------------
    |
    | Este archivo almacena las credenciales para servicios de terceros como
    | Mailgun, Postmark, AWS y otros. Es la ubicación estándar para este tipo
    | de información, permitiendo que los paquetes la encuentren fácilmente.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google_drive' => [
        'enabled' => env('GOOGLE_DRIVE_ENABLED', false),
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'share_publicly' => env('GOOGLE_DRIVE_SHARE_PUBLICLY', true),
        'verify_ssl' => env('GOOGLE_DRIVE_VERIFY_SSL', true),
    ],

];


