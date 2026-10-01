<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * Lista de entradas que nunca se envían a la sesión en excepciones de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registrar los callbacks de manejo de excepciones de la aplicación.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            // Manejar subidas demasiado grandes (413) de forma amigable
            if ($e instanceof PostTooLargeException || ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 413)) {
                // Obtener un valor legible del límite configurado en php.ini
                $maxSize = ini_get('upload_max_filesize') ?: 'desconocido';

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'file_too_large',
                        'message' => "El archivo es demasiado grande. Tamaño máximo permitido: {$maxSize}."
                    ], 413);
                }

                return redirect()->back()->withInput()->with('error', "El archivo es demasiado grande. Tamaño máximo permitido: {$maxSize}.");
            }

        });
    }
}


