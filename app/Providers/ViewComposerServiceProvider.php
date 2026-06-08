<?php

namespace App\Providers;

use App\View\Composers\NotificationComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewComposerServiceProvider extends ServiceProvider
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
        // Compose notifications component with preloaded data
        View::composer(
            'components.notifications',
            NotificationComposer::class
        );
    }
}
