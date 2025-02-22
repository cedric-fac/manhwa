<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications.
     */
    public function unread()
    {
        $notifications = Auth::user()
            ->unreadNotifications()
            ->where('type', 'App\Notifications\OcrReviewNeededNotification')
            ->get();

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()
            ->unreadNotifications
            ->where('type', 'App\Notifications\OcrReviewNeededNotification')
            ->each(function ($notification) {
                $notification->markAsRead();
            });

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}