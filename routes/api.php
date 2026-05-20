<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/games', [GameController::class, 'apiIndex']);
    Route::get('/games/{game}', [GameController::class, 'apiShow']);
    Route::get('/games/{game}/reviews', [GameController::class, 'apiReviews']);
});
