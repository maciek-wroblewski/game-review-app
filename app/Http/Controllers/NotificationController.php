<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($notificationId);

        $notification->update([
            'read' => true,
        ]);

        return back();
    }

    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->notifications()
            ->where('read', false)
            ->update([
                'read' => true
            ]);

        return back();
    }
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->with('fromUser.avatar')
            ->latest()
            ->simplePaginate(10);

        if ($request->ajax()) {
            // Return JSON payload so JS knows exactly when to hide the button
            return response()->json([
                'html' => view('components.notification-items', compact('notifications'))->render(),
                'next_page_url' => $notifications->nextPageUrl()
            ]);
        }

        return back();
    }
}