<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de la aplicación. Se utiliza cuando el framework
    | necesita mostrar el nombre de la aplicación en una notificación u otro
    | lugar según lo requiera la aplicación o sus paquetes.
    |
    */

    'name' => env('APP_NAME', 'A-DDIE'),

    /*
    |--------------------------------------------------------------------------
    | Entorno de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor determina el "entorno" en el que se ejecuta actualmente la
    | aplicación. Puede influir en cómo se configuran los servicios. Defínalo
    | en su archivo ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo de Depuración de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Cuando la aplicación está en modo de depuración, se mostrarán mensajes
    | de error detallados con trazas de pila en cada error. Si está desactivado,
    | se muestra una página de error genérica.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Esta URL es utilizada por la consola para generar correctamente las URLs
    | al usar la herramienta de línea de comandos Artisan. Debe apuntar a la
    | raíz de su aplicación para que se use al ejecutar tareas Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Zona Horaria de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Aquí puede especificar la zona horaria predeterminada de la aplicación,
    | que será utilizada por las funciones de fecha y hora de PHP.
    |
    */

    'timezone' => 'America/Bogota',

    /*
    |--------------------------------------------------------------------------
    | Configuración de Localización de la Aplicación
    |--------------------------------------------------------------------------
    |
    | El locale de la aplicación determina el idioma predeterminado que usará
    | el proveedor de traducciones. Puede establecer este valor a cualquiera de
    | los locales compatibles con la aplicación.
    |
    */

    'locale' => 'es',

    /*
    |--------------------------------------------------------------------------
    | Localización de Respaldo de la Aplicación
    |--------------------------------------------------------------------------
    |
    | El locale de respaldo se usa cuando el locale actual no está disponible.
    | Puede cambiarlo para que corresponda a alguna de las carpetas de idioma
    | disponibles en la aplicación.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Localización de Faker
    |--------------------------------------------------------------------------
    |
    | Este locale será usado por la librería Faker PHP al generar datos falsos
    | para los seeders de la base de datos, como números de teléfono localizados
    | o información de dirección.
    |
    */

    'faker_locale' => 'es_CO',

    /*
    |--------------------------------------------------------------------------
    | Clave de Cifrado
    |--------------------------------------------------------------------------
    |
    | Esta clave es usada por el servicio de cifrado de Illuminate y debe ser
    | una cadena aleatoria de 32 caracteres. De lo contrario, los valores
    | cifrados no serán seguros. ¡Configure esto antes de desplegar la app!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Driver del Modo de Mantenimiento
    |--------------------------------------------------------------------------
    |
    | Estas opciones determinan el driver para gestionar el modo de mantenimiento
    | de Laravel. El driver "cache" permite controlarlo en múltiples servidores.
    |
    | Drivers soportados: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de Servicios Autocargados
    |--------------------------------------------------------------------------
    |
    | Los proveedores listados aquí serán cargados automáticamente en cada
    | petición a la aplicación. Puede agregar sus propios servicios para
    | expandir la funcionalidad de la aplicación.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Proveedores de Paquetes...
         */

        /*
         * Proveedores de la Aplicación...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Alias de Clases
    |--------------------------------------------------------------------------
    |
    | Este array de alias de clases será registrado al iniciar la aplicación.
    | Los alias se cargan de forma "lazy" por lo que no afectan el rendimiento.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];


