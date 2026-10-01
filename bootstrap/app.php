<?php

/*
|--------------------------------------------------------------------------
| Crear la Aplicación
|--------------------------------------------------------------------------
|
| Lo primero que haremos es crear una nueva instancia de la aplicación Laravel,
| que sirve como el "pegamento" para todos los componentes y actúa como el
| contenedor IoC del sistema.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Vincular Interfaces Importantes
|--------------------------------------------------------------------------
|
| A continuación, necesitamos vincular algunas interfaces importantes en el
| contenedor para poder resolverlas cuando sea necesario. Los kernels gestionan
| las solicitudes entrantes desde la web y la CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Retornar la Aplicación
|--------------------------------------------------------------------------
|
| Este script retorna la instancia de la aplicación. La instancia se entrega
| al script llamante para separar la construcción de la aplicación de su
| ejecución real y el envío de respuestas.
|
*/

return $app;


