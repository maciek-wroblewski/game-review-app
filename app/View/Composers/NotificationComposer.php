<?php

namespace App\View\Composers;

use Illuminate\View\View;

class NotificationComposer
{
    /**
     * Bind data to the view.
     * Preloads notifications for the layout component.
     * Optimized: derives unread count from the paginated results to eliminate redundant query.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if ($user) {
            $data = \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_notifications_data", 3600, function () use ($user) {
                // Single query: get paginated recent notifications
                $recentNotifications = $user->notifications()
                    ->with('fromUser.avatar')
                    ->latest()
                    ->simplePaginate(10)
                    ->withPath(route('notifications.index'));

                // Derive unread count from the loaded collection (first page)
                $unreadNotificationCount = $recentNotifications->count(fn($n) => !$n->read);

                return [
                    'recentNotifications' => $recentNotifications,
                    'unreadNotificationCount' => $unreadNotificationCount,
                ];
            });

            $view->with([
                'unreadNotificationCount' => $data['unreadNotificationCount'],
                'recentNotifications' => $data['recentNotifications'],
            ]);
        }
    }
}
