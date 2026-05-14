<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameReviewController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlaylistGameController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\FollowController;
use Illuminate\Support\Facades\Auth;

Route::get('/users/{username}', [UserController::class, 'show']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/Hub', function () {
    return view('welcome');
});

Route::get('/games/{game}/review', [GameReviewController::class, 'create']);
Route::post('/games/{game}/reviews', [GameReviewController::class, 'store']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/playlists/{playlist}/games/{game}', [PlaylistGameController::class, 'store']);
Route::post('/posts/{post}/like', [PostLikeController::class, 'store']);
Route::get('/Games', [GameController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])
    ->middleware('auth');

Route::get('/users/{user:username}/followers', [UserController::class, 'followers']);
Route::get('/users/{user:username}/following', [UserController::class, 'following']);

Route::get('/users/{user:username}/playlists', [UserController::class, 'playlists']);

Route::get('/users/{user:username}/reviews', [UserController::class, 'reviews']);

Route::post('/notifications/{notification}/read', function (\App\Models\Notification $notification) {

    if ($notification->user_id === Auth::id()) {

        $notification->update([
            'read' => true
        ]);
    }

    return back();

})->middleware('auth');

Route::patch('/profile/privacy', [ProfileController::class, 'updatePrivacy'])
    ->middleware('auth');

require __DIR__.'/auth.php';
