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
     * Fetch latest notifications for the dropdown (both read and unread).
     */
    public function index()
    {
        $notifiable = $this->getNotifiable();
        if (!$notifiable) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notifications = $notifiable->notifications()->latest()->take(10)->get()->map(function ($notification) {
            return [
                'id'         => $notification->id,
                'title'      => $notification->data['title'] ?? 'Notification',
                'message'    => $notification->data['message'] ?? '',
                'url'        => $notification->data['url'] ?? '#',
                'type'       => $notification->data['type'] ?? 'info',
                'icon'       => $notification->data['icon'] ?? 'fa-circle-info',
                'created_at' => $notification->created_at->diffForHumans(),
                'read_at'    => $notification->read_at,
            ];
        });

        return response()->json([
            'unread_count'  => $notifiable->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * View all notifications in a paginated list/table.
     */
    public function all(Request $request)
    {
        $notifiable = $this->getNotifiable();
        if (!$notifiable) {
            abort(401, 'Unauthenticated');
        }

        $query = $notifiable->notifications();

        // Search functionality in the notification content
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                  ->orWhere('data->message', 'like', "%{$search}%");
            });
        }

        // Status filter (all, read, unread)
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($status === 'unread') {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->latest()->paginate(10)->withQueryString();

        if (Auth::guard('seller')->check()) {
            return view('marketplace::seller.notifications.index', compact('notifications'));
        }

        return view('admin.notifications.index', compact('notifications'));
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
