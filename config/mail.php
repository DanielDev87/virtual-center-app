<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailer por Defecto
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el mailer predeterminado para enviar mensajes de
    | correo electrónico. Se pueden configurar mailers alternativos según sea
    | necesario; sin embargo, este mailer se usará por defecto.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Mailer
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar todos los mailers de la aplicación y sus ajustes.
    | Laravel soporta una variedad de drivers de transporte de correo.
    |
    | Soportados: "smtp", "sendmail", "mailgun", "ses",
    |            "postmark", "log", "array", "failover"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dirección "De" Global
    |--------------------------------------------------------------------------
    |
    | Puede especificar una dirección y nombre que se usarán de forma global
    | en todos los correos electrónicos enviados por la aplicación.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Correo Markdown
    |--------------------------------------------------------------------------
    |
    | Si usa renderizado de correo basado en Markdown, puede configurar el
    | tema y las rutas de componentes aquí para personalizar el diseño.
    |
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];


