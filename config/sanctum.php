<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Dominios con Estado
    |--------------------------------------------------------------------------
    |
    | Las solicitudes desde los siguientes dominios/hosts recibirán cookies de
    | autenticación de API con estado. Normalmente incluyen los dominios locales
    | y de producción que acceden a la API desde una SPA frontend.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    /*
    |--------------------------------------------------------------------------
    | Guardias de Sanctum
    |--------------------------------------------------------------------------
    |
    | Este array contiene los guards de autenticación que se verificarán cuando
    | Sanctum intente autenticar una solicitud. Si ninguno puede autenticarla,
    | Sanctum usará el token bearer presente en la solicitud entrante.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Minutos de Expiración
    |--------------------------------------------------------------------------
    |
    | Este valor controla el número de minutos hasta que un token emitido se
    | considere expirado. Si es null, los tokens de acceso personal no expiran.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Prefijo de Token
    |--------------------------------------------------------------------------
    |
    | Sanctum puede agregar un prefijo a los nuevos tokens para aprovechar
    | iniciativas de escaneo de seguridad de plataformas de código abierto
    | que notifican a los desarrolladores si comprometen tokens en repositorios.
    |
    | Ver: https://docs.github.com/en/github/administering-a-repository/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware de Sanctum
    |--------------------------------------------------------------------------
    |
    | Al autenticar su SPA de primera parte con Sanctum, puede necesitar
    | personalizar el middleware que Sanctum usa al procesar la solicitud.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];


