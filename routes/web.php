<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index', [
        'headtitle' => 'Home'
    ]);
});

Route::get('/Hub', function () {
    return view('index', [
        'headtitle' => 'Hub'
    ]);
});
