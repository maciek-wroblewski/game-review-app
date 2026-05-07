<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuthController;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/hub', [HubController::class, 'index'])->name('hub.index');
Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('game.show');
Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/messages/conversations', [\App\Http\Controllers\MessageController::class, 'fetchConversations']);
    Route::post('/messages/send', [\App\Http\Controllers\MessageController::class, 'sendMessage']);
    
    Route::post('/posts/{post}/upvote', [\App\Http\Controllers\HubController::class, 'upvote'])->name('post.upvote');
    Route::post('/posts/{post}/comment', [\App\Http\Controllers\HubController::class, 'comment'])->name('post.comment');
});

