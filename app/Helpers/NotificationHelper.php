<?php

namespace App\Helpers;

use App\Models\User;
use SGCart\Marketplace\Models\Seller;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Notification;

class NotificationHelper
{
    /**
     * Dispatch notification to all platform administrators.
     */
    public static function sendToAdmin(
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'info',
        string $icon = 'fa-circle-info'
    ): void {
        $admins = User::all();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new GenericNotification($title, $message, $url, $type, $icon));
        }
    }

    /**
     * Dispatch notification to a specific Seller.
     */
    public static function sendToSeller(
        $sellerOrId,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'info',
        string $icon = 'fa-circle-info'
    ): void {
        $seller = $sellerOrId instanceof Seller ? $sellerOrId : Seller::find($sellerOrId);
        if ($seller) {
            $seller->notify(new GenericNotification($title, $message, $url, $type, $icon));
        }
    }

    /**
     * Dispatch notification to a specific Admin/User.
     */
    public static function sendToUser(
        $userOrId,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'info',
        string $icon = 'fa-circle-info'
    ): void {
        $user = $userOrId instanceof User ? $userOrId : User::find($userOrId);
        if ($user) {
            $user->notify(new GenericNotification($title, $message, $url, $type, $icon));
        }
    }

    /**
     * Dispatch notification to a specific Customer.
     */
    public static function sendToCustomer(
        $customerOrId,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'info',
        string $icon = 'fa-circle-info'
    ): void {
        $customer = $customerOrId instanceof \App\Models\Customer ? $customerOrId : \App\Models\Customer::find($customerOrId);
        if ($customer) {
            $customer->notify(new GenericNotification($title, $message, $url, $type, $icon));
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public static function markAsRead(string $notificationId): bool
    {
        $db = \DB::table('notifications')->where('id', $notificationId);
        if ($db->exists()) {
            return $db->update(['read_at' => now()]);
        }
        return false;
    }

    /**
     * Mark all notifications for the given user/seller as read.
     */
    public static function markAllAsRead($notifiable): void
    {
        if ($notifiable && method_exists($notifiable, 'unreadNotifications')) {
            $notifiable->unreadNotifications->markAsRead();
        }
    }
}
