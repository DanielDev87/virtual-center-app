<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Theme API
Route::post('/theme', function (Request $request) {
    $request->validate([
        'theme' => 'required|in:light,dark'
    ]);
    
    session(['theme' => $request->theme]);
    
    return response()->json(['success' => true]);
});



