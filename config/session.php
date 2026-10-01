<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de Sesión por Defecto
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el "driver" de sesión predeterminado para las
    | peticiones. Por defecto se usa el driver nativo liviano.
    |
    | Soportados: "file", "cookie", "database", "apc",
    |            "memcached", "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Duración de la Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puede especificar el número de minutos que la sesión puede
    | permanecer inactiva antes de expirar. También puede configurar que
    | expire al cerrar el navegador.
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Cifrado de Sesión
    |--------------------------------------------------------------------------
    |
    | Esta opción permite especificar que todos los datos de sesión deben
    | cifrarse antes de almacenarse. Laravel gestiona el cifrado automáticamente.
    |
    */

    'encrypt' => false,

    /*
    |--------------------------------------------------------------------------
    | Ubicación de Archivos de Sesión
    |--------------------------------------------------------------------------
    |
    | Al usar el driver de sesión nativo, se necesita una ubicación para
    | almacenar los archivos de sesión. Solo aplica para sesiones de archivo.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexión de Base de Datos de Sesión
    |--------------------------------------------------------------------------
    |
    | Al usar los drivers "database" o "redis", puede especificar la conexión
    | que debe usarse para gestionar las sesiones.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de Base de Datos de Sesión
    |--------------------------------------------------------------------------
    |
    | Al usar el driver "database", puede especificar la tabla para gestionar
    | las sesiones. Se proporciona un valor predeterminado razonable.
    | */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Almacén de Caché de Sesión
    |--------------------------------------------------------------------------
    |
    | Al usar backends de sesión basados en caché, puede indicar el almacén
    | de caché a usar. Debe coincidir con uno de los "stores" configurados.
    |
    | Afecta: "apc", "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Lotería de Limpieza de Sesión
    |--------------------------------------------------------------------------
    |
    | Algunos drivers de sesión deben limpiar manualmente las sesiones antiguas.
    | Aquí se configuran las probabilidades de que ocurra en cada petición.
    | Por defecto, la probabilidad es 2 de cada 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puede cambiar el nombre de la cookie usada para identificar
    | la sesión. Este nombre se usará en cada nueva cookie de sesión.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | La ruta de la cookie de sesión determina para qué ruta estará disponible.
    | Normalmente será la ruta raíz de la aplicación.
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Dominio de Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puede cambiar el dominio de la cookie de sesión, determinando
    | en qué dominios estará disponible dentro de la aplicación.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies Solo HTTPS
    |--------------------------------------------------------------------------
    |
    | Al establecer esta opción en true, las cookies de sesión solo se
    | enviarán al servidor si el navegador tiene una conexión HTTPS.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Solo Acceso HTTP
    |--------------------------------------------------------------------------
    |
    | Establecer este valor en true impedirá que JavaScript acceda al valor
    | de la cookie; solo será accesible a través del protocolo HTTP.
    |
    */

    'http_only' => true,

    /*
    |--------------------------------------------------------------------------
    | Cookies del Mismo Sitio
    |--------------------------------------------------------------------------
    |
    | Esta opción determina cómo se comportan las cookies en peticiones
    | entre sitios, y puede usarse para mitigar ataques CSRF.
    |
    | Soportados: "strict", "lax", "none", null
    |
    */

    'same_site' => 'lax',

];