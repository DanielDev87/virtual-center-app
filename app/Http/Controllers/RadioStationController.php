<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RadioStationController extends Controller
{
    /**
     * Mostrar página de la emisora
     */
    public function index()
    {
        return view('radio-station.index');
    }
}


