<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disco de Sistema de Archivos por Defecto
    |--------------------------------------------------------------------------
    |
    | Aquí puede especificar el disco de sistema de archivos predeterminado
    | que usará el framework. El disco "local" y varios discos en la nube
    | están disponibles para la aplicación.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Discos del Sistema de Archivos
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar tantos "discos" de sistema de archivos como sea
    | necesario. Se han establecido valores predeterminados para cada driver.
    |
    | Drivers soportados: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enlaces Simbólicos
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar los enlaces simbólicos que se crearán al ejecutar
    | el comando Artisan `storage:link`. Las claves son las ubicaciones de los
    | enlaces y los valores son sus destinos.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];


