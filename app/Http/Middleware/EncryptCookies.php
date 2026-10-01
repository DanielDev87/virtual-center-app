<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * Nombres de las cookies que no deben cifrarse.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}



