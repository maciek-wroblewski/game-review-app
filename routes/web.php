<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameReviewController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistGameController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::get('/', function () { return view('index'); });
Route::get('/hub', function () { return view('welcome'); });

Route::get('/games', [GameController::class, 'index']);
Route::get('/search', [SearchController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::get('/games/{game}/discussions', [GameController::class, 'discussions']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/posts/{post}/replies', [PostController::class, 'getReplies']);

// Scoped User Relations (Public or Handled by Controller Guards)
Route::get('/users/{user:username}', [UserController::class, 'show']);
Route::get('/users/{user:username}/followers', [UserController::class, 'followers']);
Route::get('/users/{user:username}/following', [UserController::class, 'following']);
Route::get('/users/{user:username}/playlists', [UserController::class, 'playlists']);
Route::get('/users/{user:username}/reviews', [UserController::class, 'reviews']);
Route::get('/users/{user:username}/posts', [UserController::class, 'posts']);

// Authenticated Routes Group
Route::middleware('auth')->group(function () {
    Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
    
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [PostLikeController::class, 'store']);

    // Reviews & Playlists
    Route::get('/games/{game}/review', [GameReviewController::class, 'create']);
    Route::post('/games/{game}/reviews', [GameReviewController::class, 'store']);
    Route::put('/reviews/{review}', [GameReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [GameReviewController::class, 'destroy']);
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);
    Route::post('/playlists/{playlist}/games/{game}', [PlaylistGameController::class, 'store']);
    Route::delete('/playlists/{playlist}/games/{game}', [PlaylistGameController::class, 'destroy']);

    // Profiles & Socials
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/privacy', [ProfileController::class, 'updatePrivacy']);
    Route::patch('/profile/media', [ProfileController::class, 'updateMedia'])->name('profile.media.update');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle']);

    // Notifications
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

require __DIR__ . '/auth.php';