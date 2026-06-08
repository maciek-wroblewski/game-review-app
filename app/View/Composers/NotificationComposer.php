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
            // Single query: get paginated recent notifications
            $recentNotifications = $user->notifications()
                ->with('fromUser.avatar')
                ->latest()
                ->simplePaginate(10)
                ->withPath(route('notifications.index'));

            // Derive unread count from the loaded collection (first page)
            // If you need the total unread count across all pages, use the separate query approach below
            $unreadNotificationCount = $recentNotifications->count(fn($n) => !$n->read);

            $view->with([
                'unreadNotificationCount' => $unreadNotificationCount,
                'recentNotifications' => $recentNotifications,
            ]);
        }
    }
}
