<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
Use App\Http\Controllers\GameController;

Route::get('/users/{id}', [UserController::class, 'show']);

Route::get('/', function () {
<<<<<<< HEAD
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
=======
    return view('index');
});

Route::get('/Hub', function () {
    return view('welcome');
});

Route::get('/Games', [GameController::class, 'index']);
>>>>>>> main
