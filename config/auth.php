<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Valores por Defecto de Autenticación
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el guard de autenticación por defecto y las opciones
    | de restablecimiento de contraseña. Puede cambiar estos valores según sea
    | necesario; son un buen punto de partida para la mayoría de aplicaciones.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guardias de Autenticación
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir cada guard de autenticación de la aplicación.
    | Se ha definido una configuración predeterminada que utiliza almacenamiento
    | de sesión y el proveedor de usuarios Eloquent.
    |
    | Todos los drivers de autenticación tienen un proveedor de usuarios.
    | Este define cómo se recuperan los usuarios de la base de datos u otro
    | mecanismo de almacenamiento de la aplicación.
    |
    | Soportados: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de Usuarios
    |--------------------------------------------------------------------------
    |
    | Todos los drivers de autenticación tienen un proveedor de usuarios.
    | Puede configurar múltiples fuentes si tiene varias tablas de usuarios.
    |
    | Soportados: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de Contraseñas
    |--------------------------------------------------------------------------
    |
    | Puede especificar múltiples configuraciones de restablecimiento si tiene
    | más de una tabla de usuarios. El tiempo de expiración es el número de
    | minutos que cada token de restablecimiento será válido. El throttle es
    | el número de segundos que un usuario debe esperar antes de generar más
    | tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de Espera de Confirmación de Contraseña
    |--------------------------------------------------------------------------
    |
    | Aquí puede definir el tiempo en segundos antes de que expire la
    | confirmación de contraseña y se le pida al usuario que la reingrese.
    | Por defecto, el tiempo de espera dura tres horas.
    |
    */

    'password_timeout' => 10800,

];



