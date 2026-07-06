<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get the authenticated user (admin or seller).
     */
    private function getNotifiable()
    {
        if (Auth::guard('seller')->check()) {
            return Auth::guard('seller')->user();
        }
        return Auth::guard('web')->user();
    }

    /**
     * Fetch latest notifications for the dropdown.
     */
    public function index()
    {
        $notifiable = $this->getNotifiable();
        if (!$notifiable) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notifications = $notifiable->unreadNotifications()->take(10)->get()->map(function ($notification) {
            return [
                'id'         => $notification->id,
                'title'      => $notification->data['title'] ?? 'Notification',
                'message'    => $notification->data['message'] ?? '',
                'url'        => $notification->data['url'] ?? '#',
                'type'       => $notification->data['type'] ?? 'info',
                'icon'       => $notification->data['icon'] ?? 'fa-circle-info',
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count'  => $notifiable->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead($id)
    {
        $notifiable = $this->getNotifiable();
        if (!$notifiable) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = $notifiable->unreadNotifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        $notifiable = $this->getNotifiable();
        if (!$notifiable) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notifiable->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
