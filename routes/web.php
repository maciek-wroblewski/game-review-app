<?php

use Illuminate\Support\Facades\Route;
Use App\Http\Controllers\GameController;

Route::get('/', function () {
    return view('index');
});

Route::get('/Hub', function () {
    return view('welcome');
});

Route::get('/Games', [GameController::class, 'index']);
