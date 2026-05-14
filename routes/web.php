<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameReviewController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlaylistGameController;
use App\Http\Controllers\PostLikeController;

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

Route::get('/games/{game}/review', [GameReviewController::class, 'create']);
Route::post('/games/{game}/reviews', [GameReviewController::class, 'store']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/playlists/{playlist}/games/{game}', [PlaylistGameController::class, 'store']);
Route::post('/posts/{post}/like', [PostLikeController::class, 'store']);
