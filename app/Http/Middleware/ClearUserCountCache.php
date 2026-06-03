<?php

namespace App\Http\Middleware;

use App\Support\UserCountCache;
use Closure;
use Illuminate\Http\Request;

class ClearUserCountCache
{
    /**
     * Handle the request and clear the user count cache after response is sent.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Clear cache after request completes
        app()->terminating(fn() => UserCountCache::clear());

        return $response;
    }
}
