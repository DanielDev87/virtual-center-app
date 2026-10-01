<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rutas de Almacenamiento de Vistas
    |--------------------------------------------------------------------------
    |
    | La mayoría de los sistemas de plantillas cargan templates desde disco.
    | Aquí puede especificar las rutas donde se buscarán las vistas. La ruta
    | estándar de Laravel ya está registrada por defecto.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ruta de Vistas Compiladas
    |--------------------------------------------------------------------------
    |
    | Esta opción determina dónde se almacenarán todas las plantillas Blade
    | compiladas. Normalmente se encuentra en el directorio storage.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];


