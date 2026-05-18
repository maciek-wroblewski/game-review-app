<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\Playlist;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Relation::morphMap([
            'game' => Game::class,
            'playlist' => Playlist::class,
            'user' => User::class,
        ]);
        Route::bind('user', function (string $value) {
            return User::where('id', $value)
                ->orWhere('username', $value)
                ->firstOrFail();
        });
    }
}
