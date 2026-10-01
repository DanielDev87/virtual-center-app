<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí puede registrar las rutas de la API de la aplicación. Estas rutas son
| cargadas por el RouteServiceProvider y todas se asignarán al grupo de
| middleware "api".
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API de Tema (Movida a web.php para soporte de sesión)
// Route::post('/theme', ...);




