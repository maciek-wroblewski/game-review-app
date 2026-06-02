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
use App\Http\Controllers\AdminController;

// Public Guest Routes
Route::get('/', function () { return view('index'); });
Route::get('/hub', function () { return view('welcome'); });

Route::middleware('auth')->group(function () {
    Route::get('/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
});

Route::get('/games', [GameController::class, 'index']);
Route::get('/search', [SearchController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::get('/games/{game}/discussions', [GameController::class, 'discussions']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/posts/{post}/replies', [PostController::class, 'getReplies']);

// Scoped User Relations
Route::get('/users/{user:username}', [UserController::class, 'show']);
Route::get('/users/{user:username}/followers', [UserController::class, 'followers']);
Route::get('/users/{user:username}/following', [UserController::class, 'following']);
Route::get('/users/{user:username}/playlists', [UserController::class, 'playlists']);
Route::get('/users/{user:username}/reviews', [UserController::class, 'reviews']);
Route::get('/users/{user:username}/posts', [UserController::class, 'posts']);

Route::middleware('auth')->group(function () {
    Route::get('/playlists/create', [PlaylistController::class, 'create']);
    Route::post('/playlists', [PlaylistController::class, 'store']);
});

Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);

// Locale Switcher
Route::get('/lang/{locale}', [\App\Http\Controllers\LocaleController::class, 'setLocale'])->name('lang.switch');

// Authenticated Routes Group
Route::middleware('auth')->group(function () {
    Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
    
    // Edit Game
    Route::get('/games/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
    Route::patch('/games/{game}', [GameController::class, 'update'])->name('games.update');
        
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

    Route::get('/playlists/{playlist}/edit', [PlaylistController::class, 'edit']);
    Route::put('/playlists/{playlist}', [PlaylistController::class, 'update']);
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy']);
    
    Route::post('/playlists/{playlist}/games/{game}', [PlaylistGameController::class, 'store']);
    Route::delete('/playlists/{playlist}/games/{game}', [PlaylistGameController::class, 'destroy']);

    // Profiles & Socials
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/privacy', [ProfileController::class, 'updatePrivacy']);
    Route::patch('/profile/media', [ProfileController::class, 'updateMedia'])->name('profile.media.update');
    Route::post('/users/{user:username}/follow', [FollowController::class, 'toggle']);

    // Component User Search API
    Route::get('/api/users/search', [UserController::class, 'searchApi']);

    // Admin Routes
    Route::get('/admin', [AdminController::class, 'index']);
    Route::post('/admin/users/{user}/verify', [AdminController::class, 'verifyUser']);
    Route::post('/admin/users/{user}/admin', [AdminController::class, 'toggleAdmin']);
    Route::post('/admin/users/{user}/suspend', [AdminController::class, 'toggleSuspend']);
    Route::post('/admin/posts/{post}/pin', [AdminController::class, 'togglePinned']);
    Route::post('/admin/posts/{post}/lock', [AdminController::class, 'toggleLock']);
    
    // Notifications
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// public API
Route::prefix('api')->group(function () {
    Route::get('/games/top', [GameController::class, 'apiIndex']);
});

require __DIR__ . '/auth.php';